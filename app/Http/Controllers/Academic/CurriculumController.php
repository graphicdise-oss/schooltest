<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Curriculum;
use App\Models\Academic\CurriculumSubject;
use App\Models\Academic\Subject;
use App\Models\Academic\Level;
use App\Models\Academic\Program;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Semester;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class CurriculumController extends Controller
{
    // ตรวจ return_to ที่ส่งมาจากหน้าที่ผู้ใช้มาจริงๆ (เช่น /programs หรือ /programs/{id}/plans) ก่อนเอาไปใช้
    // เป็นปลายทางของปุ่ม "ย้อนกลับ" — รับเฉพาะ URL ของแอปเราเอง กัน open redirect ไปโดเมนอื่น
    private function sanitizeReturnTo(?string $url): ?string
    {
        if (!$url) {
            return null;
        }
        return str_starts_with($url, url('/')) ? $url : null;
    }

    public function byYear($year)
    {
        $curriculums = Curriculum::with(['level', 'curriculumSubjects'])
            ->where('year_applied', $year)
            ->orderBy('curriculum_id')->get();
        $levels = Level::orderBy('sort_order')->get();
        return view('academic.curriculum_by_year', compact('curriculums', 'year', 'levels'));
    }

    public function copy(Request $request, $id)
    {
        $original = Curriculum::with('curriculumSubjects')->findOrFail($id);
        $new = $original->replicate();

        // ถ้าระบุปีการศึกษาใหม่มา (เช่น คัดลอกไปปีหน้า) ให้ใช้ปีนั้นแทน คงชื่อเดิมไว้ (คนละปีชื่อซ้ำกันได้)
        // ถ้าไม่ได้ระบุปี ถือว่าคัดลอกในปีเดิม ต้องเติม "(คัดลอก)" กันชื่อซ้ำกันเป๊ะๆ ในปีเดียวกัน
        $targetYear = trim((string) $request->input('year_applied', ''));
        $copyingToNewYear = $targetYear !== '' && $targetYear !== $original->year_applied;
        if ($copyingToNewYear) {
            $new->year_applied = $targetYear;
        } else {
            $new->name = $original->name . ' (คัดลอก)';
        }
        $new->save();

        foreach ($original->curriculumSubjects as $cs) {
            $new->curriculumSubjects()->create([
                'subject_id'     => $cs->subject_id,
                'semester_type'  => $cs->semester_type,
                'is_required'    => $cs->is_required,
                'personnel_id'   => $cs->personnel_id,
                'credits'        => $cs->credits,
                'hours_per_year' => $cs->hours_per_year,
                'hours_per_week' => $cs->hours_per_week,
            ]);
        }

        if ($copyingToNewYear) {
            $returnTo = $this->sanitizeReturnTo($request->input('return_to'));
            return redirect()->route('curriculums.edit', array_filter(['id' => $new->curriculum_id, 'return_to' => $returnTo]))
                ->with('success', "คัดลอกแผนการเรียนไปปีการศึกษา {$targetYear} สำเร็จ");
        }
        return redirect()->back()->with('success', 'คัดลอกแผนการเรียนสำเร็จ');
    }

    public function create(Request $request)
    {
        $levels = Level::orderBy('sort_order')->get();
        $programs = Program::orderBy('name')->get();

        $program = null;
        $usedLevelIds = collect();
        $existingPlans = collect();
        $sectionsByCurriculum = collect();
        // จำหน้าที่ผู้ใช้กดมาจริงๆ ไว้ (เช่น /programs หรือ /programs/{id}/plans) ให้ปุ่ม "ย้อนกลับ" พาไปที่นั่น
        // แทนที่จะเดาว่าน่าจะมาจากไหน — ส่งต่อผ่านฟอร์มเป็นทอดๆ ไปจนถึงหน้าแก้ไขด้วย
        $returnTo = $this->sanitizeReturnTo($request->query('return_to'));
        // ถ้าไม่ได้ส่งปีมากับลิงก์ (เช่น มาจากหน้าที่ยังไม่ได้เลือกปีไว้) ให้ใช้ปีการศึกษาปัจจุบันเป็นค่าเริ่มต้นแทน
        // กันไม่ให้ต้องพิมพ์ปีเองทุกครั้ง — ถ้ายังไม่มีปีปัจจุบันตั้งไว้เลย ก็ปล่อยว่างให้พิมพ์เองเหมือนเดิม
        $yearApplied = $request->year_applied ?: AcademicYear::where('is_current', true)->value('year_name');
        if ($request->filled('program_id')) {
            $program = Program::find($request->program_id);
            if ($program) {
                // นับเฉพาะระดับที่ถูกใช้ไปแล้ว "ในปีการศึกษาเดียวกัน" เท่านั้น
                // (คนละปีสร้างระดับเดิมซ้ำได้ เช่น "EP ป.1" ปี 2568 กับปี 2569)
                $usedLevelIds = $program->curriculums()
                    ->when($yearApplied, fn ($q) => $q->where('year_applied', $yearApplied))
                    ->pluck('level_id');

                // โชว์รายการแผนที่มีอยู่แล้วในหลักสูตรนี้ตรงนี้เลย ไม่ต้องมีหน้าลิสต์แยกต่างหาก
                // กรองเฉพาะปีที่กำลังจะสร้างแผนนี้ (กันงงว่าทำไมมีปีอื่นโผล่มาปนด้วย) — ถ้าอยากดูแผนทุกปี
                // ของหลักสูตรนี้ ไปดูได้ที่หน้า "แผน" ของหลักสูตร (ไม่กรองปี อยู่แล้วโดยตั้งใจ)
                $existingPlans = $program->curriculums()->with('level')
                    ->when($yearApplied, fn ($q) => $q->where('year_applied', $yearApplied))
                    ->orderByDesc('year_applied')->orderBy('level_id')->get();
                $sectionsByCurriculum = ClassSection::with('level')
                    ->whereIn('curriculum_id', $existingPlans->pluck('curriculum_id'))
                    ->get()->groupBy('curriculum_id');
            }
        }

        return view('academic.curriculum_form', compact(
            'levels', 'programs', 'program', 'usedLevelIds', 'yearApplied', 'existingPlans', 'sectionsByCurriculum', 'returnTo'
        ));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        $cur = Curriculum::create($request->only(['name', 'level_id', 'year_applied', 'description']) + [
            'program_id' => $request->program_id ?: null,
        ]);
        $returnTo = $this->sanitizeReturnTo($request->input('return_to'));
        return redirect()->route('curriculums.edit', array_filter(['id' => $cur->curriculum_id, 'return_to' => $returnTo]))
            ->with('success', 'สร้างหลักสูตรสำเร็จ');
    }

    public function edit(Request $request, $id)
    {
        $curriculum = Curriculum::with(['curriculumSubjects.subject', 'curriculumSubjects.personnel', 'curriculumSubjects.teachers'])->findOrFail($id);
        $levels     = Level::orderBy('sort_order')->get();
        $programs   = Program::orderBy('name')->get();
        $subjects   = Subject::where('is_active', true)->orderBy('code')->get();
        $personnels = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();
        $returnTo   = $this->sanitizeReturnTo($request->query('return_to'));
        // เผื่อไม่มี return_to ส่งมา (เช่น กด "แก้ไข" จากที่อื่น) ใช้หลักสูตรของแผนนี้เอง คำนวณปลายทาง
        // fallback ของปุ่ม "ย้อนกลับ" ให้ถูกต้อง (พาไปหน้า "แผน" ของหลักสูตรนั้น ไม่ใช่ /programs เฉยๆ)
        $program    = $curriculum->program_id ? Program::find($curriculum->program_id) : null;
        return view('academic.curriculum_form', compact('curriculum', 'levels', 'programs', 'program', 'subjects', 'personnels', 'returnTo'));
    }

    public function update(Request $request, $id)
    {
        $cur = Curriculum::findOrFail($id);
        $cur->update($request->only(['name', 'level_id', 'year_applied', 'description']) + [
            'program_id' => $request->program_id ?: null,
        ]);
        return redirect()->back()->with('success', 'แก้ไขหลักสูตรสำเร็จ');
    }

    public function destroy(Request $request, $id)
    {
        Curriculum::findOrFail($id)->delete();
        $returnTo = $this->sanitizeReturnTo($request->input('return_to'));
        return ($returnTo ? redirect($returnTo) : redirect()->back())->with('success', 'ลบหลักสูตรสำเร็จ');
    }

    // ห้องเรียนของแผนนี้ (เช่น ม.4 -> ม.4/1, ม.4/2, ...) — คลิกจากหน้า "แผนของหลักสูตร" เข้ามาดู/เพิ่มห้อง
    public function sections(Request $request, $id)
    {
        $curriculum = Curriculum::with(['level', 'program'])->findOrFail($id);
        $semesters  = Semester::with('academicYear')->orderedByRecency()->get();
        $teachers   = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');

        $sections = ClassSection::with(['level', 'homeroomTeacher', 'semester.academicYear', 'studentSections'])
            ->where('curriculum_id', $id)
            ->get()
            ->sortBy([
                fn ($s) => -$s->semester_id,
                fn ($s) => $s->section_number,
            ])
            ->values();

        $returnTo = $this->sanitizeReturnTo($request->query('return_to'))
            ?? ($curriculum->program_id ? route('programs.plans', $curriculum->program_id) : route('programs.index'));

        return view('academic.curriculum_sections', compact(
            'curriculum', 'sections', 'semesters', 'teachers', 'semesterId', 'returnTo'
        ));
    }

    // อ่าน personnel_ids[] จากฟอร์ม (สูงสุด 3 ช่องจากหน้าเว็บ) กรองค่าว่าง/ซ้ำออก
    private function teacherIdsFromRequest(Request $request): array
    {
        return collect($request->input('personnel_ids', []))
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
    }

    public function addSubject(Request $request, $id)
    {
        $request->validate(['subject_id' => 'required|exists:subjects,subject_id']);
        $teacherIds = $this->teacherIdsFromRequest($request);
        $cs = CurriculumSubject::firstOrCreate(
            ['curriculum_id' => $id, 'subject_id' => $request->subject_id],
            [
                'semester_type'  => $request->semester_type ?? 'both',
                'is_required'    => $request->boolean('is_required', true),
                'personnel_id'   => $teacherIds[0] ?? null,
                'credits'        => $request->credits !== null && $request->credits !== '' ? $request->credits : null,
                'hours_per_year' => $request->hours_per_year !== null && $request->hours_per_year !== '' ? $request->hours_per_year : null,
                'hours_per_week' => $request->hours_per_week !== null && $request->hours_per_week !== '' ? $request->hours_per_week : null,
            ]
        );
        $cs->teachers()->sync($teacherIds);
        return redirect()->back()->with('success', 'เพิ่มวิชาในหลักสูตรสำเร็จ');
    }

    public function updateSubject(Request $request, $id, $csId)
    {
        $cs = CurriculumSubject::where('id', $csId)->where('curriculum_id', $id)->firstOrFail();
        $teacherIds = $this->teacherIdsFromRequest($request);
        $cs->update([
            'semester_type'  => $request->semester_type ?? 'both',
            'is_required'    => $request->boolean('is_required', true),
            'personnel_id'   => $teacherIds[0] ?? null,
            'credits'        => $request->credits !== null && $request->credits !== '' ? $request->credits : null,
            'hours_per_year' => $request->hours_per_year !== null && $request->hours_per_year !== '' ? $request->hours_per_year : null,
            'hours_per_week' => $request->hours_per_week !== null && $request->hours_per_week !== '' ? $request->hours_per_week : null,
        ]);
        $cs->teachers()->sync($teacherIds);
        return redirect()->back()->with('success', 'แก้ไขวิชาสำเร็จ');
    }

   public function removeSubject($id, $csId)
    {
        CurriculumSubject::where('id', $csId)->where('curriculum_id', $id)->delete();
        return redirect()->back()->with('success', 'ลบวิชาออกจากหลักสูตรสำเร็จ');
    }

    // เปิด/ปิดใช้งานวิชานี้เฉพาะในแผนนี้ (ไม่กระทบวิชากลางหรือแผนอื่น) — ปิดแล้วจะไม่ถูกเสนอเป็นตัวเลือกตอนจัดตารางสอน
    public function toggleSubject($id, $csId)
    {
        $cs = CurriculumSubject::where('id', $csId)->where('curriculum_id', $id)->firstOrFail();
        $cs->update(['is_active' => !$cs->is_active]);
        return redirect()->back()->with('success', $cs->is_active ? 'เปิดใช้งานวิชาแล้ว' : 'ปิดใช้งานวิชาแล้ว');
    }

    // นำเข้ารายวิชา (พร้อมครูผู้สอนหลายคน จับคู่ด้วยเลขบัตรประชาชน) เข้าแผนนี้จากไฟล์ Excel รูปแบบ PlanCourses
    public function importSubjects(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ], [
            'file.required' => 'กรุณาเลือกไฟล์ Excel',
            'file.mimes' => 'ไฟล์ต้องเป็นสกุล .xlsx เท่านั้น',
        ]);

        set_time_limit(0);

        $path = $request->file('file')->store('imports');
        $fullPath = Storage::path($path);

        $options = ['curriculum_id' => $id, 'file' => $fullPath];
        if ($request->boolean('dry_run')) {
            $options['--dry-run'] = true;
        }

        Artisan::call('import:curriculum-plan', $options);
        $output = Artisan::output();

        @unlink($fullPath);

        return back()->with('curriculum_import_output', $output);
    }
}