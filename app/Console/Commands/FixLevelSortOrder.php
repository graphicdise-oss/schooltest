<?php

namespace App\Console\Commands;

use App\Models\Academic\Level;
use Illuminate\Console\Command;

class FixLevelSortOrder extends Command
{
    protected $signature = 'fix:level-order {name : ชื่อระดับชั้น เช่น เตรียมอนุบาล} {--first : ให้ระดับชั้นนี้เรียงมาก่อนระดับชั้นอื่นทั้งหมด}';

    protected $description = 'แก้ลำดับการเรียง (sort_order) ของระดับชั้น เช่น ให้ "เตรียมอนุบาล" เรียงมาก่อน อ.1 ทุกที่ในระบบ';

    public function handle(): int
    {
        $level = Level::where('name', $this->argument('name'))->first();
        if (!$level) {
            $this->error("ไม่พบระดับชั้นชื่อ '{$this->argument('name')}'");
            return self::FAILURE;
        }

        if ($this->option('first')) {
            $minOrder = Level::where('level_id', '!=', $level->level_id)->min('sort_order') ?? 1;
            $level->update(['sort_order' => $minOrder - 1]);
            $this->info("ตั้ง '{$level->name}' ให้เรียงก่อนระดับชั้นอื่นทั้งหมดแล้ว (sort_order={$level->sort_order})");
            return self::SUCCESS;
        }

        $this->error('กรุณาระบุ --first');
        return self::FAILURE;
    }
}
