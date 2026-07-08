<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SetAdmin extends Command
{
    protected $signature = 'admin:set {employee_code} {--super : ตั้งเป็น superadmin (ผู้ดูแลสูงสุด)}';

    protected $description = 'ตั้งบัญชีให้เป็น admin หรือ superadmin จากรหัสพนักงาน (employee_code)';

    public function handle(): int
    {
        $code = $this->argument('employee_code');
        $user = User::where('employee_code', $code)->first();

        if (!$user) {
            $this->error("ไม่พบบัญชีที่มีรหัสพนักงาน: {$code}");
            return self::FAILURE;
        }

        $role = $this->option('super') ? 'superadmin' : 'admin';
        $user->role = $role;
        $user->save();

        $name = trim(($user->thai_firstname ?? '') . ' ' . ($user->thai_lastname ?? ''));
        $this->info("✔ ตั้ง {$name} (รหัส {$code}) เป็น {$role} สำเร็จ");

        return self::SUCCESS;
    }
}
