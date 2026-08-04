<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Curriculum;
use App\Models\Academic\CurriculumSubject;
use App\Models\Academic\Subject;
use App\Models\Academic\Level;
use App\Models\Academic\Program;
use App\Models\Academic\ClassSection;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
  // แก้ไขฟังก์ชันนี้
  public function index(Request $request)
    {
        // 1. ดึงปีการศึกษาทั้งหมดเพื่อนำไปโชว์ใน Dropdown
        $academicYears = \App\Models\Academic\AcademicYear::with('semesters')->orderBy('year_name', 'desc')->get();
        
        // 2. รับค่าปีที่เลือกมาจากหน้าเว็บ (ถ้าไม่มี ให้ดึงปีปัจจุบันมาเป็นค่าเริ่มต้น)
        $currentYearId = $request->year_id;
        if ($currentYearId === null) {
            $currentYearId = $academicYears->where('is_current', true)->first()->year_id ?? 'all';
        }
        
        $query = Curriculum::with('level');
        
        // 3. ถ้าไม่ได้เลือก "ดูทั้งหมด" ให้กรองหลักสูตรเฉพาะปีนั้นๆ
        if ($currentYearId !== 'all') {
            $selectedYear = $academicYears->where('year_id', $currentYearId)->first();
            if ($selectedYear) {
                // กรองจากคอลัมน์ year_applied ให้ตรงกับชื่อปีการศึกษา (เช่น 2568)
                $query->where('year_applied', $selectedYear->year_name);
            }
        }
        
        $curriculums = $query->orderBy('curriculum_id', 'desc')->paginate(20);
        
        return view('academic.curriculums', compact('curriculums', 'academicYears', 'currentYearId'));
    }

    public function byYear($year)
    {
        $curriculums = Curriculum::with(['level', 'curriculumSubjects'])
            ->where('year_applied', $year)
            ->orderBy('curriculum_id')->get();
        $levels = Level::orderBy('sort_order')->get();
        return view('academic.curriculum_by_year', compact('curriculums', 'year', 'levels'));
    }

    public function copy($id)
    {
        $original = Curriculum::with('curriculumSubjects')->findOrFail($id);
        $new = $original->replicate();
        $new->name = $original->name . ' (คัดลอก)';
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
        $yearApplied = $request->year_applied;
        if ($request->filled('program_id')) {
            $program = Program::find($request->program_id);
            if ($program) {
                // นับเฉพาะระดับที่ถูกใช้ไปแล้ว "ในปีการศึกษาเดียวกัน" เท่านั้น
                // (คนละปีสร้างระดับเดิมซ้ำได้ เช่น "EP ป.1" ปี 2568 กับปี 2569)
                $usedLevelIds = $program->curriculums()
                    ->when($yearApplied, fn ($q) => $q->where('year_applied', $yearApplied))
                    ->pluck('level_id');

                // โชว์รายการแผนที่มีอยู่แล้วในหลักสูตรนี้ตรงนี้เลย ไม่ต้องมีหน้าลิสต์แยกต่างหาก
                $existingPlans = $program->curriculums()->with('level')
                    ->orderByDesc('year_applied')->orderBy('level_id')->get();
                $sectionsByCurriculum = ClassSection::with('level')
                    ->whereIn('curriculum_id', $existingPlans->pluck('curriculum_id'))
                    ->get()->groupBy('curriculum_id');
            }
        }

        return view('academic.curriculum_form', compact(
            'levels', 'programs', 'program', 'usedLevelIds', 'yearApplied', 'existingPlans', 'sectionsByCurriculum'
        ));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        $cur = Curriculum::create($request->only(['name', 'level_id', 'year_applied', 'description']) + [
            'program_id' => $request->program_id ?: null,
        ]);
        return redirect()->route('curriculums.edit', $cur->curriculum_id)->with('success', 'สร้างหลักสูตรสำเร็จ');
    }

    public function edit($id)
    {
        $curriculum = Curriculum::with(['curriculumSubjects.subject', 'curriculumSubjects.personnel'])->findOrFail($id);
        $levels     = Level::orderBy('sort_order')->get();
        $programs   = Program::orderBy('name')->get();
        $subjects   = Subject::where('is_active', true)->orderBy('code')->get();
        $personnels = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();
        return view('academic.curriculum_form', compact('curriculum', 'levels', 'programs', 'subjects', 'personnels'));
    }

    public function update(Request $request, $id)
    {
        $cur = Curriculum::findOrFail($id);
        $cur->update($request->only(['name', 'level_id', 'year_applied', 'description']) + [
            'program_id' => $request->program_id ?: null,
        ]);
        return redirect()->back()->with('success', 'แก้ไขหลักสูตรสำเร็จ');
    }

    public function destroy($id)
    {
        Curriculum::findOrFail($id)->delete();
        return redirect()->route('curriculums.index')->with('success', 'ลบหลักสูตรสำเร็จ');
    }

    public function addSubject(Request $request, $id)
    {
        $request->validate(['subject_id' => 'required|exists:subjects,subject_id']);
        CurriculumSubject::firstOrCreate(
            ['curriculum_id' => $id, 'subject_id' => $request->subject_id],
            [
                'semester_type'  => $request->semester_type ?? 'both',
                'is_required'    => $request->boolean('is_required', true),
                'personnel_id'   => $request->personnel_id ?: null,
                'credits'        => $request->credits !== null && $request->credits !== '' ? $request->credits : null,
                'hours_per_year' => $request->hours_per_year !== null && $request->hours_per_year !== '' ? $request->hours_per_year : null,
                'hours_per_week' => $request->hours_per_week !== null && $request->hours_per_week !== '' ? $request->hours_per_week : null,
            ]
        );
        return redirect()->back()->with('success', 'เพิ่มวิชาในหลักสูตรสำเร็จ');
    }

    public function updateSubject(Request $request, $id, $csId)
    {
        CurriculumSubject::where('id', $csId)->where('curriculum_id', $id)
            ->update([
                'semester_type'  => $request->semester_type ?? 'both',
                'is_required'    => $request->boolean('is_required', true),
                'personnel_id'   => $request->personnel_id ?: null,
                'credits'        => $request->credits !== null && $request->credits !== '' ? $request->credits : null,
                'hours_per_year' => $request->hours_per_year !== null && $request->hours_per_year !== '' ? $request->hours_per_year : null,
                'hours_per_week' => $request->hours_per_week !== null && $request->hours_per_week !== '' ? $request->hours_per_week : null,
            ]);
        return redirect()->back()->with('success', 'แก้ไขวิชาสำเร็จ');
    }

   public function removeSubject($id, $csId)
    {
        CurriculumSubject::where('id', $csId)->where('curriculum_id', $id)->delete();
        return redirect()->back()->with('success', 'ลบวิชาออกจากหลักสูตรสำเร็จ');
    }

    // --- เพิ่มฟังก์ชันนี้ลงไปใหม่ ---
  
}