<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SchoolInfoSetting;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;
use App\Models\Academic\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentListController extends Controller
{
    // หัวตาราง 162 คอลัมน์ ตำแหน่งต้องตรงกับที่คำสั่ง import:students ใช้อ่านเป๊ะๆ ห้ามสลับ/เพิ่ม/ลบคอลัมน์
    private const IMPORT_TEMPLATE_HEADERS = [
        'ลำดับ', 'ชั้น/ห้อง', 'เลขที่', 'สถานะ', 'รหัสบัตรประชาชน', 'รหัสนักศึกษา', 'รหัสลายนิ้วมือ', 'วันที่เข้าเรียน', 'เพศ', 'คำนำหน้า',
        'ชื่อ', 'นามสกุล', 'ชื่อเล่น', 'ชื่อ(อังกฤษ)', 'นามสกุล(อังกฤษ)', 'ชื่อเล่น(อังกฤษ)', 'วัน/เดือน/ปีเกิด', 'ศาสนา', 'สัญชาติ', 'เชื้อชาติ',
        'มีพี่น้องทั้งหมด', 'เป็นบุตรคนที่', 'พี่/น้องเรียนในโรงเรียนนี้', 'เบอร์โทรศัพท์', 'อีเมล์', 'เงินคงเหลือ', 'รหัสประจำบ้าน', 'บ้านเลขที่', 'ซอย', 'หมู่',
        'ถนน', 'แขวง/ตำบล', 'เขต/อำเภอ', 'จังหวัด', 'รหัสไปรษณีย์', 'เบอร์โทรศัพท์บ้าน', 'สถานที่เกิดระบุที่เกิด(รพ.)', 'สถานที่เกิดแขวง/ตำบล', 'สถานที่เกิดเขต/อำเภอ', 'สถานที่เกิดจังหวัด',
        'บ้านเลขที่ปัจจุบัน', 'หมู่', 'ซอย', 'ถนน', 'ตำบล', 'อำเภอ', 'จังหวัด', 'รหัสไปรษณีย์', 'เบอร์โทรศัพท์บ้าน', 'อาศัยอยู่กับ',
        'นามสกุล', 'ลักษณะบ้าน', 'อีเมล์', 'เบอร์ติดต่อฉุกเฉิน', 'เพื่อนใกล้บ้าน', 'นามสกุล', 'เบอร์โทรศัพท์เพื่อน', 'สถานศึกษาเดิม', 'ตำบล', 'อำเภอ',
        'จังหวัด', 'วุฒิการศึกษา', 'GPA', 'เหตุที่ย้าย', 'บ้านเกิดเลขที่', 'หมู่', 'ซอย', 'ถนน', 'ตำบล', 'อำเภอ',
        'จังหวัด', 'รหัสไปรษณีย์', 'เบอร์โทรศัพท์', 'ความสัมพันธ์', 'คำนำหน้า', 'ชื่อผู้ปกครอง', 'นามสกุล', 'ชื่อผู้ปกครอง(อังกฤษ)', 'นามสกุล(อังกฤษ)', 'วัน/เดือน/ปีเกิด',
        'ศาสนา', 'สัญชาติ', 'เชื้อชาติ', 'บ้านเลขที่', 'หมู่', 'ซอย', 'ถนน', 'ตำบล', 'อำเภอ', 'จังหวัด',
        'รหัสไปรษณีย์', 'เบอร์บ้าน', 'เบอร์มือถือ', 'เบอร์ที่ทำงาน', 'สถานะครอบครัว', 'วุฒิการศึกษา', 'อาชีพผู้ปกครอง', 'สถานที่ทำงาน', 'รายได้ต่อเดือน', 'รายได้ต่อปี',
        'เบิกค่าเล่าเรียน', 'คำนำหน้า', 'ชื่อบิดา', 'นามสกุล', 'ชื่อบิดา(อังกฤษ)', 'นามสกุล(อังกฤษ)', 'วัน/เดือน/ปีเกิด', 'ศาสนา', 'สัญชาติ', 'เชื้อชาติ',
        'บ้านเลขที่', 'หมู่', 'ซอย', 'ถนน', 'ตำบล', 'อำเภอ', 'จังหวัด', 'รหัสไปรษณีย์', 'เบอร์บ้าน', 'เบอร์มือถือ',
        'เบอร์ที่ทำงาน', 'วุฒิการศึกษา', 'อาชีพบิดา', 'สถานที่ทำงาน', 'รายได้ต่อเดือน', 'รายได้ต่อปี', 'คำนำหน้า', 'ชื่อมารดา', 'นามสกุล', 'ชื่อมารดา(อังกฤษ)',
        'นามสกุล(อังกฤษ)', 'วัน/เดือน/ปีเกิด', 'ศาสนา', 'สัญชาติ', 'เชื้อชาติ', 'บ้านเลขที่', 'หมู่', 'ซอย', 'ถนน', 'ตำบล',
        'อำเภอ', 'จังหวัด', 'รหัสไปรษณีย์', 'เบอร์บ้าน', 'เบอร์มือถือ', 'เบอร์ที่ทำงาน', 'วุฒิการศึกษา', 'อาชีพมารดา', 'สถานที่ทำงาน', 'รายได้ต่อเดือน',
        'รายได้ต่อปี', 'น้ำหนัก', 'ส่วนสูง', 'กรุ๊ปเลือด', 'แพ้อาหาร', 'แพ้ยา', 'แพ้อื่นๆ', 'โรคประจำตัว', 'โรคร้ายแรง', 'ประเภทการเดินทาง',
        'ประเภทนักเรียน', 'หมายเลขบัตร',
    ];

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

        $students = $query->with([
            'addresses',
            'education',
            'families',
            'health',
            'studentSections' => fn ($q) => $q->with('classSection.level')->latest('created_at'),
        ])->orderBy('classroom_number', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ข้อมูลนักเรียนทั้งหมด');

        $info = SchoolInfoSetting::getInstance();
        $sheet->setCellValue('B1', $info->school_name ?: 'ข้อมูลนักเรียน');
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);

        foreach (self::IMPORT_TEMPLATE_HEADERS as $i => $header) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}6", $header);
            $sheet->getStyle("{$col}6")->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setWidth(14);
        }
        $sheet->freezePane('A7');

        $row = 7;
        foreach ($students as $i => $student) {
            $reg = $student->addresses->firstWhere('address_type', 'Registered');
            $cur = $student->addresses->firstWhere('address_type', 'Current');
            $edu = $student->education;
            $guardian = $student->families->firstWhere('guardian_type', 'ผู้ปกครอง');
            $father = $student->families->firstWhere('guardian_type', 'บิดา');
            $mother = $student->families->firstWhere('guardian_type', 'มารดา');
            $health = $student->health;
            $currentSection = $student->studentSections->first();
            $classSection = $currentSection?->classSection;

            $cells = [
                1 => $i + 1,
                2 => $classSection ? ($classSection->level->name ?? '') . '/' . $classSection->section_number . ($classSection->study_plan ? ' ' . $classSection->study_plan : '') : '',
                3 => $currentSection?->student_number,
                4 => $student->status,
                5 => str_starts_with((string) $student->id_card_number, 'P') ? '' : $student->id_card_number,
                6 => $student->student_code,
                9 => $student->gender === 'M' ? 'ชาย' : ($student->gender === 'F' ? 'หญิง' : ''),
                10 => $student->thai_prefix,
                11 => $student->thai_firstname,
                12 => $student->thai_lastname,
                13 => $student->thai_nickname,
                14 => $student->english_firstname,
                15 => $student->english_lastname,
                16 => $student->english_nickname,
                17 => $this->formatThaiDate($student->date_of_birth),
                18 => $student->religion,
                19 => $student->nationality,
                20 => $student->ethnicity,
                21 => $student->total_siblings,
                22 => $student->sibling_order,
                24 => $student->phone_number,
                25 => $student->email,

                27 => $reg?->house_code, 28 => $reg?->house_number, 29 => $reg?->soi, 30 => $reg?->village_no,
                31 => $reg?->road, 35 => $reg?->postal_code, 36 => $reg?->home_phone,
                37 => $reg?->birth_hospital_th,

                41 => $cur?->house_number, 42 => $cur?->village_no, 43 => $cur?->soi, 44 => $cur?->road,
                48 => $cur?->postal_code, 49 => $cur?->home_phone,
                50 => $cur?->stay_with_first_name, 51 => $cur?->stay_with_last_name,
                52 => $cur?->house_characteristic, 53 => $cur?->stay_with_email, 54 => $cur?->emergency_contact_phone,

                58 => $edu?->school_name, 62 => $edu?->education_level, 63 => $edu?->gpa, 64 => $edu?->transfer_reason,

                160 => $student->transportation_type,
            ];

            $this->fillFamilyColumns($cells, $guardian, [
                'relationship' => 74, 'prefix_th' => 75, 'first_name_th' => 76, 'last_name_th' => 77,
                'first_name_en' => 78, 'last_name_en' => 79, 'birth_date' => 80, 'religion' => 81,
                'nationality' => 82, 'ethnicity' => 83, 'house_number' => 84, 'village_no' => 85, 'soi' => 86,
                'road' => 87, 'postal_code' => 91, 'phone_home' => 92, 'phone_mobile' => 93, 'phone_work' => 94,
                'family_status' => 95, 'education_level' => 96, 'occupation' => 97, 'workplace' => 98,
                'monthly_income' => 99, 'tuition_subsidy' => 101,
            ]);
            $this->fillFamilyColumns($cells, $father, [
                'prefix_th' => 102, 'first_name_th' => 103, 'last_name_th' => 104,
                'first_name_en' => 105, 'last_name_en' => 106, 'birth_date' => 107, 'religion' => 108,
                'nationality' => 109, 'ethnicity' => 110, 'house_number' => 111, 'village_no' => 112, 'soi' => 113,
                'road' => 114, 'postal_code' => 118, 'phone_home' => 119, 'phone_mobile' => 120, 'phone_work' => 121,
                'education_level' => 122, 'occupation' => 123, 'workplace' => 124, 'monthly_income' => 125,
            ]);
            $this->fillFamilyColumns($cells, $mother, [
                'prefix_th' => 127, 'first_name_th' => 128, 'last_name_th' => 129,
                'first_name_en' => 130, 'last_name_en' => 131, 'birth_date' => 132, 'religion' => 133,
                'nationality' => 134, 'ethnicity' => 135, 'house_number' => 136, 'village_no' => 137, 'soi' => 138,
                'road' => 139, 'postal_code' => 143, 'phone_home' => 144, 'phone_mobile' => 145, 'phone_work' => 146,
                'education_level' => 147, 'occupation' => 148, 'workplace' => 149, 'monthly_income' => 150,
            ]);

            if ($health) {
                $cells[154] = $health->blood_group;
                $cells[155] = $health->food_allergy;
                $cells[156] = $health->medicine_allergy;
                $cells[157] = $health->other_allergy;
                $cells[158] = $health->chronic_disease;
                $cells[159] = $health->serious_disease;
            }

            foreach ($cells as $colIndex => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValueExplicit("{$col}{$row}", (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }

            $row++;
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'student_export') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        $filename = 'ข้อมูลนักเรียน_' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    private function fillFamilyColumns(array &$cells, $family, array $colMap): void
    {
        if (!$family) {
            return;
        }
        foreach ($colMap as $field => $colIndex) {
            $value = $field === 'birth_date' ? $this->formatThaiDate($family->birth_date) : $family->{$field};
            $cells[$colIndex] = $value;
        }
    }

    private function formatThaiDate($date): ?string
    {
        if (!$date) {
            return null;
        }
        $carbon = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return $carbon->format('d/m/') . ($carbon->year + 543);
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

        foreach (self::IMPORT_TEMPLATE_HEADERS as $i => $header) {
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