<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\Semester;
use App\Models\Academic\StudentSection;
use App\Models\Student;
use App\Services\StudentExcelExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ClassRosterController extends Controller
{
    public function index(Request $request)
    {
        $yearId = $request->get('year_id');
        $semesterId = $request->get('semester_id');
        $levelId = $request->get('level_id');
        $sectionId = $request->get('section_id');
        $search = $request->get('search', '');
        $status = $request->get('status', '');

        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $currentYear = AcademicYear::current();

        // default ปีปัจจุบัน
        if (!$yearId && $currentYear)
            $yearId = $currentYear->year_id;

        $semesters = $yearId
            ? Semester::where('year_id', $yearId)->orderBy('semester_name')->get()
            : collect();

        // แทนด้วย
        if (!$semesterId && $yearId) {
            $defaultSem = Semester::where('year_id', $yearId)
                ->where('is_current', true)
                ->first()
                ?? Semester::where('year_id', $yearId)->orderBy('semester_name')->first();

            if ($defaultSem)
                $semesterId = $defaultSem->semester_id;
        }

        $levels = Level::orderBy('sort_order')->get();

        // sections ของ level+semester ที่เลือก
        $sections = collect();
        if ($levelId && $semesterId) {
            $sections = ClassSection::real()->with('level')
                ->where('level_id', $levelId)
                ->where('semester_id', $semesterId)
                ->orderBy('section_number')
                ->get();
        }

        // default section แรก
        if (!$sectionId && $sections->isNotEmpty()) {
            $sectionId = $sections->first()->section_id;
        }

        $exportMode = $request->query('export'); // 'excel' = ห้องนี้เท่านั้น, 'level' = ทั้งชั้น (รวมทุกห้อง)
        if ($exportMode === 'excel' || $exportMode === 'level') {
            return $this->export($sectionId, $levelId, $semesterId, $search, $status, $exportMode === 'level');
        }

        // นักเรียนในห้องที่เลือก
        $students = collect();
        if ($sectionId) {
            $query = StudentSection::with('student')
                ->where('section_id', $sectionId)
                ->orderBy('student_number');

            if ($search) {
                $query->whereHas(
                    'student',
                    fn($q) => $q
                        ->where('thai_firstname', 'like', "%{$search}%")
                        ->orWhere('thai_lastname', 'like', "%{$search}%")
                        ->orWhere('student_code', 'like', "%{$search}%")
                );
            }
            if ($status) {
                $query->whereHas('student', fn($q) => $q->where('status', $status));
            }

            $students = $query->get();
        }

        $selectedSection = $sectionId
            ? ClassSection::with('level')->find($sectionId)
            : null;

        return view('student.class_roster', compact(
            'academicYears',
            'semesters',
            'levels',
            'sections',
            'students',
            'selectedSection',
            'yearId',
            'semesterId',
            'levelId',
            'sectionId',
            'search',
            'status'
        ));
    }

    /**
     * Export ข้อมูลนักเรียนตามห้อง/ระดับชั้นที่เลือกอยู่ (หรือทั้งหมดถ้าไม่ได้เลือก)
     * ครูที่ไม่ใช่ admin จะ export ได้เฉพาะห้องที่ตัวเองเป็นครูที่ปรึกษาเท่านั้น (แม้ขอ "ทั้งชั้น" มาก็ตาม)
     *
     * @param bool $wholeLevel true = รวมทุกห้องในระดับชั้นที่เลือก (เรียงตามห้อง แล้วตามเลขที่ในห้อง)
     */
    private function export($sectionId, $levelId, $semesterId, $search, $status, bool $wholeLevel = false)
    {
        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();

        $sectionIds = null; // null = ไม่จำกัดห้อง (เฉพาะ admin เท่านั้นที่ทำได้)

        if (!$isAdmin) {
            $ownSectionIds = ClassSection::real()->where('homeroom_teacher_id', $user?->personnel_id)->pluck('section_id');
            if ($wholeLevel) {
                $sectionIds = $ownSectionIds->all();
            } elseif ($sectionId && !$ownSectionIds->contains((int) $sectionId)) {
                abort(403, 'คุณไม่มีสิทธิ์ export ห้องนี้ (ไม่ใช่ห้องที่ปรึกษาของคุณ)');
            } else {
                $sectionIds = $sectionId ? [(int) $sectionId] : $ownSectionIds->all();
            }
        } elseif ($wholeLevel && $levelId && $semesterId) {
            $sectionIds = ClassSection::real()->where('level_id', $levelId)->where('semester_id', $semesterId)->pluck('section_id')->all();
        } elseif ($sectionId) {
            $sectionIds = [(int) $sectionId];
        } elseif ($levelId && $semesterId) {
            $sectionIds = ClassSection::real()->where('level_id', $levelId)->where('semester_id', $semesterId)->pluck('section_id')->all();
        }

        $query = Student::query();
        if ($sectionIds !== null) {
            $query->whereHas('studentSections', fn ($q) => $q->whereIn('section_id', $sectionIds));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('thai_firstname', 'like', "%{$search}%")
                    ->orWhere('thai_lastname', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%");
            });
        }
        if ($status) {
            $query->where('status', $status);
        }

        $students = $query->with(StudentExcelExporter::eagerLoad())->get();

        if ($wholeLevel && $sectionIds) {
            // เรียงตามห้องก่อน (เลขห้องน้อยไปมาก) แล้วค่อยตามเลขที่ในห้อง จะได้อ่านเป็นห้องๆ ไม่ปนกัน
            $students = $students->sortBy(function ($student) use ($sectionIds) {
                $ss = $student->studentSections->first(fn ($s) => in_array($s->section_id, $sectionIds, true));
                $sectionNumber = $ss?->classSection?->section_number ?? 9999;
                $studentNumber = $ss?->student_number ?? 9999;
                return sprintf('%05d-%05d', $sectionNumber, $studentNumber);
            })->values();
        } else {
            $students = $students->sortBy('classroom_number')->values();
        }

        $spreadsheet = StudentExcelExporter::build($students);

        $tmpPath = tempnam(sys_get_temp_dir(), 'roster_export') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        $filename = 'ข้อมูลนักเรียน_' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }
}