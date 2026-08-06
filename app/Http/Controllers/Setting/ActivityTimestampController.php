<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\ActivityTimestamp;
use Illuminate\Http\Request;

class ActivityTimestampController extends Controller
{
    // แสดงรายการ "ครั้งแรก" ที่แต่ละคนบันทึก/แก้ไขข้อมูลในแต่ละหน้า (เฉพาะ admin/superadmin)
    public function index(Request $request)
    {
        $personnelId = $request->personnel_id;
        $pageGroup = $request->page_group;

        $query = ActivityTimestamp::orderByDesc('first_recorded_at');
        if ($personnelId) $query->where('personnel_id', $personnelId);
        if ($pageGroup) $query->where('route_name', 'like', $pageGroup . '.%');

        $logs = $query->paginate(50)->withQueryString();

        $people = ActivityTimestamp::select('personnel_id', 'personnel_name', 'employee_code')
            ->distinct()
            ->orderBy('personnel_name')
            ->get();

        $pageGroups = ActivityTimestamp::select('page_label', 'route_name')
            ->get()
            ->map(fn ($r) => ['group' => explode('.', $r->route_name)[0], 'label' => $r->page_label])
            ->unique('group')
            ->sortBy('label')
            ->values();

        return view('settings.activity_timestamps', compact('logs', 'people', 'pageGroups', 'personnelId', 'pageGroup'));
    }
}
