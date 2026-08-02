<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateBaselineMigration extends Command
{
    protected $signature = 'db:generate-baseline-migration';

    protected $description = 'อ่านโครงสร้างตารางจริงทั้งหมดจากฐานข้อมูล Postgres ที่ใช้งานอยู่ แล้วสร้างไฟล์ migration '
        . 'ที่พอเอาโปรเจกต์ไปตั้งบนเซิร์ฟเวอร์ใหม่ (ฐานข้อมูลว่างเปล่า) แล้วรัน php artisan migrate จะได้ตารางครบทุกตารางทันที '
        . '(ปลอดภัยกับฐานข้อมูลปัจจุบันด้วย เพราะเช็คก่อนว่ามีตารางอยู่แล้วหรือยัง ถ้ามีแล้วจะข้ามไม่ไปแตะของเดิม)';

    public function handle(): int
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->error('คำสั่งนี้ใช้ได้กับฐานข้อมูล PostgreSQL เท่านั้น (การเชื่อมต่อปัจจุบันไม่ใช่ pgsql — เช็คค่า DB_CONNECTION ใน .env)');
            return self::FAILURE;
        }

        $tables = collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename"))
            ->pluck('tablename')
            ->reject(fn ($t) => $t === 'migrations')
            ->values();

        if ($tables->isEmpty()) {
            $this->error('ไม่พบตารางใดๆ ในฐานข้อมูล');
            return self::FAILURE;
        }

        $this->info('พบ ' . $tables->count() . ' ตาราง กำลังอ่านโครงสร้าง...');

        $upBlocks = [];
        foreach ($tables as $table) {
            $this->line("  - {$table}");
            $upBlocks[] = $this->buildTableBlock($table);
        }

        $className = 'BaselineSchemaSnapshot' . now()->format('YmdHis');
        $fileName = now()->format('Y_m_d_His') . '_baseline_schema_snapshot.php';
        $path = database_path('migrations/' . $fileName);

        $content = $this->renderMigrationFile($upBlocks);
        file_put_contents($path, $content);

        $this->newLine();
        $this->info("สร้างไฟล์แล้ว: database/migrations/{$fileName}");
        $this->comment('แนะนำให้เปิดไฟล์ดูคร่าวๆ ก่อน แล้วลองรัน `php artisan migrate` กับฐานข้อมูลปัจจุบัน (ของเดิมมีอยู่แล้วทุกตารางจึงจะข้ามหมด ไม่กระทบข้อมูลเดิม)');
        $this->comment('จากนั้น commit + push ไฟล์นี้เข้า git — เซิร์ฟเวอร์ใหม่ที่ฐานข้อมูลว่างเปล่า แค่รัน `php artisan migrate` ก็จะได้ตารางครบทุกตารางทันที');

        return self::SUCCESS;
    }

    private function buildTableBlock(string $table): string
    {
        $columns = DB::select("
            SELECT column_name, data_type, character_maximum_length, numeric_precision, numeric_scale, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = ?
            ORDER BY ordinal_position
        ", [$table]);

        $pkColumns = collect(DB::select("
            SELECT kcu.column_name
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
              ON tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema
            WHERE tc.table_schema = 'public' AND tc.table_name = ? AND tc.constraint_type = 'PRIMARY KEY'
            ORDER BY kcu.ordinal_position
        ", [$table]))->pluck('column_name')->all();

        $uniqueGroups = collect(DB::select("
            SELECT tc.constraint_name, kcu.column_name
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
              ON tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema
            WHERE tc.table_schema = 'public' AND tc.table_name = ? AND tc.constraint_type = 'UNIQUE'
            ORDER BY tc.constraint_name, kcu.ordinal_position
        ", [$table]))->groupBy('constraint_name');

        $foreignKeys = collect(DB::select("
            SELECT
                tc.constraint_name, kcu.column_name,
                ccu.table_name AS foreign_table, ccu.column_name AS foreign_column,
                rc.update_rule, rc.delete_rule
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
              ON tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage ccu
              ON ccu.constraint_name = tc.constraint_name AND ccu.table_schema = tc.table_schema
            JOIN information_schema.referential_constraints rc
              ON rc.constraint_name = tc.constraint_name AND rc.constraint_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_schema = 'public' AND tc.table_name = ?
        ", [$table]))->groupBy('constraint_name');

        $lines = [];

        // ลำดับ primary key เดี่ยวๆ ที่เป็นเลขรันอัตโนมัติ (serial) -> ใช้ id()/increments() ให้อ่านง่าย
        $singlePk = count($pkColumns) === 1 ? $pkColumns[0] : null;
        $handledAsAutoPk = false;

        foreach ($columns as $col) {
            $isPk = $singlePk === $col->column_name;
            $isSerial = $col->column_default && str_starts_with($col->column_default, 'nextval(');

            if ($isPk && $isSerial) {
                $method = $col->data_type === 'bigint' ? 'id' : 'increments';
                $lines[] = "                \$table->{$method}('{$col->column_name}');";
                $handledAsAutoPk = true;
                continue;
            }

            $lines[] = '                ' . $this->columnLine($col);
        }

        if (!empty($pkColumns) && !($handledAsAutoPk && count($pkColumns) === 1)) {
            $cols = collect($pkColumns)->map(fn ($c) => "'{$c}'")->implode(', ');
            $lines[] = "                \$table->primary([{$cols}]);";
        }

        foreach ($uniqueGroups as $constraintName => $rows) {
            $cols = $rows->pluck('column_name')->map(fn ($c) => "'{$c}'")->implode(', ');
            $lines[] = "                \$table->unique([{$cols}], '{$constraintName}');";
        }

        foreach ($foreignKeys as $constraintName => $rows) {
            if ($rows->count() > 1) {
                // FK แบบหลายคอลัมน์พร้อมกัน (composite) พบน้อยมาก ข้ามไปก่อนเพื่อความปลอดภัย ให้ผู้ดูแลระบบเพิ่มเองถ้าจำเป็น
                $lines[] = "                // TODO: composite foreign key '{$constraintName}' ไม่ได้สร้างอัตโนมัติ กรุณาตรวจสอบเพิ่มเติม";
                continue;
            }
            $fk = $rows->first();
            $line = "                \$table->foreign('{$fk->column_name}', '{$constraintName}')->references('{$fk->foreign_column}')->on('{$fk->foreign_table}')";
            if ($fk->update_rule && $fk->update_rule !== 'NO ACTION') {
                $line .= "->onUpdate('" . strtolower($fk->update_rule) . "')";
            }
            if ($fk->delete_rule && $fk->delete_rule !== 'NO ACTION') {
                $line .= "->onDelete('" . strtolower($fk->delete_rule) . "')";
            }
            $lines[] = $line . ';';
        }

        $constraintIndexNames = array_merge(
            $singlePk && $handledAsAutoPk ? [] : [],
            $uniqueGroups->keys()->all()
        );
        $indexes = DB::select("SELECT indexname, indexdef FROM pg_indexes WHERE schemaname = 'public' AND tablename = ?", [$table]);
        foreach ($indexes as $idx) {
            if (str_ends_with($idx->indexname, '_pkey') || in_array($idx->indexname, $constraintIndexNames, true)) {
                continue;
            }
            // ดัชนีเดี่ยวๆ (ไม่ใช่ constraint) แปลงจาก indexdef ตรงๆ ผ่าน DB::statement กันพลาดเรื่อง syntax คอลัมน์ซับซ้อน
            $escaped = addslashes($idx->indexdef);
            $lines[] = "                DB::statement(\"{$escaped}\");";
        }

        $body = implode("\n", $lines);

        return <<<PHP
        if (!Schema::hasTable('{$table}')) {
            Schema::create('{$table}', function (Blueprint \$table) {
{$body}
            });
        }
PHP;
    }

    private function columnLine(object $col): string
    {
        [$method, $args] = $this->mapColumnMethod($col);
        $line = "\$table->{$method}('{$col->column_name}'" . ($args !== '' ? ", {$args}" : '') . ')';

        if ($col->is_nullable === 'YES') {
            $line .= '->nullable()';
        }

        if ($col->column_default !== null && !str_starts_with($col->column_default, 'nextval(')) {
            $default = $this->formatDefault($col->column_default, $method);
            if ($default !== null) {
                $line .= "->default({$default})";
            }
        }

        return $line . ';';
    }

    /** @return array{0: string, 1: string} */
    private function mapColumnMethod(object $col): array
    {
        return match ($col->data_type) {
            'integer' => ['integer', ''],
            'bigint' => ['bigInteger', ''],
            'smallint' => ['smallInteger', ''],
            'boolean' => ['boolean', ''],
            'text' => ['text', ''],
            'json' => ['json', ''],
            'jsonb' => ['jsonb', ''],
            'date' => ['date', ''],
            'timestamp without time zone', 'timestamp' => ['timestamp', ''],
            'timestamp with time zone' => ['timestampTz', ''],
            'time without time zone', 'time' => ['time', ''],
            'double precision' => ['double', ''],
            'real' => ['float', ''],
            'uuid' => ['uuid', ''],
            'numeric' => [
                'decimal',
                $col->numeric_precision ? "{$col->numeric_precision}, " . ($col->numeric_scale ?? 0) : '10, 2',
            ],
            'character varying' => [
                'string',
                $col->character_maximum_length ? (string) $col->character_maximum_length : '255',
            ],
            'character' => [
                'char',
                $col->character_maximum_length ? (string) $col->character_maximum_length : '1',
            ],
            default => ['string', '255 /* ชนิดข้อมูลจริงคือ ' . $col->data_type . ' — ตรวจสอบก่อนใช้งาน */'],
        };
    }

    private function formatDefault(string $raw, string $method): ?string
    {
        // ค่า default ของ Postgres มักมี type-cast ต่อท้าย เช่น 'ปกติ'::character varying — ตัดส่วนนั้นออก
        $value = preg_replace('/::[a-zA-Z ]+(\([\d,]+\))?$/', '', $raw);
        $value = trim($value, "'");

        if (in_array($method, ['integer', 'bigInteger', 'smallInteger', 'decimal', 'double', 'float'], true) && is_numeric($value)) {
            return $value;
        }

        if ($method === 'boolean') {
            return in_array(strtolower($value), ['true', 't', '1'], true) ? 'true' : 'false';
        }

        if (in_array($raw, ['now()', 'CURRENT_TIMESTAMP'], true)) {
            return "DB::raw('CURRENT_TIMESTAMP')";
        }

        return "'" . addslashes($value) . "'";
    }

    private function renderMigrationFile(array $upBlocks): string
    {
        $body = implode("\n\n", $upBlocks);

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ไฟล์นี้สร้างอัตโนมัติจากคำสั่ง `php artisan db:generate-baseline-migration`
// โดยอ่านโครงสร้างจริงจากฐานข้อมูล ณ ตอนที่รันคำสั่ง (production ปัจจุบัน)
// ทุกตารางเช็ค Schema::hasTable() ก่อนสร้างเสมอ จึงปลอดภัยกับฐานข้อมูลเดิมที่มีตารางอยู่แล้ว
// (จะข้ามไปเฉยๆ ไม่ไปแตะของเดิม) แต่ถ้ารันบนฐานข้อมูลว่างเปล่า (เซิร์ฟเวอร์ใหม่) จะสร้างครบทุกตาราง
return new class extends Migration
{
    public function up(): void
    {
{$body}
    }

    public function down(): void
    {
        // ตั้งใจเว้นว่างไว้ — ไฟล์นี้ครอบคลุมตารางหลักเกือบทั้งหมดของระบบ
        // การ rollback (DROP TABLE) โดยไม่ตั้งใจอาจทำข้อมูลจริงหายทั้งระบบ จึงไม่ทำ down() ให้อัตโนมัติ
    }
};

PHP;
    }
}
