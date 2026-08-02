<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SchoolInfoSetting;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;
use App\Models\Academic\AcademicYear;
use App\Services\StudentExcelExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentListController extends Controller
{
    /**
     * แสดงรายการนักเรียน + ค้นหา/กรอง
     */
    public function index(Request $request)
    {
        $query = Student::query();

        $query = $this->applyStudentFilters($query, $request);

        $students = $query->orderBy('classroom_number', 'asc')->paginate(20);

        $levels = Level::orderBy('sort_order')->get();

        $classrooms = ClassSection::with('level')
            ->orderBy('level_id')->orderBy('section_number')
            ->get()
            ->map(fn($s) => [
                'section_id' => $s->section_id,
                'level_id' => $s->level_id,
                'label' => ($s->level->name ?? '?') . '/' . $s->section_number . ($s->study_plan ? ' '.$s->study_plan : ''),
            ]);

        $schoolInfo = SchoolInfoSetting::getInstance();

        return view('student.student_index', compact('students', 'levels', 'classrooms', 'schoolInfo'));
    }

    private function applyStudentFilters($query, Request $request)
    {
        if ($request->filled('level_id') || $request->filled('section_id') || $request->filled('academic_year') || $request->filled('semester')) {
            $query->whereHas('studentSections.classSection', function ($q) use ($request) {
                if ($request->filled('level_id')) {
                    $q->where('level_id', $request->level_id);
                }
                if ($request->filled('section_id')) {
                    $q->where('section_id', $request->section_id);
                }
                if ($request->filled('academic_year') || $request->filled('semester')) {
                    $q->whereHas('semester', function ($sq) use ($request) {
                        if ($request->filled('semester')) {
                            $sq->where('semester_name', $request->semester);
                        }
                        if ($request->filled('academic_year')) {
                            $sq->whereHas('academicYear', fn($aq) => $aq->where('year_name', $request->academic_year));
                        }
                    });
                }
            });
        }

        if ($request->filled('search_name')) {
            $name = $request->search_name;
            $query->where(function ($q) use ($name) {
                $q->where('thai_firstname', 'like', "%{$name}%")
                    ->orWhere('thai_lastname', 'like', "%{$name}%");
            });
        }

        if ($request->filled('search_code')) {
            $query->where('student_code', 'like', "%{$request->search_code}%");
        }

        if ($request->filled('search_idcard')) {
            $query->where('id_card_number', 'like', "%{$request->search_idcard}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    /**
     * ส่งออกข้อมูลนักเรียน (ตามตัวกรองที่เลือกอยู่ในหน้ารายชื่อ) เป็นไฟล์ Excel
     * ใช้ตำแหน่งคอลัมน์เดียวกับแบบฟอร์มนำเข้า จึงเอาไฟล์นี้กลับไป import ซ้ำได้ด้วย
     */
    public function export(Request $request)
    {
        $query = $this->applyStudentFilters(Student::query(), $request);

        $students = $query->with(StudentExcelExporter::eagerLoad())
            ->orderBy('classroom_number', 'asc')->get();

        $spreadsheet = StudentExcelExporter::build($students);

        $tmpPath = tempnam(sys_get_temp_dir(), 'student_export') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        $filename = 'ข้อมูลนักเรียน_' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * ดูรายละเอียดนักเรียน
     */
    public function show($id)
    {
        $student = Student::with(['addresses', 'education'])
            ->where('student_id', $id)
            ->firstOrFail();

        return view('student.student_index', compact('students'));

    }

    /**
     * ลบข้อมูลนักเรียน
     */
    public function destroy($id)
    {
        $student = Student::where('student_id', $id)->firstOrFail();
        $student->delete();

        return redirect()->back()
            ->with('success', 'ลบข้อมูลนักเรียนสำเร็จ');
    }

    /**
     * รายงานชื่อนักเรียนใหม่ + วันที่เข้าเรียนล่าสุด
     *
     * ตาราง students ไม่มีคอลัมน์บอกสถานะ "เข้าใหม่/ย้ายมา" (ตัวเลือกในฟอร์มค้นหา
     * เป็นแค่ dropdown ที่ไม่เคยผูกกับคอลัมน์จริง) จึงนิยาม "นักเรียนใหม่" จากข้อมูล
     * ที่มีจริง: นักเรียนที่ถูกจัดเข้าห้องเรียนครั้งแรกสุดในปีการศึกษาปัจจุบัน
     */
    public function newStudentsReport(Request $request)
    {
        $levels  = Level::orderBy('sort_order')->get();
        $levelId = $request->get('level_id', '');
        $search  = $request->get('search', '');

        $currentYearId = optional(AcademicYear::current())->year_id;

        $students = Student::whereHas('studentSections')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('thai_firstname', 'like', "%$search%")
                       ->orWhere('thai_lastname', 'like', "%$search%")
                       ->orWhere('student_code', 'like', "%$search%");
                });
            })
            ->with(['studentSections' => function ($q) {
                $q->with(['classSection.level', 'classSection.semester.academicYear'])
                  ->orderBy('created_at'); // เรียงเก่า -> ใหม่ เพื่อหาห้องแรกสุดได้ง่าย
            }])
            ->get();

        // แปลงเป็นแถวรายงาน (ห้องแรกสุด = วันเข้าเรียนครั้งแรก, ห้องล่าสุด = วันเข้าเรียนล่าสุด)
        $rows = $students->map(function ($s) {
            $first  = $s->studentSections->first();
            $latest = $s->studentSections->last();
            $sec    = $latest?->classSection;
            return (object) [
                'code'          => $s->student_code,
                'name'          => trim(($s->thai_prefix ?? '') . ($s->thai_firstname ?? '') . ' ' . ($s->thai_lastname ?? '')),
                'gender'        => $s->gender,
                'room'          => $sec ? (($sec->level->name ?? '') . '/' . $sec->section_number . ($sec->study_plan ? ' '.$sec->study_plan : '')) : '-',
                'year'          => $sec?->semester?->academicYear?->year_name,
                'enroll_date'   => $latest?->created_at,
                'status'        => $s->status,
                'level_id'      => $sec?->level_id,
                'first_year_id' => $first?->classSection?->semester?->year_id,
            ];
        });

        // นักเรียนใหม่ = เข้าห้องครั้งแรกในปีการศึกษาปัจจุบัน (ถ้าไม่มีปีปัจจุบันตั้งไว้ ให้แสดงทั้งหมด)
        if ($currentYearId) {
            $rows = $rows->filter(fn($r) => (string) $r->first_year_id === (string) $currentYearId)->values();
        }

        // กรองตามระดับชั้นของห้องล่าสุด
        if ($levelId !== '') {
            $rows = $rows->filter(fn($r) => (string) $r->level_id === (string) $levelId)->values();
        }

        // เรียงตามวันที่เข้าเรียนล่าสุด (ใหม่สุดก่อน)
        $rows = $rows->sortByDesc(fn($r) => $r->enroll_date?->timestamp ?? 0)->values();

        return view('student.new_students_report', compact('rows', 'levels', 'levelId', 'search'));
    }

    /**
     * ดาวน์โหลดไฟล์ Excel เปล่าไว้กรอกข้อมูลนักเรียน (ตำแหน่งคอลัมน์ตรงกับที่ import:students อ่าน)
     */
    public function importTemplate()
    {
        $info = SchoolInfoSetting::getInstance();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ข้อมูลนักเรียนทั้งหมด');

        $sheet->setCellValue('B1', $info->school_name ?: 'แบบฟอร์มนำเข้าข้อมูลนักเรียน');
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);

        if ($info->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($info->logo_path)) {
            $sheet->getRowDimension(1)->setRowHeight(60);
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('ตราโรงเรียน');
            $drawing->setPath(\Illuminate\Support\Facades\Storage::disk('public')->path($info->logo_path));
            $drawing->setHeight(75);
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        }

        $contact1 = trim(collect([
            $info->phone ? "โทรศัพท์ : {$info->phone}" : null,
            $info->fax ? "โทรสาร : {$info->fax}" : null,
        ])->filter()->implode('  '));
        if ($contact1 !== '') {
            $sheet->setCellValue('B3', $contact1);
        }

        $contact2 = trim(collect([
            $info->website,
            $info->email ? "อีเมล์ : {$info->email}" : null,
        ])->filter()->implode('  '));
        if ($contact2 !== '') {
            $sheet->setCellValue('B4', $contact2);
        }

        $sheet->setCellValue('B5', 'กรอกข้อมูลนักเรียนเริ่มจากแถวที่ 7 เป็นต้นไป (แถวที่ 1-6 ห้ามลบ/ห้ามแก้) — ต้องมีเลขบัตรประชาชนหรือรหัสนักศึกษาอย่างน้อย 1 อย่าง');

        foreach (StudentExcelExporter::IMPORT_TEMPLATE_HEADERS as $i => $header) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $cell = $sheet->setCellValue("{$col}6", $header);
            $sheet->getStyle("{$col}6")->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setWidth(14);
        }
        $sheet->freezePane('A7');

        $tmpPath = tempnam(sys_get_temp_dir(), 'student_template') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, 'แบบฟอร์มนำเข้าข้อมูลนักเรียน.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * บันทึกข้อมูลโรงเรียนที่จะแสดงบนหัวแบบฟอร์ม Excel
     */
    public function saveSchoolInfo(Request $request)
    {
        $data = $request->validate([
            'school_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'fax' => 'nullable|string|max:100',
            'website' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);
        unset($data['logo']);

        $setting = SchoolInfoSetting::first() ?? new SchoolInfoSetting();

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($setting->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('school', 'public');
        }

        if ($setting->exists) {
            $setting->update($data);
        } else {
            $setting->fill($data)->save();
        }

        return back()->with('success', 'บันทึกข้อมูลโรงเรียนสำเร็จ');
    }

    /**
     * รับไฟล์ Excel ที่ครูกรอกมา แล้วรันคำสั่งนำเข้าข้อมูลนักเรียนให้
     */
    public function importUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ], [
            'file.required' => 'กรุณาเลือกไฟล์ Excel',
            'file.mimes' => 'ไฟล์ต้องเป็นสกุล .xlsx เท่านั้น',
        ]);

        set_time_limit(0); // ไฟล์ใหญ่อาจใช้เวลาหลายนาที

        $path = $request->file('file')->store('imports');
        $fullPath = \Illuminate\Support\Facades\Storage::path($path);

        $options = ['file' => $fullPath];
        if ($request->boolean('dry_run')) {
            $options['--dry-run'] = true;
        }
        if ($request->boolean('assign_rooms')) {
            $options['--assign-rooms'] = true;
        }
        if ($request->boolean('fill_classroom_number')) {
            $options['--fill-classroom-number'] = true;
        }

        Artisan::call('import:students', $options);
        $output = Artisan::output();

        @unlink($fullPath);

        return back()->with('import_output', $output);
    }
}