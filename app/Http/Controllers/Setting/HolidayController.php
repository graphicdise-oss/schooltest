<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Academic\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

    // ชื่อวันหยุดไทยที่ Thailand Formats API ส่งมาเป็นภาษาอังกฤษ (API ไม่มีชื่อภาษาไทยให้) แปลเป็นไทยไว้ล่วงหน้า
    // สำหรับวันหยุดราชการ/วันสำคัญมาตรฐานที่รู้จักชื่อทางการแน่นอนอยู่แล้ว — ถ้าปีไหนมีรายการที่ไม่อยู่ใน
    // ลิสต์นี้ (เช่น API เพิ่มวันใหม่ที่ไม่เคยเจอ) จะเหลือเป็นชื่ออังกฤษเดิม ผู้ใช้แก้ในหน้า preview เองได้
    private const THAI_HOLIDAY_TITLES = [
        "New Year's Day" => 'วันขึ้นปีใหม่',
        'Special Public Holiday' => 'วันหยุดราชการกรณีพิเศษ',
        'Makha Bucha Day' => 'วันมาฆบูชา',
        'Chakri Memorial Day' => 'วันจักรี',
        'Songkran Festival' => 'วันสงกรานต์',
        'National Labour Day' => 'วันแรงงานแห่งชาติ',
        'Coronation Day' => 'วันฉัตรมงคล',
        'Visakha Bucha Day' => 'วันวิสาขบูชา',
        'Substitution for Visakha Bucha Day' => 'วันหยุดชดเชยวันวิสาขบูชา',
        "H.M. Queen Suthida's Birthday" => 'วันเฉลิมพระชนมพรรษาสมเด็จพระนางเจ้าสุทิดา พัชรสุธาพิมลลักษณ พระบรมราชินี',
        'Substitution for Buddhist Lent Day (Khao Phansa)' => 'วันหยุดชดเชยวันเข้าพรรษา',
        "H.M. King Maha Vajiralongkorn's Birthday" => 'วันเฉลิมพระชนมพรรษาพระบาทสมเด็จพระวชิรเกล้าเจ้าอยู่หัว',
        'Asanha Bucha Day' => 'วันอาสาฬหบูชา',
        'Buddhist Lent Day' => 'วันเข้าพรรษา',
        "H.M. Queen Sirikit The Queen Mother's Birthday / Mother's Day" => 'วันเฉลิมพระชนมพรรษาสมเด็จพระบรมราชชนนีพันปีหลวง (วันแม่แห่งชาติ)',
        'H.M. King Bhumibol Adulyadej The Great Memorial Day' => 'วันคล้ายวันสวรรคตพระบาทสมเด็จพระบรมชนกาธิเบศร มหาภูมิพลอดุลยเดชมหาราช บรมนาถบพิตร',
        'Chulalongkorn Memorial Day' => 'วันปิยมหาราช',
        "H.M. King Bhumibol Adulyadej's Birthday / National Day / Father's Day" => 'วันคล้ายวันพระบรมราชสมภพพระบาทสมเด็จพระบรมชนกาธิเบศร มหาภูมิพลอดุลยเดชมหาราช บรมนาถบพิตร (วันชาติ/วันพ่อแห่งชาติ)',
        "Substitution for H.M. King Bhumibol Adulyadej's Birthday, National Day, and Father's Day" => 'วันหยุดชดเชยวันพ่อแห่งชาติ',
        'Constitution Day' => 'วันรัฐธรรมนูญ',
        "New Year's Eve" => 'วันสิ้นปี',
    ];

    // ดึงรายชื่อวันหยุด/วันสำคัญไทยจาก Thailand Formats API (ฟรี ไม่ต้องขอ API Key) มาให้เลือกก่อนนำเข้าจริง
    // ครอบคลุมกว่า Nager.Date เดิม (มีวันพระ/วันหยุดชดเชยครบ) แปลชื่อเป็นไทยให้อัตโนมัติเท่าที่รู้จัก
    // (ดู THAI_HOLIDAY_TITLES) รองรับวันหยุดหลายวันติดกัน (เช่น สงกรานต์) ด้วย end_date จริงจาก API
    public function importPreview(Request $request)
    {
        $request->validate([
            'ce_year' => 'required|integer|min:2000|max:2100',
        ]);

        try {
            $response = Http::timeout(10)->get(
                "https://thailandformats.com/api/v1/holidays/{$request->ce_year}"
            );
        } catch (\Throwable $e) {
            return response()->json(['error' => 'เชื่อมต่อ API ไม่สำเร็จ กรุณาลองใหม่อีกครั้ง'], 500);
        }

        if (! $response->successful()) {
            return response()->json(['error' => 'ดึงข้อมูลไม่สำเร็จ (API ตอบกลับผิดพลาด)'], 500);
        }

        $existingDates = Holiday::pluck('start_date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->flip();

        $items = collect($response->json('holidays', []))->map(fn ($h) => [
            'date'     => $h['start_date'],
            'end_date' => $h['end_date'] ?? $h['start_date'],
            'title'    => self::THAI_HOLIDAY_TITLES[$h['title']] ?? ($h['title'] ?? 'วันหยุด'),
            'exists'   => $existingDates->has($h['start_date']),
        ])->values();

        return response()->json(['items' => $items]);
    }

    public function importApply(Request $request)
    {
        $data = $request->validate([
            'year_id'           => 'required|exists:academic_years,year_id',
            'items'             => 'required|array|min:1',
            'items.*.date'      => 'required|date',
            'items.*.end_date'  => 'nullable|date|after_or_equal:items.*.date',
            'items.*.title'     => 'required|string|max:255',
        ]);

        $count = 0;
        foreach ($data['items'] as $item) {
            // ใช้ whereDate() ไม่ใช่ where() ตรงๆ เพราะคอลัมน์ start_date ถูก cast เป็น date แต่ค่าที่บันทึก
            // จริงในฐานข้อมูลมีเวลาต่อท้ายด้วย (เช่น 2026-01-01 00:00:00) เทียบ string ตรงๆ กับ "2026-01-01"
            // เฉยๆ จะไม่ match กันเลย ทำให้เช็คซ้ำไม่เจอ นำเข้าปีเดิมซ้ำได้เรื่อยๆ
            $duplicate = Holiday::where('year_id', $data['year_id'])
                ->whereDate('start_date', $item['date'])
                ->where('title', $item['title'])
                ->exists();
            if ($duplicate) continue;

            Holiday::create([
                'year_id'    => $data['year_id'],
                'title'      => $item['title'],
                'type'       => 'วันหยุดราชการ',
                'start_date' => $item['date'],
                'end_date'   => $item['end_date'] ?? $item['date'],
                'note'       => 'นำเข้าจาก Thailand Formats API',
            ]);
            $count++;
        }

        return back()->with('success', "นำเข้าวันหยุดสำเร็จ {$count} รายการ");
    }
}
