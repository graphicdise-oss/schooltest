<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Curriculum;
use App\Models\Academic\CurriculumSubject;
use App\Models\Academic\Subject;
use App\Models\Academic\Level;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    public function index()
    {
        $years = Curriculum::selectRaw('year_applied, count(*) as total')
            ->whereNotNull('year_applied')
            ->groupBy('year_applied')
            ->orderByDesc('year_applied')
            ->get();
        return view('academic.curriculums', compact('years'));
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
                'subject_id'    => $cs->subject_id,
                'semester_type' => $cs->semester_type,
                'is_required'   => $cs->is_required,
            ]);
        }
        return redirect()->back()->with('success', 'คัดลอกแผนการเรียนสำเร็จ');
    }

    public function create()
    {
        $levels = Level::orderBy('sort_order')->get();
        return view('academic.curriculum_form', compact('levels'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        $cur = Curriculum::create($request->only(['name', 'level_id', 'year_applied', 'description']));
        return redirect()->route('curriculums.edit', $cur->curriculum_id)->with('success', 'สร้างหลักสูตรสำเร็จ');
    }

    public function edit($id)
    {
        $curriculum = Curriculum::with(['curriculumSubjects.subject'])->findOrFail($id);
        $levels = Level::orderBy('sort_order')->get();
        $subjects = Subject::where('is_active', true)->orderBy('code')->get();
        return view('academic.curriculum_form', compact('curriculum', 'levels', 'subjects'));
    }

    public function update(Request $request, $id)
    {
        $cur = Curriculum::findOrFail($id);
        $cur->update($request->only(['name', 'level_id', 'year_applied', 'description']));
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
            ['semester_type' => $request->semester_type ?? 'both', 'is_required' => $request->boolean('is_required', true)]
        );
        return redirect()->back()->with('success', 'เพิ่มวิชาในหลักสูตรสำเร็จ');
    }

    public function removeSubject($id, $csId)
    {
        CurriculumSubject::where('id', $csId)->where('curriculum_id', $id)->delete();
        return redirect()->back()->with('success', 'ลบวิชาออกจากหลักสูตรสำเร็จ');
    }
}