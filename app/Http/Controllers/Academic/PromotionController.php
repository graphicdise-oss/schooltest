<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Promotion;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use App\Models\Academic\Semester;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Level;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    // หน้าหลัก ย้ายห้อง/เลื่อนชั้น/บันทึกจบ (3 Tabs)
    public function index(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $semesters = Semester::with('academicYear')->orderedByRecency()->get();
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        // เอาไว้ pre-select ดรอปดาวน์ "ปีการศึกษา" ให้ตรงกับเทอมที่กำลังดูอยู่ตอนโหลดหน้า
        $yearId = $semesters->firstWhere('semester_id', $semesterId)?->year_id;
        $levels = Level::orderBy('sort_order')->get();

        $fromSections = ClassSection::with(['level', 'studentSections.student'])
            ->where('semester_id', $semesterId)
            ->orderBy('level_id')->orderBy('section_number')->get();

        // ห้องปลายทาง (แท็บเลื่อนชั้น) ต้องเป็น "เทอมแรกของปีการศึกษาถัดไป" เสมอ ไม่ใช่แค่เทอมถัดไปตามลำดับเวลา
        // เพราะเลื่อนชั้น (ขึ้นทั้งระดับ เช่น ม.5 -> ม.6) เกิดข้ามปีการศึกษาเท่านั้น ถ้าเผลอใช้แค่ "เทอมถัดไป"
        // ตามลำดับเวลา จะข้ามไปเทอม 2 ของปีเดียวกันได้ ซึ่งผิด (นักเรียนเรียนไม่จบปียังไม่ควรขึ้นชั้นกลางคัน)
        // ห้ามใช้ semester_id เทียบตรงๆ เพราะไม่ได้เรียงตามลำดับปี/เทอมจริงเสมอไป (เหตุผลเดียวกับ orderedByRecency())
        $semestersAsc = $semesters->sortBy(fn($s) => [(string) ($s->academicYear->year_name ?? ''), (string) $s->semester_name])->values();
        $currentYearName = (string) ($semestersAsc->firstWhere('semester_id', $semesterId)?->academicYear?->year_name ?? '');
        $nextSemester = $semestersAsc->first(fn($s) => (string) ($s->academicYear->year_name ?? '') > $currentYearName);
        $toSections = $nextSemester
            ? ClassSection::with('level')->where('semester_id', $nextSemester->semester_id)->orderBy('level_id')->orderBy('section_number')->get()
            : collect();

        // บันทึกจบ (Tab 3) เลือกได้เฉพาะห้องของ "ชั้นปีสุดท้าย" ของแต่ละช่วงชั้นเท่านั้น (ป.6/ม.3/ม.6)
        // ใช้ชื่อระดับชั้นตรงๆ แทนการอิง level_group เพราะแต่ละโรงเรียนตั้ง level_group ไม่เหมือนกัน
        // (บางที่แยก "มัธยมศึกษาตอนต้น/ตอนปลาย" บางที่รวม ม.1-ม.6 เป็น "มัธยมศึกษา" กลุ่มเดียว ซึ่งถ้าอิงกลุ่ม
        // จะหาจุดจบ ม.3 ไม่เจอเลยเพราะ sort_order สูงสุดในกลุ่มเดียวกันจะเป็น ม.6 เท่านั้น)
        $terminalLevelIds = $levels->whereIn('name', ['ป.6', 'ม.3', 'ม.6'])->pluck('level_id')->values();

        // เผื่อโรงเรียนตั้งชื่อระดับชั้นไม่ตรงรูปแบบมาตรฐาน ("ป.6" ฯลฯ) ให้ลองเดาจาก level_group แทน
        // โดยเอาระดับสุดท้าย (sort_order สูงสุด) ของแต่ละกลุ่มที่ไม่ใช่อนุบาล/เตรียมอนุบาล
        if ($terminalLevelIds->isEmpty()) {
            $terminalLevelIds = $levels
                ->reject(fn($lv) => str_contains($lv->level_group ?? '', 'อนุบาล'))
                ->groupBy('level_group')
                ->map(fn($group) => $group->sortByDesc('sort_order')->first()?->level_id)
                ->filter()
                ->values();
        }
        // ถ้าหาชั้นปีสุดท้ายไม่เจอเลยสักช่วงชั้น (เช่น ยังไม่ได้ตั้งค่า level_group ให้ระดับชั้นในระบบ) ให้แสดงห้องทั้งหมด
        // แทนปล่อยดรอปดาวน์ว่างเปล่าจนใช้งานไม่ได้เลย — ต่างจากกรณีเทอมนี้แค่ยังไม่มีห้อง ป.6/ม.3/ม.6 ที่ควรปล่อยว่างไว้ตามจริง
        $graduateSections = $terminalLevelIds->isEmpty()
            ? $fromSections
            : $fromSections->whereIn('level_id', $terminalLevelIds)->values();
        // รายการระดับชั้นสำหรับดรอปดาวน์ "ระดับ" ในแท็บบันทึกจบ — ให้เลือกชั้นก่อนแล้วค่อยกรองห้อง (แสดงครบทั้ง 3
        // ชั้นปีสุดท้ายเสมอ ต่อให้เทอมนี้ยังไม่มีห้องของบางชั้น จะได้เห็นว่ามีตัวเลือกอยู่ แค่ยังไม่มีห้องให้เลือกในเทอมนี้)
        $graduateLevels = $terminalLevelIds->isEmpty()
            ? $levels
            : $levels->whereIn('level_id', $terminalLevelIds)->sortBy('sort_order')->values();

        // แผนที่ "ระดับถัดไป" ของแต่ละระดับชั้น (เรียงตาม sort_order) ใช้จำกัดตอนเลื่อนชั้น
        // ไม่ให้เลือกห้องปลายทางข้ามระดับได้ (เช่น ม.2 ต้องขึ้น ม.3 เท่านั้น)
        $nextLevelMap = [];
        $orderedLevels = $levels->values();
        foreach ($orderedLevels as $i => $lv) {
            // key เป็น string เสมอ กัน json_encode ตีความเป็น array แทน object ตอนส่งไปหน้าเว็บ
            $nextLevelMap[(string) $lv->level_id] = $orderedLevels[$i + 1]->level_id ?? null;
        }

        // ห้องทั้งหมดของ "เทอม 1" ทุกปีการศึกษา ใช้ในแท็บ "เปิดเทอม 2" (คัดลอกห้อง+แผนการเรียนจากเทอม 1 ไปสร้างในเทอม 2)
        $term1Sections = ClassSection::with(['level', 'semester'])
            ->whereHas('semester', fn($q) => $q->where('semester_name', '1'))
            ->orderBy('level_id')->orderBy('section_number')->get();

        return view('academic.promotions', compact('semesters', 'academicYears', 'yearId', 'levels', 'fromSections', 'toSections', 'graduateSections', 'graduateLevels', 'semesterId', 'nextSemester', 'nextLevelMap', 'term1Sections'));
    }

    // ย้ายห้อง (เทอมเดียวกัน)
    public function transfer(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'to_section_id' => 'required|exists:class_sections,section_id',
            'from_section_id' => 'required|exists:class_sections,section_id',
        ]);

        $toSection = ClassSection::findOrFail($request->to_section_id);
        $lastNumber = StudentSection::where('section_id', $toSection->section_id)->max('student_number') ?? 0;

        foreach ($request->student_ids as $studentId) {
            // ลบจากห้องเดิม
            StudentSection::where('student_id', $studentId)->where('section_id', $request->from_section_id)->delete();

            // เพิ่มเข้าห้องใหม่
            $lastNumber++;
            StudentSection::create([
                'student_id' => $studentId,
                'section_id' => $toSection->section_id,
                'student_number' => $lastNumber,
                'status' => 'กำลังศึกษา',
            ]);

            // บันทึกประวัติ
            Promotion::create([
                'student_id' => $studentId,
                'from_section_id' => $request->from_section_id,
                'to_section_id' => $toSection->section_id,
                'promo_type' => 'ย้ายห้อง',
                'promo_date' => now(),
                'created_by' => Auth::user()->thai_firstname ?? 'system',
            ]);
        }

        return redirect()->back()->with('success', 'ย้ายห้องสำเร็จ ' . count($request->student_ids) . ' คน');
    }

    // เลื่อนชั้น (เทอมถัดไป)
    public function promote(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'to_section_id' => 'required|exists:class_sections,section_id',
            'from_section_id' => 'required|exists:class_sections,section_id',
        ]);

        $fromSection = ClassSection::with('semester.academicYear')->findOrFail($request->from_section_id);
        $toSection = ClassSection::with('semester.academicYear')->findOrFail($request->to_section_id);

        // กันเลื่อนชั้นข้ามระดับ (เช่น ม.2 ต้องเลื่อนไป ม.3 เท่านั้น จะข้ามไป ม.4 ไม่ได้)
        // เทียบจาก sort_order ของระดับชั้น ห้องปลายทางต้องเป็นระดับ "ถัดไปทันที" เท่านั้น
        $levelIds = Level::orderBy('sort_order')->pluck('level_id')->values();
        $fromIndex = $levelIds->search($fromSection->level_id);
        $toIndex = $levelIds->search($toSection->level_id);
        if ($fromIndex === false || $toIndex === false || $toIndex !== $fromIndex + 1) {
            return redirect()->back()->with('error', 'เลื่อนชั้นข้ามระดับไม่ได้ กรุณาเลือกห้องของระดับถัดไปเท่านั้น');
        }

        // กันเลื่อนชั้นข้ามไปเทอมของปีการศึกษาเดียวกัน (เช่น ม.5 เทอม 1 -> ม.6 เทอม 2 ปีเดียวกัน) ต้องข้ามปีการศึกษาเท่านั้น
        // เพราะนักเรียนเรียนไม่จบปียังไม่ควรขึ้นชั้นกลางคัน
        $fromYearName = (string) ($fromSection->semester?->academicYear?->year_name ?? '');
        $toYearName   = (string) ($toSection->semester?->academicYear?->year_name ?? '');
        if ($fromYearName === '' || $toYearName === '' || $toYearName <= $fromYearName) {
            return redirect()->back()->with('error', 'เลื่อนชั้นได้เฉพาะข้ามปีการศึกษาเท่านั้น กรุณาเลือกห้องของปีการศึกษาถัดไป');
        }

        $lastNumber = StudentSection::where('section_id', $toSection->section_id)->max('student_number') ?? 0;

        foreach ($request->student_ids as $studentId) {
            // อัปเดตสถานะห้องเดิม
            StudentSection::where('student_id', $studentId)
                ->where('section_id', $request->from_section_id)
                ->update(['status' => 'เลื่อนชั้น']);

            // เพิ่มเข้าห้องใหม่
            $lastNumber++;
            StudentSection::create([
                'student_id' => $studentId,
                'section_id' => $toSection->section_id,
                'student_number' => $lastNumber,
                'status' => 'กำลังศึกษา',
            ]);

            Promotion::create([
                'student_id' => $studentId,
                'from_section_id' => $request->from_section_id,
                'to_section_id' => $toSection->section_id,
                'promo_type' => 'เลื่อนชั้น',
                'promo_date' => now(),
                'created_by' => Auth::user()->thai_firstname ?? 'system',
            ]);
        }

        return redirect()->back()->with('success', 'เลื่อนชั้นสำเร็จ ' . count($request->student_ids) . ' คน');
    }

    // เปิดเทอม 2: คัดลอกห้อง+แผนการเรียนจากเทอม 1 ไปสร้างเป็นห้องใหม่ในเทอม 2 ของปีการศึกษาเดียวกัน
    // ไม่คัดลอกตารางสอน (teaching_assigns/timetable_slots) และไม่คัดลอกรายชื่อนักเรียน ตามที่ผู้ใช้ระบุไว้
    // (ห้องเทอม 2 จะว่างเปล่า รอจัดตารางสอน/ลงทะเบียนนักเรียนทีหลัง)
    public function openSemester2(Request $request)
    {
        $request->validate([
            'year_id' => 'required|exists:academic_years,year_id',
            'section_ids' => 'required|array',
        ]);

        $semester1 = Semester::where('year_id', $request->year_id)->where('semester_name', '1')->first();
        if (!$semester1) {
            return redirect()->back()->with('error', 'ไม่พบเทอม 1 ของปีการศึกษานี้');
        }

        $semester2 = Semester::firstOrCreate(
            ['year_id' => $request->year_id, 'semester_name' => '2']
        );

        $copied = 0;
        $skipped = 0;

        foreach ($request->section_ids as $sectionId) {
            $source = ClassSection::where('section_id', $sectionId)
                ->where('semester_id', $semester1->semester_id)
                ->first();
            if (!$source) continue;

            $alreadyExists = ClassSection::where('semester_id', $semester2->semester_id)
                ->where('level_id', $source->level_id)
                ->where('section_number', $source->section_number)
                ->exists();
            if ($alreadyExists) {
                $skipped++;
                continue;
            }

            ClassSection::create([
                'semester_id'         => $semester2->semester_id,
                'level_id'            => $source->level_id,
                'section_number'      => $source->section_number,
                'study_plan'          => $source->study_plan,
                'homeroom_teacher_id' => $source->homeroom_teacher_id,
                'max_students'        => $source->max_students,
                'curriculum_id'       => $source->curriculum_id,
                'lunch_start'         => $source->lunch_start,
                'lunch_end'           => $source->lunch_end,
            ]);
            $copied++;
        }

        $message = "เปิดเทอม 2 สำเร็จ คัดลอกห้อง {$copied} ห้อง";
        if ($skipped > 0) {
            $message .= " (ข้าม {$skipped} ห้องที่มีอยู่แล้วในเทอม 2)";
        }

        return redirect()->back()->with('success', $message);
    }

    // บันทึกสำเร็จการศึกษา
    public function graduate(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'from_section_id' => 'required|exists:class_sections,section_id',
        ]);

        foreach ($request->student_ids as $studentId) {
            // อัปเดตสถานะห้อง
            StudentSection::where('student_id', $studentId)
                ->where('section_id', $request->from_section_id)
                ->update(['status' => 'จบการศึกษา']);

            // อัปเดตสถานะนักเรียน
            Student::where('student_id', $studentId)->update(['status' => 'จำหน่าย']);

            Promotion::create([
                'student_id' => $studentId,
                'from_section_id' => $request->from_section_id,
                'to_section_id' => null,
                'promo_type' => 'บันทึกจบ',
                'promo_date' => now(),
                'remark' => $request->remark,
                'created_by' => Auth::user()->thai_firstname ?? 'system',
            ]);
        }

        return redirect()->back()->with('success', 'บันทึกจบการศึกษาสำเร็จ ' . count($request->student_ids) . ' คน');
    }

    // ประวัติการเลื่อนชั้น/ย้าย
    public function history(Request $request)
    {
        $query = Promotion::with(['student', 'fromSection.level', 'toSection.level']);

        if ($request->filled('student_id')) $query->where('student_id', $request->student_id);
        if ($request->filled('type')) $query->where('promo_type', $request->type);

        $promotions = $query->orderBy('promo_date', 'desc')->paginate(30);

        return view('academic.promotion_history', compact('promotions'));
    }
}