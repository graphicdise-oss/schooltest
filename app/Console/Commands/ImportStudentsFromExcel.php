<?php

namespace App\Console\Commands;

use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\Semester;
use App\Models\Academic\StudentSection;
use App\Models\Student;
use App\Models\StudentAddress;
use App\Models\StudentEducation;
use App\Models\StudentFamily;
use App\Models\StudentHealth;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportStudentsFromExcel extends Command
{
    protected $signature = 'import:students
        {file : พาธไฟล์ .xlsx}
        {--dry-run : ตรวจสอบข้อมูลเฉยๆ ไม่บันทึกลงฐานข้อมูลจริง}
        {--limit=0 : จำกัดจำนวนแถวที่ประมวลผล (0 = ทั้งหมด)}
        {--fill-classroom-number : สำหรับนักเรียนที่มีอยู่แล้ว (import ไปรอบก่อนหน้า) เติมเฉพาะช่อง "เลขที่นักเรียน" ให้ ถ้าช่องนั้นยังว่างอยู่ ไม่แตะข้อมูลช่องอื่น}
        {--assign-rooms : จัดห้องเรียนตามคอลัมน์ "ชั้น/ห้อง" ในไฟล์ (เอาแค่ชั้น+เลขห้อง ไม่เอาแผนการเรียน) สร้างห้อง/ชั้นใหม่อัตโนมัติถ้ายังไม่มี}
        {--semester-id= : ระบุภาคเรียนที่จะใช้จัดห้อง (ค่าเริ่มต้น = ภาคเรียนปัจจุบันที่ตั้ง is_current ไว้)}';

    protected $description = 'นำเข้าข้อมูลนักเรียนจากไฟล์ Excel (ข้อมูลส่วนตัว/ที่อยู่/ผู้ปกครอง/สุขภาพ) โดยข้ามนักเรียนที่มีเลขบัตรประชาชนซ้ำในระบบอยู่แล้ว และไม่ยุ่งกับการจัดห้องเรียน';

    // ดัชนีคอลัมน์ (1-based) ในไฟล์ Excel ต้นฉบับของโรงเรียน -> ไม่ผูกกับข้อความหัวตาราง เพราะมีชื่อซ้ำกันหลายจุด (เช่น "นามสกุล")
    private const COL = [
        'status' => 4, 'id_card_number' => 5, 'student_code' => 6, 'classroom_number' => 3, 'class_room' => 2,
        'gender' => 9, 'thai_prefix' => 10, 'thai_firstname' => 11, 'thai_lastname' => 12,
        'thai_nickname' => 13, 'english_firstname' => 14, 'english_lastname' => 15, 'english_nickname' => 16,
        'date_of_birth' => 17, 'religion' => 18, 'nationality' => 19, 'ethnicity' => 20,
        'total_siblings' => 21, 'sibling_order' => 22, 'phone_number' => 24, 'email' => 25,

        // ที่อยู่ตามทะเบียนบ้าน (Registered) + สถานที่เกิด
        'reg_house_code' => 27, 'reg_house_number' => 28, 'reg_soi' => 29, 'reg_village_no' => 30,
        'reg_road' => 31, 'reg_subdistrict_id' => 32, 'reg_district_id' => 33, 'reg_province_id' => 34,
        'reg_postal_code' => 35, 'reg_home_phone' => 36,
        'birth_hospital_th' => 37, 'birth_subdistrict_id' => 38, 'birth_district_id' => 39, 'birth_province_id' => 40,

        // ที่อยู่ปัจจุบัน (Current)
        'cur_house_number' => 41, 'cur_village_no' => 42, 'cur_soi' => 43, 'cur_road' => 44,
        'cur_subdistrict_id' => 45, 'cur_district_id' => 46, 'cur_province_id' => 47, 'cur_postal_code' => 48,
        'cur_home_phone' => 49, 'stay_with_first_name' => 50, 'stay_with_last_name' => 51,
        'house_characteristic' => 52, 'stay_with_email' => 53, 'emergency_contact_phone' => 54,

        // ประวัติการศึกษาเดิม
        'edu_school_name' => 58, 'edu_subdistrict_id' => 59, 'edu_district_id' => 60, 'edu_province_id' => 61,
        'edu_education_level' => 62, 'edu_gpa' => 63, 'edu_transfer_reason' => 64,

        // ผู้ปกครอง / บิดา / มารดา (แต่ละกลุ่มมีคอลัมน์ตรงกัน offset คนละชุด)
        'guardian' => [
            'relationship' => 74, 'prefix_th' => 75, 'first_name_th' => 76, 'last_name_th' => 77,
            'first_name_en' => 78, 'last_name_en' => 79, 'birth_date' => 80, 'religion' => 81,
            'nationality' => 82, 'ethnicity' => 83, 'house_number' => 84, 'village_no' => 85, 'soi' => 86,
            'road' => 87, 'subdistrict_id' => 88, 'district_id' => 89, 'province_id' => 90, 'postal_code' => 91,
            'phone_home' => 92, 'phone_mobile' => 93, 'phone_work' => 94, 'family_status' => 95,
            'education_level' => 96, 'occupation' => 97, 'workplace' => 98, 'monthly_income' => 99,
            'tuition_subsidy' => 101,
        ],
        'father' => [
            'prefix_th' => 102, 'first_name_th' => 103, 'last_name_th' => 104,
            'first_name_en' => 105, 'last_name_en' => 106, 'birth_date' => 107, 'religion' => 108,
            'nationality' => 109, 'ethnicity' => 110, 'house_number' => 111, 'village_no' => 112, 'soi' => 113,
            'road' => 114, 'subdistrict_id' => 115, 'district_id' => 116, 'province_id' => 117, 'postal_code' => 118,
            'phone_home' => 119, 'phone_mobile' => 120, 'phone_work' => 121,
            'education_level' => 122, 'occupation' => 123, 'workplace' => 124, 'monthly_income' => 125,
        ],
        'mother' => [
            'prefix_th' => 127, 'first_name_th' => 128, 'last_name_th' => 129,
            'first_name_en' => 130, 'last_name_en' => 131, 'birth_date' => 132, 'religion' => 133,
            'nationality' => 134, 'ethnicity' => 135, 'house_number' => 136, 'village_no' => 137, 'soi' => 138,
            'road' => 139, 'subdistrict_id' => 140, 'district_id' => 141, 'province_id' => 142, 'postal_code' => 143,
            'phone_home' => 144, 'phone_mobile' => 145, 'phone_work' => 146,
            'education_level' => 147, 'occupation' => 148, 'workplace' => 149, 'monthly_income' => 150,
        ],

        // สุขภาพ
        'blood_group' => 154, 'food_allergy' => 155, 'medicine_allergy' => 156, 'other_allergy' => 157,
        'chronic_disease' => 158, 'serious_disease' => 159,

        'transportation_type' => 160,
    ];

    // คอลัมน์ในไฟล์ที่ไม่มีช่องในระบบให้เก็บ ณ ตอนนี้ (แจ้งเตือนผู้ใช้ ไม่ทิ้งข้อมูลแบบเงียบๆ)
    private const UNMAPPED_LABELS = [
        'รหัสลายนิ้วมือ (7)', 'พี่/น้องเรียนในโรงเรียนนี้ (23)', 'เงินคงเหลือ (26)',
        'บ้านเกิดเลขที่...เบอร์โทรศัพท์ (65-73, ที่อยู่บ้านเกิดชุดที่ 2)',
        'เพื่อนใกล้บ้าน/นามสกุล/เบอร์โทรศัพท์เพื่อน (55-57)',
        'รายได้ต่อปี ของผู้ปกครอง/บิดา/มารดา (100, 126, 151)',
        'น้ำหนัก/ส่วนสูง (152-153)', 'ประเภทนักเรียน (161)', 'หมายเลขบัตร (162)',
    ];

    public function handle(): int
    {
        $path = $this->argument('file');
        if (!is_file($path)) {
            $this->error("ไม่พบไฟล์: {$path}");
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $assignRooms = (bool) $this->option('assign-rooms');

        $semesterId = null;
        if ($assignRooms) {
            $semesterId = $this->option('semester-id')
                ? (int) $this->option('semester-id')
                : Semester::where('is_current', true)->value('semester_id');

            if (!$semesterId) {
                $this->error('ไม่พบภาคเรียนปัจจุบัน (is_current) กรุณาระบุ --semester-id=<รหัสภาคเรียน> เอง');
                return self::FAILURE;
            }
        }

        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่มีการบันทึกข้อมูลจริง ===' : '=== โหมดนำเข้าจริง ===');
        $this->newLine();
        $this->warn('หมายเหตุ: คอลัมน์ต่อไปนี้ในไฟล์ยังไม่ถูกนำเข้า (ไม่มีช่องรองรับในระบบตอนนี้ หรือข้ามตามที่ตกลงกันไว้):');
        foreach (self::UNMAPPED_LABELS as $label) {
            $this->line("  - {$label}");
        }
        if (!$assignRooms) {
            $this->line('  - ชั้น/ห้อง (2) — ไม่ได้ใส่ --assign-rooms จึงยังไม่จัดห้องให้ (เลขที่นักเรียนตามคอลัมน์ 3 นำเข้าแล้ว)');
        } else {
            $this->line("  - ชั้น/ห้อง (2): จะจัดเข้าห้องตามชั้น+เลขห้องเท่านั้น (ตัดแผนการเรียน เช่น 'IEP','วิทย์-คณิต' ทิ้ง) โดยใช้ภาคเรียน semester_id={$semesterId}");
        }
        $this->newLine();

        $sheet = IOFactory::load($path)->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $created = 0;
        $skippedExisting = 0;
        $skippedInvalid = 0;
        $filledClassroomNumber = 0;
        $roomsAssigned = 0;
        $roomsAlready = 0;
        $roomsSkippedConflict = 0;
        $roomsUnparsed = 0;
        $newLevels = [];
        $newSections = [];
        $errors = [];

        $processed = 0;
        for ($row = 7; $row <= $highestRow; $row++) {
            if ($limit > 0 && $processed >= $limit) {
                break;
            }

            $get = fn (int $col) => trim((string) ($sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue() ?? ''));
            $rowNo = $get(1);

            // ใช้ค่าดิบตามที่กรอกจริง (ห้ามตัดตัวอักษรทิ้ง) เพราะนักเรียนต่างชาติบางคนใช้รหัส 13 หลักแบบมีตัวอักษรนำ
            // เช่น G691300019802 ซึ่งผ่านกฎ size:13 ของระบบเดิมอยู่แล้ว (ไม่ใช่ตัวเลขล้วนเสมอไป)
            $idCard = $get(self::COL['id_card_number']);
            $firstname = $get(self::COL['thai_firstname']);

            if ($idCard === '' && $firstname === '') {
                continue; // แถวว่าง
            }

            $processed++;
            $label = "แถว {$row} (ลำดับ {$rowNo}: {$get(self::COL['thai_prefix'])}{$firstname} {$get(self::COL['thai_lastname'])})";

            if (strlen($idCard) !== 13) {
                $skippedInvalid++;
                $errors[] = "{$label}: เลขบัตรประชาชน/รหัสไม่ครบ 13 หลัก ('{$idCard}') — ข้าม";
                continue;
            }

            $existing = Student::where('id_card_number', $idCard)->first();
            if ($existing) {
                $skippedExisting++;

                if ($this->option('fill-classroom-number') && empty($existing->classroom_number)) {
                    $classroomNumber = $get(self::COL['classroom_number']) ?: null;
                    if ($classroomNumber !== null) {
                        if (!$this->option('dry-run')) {
                            $existing->update(['classroom_number' => $classroomNumber]);
                        }
                        $filledClassroomNumber++;
                    }
                }

                if ($assignRooms) {
                    $roomResult = null;
                    try {
                        DB::transaction(function () use ($existing, $get, $semesterId, &$newLevels, &$newSections, &$roomResult) {
                            $roomResult = $this->assignRoom($existing, $get(self::COL['class_room']), $get(self::COL['classroom_number']), $semesterId, $newLevels, $newSections);
                            if ($this->option('dry-run')) {
                                throw new \RuntimeException('__DRY_RUN_ROLLBACK__');
                            }
                        });
                    } catch (\RuntimeException $e) {
                        if ($e->getMessage() !== '__DRY_RUN_ROLLBACK__') {
                            $errors[] = "{$label}: จัดห้องไม่สำเร็จ — " . $e->getMessage();
                            $roomResult = null;
                        }
                    }

                    match ($roomResult) {
                        'assigned' => $roomsAssigned++,
                        'already' => $roomsAlready++,
                        'conflict' => $roomsSkippedConflict++,
                        'unparsed' => $roomsUnparsed++,
                        default => null,
                    };
                }

                continue; // มีอยู่แล้ว ไม่แตะข้อมูลอื่นของเดิม ตามที่ตกลงไว้
            }

            $dob = $this->parseThaiDate($get(self::COL['date_of_birth']));
            if (!$dob) {
                $skippedInvalid++;
                $errors[] = "{$label}: วันเกิดอ่านไม่ได้ ('{$get(self::COL['date_of_birth'])}') — ข้าม";
                continue;
            }

            $newStudentRoomResult = null;
            try {
                DB::transaction(function () use ($get, $idCard, $dob, $row, $assignRooms, $semesterId, &$newLevels, &$newSections, &$newStudentRoomResult) {
                    $student = Student::create([
                        'student_code'       => $get(self::COL['student_code']) ?: null,
                        'id_card_number'     => $idCard,
                        'status'              => $get(self::COL['status']) ?: 'กำลังศึกษา',
                        'gender'              => $this->mapGender($get(self::COL['gender'])),
                        'thai_prefix'         => $get(self::COL['thai_prefix']),
                        'thai_firstname'      => $get(self::COL['thai_firstname']),
                        'thai_lastname'       => $get(self::COL['thai_lastname']),
                        'thai_nickname'       => $get(self::COL['thai_nickname']) ?: null,
                        'english_firstname'   => $get(self::COL['english_firstname']) ?: null,
                        'english_lastname'    => $get(self::COL['english_lastname']) ?: null,
                        'english_nickname'    => $get(self::COL['english_nickname']) ?: null,
                        'date_of_birth'       => $dob,
                        'religion'            => $get(self::COL['religion']) ?: null,
                        'nationality'         => $get(self::COL['nationality']) ?: null,
                        'ethnicity'           => $get(self::COL['ethnicity']) ?: null,
                        'total_siblings'      => $get(self::COL['total_siblings']) ?: null,
                        'sibling_order'       => $get(self::COL['sibling_order']) ?: null,
                        'phone_number'        => $get(self::COL['phone_number']) ?: null,
                        'email'               => $get(self::COL['email']) ?: null,
                        'transportation_type' => $get(self::COL['transportation_type']) ?: null,
                        'classroom_number'    => $get(self::COL['classroom_number']) ?: null,
                        'parent_password'     => Hash::make($idCard),
                        'created_by'          => 'import:students',
                    ]);

                    StudentAddress::create([
                        'student_id' => $student->student_id,
                        'address_type' => 'Registered',
                        'house_code' => $get(self::COL['reg_house_code']) ?: null,
                        'house_number' => $get(self::COL['reg_house_number']) ?: null,
                        'village_no' => $get(self::COL['reg_village_no']) ?: null,
                        'soi' => $get(self::COL['reg_soi']) ?: null,
                        'road' => $get(self::COL['reg_road']) ?: null,
                        'subdistrict_id' => $get(self::COL['reg_subdistrict_id']) ?: null,
                        'district_id' => $get(self::COL['reg_district_id']) ?: null,
                        'province_id' => $get(self::COL['reg_province_id']) ?: null,
                        'postal_code' => $get(self::COL['reg_postal_code']) ?: null,
                        'home_phone' => $get(self::COL['reg_home_phone']) ?: null,
                        'birth_hospital_th' => $get(self::COL['birth_hospital_th']) ?: null,
                        'birth_subdistrict_id' => $get(self::COL['birth_subdistrict_id']) ?: null,
                        'birth_district_id' => $get(self::COL['birth_district_id']) ?: null,
                        'birth_province_id' => $get(self::COL['birth_province_id']) ?: null,
                    ]);

                    StudentAddress::create([
                        'student_id' => $student->student_id,
                        'address_type' => 'Current',
                        'house_number' => $get(self::COL['cur_house_number']) ?: null,
                        'village_no' => $get(self::COL['cur_village_no']) ?: null,
                        'soi' => $get(self::COL['cur_soi']) ?: null,
                        'road' => $get(self::COL['cur_road']) ?: null,
                        'subdistrict_id' => $get(self::COL['cur_subdistrict_id']) ?: null,
                        'district_id' => $get(self::COL['cur_district_id']) ?: null,
                        'province_id' => $get(self::COL['cur_province_id']) ?: null,
                        'postal_code' => $get(self::COL['cur_postal_code']) ?: null,
                        'home_phone' => $get(self::COL['cur_home_phone']) ?: null,
                        'house_characteristic' => $get(self::COL['house_characteristic']) ?: null,
                        'stay_with_first_name' => $get(self::COL['stay_with_first_name']) ?: null,
                        'stay_with_last_name' => $get(self::COL['stay_with_last_name']) ?: null,
                        'stay_with_email' => $get(self::COL['stay_with_email']) ?: null,
                        'emergency_contact_phone' => $get(self::COL['emergency_contact_phone']) ?: null,
                    ]);

                    if ($get(self::COL['edu_school_name']) !== '') {
                        StudentEducation::create([
                            'student_id' => $student->student_id,
                            'school_name' => $get(self::COL['edu_school_name']) ?: null,
                            'subdistrict_id' => $get(self::COL['edu_subdistrict_id']) ?: null,
                            'district_id' => $get(self::COL['edu_district_id']) ?: null,
                            'province_id' => $get(self::COL['edu_province_id']) ?: null,
                            'education_level' => $get(self::COL['edu_education_level']) ?: null,
                            'gpa' => $get(self::COL['edu_gpa']) ?: null,
                            'transfer_reason' => $get(self::COL['edu_transfer_reason']) ?: null,
                            'education_type' => 'ปกติ',
                        ]);
                    }

                    foreach (['guardian' => 'ผู้ปกครอง', 'father' => 'บิดา', 'mother' => 'มารดา'] as $key => $guardianType) {
                        $cols = self::COL[$key];
                        $firstNameTh = $get($cols['first_name_th']);
                        if ($firstNameTh === '') {
                            continue; // ไม่มีข้อมูลกลุ่มนี้ในแถว ไม่สร้างแถวเปล่า
                        }

                        StudentFamily::create([
                            'student_id' => $student->student_id,
                            'guardian_type' => $guardianType,
                            'family_type' => 'ปกติ',
                            'relationship' => isset($cols['relationship'])
                                ? $get($cols['relationship'])
                                : ($key === 'father' ? 'บิดา' : ($key === 'mother' ? 'มารดา' : null)),
                            'prefix_th' => $get($cols['prefix_th']) ?: null,
                            'first_name_th' => $firstNameTh,
                            'last_name_th' => $get($cols['last_name_th']) ?: null,
                            'first_name_en' => $get($cols['first_name_en']) ?: null,
                            'last_name_en' => $get($cols['last_name_en']) ?: null,
                            'birth_date' => $this->parseThaiDate($get($cols['birth_date'])),
                            'religion' => $get($cols['religion']) ?: null,
                            'nationality' => $get($cols['nationality']) ?: null,
                            'ethnicity' => $get($cols['ethnicity']) ?: null,
                            'house_number' => $get($cols['house_number']) ?: null,
                            'village_no' => $get($cols['village_no']) ?: null,
                            'soi' => $get($cols['soi']) ?: null,
                            'road' => $get($cols['road']) ?: null,
                            'subdistrict_id' => $get($cols['subdistrict_id']) ?: null,
                            'district_id' => $get($cols['district_id']) ?: null,
                            'province_id' => $get($cols['province_id']) ?: null,
                            'postal_code' => $get($cols['postal_code']) ?: null,
                            'phone_home' => $get($cols['phone_home']) ?: null,
                            'phone_mobile' => $get($cols['phone_mobile']) ?: null,
                            'phone_work' => $get($cols['phone_work']) ?: null,
                            'family_status' => isset($cols['family_status']) ? $this->mapFamilyStatus($get($cols['family_status'])) : null,
                            'education_level' => $get($cols['education_level']) ?: null,
                            'occupation' => $get($cols['occupation']) ?: null,
                            'workplace' => $get($cols['workplace']) ?: null,
                            'monthly_income' => $get($cols['monthly_income']) ?: null,
                            'tuition_subsidy' => isset($cols['tuition_subsidy']) ? ($get($cols['tuition_subsidy']) ?: null) : null,
                        ]);
                    }

                    if ($get(self::COL['blood_group']) !== '' || $get(self::COL['food_allergy']) !== '' || $get(self::COL['chronic_disease']) !== '') {
                        StudentHealth::create([
                            'student_id' => $student->student_id,
                            'blood_group' => $get(self::COL['blood_group']) ?: null,
                            'food_allergy' => $get(self::COL['food_allergy']) ?: null,
                            'medicine_allergy' => $get(self::COL['medicine_allergy']) ?: null,
                            'other_allergy' => $get(self::COL['other_allergy']) ?: null,
                            'chronic_disease' => $get(self::COL['chronic_disease']) ?: null,
                            'serious_disease' => $get(self::COL['serious_disease']) ?: null,
                        ]);
                    }

                    if ($assignRooms) {
                        $newStudentRoomResult = $this->assignRoom($student, $get(self::COL['class_room']), $get(self::COL['classroom_number']), $semesterId, $newLevels, $newSections);
                    }

                    if ($this->option('dry-run')) {
                        // ย้อนกลับเสมอในโหมดทดสอบ แม้ว่าทุกอย่างจะผ่านการตรวจสอบแล้วก็ตาม
                        throw new \RuntimeException('__DRY_RUN_ROLLBACK__');
                    }
                });

                $created++;
                match ($newStudentRoomResult) {
                    'assigned' => $roomsAssigned++,
                    'conflict' => $roomsSkippedConflict++,
                    'unparsed' => $roomsUnparsed++,
                    default => null,
                };
            } catch (\RuntimeException $e) {
                if ($e->getMessage() === '__DRY_RUN_ROLLBACK__') {
                    $created++; // นับเป็น "จะสร้าง" ในโหมดทดสอบ
                    match ($newStudentRoomResult) {
                        'assigned' => $roomsAssigned++,
                        'conflict' => $roomsSkippedConflict++,
                        'unparsed' => $roomsUnparsed++,
                        default => null,
                    };
                } else {
                    $skippedInvalid++;
                    $errors[] = "{$label}: {$e->getMessage()}";
                }
            } catch (\Throwable $e) {
                $skippedInvalid++;
                $errors[] = "{$label}: " . $e->getMessage();
            }
        }

        $this->newLine();
        $this->info("ประมวลผลทั้งหมด: {$processed} แถว");
        $this->info(($dryRun ? 'จะสร้างใหม่: ' : 'สร้างใหม่สำเร็จ: ') . "{$created} คน");
        $this->info("ข้าม (มีในระบบอยู่แล้ว): {$skippedExisting} คน");
        $this->info("ข้าม (ข้อมูลไม่ครบ/ผิดพลาด): {$skippedInvalid} คน");
        if ($this->option('fill-classroom-number')) {
            $this->info(($dryRun ? 'จะเติมเลขที่นักเรียนให้: ' : 'เติมเลขที่นักเรียนสำเร็จ: ') . "{$filledClassroomNumber} คน");
        }
        if ($assignRooms) {
            $this->info(($dryRun ? 'จะจัดห้องให้: ' : 'จัดห้องสำเร็จ: ') . "{$roomsAssigned} คน");
            $this->info("อยู่ห้องนี้อยู่แล้ว (ข้าม): {$roomsAlready} คน");
            $this->info("ข้าม (มีห้องอื่นอยู่แล้วในภาคเรียนนี้ ไม่ทับของเดิม): {$roomsSkippedConflict} คน");
            $this->info("ข้าม (อ่านชั้น/ห้องจากไฟล์ไม่ได้): {$roomsUnparsed} คน");
            if (!empty($newLevels)) {
                $this->info('ชั้นใหม่ที่ ' . ($dryRun ? 'จะสร้าง' : 'สร้างแล้ว') . ': ' . implode(', ', array_keys($newLevels)));
            }
            if (!empty($newSections)) {
                $this->info('ห้องใหม่ที่ ' . ($dryRun ? 'จะสร้าง' : 'สร้างแล้ว') . ': ' . implode(', ', array_keys($newSections)));
            }
        }

        if (!empty($errors)) {
            $this->newLine();
            $this->warn('รายละเอียดแถวที่ข้าม/ผิดพลาด:');
            foreach ($errors as $err) {
                $this->line("  - {$err}");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->comment('นี่คือผลทดสอบ ยังไม่มีข้อมูลถูกบันทึกจริง หากตรวจสอบแล้วถูกต้อง ให้รันคำสั่งเดิมโดยไม่ใส่ --dry-run');
        }

        return self::SUCCESS;
    }

    // จัดนักเรียนเข้าห้องตามคอลัมน์ "ชั้น/ห้อง" (เอาแค่ชั้น+เลขห้อง ตัดแผนการเรียนทิ้งตามที่ตกลงไว้)
    // คืนค่า: assigned | already (อยู่ห้องนี้แล้ว) | conflict (มีห้องอื่นในภาคเรียนนี้แล้ว ไม่ทับของเดิม) | unparsed (อ่านชั้น/ห้องจากไฟล์ไม่ได้)
    private function assignRoom(Student $student, string $rawClassRoom, string $rollNumberRaw, int $semesterId, array &$newLevels, array &$newSections): string
    {
        $parsed = $this->parseClassRoom($rawClassRoom);
        if (!$parsed) {
            return 'unparsed';
        }

        $level = Level::firstOrCreate(
            ['name' => $parsed['level_name']],
            ['level_group' => null, 'sort_order' => (Level::max('sort_order') ?? 0) + 1]
        );
        if ($level->wasRecentlyCreated) {
            $newLevels[$parsed['level_name']] = true;
        }

        $section = ClassSection::firstOrCreate(
            ['level_id' => $level->level_id, 'section_number' => $parsed['section_number'], 'semester_id' => $semesterId],
            ['homeroom_teacher_id' => null, 'max_students' => null, 'curriculum_id' => null]
        );
        if ($section->wasRecentlyCreated) {
            $newSections["{$parsed['level_name']}/{$parsed['section_number']}"] = true;
        }

        if (StudentSection::where('student_id', $student->student_id)->where('section_id', $section->section_id)->exists()) {
            return 'already';
        }

        $conflict = StudentSection::where('student_id', $student->student_id)
            ->whereHas('classSection', fn ($q) => $q->where('semester_id', $semesterId))
            ->exists();
        if ($conflict) {
            return 'conflict';
        }

        $rollNumber = $rollNumberRaw !== '' ? (int) $rollNumberRaw : null;
        if ($rollNumber === null) {
            $rollNumber = (StudentSection::where('section_id', $section->section_id)->max('student_number') ?? 0) + 1;
        }

        StudentSection::create([
            'student_id' => $student->student_id,
            'section_id' => $section->section_id,
            'student_number' => $rollNumber,
            'status' => 'กำลังศึกษา',
        ]);

        return 'assigned';
    }

    // แยก "ชั้น/ห้อง" เช่น "ม.1/4 ICP" -> ชั้น "ม.1" + ห้อง 4 (ตัดแผนการเรียน "ICP" ทิ้ง)
    private function parseClassRoom(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '' || !str_contains($raw, '/')) {
            return null;
        }

        [$levelName, $rest] = explode('/', $raw, 2);
        $levelName = trim($levelName);
        $rest = trim($rest);

        if ($levelName === '' || $rest === '' || !preg_match('/^(\d+)/', $rest, $m)) {
            return null;
        }

        return ['level_name' => $levelName, 'section_number' => (int) $m[1]];
    }

    private function mapGender(string $value): ?string
    {
        return match ($value) {
            'ชาย' => 'M',
            'หญิง' => 'F',
            default => $value ?: null,
        };
    }

    private function mapFamilyStatus(string $value): ?string
    {
        $allowed = ['อยู่ร่วมกัน', 'หย่าร้าง', 'แยกกันอยู่', 'ถึงแก่กรรม'];
        return in_array($value, $allowed, true) ? $value : null;
    }

    // แปลงวันที่รูปแบบไทย d/m/Y (พ.ศ.) เป็น Y-m-d (ค.ศ.) สำหรับคอลัมน์ date
    private function parseThaiDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (!preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $value, $m)) {
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
