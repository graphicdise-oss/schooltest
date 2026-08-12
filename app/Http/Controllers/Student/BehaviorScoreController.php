<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\Semester;
use App\Models\Academic\StudentSection;
use App\Models\Student;
use App\Models\Student\BehaviorItem;
use App\Models\Student\BehaviorRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BehaviorScoreController extends Controller
{
    private function fullScore(): float
    {
        return (float) config('school.conduct_full_score', 100);
    }

    public function index(Request $request)
    {
        $year = AcademicYear::current() ?? AcademicYear::orderByDesc('year_name')->first();
        $semester = $year
            ? (Semester::where('year_id', $year->year_id)->where('is_current', true)->first()
                ?? Semester::where('year_id', $year->year_id)->orderByDesc('semester_name')->first())
            : null;

        $levels = Level::orderBy('sort_order')->get();
        $levelId = $request->get('level_id');
        $search = $request->get('search', '');

        $sections = collect();
        if ($levelId && $semester) {
            $sections = ClassSection::with('level')
                ->where('level_id', $levelId)
                ->where('semester_id', $semester->semester_id)
                ->orderBy('section_number')
                ->get();
        }

        $sectionId = $request->get('section_id');
        if (!$sectionId && $sections->isNotEmpty()) {
            $sectionId = $sections->first()->section_id;
        }

        $students = collect();
        $balances = collect();
        if ($sectionId) {
            $query = StudentSection::with('student')->where('section_id', $sectionId)->orderBy('student_number');

            if ($search) {
                $query->whereHas('student', fn($q) => $q
                    ->where('thai_firstname', 'like', "%{$search}%")
                    ->orWhere('thai_lastname', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%"));
            }

            $students = $query->get();

            $studentIds = $students->pluck('student_id');
            if ($studentIds->isNotEmpty() && $year && $semester) {
                $balances = BehaviorRecord::whereIn('student_id', $studentIds)
                    ->where('year_id', $year->year_id)
                    ->where('semester_id', $semester->semester_id)
                    ->selectRaw('student_id, SUM(signed_points) as total')
                    ->groupBy('student_id')
                    ->pluck('total', 'student_id');
            }
        }

        $selectedSection = $sectionId ? ClassSection::with('level')->find($sectionId) : null;

        $addItems = BehaviorItem::where('type', 'add')->orderBy('sort_order')->get();
        $deductItems = BehaviorItem::where('type', 'deduct')->orderBy('sort_order')->get();

        return view('student.behavior_score_index', compact(
            'levels',
            'sections',
            'levelId',
            'sectionId',
            'search',
            'students',
            'balances',
            'selectedSection',
            'year',
            'semester',
            'addItems',
            'deductItems'
        ) + ['fullScore' => $this->fullScore()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer|exists:students,student_id',
            'behavior_item_id' => 'required|integer|exists:behavior_items,behavior_item_id',
            'note' => 'nullable|string|max:1000',
        ]);

        $year = AcademicYear::current() ?? AcademicYear::orderByDesc('year_name')->first();
        $semester = $year
            ? (Semester::where('year_id', $year->year_id)->where('is_current', true)->first()
                ?? Semester::where('year_id', $year->year_id)->orderByDesc('semester_name')->first())
            : null;

        if (!$year || !$semester) {
            return redirect()->back()->with('error', 'กรุณาตั้งค่าปีการศึกษา/ภาคเรียนปัจจุบันก่อนบันทึกคะแนนความประพฤติ');
        }

        DB::transaction(function () use ($request, $year, $semester) {
            $item = BehaviorItem::findOrFail($request->behavior_item_id);

            $signedPoints = $item->type === 'deduct' ? -$item->points : (float) $item->points;

            // lockForUpdate ป้องกันสองคำขอพร้อมกัน (เช่น ครูสองคนกดบันทึกให้นักเรียนคนเดียวกันในเวลาไล่เลี่ยกัน)
            // อ่านผลรวมคนละค่ากันแล้วคำนวณ balance_after ทับกัน
            $priorSum = BehaviorRecord::where('student_id', $request->student_id)
                ->where('year_id', $year->year_id)
                ->where('semester_id', $semester->semester_id)
                ->lockForUpdate()
                ->sum('signed_points');

            $balanceAfter = $this->fullScore() + $priorSum + $signedPoints;

            BehaviorRecord::create([
                'student_id' => $request->student_id,
                'behavior_item_id' => $item->behavior_item_id,
                'type' => $item->type,
                'item_name_th' => $item->name_th,
                'points' => $item->points,
                'signed_points' => $signedPoints,
                'balance_after' => $balanceAfter,
                'year_id' => $year->year_id,
                'semester_id' => $semester->semester_id,
                'note' => $request->note,
                'recorded_by' => Auth::id(),
            ]);
        });

        return redirect()->back()->with('success', 'บันทึกคะแนนความประพฤติสำเร็จ');
    }

    public function history($studentId)
    {
        $student = Student::findOrFail($studentId);

        $records = BehaviorRecord::with(['year', 'semester'])
            ->where('student_id', $studentId)
            ->orderBy('created_at')
            ->paginate(20);

        return view('student.behavior_score_history', compact('student', 'records') + ['fullScore' => $this->fullScore()]);
    }
}
