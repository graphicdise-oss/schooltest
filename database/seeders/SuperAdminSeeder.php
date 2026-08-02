<?php

namespace Database\Seeders;

use App\Models\Personne\Personnel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    // รหัสพนักงาน + รหัสผ่านเริ่มต้นของ superadmin (ล็อกอินครั้งแรกได้เลยโดยไม่ต้องสร้างเองผ่าน DB)
    // ควรเปลี่ยนรหัสผ่านทันทีหลังล็อกอินครั้งแรกผ่านเมนู "จัดการรหัสผ่าน"
    private const EMPLOYEE_CODE = '99999999';

    public function run(): void
    {
        Personnel::firstOrCreate(
            ['employee_code' => self::EMPLOYEE_CODE],
            [
                'personnel_type' => 'ผู้บริหาร',
                'thai_prefix' => 'นาย',
                'thai_firstname' => 'Super',
                'thai_lastname' => 'Admin',
                'role' => 'superadmin',
                'status' => 'ปฏิบัติงาน',
                'password' => Hash::make(self::EMPLOYEE_CODE),
                'created_by' => 'seeder:superadmin',
            ]
        );
    }
}
