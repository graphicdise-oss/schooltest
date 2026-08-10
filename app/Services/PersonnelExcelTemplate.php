<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * เทมเพลต Excel "รายชื่อพนักงาน" แบบเต็ม — ใช้ทั้งฝั่งดาวน์โหลดแบบฟอร์มเปล่า
 * (PersonnelController::importTemplate), ฝั่ง export ข้อมูลจริง, และฝั่งนำเข้าข้อมูล
 * เพื่อให้คอลัมน์ตรงกันทุกทาง แก้/เพิ่มคอลัมน์ที่นี่ที่เดียว
 */
class PersonnelExcelTemplate
{
    public const HEADERS = [
        'ลำดับ', 'ประเภทบุคลากร', 'รหัสพนักงาน', 'ตำแหน่ง', 'แผนก', 'เพศ', 'คำนำหน้านาม', 'ชื่อ', 'นามสกุล',
        'ชื่อ(ภาษาอังกฤษ)', 'นามสกุล(ภาษาอังกฤษ)', 'ยอดเงินคงเหลือ', 'เลขบัตรประชาชน', 'เลขที่หนังสือเดินทาง',
        'ประเทศหนังสือเดินทาง', 'วันเกิด', 'กรุ๊ปเลือด', 'สัญชาติ', 'เชื้อชาติ', 'ศาสนา', 'สถานภาพสมรส',
        'ชื่อคู่สมรส', 'นามสกุลคู่สมรส', 'หมายเลขโทรศัพท์', 'อีเมล์', 'ตารางเวลา', 'ใช้ระบบ Biometric', 'รหัสลายนิ้วมือ',
        // ที่อยู่ตามทะเบียนบ้าน (29-38)
        'บ้านเลขที่', 'หมู่ที่', 'หมู่บ้าน', 'ซอย', 'อาคาร/ชั้น', 'ถนน', 'จังหวัด', 'เขต/อำเภอ', 'แขวง/ตำบล', 'รหัสไปรษณีย์',
        // ที่อยู่ที่ติดต่อได้ (39-48)
        'บ้านเลขที่', 'หมู่ที่', 'หมู่บ้าน', 'ซอย', 'อาคาร/ชั้น', 'ถนน', 'จังหวัด', 'เขต/อำเภอ', 'แขวง/ตำบล', 'รหัสไปรษณีย์',
        // ตำแหน่งงาน (49-59)
        'สถานภาพการทำงาน', 'ระดับ', 'วันที่ปฏิบัติงานในสถานศึกษา', 'วันสั่งบรรจุ', 'เงินเดือน', 'วันเริ่มปฏิบัติราชการ',
        'เงินประจำตำแหน่ง', 'จำนวนเงินวิทยฐานะ', 'วันครบเกษียณอายุ', 'รายได้สุทธิรวม', 'อายุงานเหลือ',
        // ข้อมูลครอบครัว (60-66) — ระบบยังไม่มีตารางเก็บข้อมูลนี้ ปล่อยว่างทั้ง export/import
        'ลำดับ', 'ความสัมพันธ์', 'คำนำหน้า', 'ชื่อ', 'นามสกุล', 'วันเกิด', 'สถานภาพ',
        // ข้อมูลการศึกษา (67-72)
        'ลำดับ', 'ปีที่', 'ปีที่', 'ระดับการศึกษา', 'วิชาเอก', 'สถาบันการศึกษา',
        // ข้อมูลเกียรติคุณ (73-76)
        'ลำดับ', 'ประเภทเกียรติคุณ', 'หน่วยงาน', 'ปีที่ได้รับ',
        // ประวัติการศึกษา อบรม ดูงาน (77-81)
        'ลำดับ', 'โครงการ', 'ชื่อหลักสูตรอบรม', 'ว/ด/ป ที่เริ่ม', 'ว/ด/ป ที่สิ้น',
        // ใบอนุญาตประกอบวิชาชีพ (82-87)
        'ลำดับ', 'ประเภทใบอนุญาต', 'เลขที่ใบประกอบ', 'ชื่อใบประกอบ', 'ว/ด/ป ออกใบประกอบ', 'ว/ด/ป หมดอายุ',
        // ประวัติการรับเครื่องราชฯ (88-92)
        'ลำดับ', 'ปีที่ได้รับ', 'ชั้นเครื่องราชฯ', 'ตำแหน่ง', 'ลงวันที่',
    ];

    // ป้ายกลุ่มคอลัมน์ (แถวที่ 6) -> [คอลัมน์เริ่มต้น 1-based => ป้ายกลุ่ม]
    public const GROUPS = [
        2 => 'ประวัติส่วนตัว',
        29 => 'ที่อยู่ตามทะเบียนบ้าน',
        39 => 'ที่อยู่ที่ติดต่อได้',
        49 => 'ตำแหน่งงาน',
        60 => 'ข้อมูลครอบครัว',
        67 => 'ข้อมูลการศึกษา',
        73 => 'ข้อมูลเกียรติคุณ',
        77 => 'ประวัติการศึกษา อบรม ดูงาน',
        82 => 'ใบอนุญาตประกอบวิชาชีพ',
        88 => 'ประวัติการรับเครื่องราชฯ',
    ];

    // ดัชนีคอลัมน์ (1-based) ตรงกับ IMPORT_TEMPLATE_HEADERS/import:personnel
    public const COL = [
        'personnel_type' => 2, 'employee_code' => 3, 'position' => 4, 'department' => 5,
        'gender' => 6, 'thai_prefix' => 7, 'thai_firstname' => 8, 'thai_lastname' => 9,
        'eng_firstname' => 10, 'eng_lastname' => 11,
        'id_card_number' => 13, 'passport_number' => 14, 'passport_country' => 15,
        'date_of_birth' => 16, 'blood_group' => 17, 'nationality' => 18, 'ethnicity' => 19, 'religion' => 20,
        'phone' => 24, 'email' => 25, 'schedule' => 26,

        'reg_house_no' => 29, 'reg_moo' => 30, 'reg_village' => 31, 'reg_soi' => 32,
        'reg_building_floor' => 33, 'reg_road' => 34, 'reg_province' => 35, 'reg_district' => 36,
        'reg_sub_district' => 37, 'reg_postal_code' => 38,

        'cur_house_no' => 39, 'cur_moo' => 40, 'cur_village' => 41, 'cur_soi' => 42,
        'cur_building_floor' => 43, 'cur_road' => 44, 'cur_province' => 45, 'cur_district' => 46,
        'cur_sub_district' => 47, 'cur_postal_code' => 48,

        'work_status' => 49, 'level' => 50, 'school_start_date' => 51, 'appointment_date' => 52,
        'salary' => 53, 'government_start_date' => 54, 'position_allowance' => 55,
        'academic_allowance' => 56, 'retirement_date' => 57,

        'edu_start_year' => 68, 'edu_end_year' => 69, 'edu_level' => 70, 'edu_major' => 71, 'edu_institution' => 72,

        'honor_type' => 74, 'honor_org' => 75, 'honor_year' => 76,

        'training_project' => 78, 'training_course' => 79, 'training_start' => 80, 'training_end' => 81,

        'license_type' => 83, 'license_number' => 84, 'license_name' => 85, 'license_issue' => 86, 'license_expiry' => 87,

        'decoration_year' => 89, 'decoration_class' => 90, 'decoration_position' => 91, 'decoration_gazette_date' => 92,
    ];

    public static function totalCols(): int
    {
        return count(self::HEADERS);
    }

    /**
     * วาดแถว 1-7 (หัวโรงเรียน + ป้ายกลุ่ม + หัวคอลัมน์) ให้เหมือนกันทุกไฟล์ — คืนค่า [totalCols, lastColLetter, hasLogo]
     */
    public static function buildHeaderRows(Worksheet $sheet, ?string $instructionText = null): array
    {
        $totalCols = self::totalCols();
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);

        $hasLogo = ExcelSchoolHeader::apply($sheet, $totalCols);
        if ($instructionText) {
            ExcelSchoolHeader::applyInstructionRow($sheet, $totalCols, $instructionText);
        }

        $groupStarts = array_keys(self::GROUPS);
        foreach (self::GROUPS as $startCol => $groupLabel) {
            $idx = array_search($startCol, $groupStarts);
            $endCol = ($groupStarts[$idx + 1] ?? ($totalCols + 1)) - 1;
            $startLetter = Coordinate::stringFromColumnIndex($startCol);
            $endLetter = Coordinate::stringFromColumnIndex($endCol);
            $sheet->mergeCells("{$startLetter}6:{$endLetter}6");
            $sheet->setCellValue("{$startLetter}6", $groupLabel);
        }
        $sheet->getStyle("B6:{$lastCol}6")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '34495E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(6)->setRowHeight(24);

        $sheet->mergeCells('A6:A7');
        $sheet->setCellValue('A6', 'ลำดับ');
        $sheet->getStyle('A6:A7')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '34495E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        foreach (self::HEADERS as $i => $header) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            if ($i !== 0 || !$hasLogo) {
                $sheet->getColumnDimension($col)->setWidth(15);
            }
            if ($i === 0) {
                continue;
            }
            $sheet->setCellValue("{$col}7", $header);
        }
        $sheet->getStyle("B7:{$lastCol}7")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF2F8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B8C1']]],
        ]);
        $sheet->getRowDimension(7)->setRowHeight(32);

        $sheet->freezePane('A8');

        return [$totalCols, $lastCol, $hasLogo];
    }

    // แปลงวันที่ Y-m-d (ค.ศ.) เป็น d/m/Y (พ.ศ.) สำหรับเขียนลงไฟล์ Excel
    public static function toThaiDate(?string $ymd): string
    {
        if (!$ymd) {
            return '';
        }
        try {
            $date = \Carbon\Carbon::parse($ymd);
        } catch (\Throwable) {
            return '';
        }
        return $date->format('d/m/') . ($date->year + 543);
    }

    // แปลงวันที่ d/m/Y (พ.ศ.) จากไฟล์ Excel กลับเป็น Y-m-d (ค.ศ.)
    public static function fromThaiDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || !preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $value, $m)) {
            return null;
        }

        [, $day, $month, $yearBe] = $m;
        $yearCe = (int) $yearBe - 543;

        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $yearCe, $month, $day);
    }
}
