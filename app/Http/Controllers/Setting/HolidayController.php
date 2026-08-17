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

        $typeColors = self::TYPE_COLORS;
        $calendarEvents = $holidays->map(function ($h) use ($typeColors) {
            $end = $h->end_date ?? $h->start_date;
            return [
                'id'    => $h->id,
                'title' => $h->title,
                'start' => $h->start_date->format('Y-m-d'),
                // FullCalendar treats the end date of an all-day event as exclusive
                'end'   => $end->copy()->addDay()->format('Y-m-d'),
                'allDay' => true,
                'color' => $typeColors[$h->type] ?? $typeColors['อื่นๆ'],
                'extendedProps' => [
                    'type'     => $h->type ?: 'อื่นๆ',
                    'note'     => $h->note,
                    'start_th' => self::thaiDate($h->start_date),
                    'end_th'   => self::thaiDate($end),
                ],
            ];
        })->values();

        return view('settings.holiday_index', compact(
            'academicYears', 'yearId', 'holidays', 'totalDays', 'calendarEvents'
        ));
    }

    public const TYPE_COLORS = [
        'วันหยุดราชการ'     => '#00bcd4',
        'วันกิจกรรมประจำปี' => '#f97316',
        'วันสอบ'             => '#8b5cf6',
        'อื่นๆ'               => '#64748b',
    ];

    public const TYPES = ['วันหยุดราชการ', 'วันกิจกรรมประจำปี', 'วันสอบ', 'อื่นๆ'];

    private static function thaiDate($date): string
    {
        static $months = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                           7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
        if (! $date) return '';
        return $date->day . ' ' . $months[$date->month] . ' ' . ($date->year + 543);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year_id'    => 'required|exists:academic_years,year_id',
            'title'      => 'required|string|max:255',
            'type'       => 'nullable|string|max:50',
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
            'type'       => 'nullable|string|max:50',
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
