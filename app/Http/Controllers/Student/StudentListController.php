<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SchoolInfoSetting;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;
use App\Services\ExcelSchoolHeader;
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
     * รายงานชื่อนักเรียนใหม่ + วันที่เข้าเรียนล่าสุด — แสดงนักเรียนทุกคนที่เคยถูกจัดเข้าห้องเรียน
     * เรียงตามวันที่เข้าเรียนล่าสุด กรองตามช่วงวันที่/ระดับชั้น/คำค้นหาได้ตามต้องการ (ไม่กรองอะไรเป็นค่าเริ่มต้น)
     */
    public function newStudentsReport(Request $request)
    {
        $levels   = Level::orderBy('sort_order')->get();
        $levelId  = $request->get('level_id', '');
        $search   = $request->get('search', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo   = $request->get('date_to', '');

        // เช็คว่าฐานข้อมูลมีนักเรียนที่เคยถูกจัดห้องเรียนอยู่เลยหรือไม่ (ไม่ผูกกับตัวกรองใดๆ เลย แม้แต่คำค้นหา)
        // ไว้แยกข้อความตอนว่างเปล่าให้ผู้ใช้รู้ทันทีว่า "ไม่มีข้อมูลในระบบเลย" กับ "มีข้อมูลแต่ไม่ตรงตัวกรองที่เลือก" ต่างกันตรงไหน
        $hasAnyData = Student::whereHas('studentSections')->exists();

        $students = Student::whereHas('studentSections')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('thai_firstname', 'like', "%$search%")
                       ->orWhere('thai_lastname', 'like', "%$search%")
                       ->orWhere('student_code', 'like', "%$search%");
                });
            })
            ->with([
                'education',
                'studentSections' => function ($q) {
                    $q->with(['classSection.level', 'classSection.semester.academicYear']);
                },
            ])
            ->get();

        // แปลงเป็นแถวรายงาน (ห้องล่าสุด = วันเข้าเรียนล่าสุด)
        $rows = $students->map(function ($s) {
            // หาแถว "ล่าสุด" เองใน PHP แทนการพึ่ง ORDER BY created_at ที่ฐานข้อมูล — เพราะ MySQL/SQLite
            // เรียงค่า NULL ไว้ต้นแถว (ASC) แต่ PostgreSQL เรียงค่า NULL ไว้ท้ายแถวแทน ถ้าใช้ ->last() หลัง
            // orderBy('created_at') ตรงๆ บน Postgres จะได้แถวที่ไม่มีวันที่ (NULL) มาเป็น "ล่าสุด" ผิดๆ
            // ทันทีที่นักเรียนมีประวัติห้องเก่าที่นำเข้าแบบไม่มี created_at ปนอยู่แม้แต่แถวเดียว
            $latest = $s->studentSections->sortBy(fn ($ss) => $ss->created_at ?? \Carbon\Carbon::createFromTimestamp(0))->last();
            $sec    = $latest?->classSection;
            return (object) [
                'code'            => $s->student_code,
                'name'            => trim(($s->thai_prefix ?? '') . ($s->thai_firstname ?? '') . ' ' . ($s->thai_lastname ?? '')),
                'gender'          => $s->gender,
                'room'            => $sec ? (($sec->level->name ?? '') . '/' . $sec->section_number . ($sec->study_plan ? ' '.$sec->study_plan : '')) : '-',
                'year'            => $sec?->semester?->academicYear?->year_name,
                'enroll_date'     => $latest?->created_at,
                'previous_school' => $s->education?->previous_school,
                'status'          => $s->status,
                'level_id'        => $sec?->level_id,
            ];
        });

        // ถ้ากรองตามระดับชั้นแล้วไม่เจอใครเลยทั้งที่มีข้อมูลอยู่ ให้เก็บรายการ "ห้อง/รหัสระดับชั้นที่มีข้อมูลจริง" ไว้เทียบ
        // กับ level_id ที่เลือก เผื่อระดับชั้นในระบบมีรายการซ้ำ (ชื่อเหมือนกันแต่คนละ id) จะได้เห็นสาเหตุตรงๆ
        $availableLevelDebug = collect();
        if ($levelId !== '') {
            $availableLevelDebug = $rows->filter(fn($r) => $r->level_id !== null)
                ->map(fn($r) => $r->level_id . ' (' . $r->room . ')')
                ->unique()
                ->values();
        }

        // กรองตามระดับชั้นของห้องล่าสุด (trim กันช่องว่างแปลกๆ ที่อาจติดมากับค่าจาก query string)
        if ($levelId !== '') {
            $rows = $rows->filter(fn($r) => trim((string) $r->level_id) === trim((string) $levelId))->values();
        }

        // นักเรียนบางคน (มักเป็นข้อมูลเก่าที่นำเข้าตรงๆ ไม่ผ่านหน้าจัดห้องของระบบ) ไม่มีวันที่เข้าเรียนบันทึกไว้เลย
        // นับจำนวนไว้แจ้งผู้ใช้ให้รู้ (คนกลุ่มนี้จะยังโชว์อยู่เสมอไม่ว่าจะกรองวันที่หรือไม่ เพราะไม่รู้ว่าเข้าวันไหนจริง
        // เลยพิสูจน์ไม่ได้ว่าอยู่นอกช่วงที่เลือก จึงไม่ตัดออกอัตโนมัติ กันหน้าเว็บดูเหมือน "ไม่มีข้อมูล" ทั้งที่มีข้อมูลอยู่)
        $missingDateCount = $rows->filter(fn($r) => !$r->enroll_date)->count();

        // แสดงนักเรียนทั้งหมดที่เคยเข้าเรียนเป็นค่าเริ่มต้น ไม่กรองตามวันใดๆ จนกว่าจะเลือกวันที่มาเอง
        // คนที่ไม่มี enroll_date บันทึกไว้จะยังโชว์อยู่เสมอ (ตัดออกได้เฉพาะคนที่รู้วันที่จริงแล้วอยู่นอกช่วงเท่านั้น)
        if ($dateFrom !== '') {
            $rows = $rows->filter(fn($r) => !$r->enroll_date || $r->enroll_date->format('Y-m-d') >= $dateFrom)->values();
        }
        if ($dateTo !== '') {
            $rows = $rows->filter(fn($r) => !$r->enroll_date || $r->enroll_date->format('Y-m-d') <= $dateTo)->values();
        }

        // เรียงตามวันที่เข้าเรียนล่าสุด (ใหม่สุดก่อน)
        $rows = $rows->sortByDesc(fn($r) => $r->enroll_date?->timestamp ?? 0)->values();

        return view('student.new_students_report', compact('rows', 'levels', 'levelId', 'search', 'dateFrom', 'dateTo', 'hasAnyData', 'missingDateCount', 'availableLevelDebug'));
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