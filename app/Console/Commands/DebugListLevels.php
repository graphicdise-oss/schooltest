<?php

namespace App\Console\Commands;

use App\Models\Academic\Level;
use Illuminate\Console\Command;

class DebugListLevels extends Command
{
    protected $signature = 'debug:levels';
    protected $description = 'แสดงรายการระดับชั้นทั้งหมดพร้อม level_group จริงในฐานข้อมูล (ใช้ตรวจสอบปัญหาดรอปดาวน์บันทึกจบ)';

    public function handle()
    {
        $levels = Level::orderBy('sort_order')->get(['level_id', 'name', 'level_group', 'sort_order']);

        $this->table(
            ['level_id', 'name', 'level_group', 'sort_order'],
            $levels->map(fn($l) => [
                $l->level_id,
                $l->name,
                $l->level_group === null ? '(NULL)' : "[{$l->level_group}]",
                $l->sort_order,
            ])
        );

        return 0;
    }
}
