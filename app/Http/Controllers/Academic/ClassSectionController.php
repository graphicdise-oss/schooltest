<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Academic\Curriculum;
use App\Models\Student;
use Illuminate\Http\Request;

class ClassSectionController extends Controller
{
    public function index(Request $request)
    {
        $semesters = Semester::with('academicYear')->orderedByRecency()->get();
        $levels = Level::orderBy('sort_order')->get();
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');

        $sections = ClassSection::with(['level', 'homeroomTeacher', 'studentSections', 'curriculum'])
            ->where('semester_id', $semesterId)
            ->get()
            ->sortBy([
                fn ($s) => $s->level->sort_order ?? 0,
                fn ($s) => $s->section_number,
            ])
            ->values();

        $curriculums = Curriculum::with('level')->where('is_active', true)
            ->orderBy('year_applied', 'desc')->orderBy('name')->get();

        // ปีของเทอมที่กำลังดูอยู่ — ใช้กรอง dropdown "แผนการเรียน" ให้ขึ้นเฉพาะแผนของปี+ระดับชั้นห้องนั้นจริงๆ
        $currentYearName = $semesters->firstWhere('semester_id', $semesterId)?->academicYear?->year_name;

        return view('academic.class_sections', compact('sections', 'semesters', 'levels', 'semesterId', 'curriculums', 'currentYearName'));
    }

    public function store(Request $request)
    {
        $request->validate(['semester_id' => 'required', 'level_id' => 'required', 'room_label' => 'required']);
        $levelName = Level::find($request->level_id)->name ?? null;
        $label = $this->stripLevelPrefix($request->room_label, $levelName);
        [$sectionNumber, $studyPlan] = $this->parseRoomLabel($label);
        if ($sectionNumber === null) {
            return redirect()->back()->withErrors(['room_label' => 'กรุณากรอกห้องที่เป็นตัวเลข เช่น 1 หรือ 2 วิทย์-คณิต']);
        }

        ClassSection::create($request->only(['semester_id', 'level_id', 'homeroom_teacher_id', 'max_students']) + [
            'section_number' => $sectionNumber,
            'study_plan' => $studyPlan,
            'curriculum_id' => $request->curriculum_id ?: null,
        ]);
        return redirect()->back()->with('success', 'เพิ่มห้องเรียนสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $section = ClassSection::findOrFail($id);
        $levelName = $section->level->name ?? null;
        $label = $this->stripLevelPrefix($request->room_label, $levelName);
        [$sectionNumber, $studyPlan] = $this->parseRoomLabel($label);
        if ($sectionNumber === null) {
            return redirect()->back()->withErrors(['room_label' => 'กรุณากรอกห้องที่เป็นตัวเลข เช่น 1 หรือ 2 วิทย์-คณิต']);
        }

        $section->update($request->only(['homeroom_teacher_id', 'max_students']) + [
            'section_number' => $sectionNumber,
            'study_plan' => $studyPlan,
            'curriculum_id' => $request->curriculum_id ?: null,
        ]);
        return redirect()->back()->with('success', 'แก้ไขสำเร็จ');
    }

    // ตัดคำนำหน้าชื่อระดับชั้นออก เช่น "ม.4/2 วิทย์-คณิต" -> "2 วิทย์-คณิต" (ถ้าไม่มีคำนำหน้าก็ปล่อยผ่าน)
    private function stripLevelPrefix(?string $label, ?string $levelName): string
    {
        $label = trim((string) $label);
        if ($levelName && str_starts_with($label, $levelName . '/')) {
            return trim(substr($label, strlen($levelName) + 1));
        }
        return $label;
    }

    // แยก "2 วิทย์-คณิต" -> เลขห้อง 2 + แผนการเรียน "วิทย์-คณิต" (เว้นแผนการเรียนได้ เช่น "2" เฉยๆ)
    private function parseRoomLabel(?string $label): array
    {
        $label = trim((string) $label);
        if (!preg_match('/^(\d+)\s*(.*)$/', $label, $m)) {
            return [null, null];
        }

        return [(int) $m[1], trim($m[2])];
    }

    public function destroy($id)
    {
        ClassSection::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบห้องเรียนสำเร็จ');
    }

    // หน้าจัดนักเรียนเข้าห้อง
  // หน้าจัดนักเรียนเข้าห้อง
// หน้าจัดนักเรียนเข้าห้อง
public function manageStudents($id)
{
    $section = ClassSection::with(['level', 'semester.academicYear', 'studentSections.student'])->findOrFail($id);
    $semesterId = $section->semester_id;

    // นักเรียนที่มีห้องในเทอมนี้แล้ว (ทุกห้อง)
    $alreadyAssignedIds = StudentSection::whereHas('classSection', function ($q) use ($semesterId) {
        $q->where('semester_id', $semesterId);
    })->pluck('student_id');

    // แสดงนักเรียนที่ยังไม่มีห้องในเทอมนี้
    $availableStudents = Student::where('status', 'กำลังศึกษา')
        ->whereNotIn('student_id', $alreadyAssignedIds)
        ->orderBy('thai_firstname')
        ->get();

    return view('academic.section_students', compact('section', 'availableStudents'));
}

    public function assignStudents(Request $request, $id)
    {
        $request->validate(['student_ids' => 'required|array']);
        $section = ClassSection::findOrFail($id);

        $lastNumber = StudentSection::where('section_id', $id)->max('student_number') ?? 0;

        foreach ($request->student_ids as $studentId) {
            $lastNumber++;
            StudentSection::firstOrCreate(
                ['student_id' => $studentId, 'section_id' => $id],
                ['student_number' => $lastNumber, 'status' => 'กำลังศึกษา']
            );
        }

        return redirect()->back()->with('success', 'จัดนักเรียนเข้าห้องสำเร็จ');
    }

    public function removeStudent($id, $ssId)
    {
        StudentSection::where('id', $ssId)->where('section_id', $id)->delete();
        return redirect()->back()->with('success', 'นำนักเรียนออกจากห้องสำเร็จ');
    }

    public function renumberStudents($id)
    {
        $students = StudentSection::where('section_id', $id)
            ->join('students', 'student_sections.student_id', '=', 'students.student_id')
            ->orderByRaw('students.enroll_date ASC NULLS LAST')
            ->orderBy('students.created_at')
            ->select('student_sections.*')
            ->get();

        foreach ($students as $i => $ss) {
            $ss->update(['student_number' => $i + 1]);
        }

        return redirect()->back()->with('success', 'เรียงเลขที่นักเรียนใหม่สำเร็จ');
    }

    public function copyToTerm2(Request $request)
        {
            $request->validate([
                'from_semester_id' => 'required|exists:semesters,semester_id',
            ]);

            $fromSemester = Semester::with('academicYear')->findOrFail($request->from_semester_id);

            // หาเทอม 2 ของปีเดียวกัน
            $toSemester = Semester::where('year_id', $fromSemester->year_id)
                ->where('semester_name', '2')
                ->first();

            // ถ้ายังไม่มีเทอม 2 ให้สร้างอัตโนมัติ
            if (!$toSemester) {
                $toSemester = Semester::create([
                    'year_id'       => $fromSemester->year_id,
                    'semester_name' => '2',
                    'is_current'    => false,
                ]);
            }

            $fromSections = ClassSection::with('studentSections')
                ->where('semester_id', $fromSemester->semester_id)
                ->get();

            $count = 0;
            foreach ($fromSections as $sec) {
                $exists = ClassSection::where('semester_id', $toSemester->semester_id)
                    ->where('level_id', $sec->level_id)
                    ->where('section_number', $sec->section_number)
                    ->exists();

                if (!$exists) {
                    $newSection = ClassSection::create([
                        'semester_id'         => $toSemester->semester_id,
                        'level_id'            => $sec->level_id,
                        'section_number'      => $sec->section_number,
                        'homeroom_teacher_id' => $sec->homeroom_teacher_id,
                        'max_students'        => $sec->max_students,
                        'curriculum_id'       => $sec->curriculum_id,
                    ]);

                    // ย้ายนักเรียนเข้าห้องใหม่
                    foreach ($sec->studentSections->where('status', 'กำลังศึกษา') as $ss) {
                        StudentSection::firstOrCreate(
                            ['student_id' => $ss->student_id, 'section_id' => $newSection->section_id],
                            ['student_number' => $ss->student_number, 'status' => 'กำลังศึกษา']
                        );
                    }
                    $count++;
                }
            }

            return redirect()->back()->with('success',
                "เปิดภาคเรียนที่ 2 ปี {$fromSemester->academicYear->year_name} สำเร็จ สร้าง {$count} ห้องเรียน"
            );
        }
}