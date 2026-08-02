<?php

namespace App\Services;

use App\Models\SchoolInfoSetting;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class StudentExcelExporter
{
    // หัวตาราง 162 คอลัมน์ ตำแหน่งต้องตรงกับที่คำสั่ง import:students ใช้อ่านเป๊ะๆ ห้ามสลับ/เพิ่ม/ลบคอลัมน์
    public const IMPORT_TEMPLATE_HEADERS = [
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
     * นักเรียนแต่ละคนต้อง eager-load: addresses, education, families, health,
     * studentSections.classSection.level (ตัวปัจจุบันสุด = studentSections->first())
     */
    public static function build(Collection $students, ?string $title = null): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ข้อมูลนักเรียนทั้งหมด');

        $info = SchoolInfoSetting::getInstance();
        $sheet->setCellValue('B1', $title ?: ($info->school_name ?: 'ข้อมูลนักเรียน'));
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);

        foreach (self::IMPORT_TEMPLATE_HEADERS as $i => $header) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
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
                17 => self::formatThaiDate($student->date_of_birth),
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

            self::fillFamilyColumns($cells, $guardian, [
                'relationship' => 74, 'prefix_th' => 75, 'first_name_th' => 76, 'last_name_th' => 77,
                'first_name_en' => 78, 'last_name_en' => 79, 'birth_date' => 80, 'religion' => 81,
                'nationality' => 82, 'ethnicity' => 83, 'house_number' => 84, 'village_no' => 85, 'soi' => 86,
                'road' => 87, 'postal_code' => 91, 'phone_home' => 92, 'phone_mobile' => 93, 'phone_work' => 94,
                'family_status' => 95, 'education_level' => 96, 'occupation' => 97, 'workplace' => 98,
                'monthly_income' => 99, 'tuition_subsidy' => 101,
            ]);
            self::fillFamilyColumns($cells, $father, [
                'prefix_th' => 102, 'first_name_th' => 103, 'last_name_th' => 104,
                'first_name_en' => 105, 'last_name_en' => 106, 'birth_date' => 107, 'religion' => 108,
                'nationality' => 109, 'ethnicity' => 110, 'house_number' => 111, 'village_no' => 112, 'soi' => 113,
                'road' => 114, 'postal_code' => 118, 'phone_home' => 119, 'phone_mobile' => 120, 'phone_work' => 121,
                'education_level' => 122, 'occupation' => 123, 'workplace' => 124, 'monthly_income' => 125,
            ]);
            self::fillFamilyColumns($cells, $mother, [
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
                $col = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValueExplicit("{$col}{$row}", (string) $value, DataType::TYPE_STRING);
            }

            $row++;
        }

        return $spreadsheet;
    }

    private static function fillFamilyColumns(array &$cells, $family, array $colMap): void
    {
        if (!$family) {
            return;
        }
        foreach ($colMap as $field => $colIndex) {
            $value = $field === 'birth_date' ? self::formatThaiDate($family->birth_date) : $family->{$field};
            $cells[$colIndex] = $value;
        }
    }

    public static function formatThaiDate($date): ?string
    {
        if (!$date) {
            return null;
        }
        $carbon = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return $carbon->format('d/m/') . ($carbon->year + 543);
    }

    public static function eagerLoad()
    {
        return [
            'addresses',
            'education',
            'families',
            'health',
            'studentSections' => fn ($q) => $q->with('classSection.level')->latest('created_at'),
        ];
    }
}
