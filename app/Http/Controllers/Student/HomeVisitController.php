<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\Semester;
use App\Models\Academic\StudentSection;
use App\Models\HomeVisit;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HomeVisitController extends Controller
{
    // ===== บันทึกการเยี่ยมบ้านรายบุคคล =====
    public function index(Request $request)
    {
        $studentId = $request->get('student_id');
        $student = null;
        $currentSection = null;
        $visits = collect();

        if ($studentId) {
            $student = Student::find($studentId);
            if ($student) {
                $currentSection = StudentSection::with('classSection.level')
                    ->where('student_id', $studentId)
                    ->where('status', 'กำลังศึกษา')
                    ->first();

                $visits = HomeVisit::with('personnel')
                    ->where('student_id', $studentId)
                    ->orderByDesc('visit_date')
                    ->get();
            }
        }

        return view('student.home_visit_index', compact('student', 'currentSection', 'visits'));
    }

    // ค้นหานักเรียนสำหรับเลือกมาบันทึกการเยี่ยมบ้าน (AJAX)
    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $students = Student::where(function ($qq) use ($q) {
                $qq->where('student_code', 'like', "%$q%")
                    ->orWhere('thai_firstname', 'like', "%$q%")
                    ->orWhere('thai_lastname', 'like', "%$q%");
            })
            ->limit(10)
            ->get(['student_id', 'student_code', 'thai_prefix', 'thai_firstname', 'thai_lastname']);

        return response()->json($students->map(fn($s) => [
            'id' => $s->student_id,
            'code' => $s->student_code,
            'name' => trim($s->thai_prefix . $s->thai_firstname . ' ' . $s->thai_lastname),
        ])->values());
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'visit_date' => 'required|date',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $data = $request->only([
            'student_id', 'visit_date', 'visit_no', 'family_status', 'economic_status',
            'house_condition', 'problems_found', 'suggestion',
        ]);
        $data['need_followup'] = $request->boolean('need_followup');
        $data['personnel_id'] = Auth::id();
        $data['created_by'] = Auth::user()->name ?? Auth::user()->employee_code ?? 'system';
        $data['visit_no'] = $data['visit_no'] ?: 1;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('home_visits', 'public');
        }

        HomeVisit::create($data);

        return redirect()->route('home-visits.index', ['student_id' => $request->student_id])
            ->with('success', 'บันทึกการเยี่ยมบ้านสำเร็จ');
    }

    public function destroy($id)
    {
        $visit = HomeVisit::findOrFail($id);
        $studentId = $visit->student_id;

        if ($visit->photo) {
            Storage::disk('public')->delete($visit->photo);
        }
        $visit->delete();

        return redirect()->route('home-visits.index', ['student_id' => $studentId])
            ->with('success', 'ลบข้อมูลการเยี่ยมบ้านสำเร็จ');
    }

    // ===== สรุปสถานะการเยี่ยมบ้าน (รายห้อง) =====
    public function statusSummary(Request $request)
    {
        [$academicYears, $yearId, $semesters, $semesterId, $levels, $levelId, $sections, $sectionId] =
            $this->resolveClassFilters($request);

        $students = collect();
        if ($sectionId) {
            $studentSections = StudentSection::with('student')
                ->where('section_id', $sectionId)
                ->orderBy('student_number')
                ->get();

            $studentIds = $studentSections->pluck('student_id');
            $semester = $semesterId ? Semester::find($semesterId) : null;

            $visitQuery = HomeVisit::whereIn('student_id', $studentIds);
            if ($semester?->start_date && $semester?->end_date) {
                // จำกัดเฉพาะการเยี่ยมในช่วงเทอมที่เลือก ไม่งั้นนักเรียนที่เยี่ยมไปแล้วในเทอมก่อนๆ
                // จะติดสถานะ "เยี่ยมแล้ว" ค้างอยู่ตลอดไปทุกเทอมถัดมา
                $visitQuery->whereBetween('visit_date', [$semester->start_date, $semester->end_date]);
            }

            $visitCounts = $visitQuery
                ->selectRaw('student_id, count(*) as total, max(visit_date) as last_visit')
                ->groupBy('student_id')
                ->get()
                ->keyBy('student_id');

            $students = $studentSections->map(function ($ss) use ($visitCounts) {
                $info = $visitCounts->get($ss->student_id);
                return [
                    'student_number' => $ss->student_number,
                    'student' => $ss->student,
                    'visit_count' => $info->total ?? 0,
                    'last_visit' => $info->last_visit ?? null,
                ];
            });
        }

        $visitedCount = $students->where('visit_count', '>', 0)->count();
        $totalCount = $students->count();

        return view('student.home_visit_status', compact(
            'academicYears', 'yearId', 'semesters', 'semesterId', 'levels', 'levelId',
            'sections', 'sectionId', 'students', 'visitedCount', 'totalCount'
        ));
    }

    // ===== สรุปผลการเยี่ยมบ้าน =====
    public function resultsSummary(Request $request)
    {
        [$academicYears, $yearId, $semesters, $semesterId, $levels, $levelId, $sections, $sectionId] =
            $this->resolveClassFilters($request, requireSection: false);

        $query = HomeVisit::with(['student', 'personnel']);

        if ($sectionId) {
            $studentIds = StudentSection::where('section_id', $sectionId)->pluck('student_id');
            $query->whereIn('student_id', $studentIds);
        } elseif ($levelId) {
            $sectionIds = ClassSection::real()->where('level_id', $levelId)->pluck('section_id');
            $studentIds = StudentSection::whereIn('section_id', $sectionIds)->pluck('student_id');
            $query->whereIn('student_id', $studentIds);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('visit_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('visit_date', '<=', $request->date_to);
        }
        if ($request->filled('economic_status')) {
            $query->where('economic_status', $request->economic_status);
        }
        if ($request->boolean('need_followup_only')) {
            $query->where('need_followup', true);
        }

        // ต้อง clone ก่อน orderBy/paginate เพราะทั้งสองจะ mutate $query เดิม (เติม limit/offset/orderBy ค้างไว้)
        // ถ้า clone หลังจากนั้น ผลรวมจะเพี้ยนตาม limit/offset ของหน้าที่เลือกดู แถม orderBy ที่ไม่ได้อยู่ใน
        // group by จะทำให้ query ผลรวมพังได้ในบางฐานข้อมูล
        $economicSummary = (clone $query)->selectRaw('economic_status, count(*) as total')
            ->groupBy('economic_status')
            ->pluck('total', 'economic_status');

        $followupCount = (clone $query)->where('need_followup', true)->count();

        $visits = $query->orderByDesc('visit_date')->paginate(20)->withQueryString();

        return view('student.home_visit_results', compact(
            'academicYears', 'yearId', 'semesters', 'semesterId', 'levels', 'levelId',
            'sections', 'sectionId', 'visits', 'economicSummary', 'followupCount'
        ));
    }

    private function resolveClassFilters(Request $request, bool $requireSection = true): array
    {
        $yearId = $request->get('year_id');
        $semesterId = $request->get('semester_id');
        $levelId = $request->get('level_id');
        $sectionId = $request->get('section_id');

        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $currentYear = AcademicYear::where('is_current', true)->first();
        if (!$yearId && $currentYear) {
            $yearId = $currentYear->year_id;
        }

        $semesters = $yearId
            ? Semester::where('year_id', $yearId)->orderBy('semester_name')->get()
            : collect();

        if (!$semesterId && $yearId) {
            $defaultSem = Semester::where('year_id', $yearId)->where('is_current', true)->first()
                ?? Semester::where('year_id', $yearId)->orderBy('semester_name')->first();
            $semesterId = $defaultSem?->semester_id;
        }

        $levels = Level::orderBy('sort_order')->get();

        $sections = collect();
        if ($levelId && $semesterId) {
            $sections = ClassSection::real()->with('level')
                ->where('level_id', $levelId)
                ->where('semester_id', $semesterId)
                ->orderBy('section_number')
                ->get();
        }

        if ($requireSection && !$sectionId && $sections->isNotEmpty()) {
            $sectionId = $sections->first()->section_id;
        }

        return [$academicYears, $yearId, $semesters, $semesterId, $levels, $levelId, $sections, $sectionId];
    }
}
