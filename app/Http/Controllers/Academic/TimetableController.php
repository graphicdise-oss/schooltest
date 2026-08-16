<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\TeachingAssign;
use App\Models\Academic\TimetableSlot;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Subject;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Academic\Curriculum;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $levels     = Level::orderBy('sort_order')->get();
        $semesters  = Semester::with('academicYear')->orderedByRecency()->get();

        $query = ClassSection::with(['level', 'homeroomTeacher', 'teachingAssigns.timetableSlots'])
            ->where('semester_id', $semesterId)
            ->orderBy('level_id')->orderBy('section_number');

        if ($request->filled('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        $sections        = $query->get();
        $currentSemester = $semesters->firstWhere('semester_id', $semesterId);

        return view('academic.timetable_index', compact('sections', 'semesters', 'levels', 'semesterId', 'currentSemester'));
    }

    public function storeAssign(Request $request)
    {
        $request->validate(['personnel_id' => 'required', 'subject_id' => 'required', 'section_id' => 'required', 'semester_id' => 'required']);
        TeachingAssign::firstOrCreate($request->only(['personnel_id', 'subject_id', 'section_id', 'semester_id']));
        return redirect()->back()->with('success', 'มอบหมายการสอนสำเร็จ');
    }

    public function destroyAssign($id)
    {
        TeachingAssign::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบการมอบหมายสำเร็จ');
    }

    public function storeSlot(Request $request)
    {
        $request->validate([
            'assign_id'   => 'required',
            'day_of_week' => 'required',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        $assign = TeachingAssign::findOrFail($request->assign_id);
        $conflict = $this->findScheduleConflict($assign->section_id, $request->day_of_week, $request->start_time, $request->end_time);
        if ($conflict) {
            return redirect()->back()->with('error', $conflict)->withInput();
        }

        TimetableSlot::create($request->only(['assign_id', 'day_of_week', 'start_time', 'end_time', 'room']));
        return redirect()->back()->with('success', 'เพิ่มคาบเรียนสำเร็จ');
    }

    public function updateSlot(Request $request, $id)
    {
        $request->validate([
            'day_of_week' => 'required',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        $slot = TimetableSlot::findOrFail($id);
        $conflict = $this->findScheduleConflict($slot->teachingAssign->section_id, $request->day_of_week, $request->start_time, $request->end_time, $slot->slot_id);
        if ($conflict) {
            return redirect()->back()->with('error', $conflict)->withInput();
        }

        $slot->update($request->only(['day_of_week', 'start_time', 'end_time', 'room']));
        return redirect()->back()->with('success', 'แก้ไขคาบเรียนสำเร็จ');
    }

    // ตรวจสอบว่าคาบเรียนใหม่ (วัน+ช่วงเวลา) ชนกับคาบอื่นของห้องเดียวกัน หรือชนเวลาพักกลางวันหรือไม่
    // คืนค่าข้อความ error ถ้าชน หรือ null ถ้าไม่ชน — ไม่ยึดกริด 30 นาทีอีกต่อไป กำหนดเวลาอะไรก็ได้ขอแค่ไม่ทับกัน
    private function findScheduleConflict(int $sectionId, string $day, string $start, string $end, ?int $excludeSlotId = null): ?string
    {
        $newStart = \Carbon\Carbon::createFromFormat('H:i', $start);
        $newEnd   = \Carbon\Carbon::createFromFormat('H:i', $end);

        $existingSlots = TimetableSlot::with(['teachingAssign.subject'])
            ->whereHas('teachingAssign', fn($q) => $q->where('section_id', $sectionId))
            ->where('day_of_week', $day)
            ->when($excludeSlotId, fn($q) => $q->where('slot_id', '!=', $excludeSlotId))
            ->get();

        foreach ($existingSlots as $existing) {
            $exStart = \Carbon\Carbon::parse($existing->start_time);
            $exEnd   = \Carbon\Carbon::parse($existing->end_time);
            if ($newStart->lt($exEnd) && $newEnd->gt($exStart)) {
                $subjectName = $existing->teachingAssign->subject->name_th ?? 'วิชาอื่น';
                return "เวลาชนกับวิชา \"{$subjectName}\" ({$exStart->format('H:i')}-{$exEnd->format('H:i')} วัน{$day})";
            }
        }

        $section = ClassSection::find($sectionId);
        $lunchStart = $section?->lunch_start ? substr($section->lunch_start, 0, 5) : config('school.lunch_start', '12:00');
        $lunchEnd   = $section?->lunch_end   ? substr($section->lunch_end, 0, 5)   : config('school.lunch_end', '13:00');
        if ($lunchStart && $lunchEnd) {
            $lStart = \Carbon\Carbon::createFromFormat('H:i', $lunchStart);
            $lEnd   = \Carbon\Carbon::createFromFormat('H:i', $lunchEnd);
            if ($newStart->lt($lEnd) && $newEnd->gt($lStart)) {
                return "เวลาชนกับพักกลางวัน ({$lStart->format('H:i')}-{$lEnd->format('H:i')})";
            }
        }

        return null;
    }

    public function destroySlot($id)
    {
        TimetableSlot::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบคาบเรียนสำเร็จ');
    }

    // แสดงตารางสอนแบบตาราง (วัน x คาบ)
    public function viewTimetable(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $sectionId = $request->section_id;
        $teacherId = $request->teacher_id;

        $query = TimetableSlot::with(['teachingAssign.personnel', 'teachingAssign.subject', 'teachingAssign.classSection.level'])
            ->whereHas('teachingAssign', fn($q) => $q->where('semester_id', $semesterId));

        if ($sectionId) $query->whereHas('teachingAssign', fn($q) => $q->where('section_id', $sectionId));
        if ($teacherId) $query->whereHas('teachingAssign', fn($q) => $q->where('personnel_id', $teacherId));

        $slots = $query->orderBy('start_time')->get();

        $days = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
        $sections = ClassSection::with('level')->where('semester_id', $semesterId)->orderBy('level_id')->orderBy('section_number')->get();
        $teachers = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();
        $semesters = Semester::with('academicYear')->orderedByRecency()->get();

        return view('academic.timetable_view', compact('slots', 'days', 'sections', 'teachers', 'semesters', 'semesterId', 'sectionId', 'teacherId'));
    }

    public function sectionView($sectionId)
{
    $section = ClassSection::with(['level', 'semester.academicYear', 'homeroomTeacher', 'curriculum'])->findOrFail($sectionId);

    $assigns = TeachingAssign::with(['personnel', 'subject', 'timetableSlots'])
        ->where('section_id', $sectionId)
        ->where('semester_id', $section->semester_id)
        ->get();

    $teachers = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();
    $subjects = Subject::where('is_active', true)->orderBy('code')->get();
    $days     = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'];

    $dayBlocks = $this->buildDayBlocks($section, $assigns, $days);

    $curriculums = Curriculum::with([
            'curriculumSubjects' => fn ($q) => $q->where('is_active', true),
            'curriculumSubjects.subject',
            'curriculumSubjects.personnel',
        ])
        ->where('level_id', $section->level_id)
        ->where('is_active', true)
        ->orderBy('year_applied', 'desc')
        ->get();

    $lunchStart = $section->lunch_start ? substr($section->lunch_start, 0, 5) : config('school.lunch_start', '12:00');
    $lunchEnd   = $section->lunch_end   ? substr($section->lunch_end, 0, 5)   : config('school.lunch_end', '13:00');

    return view('academic.timetable_section', compact(
        'section', 'assigns', 'teachers', 'subjects', 'days', 'dayBlocks', 'curriculums',
        'lunchStart', 'lunchEnd'
    ));
}

public function updateLunch(Request $request, $sectionId)
{
    $section = ClassSection::findOrFail($sectionId);

    $data = $request->validate([
        'lunch_start' => ['nullable', 'date_format:H:i'],
        'lunch_end'   => ['nullable', 'date_format:H:i', 'after:lunch_start'],
    ]);

    if (!empty($data['lunch_start']) && !empty($data['lunch_end'])) {
        $lStart = \Carbon\Carbon::createFromFormat('H:i', $data['lunch_start']);
        $lEnd   = \Carbon\Carbon::createFromFormat('H:i', $data['lunch_end']);
        $existingSlots = TimetableSlot::with(['teachingAssign.subject'])
            ->whereHas('teachingAssign', fn($q) => $q->where('section_id', $sectionId))
            ->get();
        foreach ($existingSlots as $existing) {
            $exStart = \Carbon\Carbon::parse($existing->start_time);
            $exEnd   = \Carbon\Carbon::parse($existing->end_time);
            if ($lStart->lt($exEnd) && $lEnd->gt($exStart)) {
                $subjectName = $existing->teachingAssign->subject->name_th ?? 'วิชาอื่น';
                return back()->with('error', "เวลาพักกลางวันชนกับวิชา \"{$subjectName}\" ({$exStart->format('H:i')}-{$exEnd->format('H:i')} วัน{$existing->day_of_week}) กรุณาย้ายคาบนั้นก่อน")->withInput();
            }
        }
    }

    $section->update([
        'lunch_start' => $data['lunch_start'] ?? null,
        'lunch_end'   => $data['lunch_end'] ?? null,
    ]);

    return back()->with('success', 'ตั้งค่าเวลาพักกลางวันเรียบร้อยแล้ว');
}

    public function print($sectionId)
    {
        $section = ClassSection::with(['level', 'semester.academicYear', 'homeroomTeacher'])->findOrFail($sectionId);

        $assigns = TeachingAssign::with(['personnel', 'subject', 'timetableSlots'])
            ->where('section_id', $sectionId)
            ->where('semester_id', $section->semester_id)
            ->get();

        $days = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'];

        $dayBlocks = $this->buildDayBlocks($section, $assigns, $days);

        return view('academic.timetable_print', compact('section', 'assigns', 'days', 'dayBlocks'));
    }

    // สร้างรายการคาบเรียนต่อวัน เรียงตามเวลาเริ่มจริง (ไม่ยึดกริด 30 นาที) พร้อมแทรกพักกลางวันตามตำแหน่งเวลา
    private function buildDayBlocks(ClassSection $section, $assigns, array $days): array
    {
        $dayBlocks = [];
        foreach ($days as $day) $dayBlocks[$day] = collect();

        foreach ($assigns as $assign) {
            foreach ($assign->timetableSlots as $slot) {
                if (!isset($dayBlocks[$slot->day_of_week])) continue;
                $dayBlocks[$slot->day_of_week]->push((object) [
                    'type'   => 'period',
                    'start'  => \Carbon\Carbon::parse($slot->start_time),
                    'end'    => \Carbon\Carbon::parse($slot->end_time),
                    'slot'   => $slot,
                    'assign' => $assign,
                ]);
            }
        }

        $lunchStart = $section->lunch_start ? substr($section->lunch_start, 0, 5) : config('school.lunch_start', '12:00');
        $lunchEnd   = $section->lunch_end   ? substr($section->lunch_end, 0, 5)   : config('school.lunch_end', '13:00');
        if ($lunchStart && $lunchEnd) {
            $lStart = \Carbon\Carbon::createFromFormat('H:i', $lunchStart);
            $lEnd   = \Carbon\Carbon::createFromFormat('H:i', $lunchEnd);
            foreach ($days as $day) {
                $dayBlocks[$day]->push((object) ['type' => 'lunch', 'start' => $lStart, 'end' => $lEnd]);
            }
        }

        foreach ($days as $day) {
            $dayBlocks[$day] = $dayBlocks[$day]->sortBy(fn($b) => $b->start->format('H:i:s'))->values();
        }

        return $dayBlocks;
    }

    public function clearSection($sectionId)
    {
        $assignIds = TeachingAssign::where('section_id', $sectionId)->pluck('assign_id');
        TimetableSlot::whereIn('assign_id', $assignIds)->delete();
        return redirect()->back()->with('success', 'ล้างข้อมูลตารางสอนสำเร็จ');
    }

    public function importCurriculum(Request $request, $sectionId)
    {
        $section = ClassSection::findOrFail($sectionId);
        $personnelIds = $request->input('personnel_ids', []);

        if ($request->curriculum_id) {
            $section->update(['curriculum_id' => $request->curriculum_id]);
        }

        // ลบวิชาที่ไม่อยู่ในแผนใหม่ออก
        $newSubjectIds = array_keys($personnelIds);
        $removeAssigns = TeachingAssign::where('section_id', $sectionId)
            ->where('semester_id', $section->semester_id)
            ->whereNotIn('subject_id', $newSubjectIds)
            ->pluck('assign_id');
        TimetableSlot::whereIn('assign_id', $removeAssigns)->delete();
        TeachingAssign::whereIn('assign_id', $removeAssigns)->delete();

        // เพิ่มวิชาใหม่ที่ยังไม่มี หรืออัปเดตครูถ้ามีอยู่แล้ว
        foreach ($personnelIds as $subjectId => $personnelId) {
            if (!$personnelId) continue;
            TeachingAssign::firstOrCreate([
                'subject_id'  => $subjectId,
                'section_id'  => $sectionId,
                'semester_id' => $section->semester_id,
            ], ['personnel_id' => $personnelId]);
        }

        return redirect()->back()->with('success', 'นำเข้าวิชาจากแผนการเรียนสำเร็จ');
    }

    public function setCurriculum(Request $request, $sectionId)
    {
        ClassSection::findOrFail($sectionId)->update([
            'curriculum_id' => $request->curriculum_id ?: null
        ]);
        return redirect()->back()->with('success', 'เปลี่ยนแผนการเรียนสำเร็จ');
    }
}