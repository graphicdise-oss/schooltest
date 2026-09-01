<?php

namespace App\Console\Commands;

use App\Models\Personne\Personnel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * แก้ปัญหาบุคลากรที่เข้าระบบไม่ได้เพราะไม่เคยมีรหัสผ่านเลย — เกิดจากบัคเดิมที่ตั้งรหัสผ่าน
 * เริ่มต้น (=เลขบัตรประชาชน) ให้เฉพาะตอนสร้างบัญชีครั้งแรกพร้อมกรอกเลขบัตรในขั้นตอนเดียวกัน
 * เท่านั้น (แก้บัคนี้ไปแล้วสำหรับเคสใหม่ ดู PersonnelController::update) แต่บัญชีเก่าที่เจอ
 * ปัญหานี้ไปแล้วก่อนแก้บัค ยังค้างไม่มีรหัสผ่านอยู่ คำสั่งนี้ไล่แก้ทีเดียวทั้งหมด
 *
 * คนที่ไม่มีเลขบัตรประชาชนในระบบเลย (เช่น บุคลากรต่างชาติ) จะไม่ถูกแก้อัตโนมัติเพราะไม่รู้
 * จะตั้งรหัสผ่านอะไรให้ — ต้องให้แอดมินไปกดปุ่ม "ตั้ง/รีเซ็ตรหัสผ่านให้คนนี้" ที่หน้าแก้ไข
 * ข้อมูลของแต่ละคนเอง คำสั่งนี้แค่รายชื่อให้รู้ว่ามีใครบ้างที่ต้องไปทำเอง
 */
class FixMissingPersonnelPasswords extends Command
{
    protected $signature = 'personnel:fix-missing-passwords {--dry-run : ทดสอบเฉยๆ ไม่บันทึกจริง}';

    protected $description = 'ตั้งรหัสผ่าน = เลขบัตรประชาชน ให้บุคลากรทุกคนที่ยังไม่เคยมีรหัสผ่านเลยแต่มีเลขบัตรประชาชนในระบบแล้ว';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $noPassword = fn ($q) => $q->whereNull('password')->orWhere('password', '');

        $fixable = Personnel::where($noPassword)
            ->whereNotNull('id_card_number')
            ->where('id_card_number', '!=', '')
            ->get();

        $needsManual = Personnel::where($noPassword)
            ->where(function ($q) {
                $q->whereNull('id_card_number')->orWhere('id_card_number', '');
            })
            ->get();

        if ($fixable->isEmpty() && $needsManual->isEmpty()) {
            $this->info('ไม่พบบุคลากรที่ขาดรหัสผ่าน — ทุกคนเข้าระบบได้ปกติ');
            return self::SUCCESS;
        }

        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่บันทึกจริง ===' : '=== กำลังตั้งรหัสผ่าน ===');

        if ($fixable->isNotEmpty()) {
            $this->line("จะตั้งรหัสผ่าน = เลขบัตรประชาชน ให้ {$fixable->count()} คน:");
            foreach ($fixable as $p) {
                $this->line("  - {$p->employee_code} {$p->thai_firstname} {$p->thai_lastname}");
            }
        }

        if ($needsManual->isNotEmpty()) {
            $this->newLine();
            $this->warn("อีก {$needsManual->count()} คนไม่มีเลขบัตรประชาชนในระบบ ต้องให้แอดมินไปตั้งรหัสผ่านเองที่หน้าแก้ไขข้อมูล (ปุ่ม \"ตั้ง/รีเซ็ตรหัสผ่านให้คนนี้\"):");
            foreach ($needsManual as $p) {
                $this->line("  - {$p->employee_code} {$p->thai_firstname} {$p->thai_lastname}");
            }
        }

        if ($fixable->isEmpty()) {
            return self::SUCCESS;
        }

        if (!$dryRun && !$this->confirm("ยืนยันตั้งรหัสผ่านให้ {$fixable->count()} คนตามรายชื่อด้านบน?", true)) {
            $this->info('ยกเลิก ไม่มีอะไรถูกบันทึก');
            return self::SUCCESS;
        }

        if (!$dryRun) {
            foreach ($fixable as $p) {
                $p->update(['password' => Hash::make($p->id_card_number)]);
            }
        }

        $this->newLine();
        $this->info(($dryRun ? 'จะตั้งรหัสผ่านให้: ' : 'ตั้งรหัสผ่านสำเร็จ: ') . "{$fixable->count()} คน");

        if ($dryRun) {
            $this->comment('นี่คือผลทดสอบ ยังไม่มีข้อมูลถูกบันทึกจริง หากตรวจสอบแล้วถูกต้อง ให้รันคำสั่งเดิมโดยไม่ใส่ --dry-run');
        }

        return self::SUCCESS;
    }
}
