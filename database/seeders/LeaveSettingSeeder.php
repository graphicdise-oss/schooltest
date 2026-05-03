<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Leave\LeaveSetting;
use App\Models\Leave\LeaveDeptApprover;
use App\Models\Leave\LeaveQuotaGroup;
use App\Models\Leave\LeaveTypeQuota;
use App\Models\Leave\LeaveNotificationSetting;
use App\Models\Leave\LeaveNotificationRecipient;

class LeaveSettingSeeder extends Seeder
{
    public function run(): void
    {
        LeaveSetting::firstOrCreate([], [
            'min_approvers' => 2,
            'cutoff_day'    => 1,
            'cutoff_month'  => 10,
        ]);

        if (LeaveDeptApprover::count() === 0) {
            $depts = [
                ['department_name' => 'ครูประถมศึกษา',    'approver_1' => 'ยุวรัตน์ นักทำนา',    'approver_2' => 'รัญญา จิตต์อาจหาญ',      'approver_3' => null, 'sort_order' => 1],
                ['department_name' => 'ครูมัธยมศึกษา',    'approver_1' => 'ยุวรัตน์ นักทำนา',    'approver_2' => 'รัญญา จิตต์อาจหาญ',      'approver_3' => null, 'sort_order' => 2],
                ['department_name' => 'เจ้าหน้าที่โรงเรียน', 'approver_1' => 'รัญญา จิตต์อาจหาญ', 'approver_2' => null,                   'approver_3' => null, 'sort_order' => 3],
                ['department_name' => 'ผู้บริหาร',          'approver_1' => 'สุวิทย์ ฝ่ายสงค์',   'approver_2' => 'วรานิษฐ์ ธนชัยวรพันธ์', 'approver_3' => null, 'sort_order' => 4],
                ['department_name' => 'แม่บ้าน',            'approver_1' => 'รัญญา จิตต์อาจหาญ', 'approver_2' => null,                   'approver_3' => null, 'sort_order' => 5],
            ];
            foreach ($depts as $d) LeaveDeptApprover::create($d);
        }

        if (LeaveQuotaGroup::count() === 0) {
            $group = LeaveQuotaGroup::create(['years_from' => 0, 'years_to' => 10, 'is_active' => true, 'sort_order' => 1]);
            $types = [
                ['key' => 'sick',       'name' => 'ลาป่วย',              'days' => 60,  'order' => 1],
                ['key' => 'personal',   'name' => 'ลากิจส่วนตัว',        'days' => 45,  'order' => 2],
                ['key' => 'vacation',   'name' => 'พักร้อน',             'days' => 15,  'order' => 3],
                ['key' => 'maternity',  'name' => 'ลาคลอดบุตร',          'days' => 90,  'order' => 4],
                ['key' => 'ordination', 'name' => 'ลาบวช',               'days' => 90,  'order' => 5],
                ['key' => 'military',   'name' => 'ลารับราชการทหาร',     'days' => 365, 'order' => 6],
                ['key' => 'official',   'name' => 'ลาไปราชการ',          'days' => 365, 'order' => 7],
                ['key' => 'study',      'name' => 'ลาศึกษา/อบรม/ดูงาน', 'days' => 365, 'order' => 8],
            ];
            foreach ($types as $t) {
                LeaveTypeQuota::create([
                    'group_id'        => $group->id,
                    'leave_type_key'  => $t['key'],
                    'leave_type_name' => $t['name'],
                    'days_per_year'   => $t['days'],
                    'sort_order'      => $t['order'],
                ]);
            }
        }

        if (LeaveNotificationSetting::count() === 0) {
            $settings = [
                ['notification_type' => 'late_arrival',      'alert_number' => 1, 'threshold_value' => 5],
                ['notification_type' => 'late_arrival',      'alert_number' => 2, 'threshold_value' => 10],
                ['notification_type' => 'visa_expiry',        'alert_number' => 1, 'threshold_value' => 45],
                ['notification_type' => 'work_permit_expiry', 'alert_number' => 1, 'threshold_value' => 60],
                ['notification_type' => 'license_expiry',     'alert_number' => 1, 'threshold_value' => 90],
            ];
            foreach ($settings as $s) LeaveNotificationSetting::create($s);
        }

        if (LeaveNotificationRecipient::count() === 0) {
            LeaveNotificationRecipient::create([
                'position_name'  => 'รองผู้อำนวยการ',
                'personnel_name' => 'สุวิทย์ ฝ่ายสงค์',
            ]);
        }
    }
}
