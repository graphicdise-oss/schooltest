<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Leave\LeaveSetting;
use App\Models\Leave\LeaveDeptApprover;
use App\Models\Leave\LeaveQuotaGroup;
use App\Models\Leave\LeaveTypeQuota;
use App\Models\Leave\LeaveNotificationSetting;
use App\Models\Leave\LeaveNotificationRecipient;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;

class LeaveSettingController extends Controller
{
    public function index()
    {
        $setting       = LeaveSetting::getOrCreate();
        $deptApprovers = LeaveDeptApprover::orderBy('sort_order')->get();
        $quotaGroups   = LeaveQuotaGroup::with('quotas')->orderBy('sort_order')->get();
        $notifications = LeaveNotificationSetting::orderBy('notification_type')->orderBy('alert_number')->get()
            ->groupBy('notification_type');
        $recipients    = LeaveNotificationRecipient::orderBy('id')->get();
        $personnelList = Personnel::orderBy('thai_firstname')
            ->get(['personnel_id', 'thai_prefix', 'thai_firstname', 'thai_lastname', 'department']);

        return view('settings.leave_setting', compact(
            'setting', 'deptApprovers', 'quotaGroups', 'notifications', 'recipients', 'personnelList'
        ));
    }

    // ---- Section 1: General setting & approvers ----

    public function saveGeneral(Request $request)
    {
        $request->validate(['min_approvers' => 'required|integer|min:1|max:10']);
        LeaveSetting::getOrCreate()->update(['min_approvers' => $request->min_approvers]);
        return back()->with('success', 'บันทึกการตั้งค่าจำนวนผู้อนุมัติสำเร็จ');
    }

    public function storeDept(Request $request)
    {
        $request->validate(['department_name' => 'required|string|max:100']);
        LeaveDeptApprover::create([
            'department_name' => $request->department_name,
            'approver_1'      => $request->approver_1 ?: null,
            'approver_2'      => $request->approver_2 ?: null,
            'approver_3'      => $request->approver_3 ?: null,
            'sort_order'      => LeaveDeptApprover::max('sort_order') + 1,
        ]);
        return back()->with('success', 'เพิ่มแผนกสำเร็จ');
    }

    public function updateDept(Request $request, $id)
    {
        $request->validate(['department_name' => 'required|string|max:100']);
        LeaveDeptApprover::findOrFail($id)->update([
            'department_name' => $request->department_name,
            'approver_1'      => $request->approver_1 ?: null,
            'approver_2'      => $request->approver_2 ?: null,
            'approver_3'      => $request->approver_3 ?: null,
        ]);
        return back()->with('success', 'แก้ไขแผนกสำเร็จ');
    }

    public function destroyDept($id)
    {
        LeaveDeptApprover::findOrFail($id)->delete();
        return back()->with('success', 'ลบแผนกสำเร็จ');
    }

    // ---- Section 2: Quota groups ----

    public function storeQuotaGroup(Request $request)
    {
        $request->validate([
            'years_from'    => 'required|integer|min:0',
            'years_to'      => 'nullable|integer|min:0',
        ]);

        $group = LeaveQuotaGroup::create([
            'years_from' => $request->years_from,
            'years_to'   => $request->years_to ?: null,
            'is_active'  => true,
            'sort_order' => LeaveQuotaGroup::max('sort_order') + 1,
        ]);

        // สร้างโควตาเริ่มต้นทุกประเภท
        $defaultTypes = $this->defaultLeaveTypes();
        foreach ($defaultTypes as $i => $type) {
            LeaveTypeQuota::create([
                'group_id'       => $group->id,
                'leave_type_key' => $type['key'],
                'leave_type_name'=> $type['name'],
                'days_per_year'  => $type['days'],
                'sort_order'     => $i + 1,
            ]);
        }

        return back()->with('success', 'เพิ่มกลุ่มช่วงปีทำงานสำเร็จ');
    }

    public function toggleQuotaGroup($id)
    {
        $group = LeaveQuotaGroup::findOrFail($id);
        $group->update(['is_active' => !$group->is_active]);
        return back();
    }

    public function destroyQuotaGroup($id)
    {
        LeaveQuotaGroup::findOrFail($id)->delete();
        return back()->with('success', 'ลบกลุ่มช่วงปีทำงานสำเร็จ');
    }

    public function updateQuotas(Request $request, $groupId)
    {
        $group  = LeaveQuotaGroup::findOrFail($groupId);
        $quotas = $request->input('quotas', []);

        foreach ($quotas as $quotaId => $days) {
            LeaveTypeQuota::where('id', $quotaId)->where('group_id', $group->id)
                ->update(['days_per_year' => (int) $days]);
        }

        // อัปเดตช่วงปีทำงาน
        $request->validate([
            'years_from' => 'required|integer|min:0',
            'years_to'   => 'nullable|integer|min:0',
        ]);
        $group->update([
            'years_from' => $request->years_from,
            'years_to'   => $request->years_to ?: null,
        ]);

        return back()->with('success', 'บันทึกโควตาวันลาสำเร็จ');
    }

    // ---- Section 3: Cutoff date ----

    public function saveCutoff(Request $request)
    {
        $request->validate([
            'cutoff_day'   => 'required|integer|min:1|max:31',
            'cutoff_month' => 'required|integer|min:1|max:12',
        ]);
        LeaveSetting::getOrCreate()->update([
            'cutoff_day'   => $request->cutoff_day,
            'cutoff_month' => $request->cutoff_month,
        ]);
        return back()->with('success', 'บันทึกวันตัดรอบปีการทำงานสำเร็จ');
    }

    // ---- Section 4: Notifications ----

    public function saveNotifications(Request $request)
    {
        $types = ['late_arrival', 'visa_expiry', 'work_permit_expiry', 'license_expiry'];
        foreach ($types as $type) {
            $alerts = $request->input("notifications.{$type}", []);
            foreach ($alerts as $alertNum => $threshold) {
                if ($threshold === null || $threshold === '') continue;
                LeaveNotificationSetting::updateOrCreate(
                    ['notification_type' => $type, 'alert_number' => $alertNum],
                    ['threshold_value' => (int) $threshold]
                );
            }
        }
        return back()->with('success', 'บันทึกการตั้งค่าการแจ้งเตือนสำเร็จ');
    }

    public function storeRecipient(Request $request)
    {
        $request->validate(['personnel_name' => 'required|string|max:100']);
        LeaveNotificationRecipient::create([
            'position_name'  => $request->position_name ?: null,
            'personnel_name' => $request->personnel_name,
            'personnel_id'   => $request->personnel_id ?: null,
        ]);
        return back()->with('success', 'เพิ่มผู้รับการแจ้งเตือนสำเร็จ');
    }

    public function destroyRecipient($id)
    {
        LeaveNotificationRecipient::findOrFail($id)->delete();
        return back()->with('success', 'ลบผู้รับการแจ้งเตือนสำเร็จ');
    }

    private function defaultLeaveTypes(): array
    {
        return [
            ['key' => 'sick',       'name' => 'ลาป่วย',                'days' => 60],
            ['key' => 'personal',   'name' => 'ลากิจส่วนตัว',          'days' => 45],
            ['key' => 'vacation',   'name' => 'พักร้อน',               'days' => 15],
            ['key' => 'maternity',  'name' => 'ลาคลอดบุตร',            'days' => 90],
            ['key' => 'ordination', 'name' => 'ลาบวช',                 'days' => 90],
            ['key' => 'military',   'name' => 'ลารับราชการทหาร',       'days' => 365],
            ['key' => 'official',   'name' => 'ลาไปราชการ',            'days' => 365],
            ['key' => 'study',      'name' => 'ลาศึกษา/อบรม/ดูงาน',   'days' => 365],
        ];
    }
}
