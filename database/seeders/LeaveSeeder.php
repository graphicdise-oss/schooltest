<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('leave_settings')->insertOrIgnore([
            ['min_approvers' => 2, 'cutoff_day' => 1, 'cutoff_month' => 10, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('leave_dept_approvers')->insertOrIgnore([
            ['department_name' => 'ครูประถมศึกษา',       'approver_1' => 'ยุวรัตน์ นักทำนา',  'approver_2' => 'รัญญา จิตต์อาจหาญ', 'approver_3' => null, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['department_name' => 'ครูมัธยมศึกษา',       'approver_1' => 'ยุวรัตน์ นักทำนา',  'approver_2' => 'รัญญา จิตต์อาจหาญ', 'approver_3' => null, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['department_name' => 'เจ้าหน้าที่โรงเรียน', 'approver_1' => 'รัญญา จิตต์อาจหาญ', 'approver_2' => null,                 'approver_3' => null, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['department_name' => 'ผู้บริหาร',            'approver_1' => 'สุวิทย์ ฝ่ายสงค์',  'approver_2' => 'วรานิษฐ์ ธนชัยวรพันธ์', 'approver_3' => null, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['department_name' => 'แม่บ้าน',              'approver_1' => 'รัญญา จิตต์อาจหาญ', 'approver_2' => null,                 'approver_3' => null, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $groupId = DB::table('leave_quota_groups')->insertGetId([
            'years_from' => 0, 'years_to' => 10, 'is_active' => true, 'sort_order' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('leave_type_quotas')->insertOrIgnore([
            ['group_id' => $groupId, 'leave_type_key' => 'sick',       'leave_type_name' => 'ลาป่วย',              'days_per_year' => 60,  'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $groupId, 'leave_type_key' => 'personal',   'leave_type_name' => 'ลากิจส่วนตัว',        'days_per_year' => 45,  'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $groupId, 'leave_type_key' => 'vacation',   'leave_type_name' => 'พักร้อน',             'days_per_year' => 15,  'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $groupId, 'leave_type_key' => 'maternity',  'leave_type_name' => 'ลาคลอดบุตร',          'days_per_year' => 90,  'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $groupId, 'leave_type_key' => 'ordination', 'leave_type_name' => 'ลาบวช',               'days_per_year' => 90,  'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $groupId, 'leave_type_key' => 'military',   'leave_type_name' => 'ลารับราชการทหาร',     'days_per_year' => 365, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $groupId, 'leave_type_key' => 'official',   'leave_type_name' => 'ลาไปราชการ',          'days_per_year' => 365, 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $groupId, 'leave_type_key' => 'study',      'leave_type_name' => 'ลาศึกษา/อบรม/ดูงาน', 'days_per_year' => 365, 'sort_order' => 8, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('leave_notification_settings')->insertOrIgnore([
            ['notification_type' => 'late_arrival',       'alert_number' => 1, 'threshold_value' => 5,  'created_at' => now(), 'updated_at' => now()],
            ['notification_type' => 'late_arrival',       'alert_number' => 2, 'threshold_value' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['notification_type' => 'visa_expiry',        'alert_number' => 1, 'threshold_value' => 45, 'created_at' => now(), 'updated_at' => now()],
            ['notification_type' => 'work_permit_expiry', 'alert_number' => 1, 'threshold_value' => 60, 'created_at' => now(), 'updated_at' => now()],
            ['notification_type' => 'license_expiry',     'alert_number' => 1, 'threshold_value' => 90, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('leave_notification_recipients')->insertOrIgnore([
            ['position_name' => 'รองผู้อำนวยการ', 'personnel_name' => 'สุวิทย์ ฝ่ายสงค์', 'personnel_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
