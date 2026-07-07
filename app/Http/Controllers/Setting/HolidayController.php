<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Academic\AcademicYear;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();

        // เลือกปีการศึกษา: ตาม URL > ปีปัจจุบัน > ปีล่าสุด
        $yearId = $request->get('year_id')
            ?? optional(AcademicYear::current())->year_id
            ?? optional($academicYears->first())->year_id;

        $holidays = Holiday::where('year_id', $yearId)
            ->orderBy('start_date')
            ->get();

        $totalDays = $holidays->sum('day_count');

        return view('settings.holiday_index', compact(
            'academicYears', 'yearId', 'holidays', 'totalDays'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year_id'    => 'required|exists:academic_years,year_id',
            'title'      => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'note'       => 'nullable|string|max:255',
        ], [
            'title.required'      => 'กรุณาระบุชื่อวันหยุด',
            'start_date.required' => 'กรุณาระบุวันเริ่ม',
            'end_date.after_or_equal' => 'วันสิ้นสุดต้องไม่ก่อนวันเริ่ม',
        ]);

        Holiday::create($data);

        return back()->with('success', 'เพิ่มวันหยุดสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $data = $request->validate([
            'year_id'    => 'required|exists:academic_years,year_id',
            'title'      => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'note'       => 'nullable|string|max:255',
        ], [
            'title.required'      => 'กรุณาระบุชื่อวันหยุด',
            'start_date.required' => 'กรุณาระบุวันเริ่ม',
            'end_date.after_or_equal' => 'วันสิ้นสุดต้องไม่ก่อนวันเริ่ม',
        ]);

        $holiday->update($data);

        return back()->with('success', 'แก้ไขวันหยุดสำเร็จ');
    }

    public function destroy($id)
    {
        Holiday::findOrFail($id)->delete();
        return back()->with('success', 'ลบวันหยุดสำเร็จ');
    }
}
