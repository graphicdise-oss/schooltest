<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SchoolInfoSetting;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;
use App\Services\ExcelSchoolHeader;
use App\Services\SchoolInfoSync;
use App\Services\StudentExcelExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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

        $classrooms = ClassSection::real()->with('level')
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
     * ดาวน์โหลดไฟล์ Excel เปล่าไว้กรอกข้อมูลนักเรียน (ตำแหน่งคอลัมน์ตรงกับที่ import:students อ่าน)
     */
    public function importTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ข้อมูลนักเรียนทั้งหมด');

        $totalCols = count(StudentExcelExporter::IMPORT_TEMPLATE_HEADERS);
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);

        $hasLogo = ExcelSchoolHeader::apply($sheet, $totalCols);
        ExcelSchoolHeader::applyInstructionRow($sheet, $totalCols, 'กรอกข้อมูลนักเรียนเริ่มจากแถวที่ 7 เป็นต้นไป (แถวที่ 1-6 ห้ามลบ/ห้ามแก้) — ต้องมีเลขบัตรประชาชนหรือรหัสนักศึกษาอย่างน้อย 1 อย่าง');

        foreach (StudentExcelExporter::IMPORT_TEMPLATE_HEADERS as $i => $header) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}6", $header);
            $sheet->getStyle("{$col}6")->getFont()->setBold(true);
            if ($col !== 'A' || !$hasLogo) {
                $sheet->getColumnDimension($col)->setWidth(14); // คอลัมน์ A ถ้ามีโลโก้ ใช้ความกว้างที่ตั้งไว้ก่อนหน้าแทน
            }
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

        // เชื่อมกับหน้า "ตั้งค่าเริ่มต้น" (Pp2Setting) ที่ใช้พิมพ์เอกสาร ปพ.ต่างๆ ด้วย
        SchoolInfoSync::syncName($data['school_name'] ?? null);
        if (!empty($data['logo_path'])) {
            SchoolInfoSync::propagateLogoToStatic($data['logo_path']);
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