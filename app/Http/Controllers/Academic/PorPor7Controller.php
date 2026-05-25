<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\StudentSection;
use App\Models\Academic\Pp2Setting;
use App\Models\Personne\Personnel;
use App\Models\Student;
use Illuminate\Http\Request;

class PorPor7Controller extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('year_name', 'desc')->get();

        $currentSem = Semester::where('is_current', true)->with('academicYear')->first();
        $yearId    = $request->year_id   ?? ($currentSem->year_id ?? $academicYears->first()?->year_id);
        $term      = $request->term      ?? ($currentSem->semester_name ?? '1');
        $levelId   = $request->level_id;
        $sectionId = $request->section_id;
        $search    = trim($request->search ?? '');

        $semester   = Semester::where('year_id', $yearId)->where('semester_name', $term)->first();
        $semesterId = $semester?->semester_id;

        $levels = Level::whereHas('classSections', fn($q) => $q->where('semester_id', $semesterId))
            ->orderBy('sort_order')
            ->get();

        $sections = ClassSection::with('level')
            ->where('semester_id', $semesterId)
            ->when($levelId, fn($q) => $q->where('level_id', $levelId))
            ->orderBy('level_id')->orderBy('section_number')
            ->get();

        $currentSection = null;
        if ($sectionId && $sectionId !== 'all') {
            $currentSection = ClassSection::with('level')->find($sectionId);
        }

        $query = StudentSection::with(['student', 'classSection.level'])
            ->whereHas('classSection', fn($q) => $q->where('semester_id', $semesterId))
            ->where('status', 'กำลังศึกษา');

        if ($levelId) {
            $query->whereHas('classSection', fn($q) => $q->where('level_id', $levelId));
        }
        if ($sectionId && $sectionId !== 'all') {
            $query->where('section_id', $sectionId);
        }
        if ($search !== '') {
            $query->whereHas('student', fn($q) => $q
                ->where('thai_firstname', 'like', "%{$search}%")
                ->orWhere('thai_lastname', 'like', "%{$search}%")
                ->orWhere('student_code', 'like', "%{$search}%")
            );
        }

        $rows = $query->orderBy('student_number')->get();

        $students = $rows->map(fn($ss) => [
            'student' => $ss->student,
            'section' => $ss->classSection,
        ])->filter(fn($r) => $r['student'])->values();

        $personnels = Personnel::where('status', 'ปฏิบัติงาน')
            ->orderBy('thai_firstname')
            ->get(['personnel_id', 'thai_prefix', 'thai_firstname', 'thai_lastname', 'position']);

        $directors = Personnel::where('position', 'ผู้อำนวยการโรงเรียน')
            ->orWhere('position', 'like', '%ผู้อำนวยการ%')
            ->orderBy('thai_firstname')
            ->get(['personnel_id', 'thai_prefix', 'thai_firstname', 'thai_lastname', 'position']);

        $signSettings = Pp2Setting::getInstance();
        $setting = $signSettings;

        return view('academic.por7_index', compact(
            'academicYears', 'levels', 'sections', 'students',
            'yearId', 'term', 'levelId', 'sectionId', 'search',
            'semesterId', 'currentSection',
            'personnels', 'directors', 'signSettings', 'setting'
        ));
    }

    public function print(Request $request, $studentId)
    {
        $student = Student::with(['families', 'education'])->findOrFail($studentId);

        $semesterId = $request->semester_id;

        // ข้อมูลห้องเรียนปัจจุบัน
        $studentSection = StudentSection::with(['classSection.level', 'classSection.semester.academicYear'])
            ->where('student_id', $studentId)
            ->where('status', 'กำลังศึกษา')
            ->when($semesterId, fn($q) => $q->whereHas('classSection', fn($q2) => $q2->where('semester_id', $semesterId)))
            ->latest()
            ->first();

        if (!$studentSection) {
            $studentSection = StudentSection::with(['classSection.level', 'classSection.semester.academicYear'])
                ->where('student_id', $studentId)
                ->latest()
                ->first();
        }

        $section  = $studentSection?->classSection;
        $level    = $section?->level;
        $semester = $section?->semester;
        $yearName = $semester?->academicYear?->year_name ?? '';

        $levelSection = ($level?->name ?? '') . '/' . ($section?->section_number ?? '');

        $father = $student->families->firstWhere('guardian_type', 'บิดา')
            ?? $student->families->firstWhere('family_type', 'บิดา');
        $mother = $student->families->firstWhere('guardian_type', 'มารดา')
            ?? $student->families->firstWhere('family_type', 'มารดา');

        $signSettings = Pp2Setting::getInstance();
        $school = config('school');
        if ($signSettings->school_name)  $school['name']           = $signSettings->school_name;
        if ($signSettings->province)     $school['changwat']        = $signSettings->province;
        if ($signSettings->affiliation)  $school['affiliation']     = $signSettings->affiliation;
        if ($signSettings->registrar_name) $school['registrar_name'] = $signSettings->registrar_name;
        if ($signSettings->director_name)  $school['director_name']  = $signSettings->director_name;

        $issueDate   = $request->issue_date ?? date('Y-m-d');
        $gradeResult = $request->grade_result ?? '';
        $behavior    = $request->behavior ?? '';

        $issueDateFormatted = $this->formatThaiDate($issueDate);

        $dob = $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth) : null;
        $thMonths = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                     'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
        $dobFormatted = $dob
            ? $dob->day . ' ' . $thMonths[$dob->month] . ' ' . ($dob->year + 543)
            : '';

        return view('academic.por7_print', compact(
            'student', 'school',
            'level', 'section', 'levelSection', 'yearName',
            'father', 'mother',
            'issueDateFormatted', 'gradeResult', 'behavior',
            'dobFormatted'
        ));
    }

    public function saveSchoolSetting(Request $request)
    {
        $request->validate([
            'school_name' => 'nullable|string|max:255',
            'province'    => 'nullable|string|max:100',
            'affiliation' => 'nullable|string|max:255',
            'director_personnel_id' => 'nullable|integer',
        ]);

        $setting = Pp2Setting::first();
        $data = $request->only(['school_name', 'province', 'affiliation']);

        if ($request->director_personnel_id) {
            $director = Personnel::find($request->director_personnel_id);
            if ($director) {
                $data['director_name'] = trim(($director->thai_prefix ?? '') . $director->thai_firstname . ' ' . $director->thai_lastname);
                $data['director_personnel_id'] = $director->personnel_id;
            }
        }

        if ($setting) {
            $setting->update($data);
        } else {
            Pp2Setting::create($data);
        }

        return redirect()->back()->with('success', 'บันทึกข้อมูลโรงเรียนสำเร็จ');
    }

    public function saveSignSettings(Request $request)
    {
        $request->validate([
            'registrar_personnel_id' => 'nullable|integer',
            'director_personnel_id'  => 'nullable|integer',
        ]);

        $setting = Pp2Setting::getInstance();
        if (!$setting->exists) {
            $setting->save();
        }

        $registrar = $request->registrar_personnel_id
            ? Personnel::find($request->registrar_personnel_id)
            : null;
        $director  = $request->director_personnel_id
            ? Personnel::find($request->director_personnel_id)
            : null;

        $setting->update([
            'registrar_personnel_id' => $registrar?->personnel_id,
            'director_personnel_id'  => $director?->personnel_id,
            'registrar_name' => $registrar
                ? trim(($registrar->thai_prefix ?? '') . $registrar->thai_firstname . ' ' . $registrar->thai_lastname)
                : $request->registrar_name_manual,
            'director_name'  => $director
                ? trim(($director->thai_prefix ?? '') . $director->thai_firstname . ' ' . $director->thai_lastname)
                : $request->director_name_manual,
        ]);

        return redirect()->back()->with('success', 'บันทึกชื่อผู้ลงนามสำเร็จ');
    }

    private function formatThaiDate(string $dateStr): string
    {
        $months = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                   'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
        $d = \Carbon\Carbon::parse($dateStr);
        return $d->day . ' ' . $months[$d->month] . ' ' . ($d->year + 543);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $dir  = public_path('img/pp_1');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // ลบไฟล์โลโก้เดิมทุก extension
        foreach (['png','jpg','jpeg','PNG','JPG','JPEG'] as $ext) {
            $old = $dir . '/logo.' . $ext;
            if (file_exists($old)) unlink($old);
        }

        $ext  = strtolower($request->file('logo')->getClientOriginalExtension());
        $request->file('logo')->move($dir, 'logo.' . $ext);

        return redirect()->back()->with('success', 'อัปโหลดตราโรงเรียนสำเร็จ');
    }
}
