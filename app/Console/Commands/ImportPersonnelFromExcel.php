<?php

namespace App\Console\Commands;

use App\Models\Personne\Personnel;
use App\Models\Personne\PersonnelAddress;
use App\Models\Personne\PersonnelDecoration;
use App\Models\Personne\PersonnelHonor;
use App\Models\Personne\PersonnelLicense;
use App\Models\Personne\PersonnelPosition;
use App\Models\Personne\PersonnelTraining;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPersonnelFromExcel extends Command
{
    protected $signature = 'import:personnel
        {file : พาธไฟล์ .xlsx}
        {--dry-run : ตรวจสอบข้อมูลเฉยๆ ไม่บันทึกลงฐานข้อมูลจริง}
        {--limit=0 : จำกัดจำนวนแถวที่ประมวลผล (0 = ทั้งหมด)}';

    protected $description = 'นำเข้าข้อมูลบุคลากรจากไฟล์ Excel โดยข้ามบุคลากรที่มีรหัสพนักงานซ้ำในระบบอยู่แล้ว';

    // ดัชนีคอลัมน์ (1-based) ตรงกับตำแหน่งจริงในไฟล์ Excel รายชื่อพนักงานของโรงเรียน (แถวหัวตารางที่ 7)
    private const COL = [
        'personnel_type' => 2, 'employee_code' => 3, 'position' => 4, 'department' => 5,
        'gender' => 6, 'thai_prefix' => 7, 'thai_firstname' => 8, 'thai_lastname' => 9,
        'eng_firstname' => 10, 'eng_lastname' => 11,
        'id_card_number' => 13, 'passport_number' => 14, 'passport_country' => 15,
        'date_of_birth' => 16, 'blood_group' => 17, 'nationality' => 18, 'ethnicity' => 19, 'religion' => 20,
        'phone' => 24, 'email' => 25, 'schedule' => 26,

        // ที่อยู่ตามทะเบียนบ้าน (Registered)
        'reg_house_no' => 29, 'reg_moo' => 30, 'reg_village' => 31, 'reg_soi' => 32,
        'reg_building_floor' => 33, 'reg_road' => 34, 'reg_province' => 35, 'reg_district' => 36,
        'reg_sub_district' => 37, 'reg_postal_code' => 38,

        // ที่อยู่ที่ติดต่อได้ (Current)
        'cur_house_no' => 39, 'cur_moo' => 40, 'cur_village' => 41, 'cur_soi' => 42,
        'cur_building_floor' => 43, 'cur_road' => 44, 'cur_province' => 45, 'cur_district' => 46,
        'cur_sub_district' => 47, 'cur_postal_code' => 48,

        // ตำแหน่งงาน
        'work_status' => 49, 'level' => 50, 'school_start_date' => 51, 'appointment_date' => 52,
        'salary' => 53, 'government_start_date' => 54, 'position_allowance' => 55,
        'academic_allowance' => 56, 'retirement_date' => 57,

        // ข้อมูลการศึกษา (แถวเดียวต่อคนในไฟล์นี้)
        'edu_start_year' => 68, 'edu_end_year' => 69, 'edu_level' => 70, 'edu_major' => 71, 'edu_institution' => 72,

        // เกียรติคุณ
        'honor_type' => 74, 'honor_org' => 75, 'honor_year' => 76,

        // ประวัติการอบรม/ศึกษา/ดูงาน
        'training_project' => 78, 'training_course' => 79, 'training_start' => 80, 'training_end' => 81,

        // ใบอนุญาตประกอบวิชาชีพ
        'license_type' => 83, 'license_number' => 84, 'license_name' => 85, 'license_issue' => 86, 'license_expiry' => 87,

        // ประวัติการรับเครื่องราชฯ
        'decoration_year' => 89, 'decoration_class' => 90, 'decoration_position' => 91, 'decoration_gazette_date' => 92,
    ];

    // คอลัมน์ในไฟล์ที่ไม่มีช่องในระบบให้เก็บ ณ ตอนนี้ (แจ้งเตือนผู้ใช้ ไม่ทิ้งข้อมูลแบบเงียบๆ)
    private const UNMAPPED_LABELS = [
        'ยอดเงินคงเหลือ (12)',
        'สถานภาพสมรส/ชื่อคู่สมรส/นามสกุลคู่สมรส (21-23) — ระบบยังไม่มีช่องเก็บข้อมูลนี้',
        'ใช้ระบบ Biometric/รหัสลายนิ้วมือ (27-28)',
        'รายได้สุทธิรวม/อายุงานเหลือ (58-59) — เป็นค่าคำนวณ ไม่ใช่ข้อมูลที่กรอกตรงๆ',
        'ข้อมูลครอบครัว (60-66) — ระบบยังไม่มีตารางเก็บข้อมูลครอบครัวของบุคลากร',
        'ใบอนุญาต: ชื่อหน่วยงานที่ออกใบอนุญาต — ไม่มีคอลัมน์นี้ในไฟล์',
        'เครื่องราชฯ: เล่ม/ตอน/หน้าราชกิจจานุเบกษา — ไม่มีคอลัมน์นี้ในไฟล์ (มีแค่วันที่ประกาศ)',
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

        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่มีการบันทึกข้อมูลจริง ===' : '=== โหมดนำเข้าจริง ===');
        $this->newLine();
        $this->warn('หมายเหตุ: คอลัมน์ต่อไปนี้ในไฟล์ยังไม่ถูกนำเข้า:');
        foreach (self::UNMAPPED_LABELS as $label) {
            $this->line("  - {$label}");
        }
        $this->newLine();

        $sheet = IOFactory::load($path)->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $created = 0;
        $skippedExisting = 0;
        $skippedInvalid = 0;
        $errors = [];

        $processed = 0;
        for ($row = 8; $row <= $highestRow; $row++) {
            if ($limit > 0 && $processed >= $limit) {
                break;
            }

            $get = fn (int $col) => trim((string) ($sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue() ?? ''));

            $employeeCode = $get(self::COL['employee_code']);
            $firstname = $get(self::COL['thai_firstname']);

            if ($employeeCode === '' && $firstname === '') {
                continue; // แถวว่าง
            }

            $processed++;
            $label = "แถว {$row} ({$get(self::COL['thai_prefix'])}{$firstname} {$get(self::COL['thai_lastname'])})";

            if ($employeeCode === '') {
                $skippedInvalid++;
                $errors[] = "{$label}: ไม่มีรหัสพนักงาน ระบุตัวคนไม่ได้ — ข้าม";
                continue;
            }

            if (Personnel::where('employee_code', $employeeCode)->exists()) {
                $skippedExisting++;
                continue; // มีอยู่แล้ว ไม่แตะข้อมูลเดิม
            }

            try {
                DB::transaction(function () use ($get) {
                    $personnel = Personnel::create([
                        'personnel_type'    => $get(self::COL['personnel_type']) ?: null,
                        'employee_code'     => $get(self::COL['employee_code']),
                        'position'          => $get(self::COL['position']) ?: null,
                        'department'        => $get(self::COL['department']) ?: null,
                        'gender'            => $this->mapGender($get(self::COL['gender'])),
                        'thai_prefix'       => $get(self::COL['thai_prefix']) ?: null,
                        'thai_firstname'    => $get(self::COL['thai_firstname']) ?: null,
                        'thai_lastname'     => $get(self::COL['thai_lastname']) ?: null,
                        'eng_firstname'     => $get(self::COL['eng_firstname']) ?: null,
                        'eng_lastname'      => $get(self::COL['eng_lastname']) ?: null,
                        'id_card_number'    => $get(self::COL['id_card_number']) ?: null,
                        'passport_number'   => $get(self::COL['passport_number']) ?: null,
                        'passport_country'  => $get(self::COL['passport_country']) ?: null,
                        'date_of_birth'     => $this->parseThaiDate($get(self::COL['date_of_birth'])),
                        'blood_group'       => $get(self::COL['blood_group']) ?: null,
                        'nationality'       => $get(self::COL['nationality']) ?: null,
                        'ethnicity'         => $get(self::COL['ethnicity']) ?: null,
                        'religion'          => $get(self::COL['religion']) ?: null,
                        'phone'             => $get(self::COL['phone']) ?: null,
                        'email'             => $get(self::COL['email']) ?: null,
                        'schedule'          => $get(self::COL['schedule']) ?: null,
                        'created_by'        => 'import:personnel',
                    ]);

                    $this->createAddressIfPresent($personnel, $get, 'reg', 'registered');
                    $this->createAddressIfPresent($personnel, $get, 'cur', 'current');

                    if ($get(self::COL['work_status']) !== '' || $get(self::COL['school_start_date']) !== '' || $get(self::COL['salary']) !== '') {
                        PersonnelPosition::create([
                            'personnel_id' => $personnel->personnel_id,
                            'work_status' => $get(self::COL['work_status']) ?: null,
                            'level' => $get(self::COL['level']) ?: null,
                            'school_start_date' => $this->parseThaiDate($get(self::COL['school_start_date'])),
                            'appointment_date' => $this->parseThaiDate($get(self::COL['appointment_date'])),
                            'salary' => $get(self::COL['salary']) ?: null,
                            'government_start_date' => $this->parseThaiDate($get(self::COL['government_start_date'])),
                            'position_allowance' => $get(self::COL['position_allowance']) ?: null,
                            'academic_allowance' => $get(self::COL['academic_allowance']) ?: null,
                            'retirement_date' => $this->parseThaiDate($get(self::COL['retirement_date'])),
                        ]);
                    }

                    if ($get(self::COL['edu_institution']) !== '') {
                        DB::table('personnel_educations')->insert([
                            'personnel_id' => $personnel->personnel_id,
                            'institution' => $get(self::COL['edu_institution']) ?: null,
                            'start_year' => $get(self::COL['edu_start_year']) ?: null,
                            'end_year' => $get(self::COL['edu_end_year']) ?: null,
                            'education_level' => $get(self::COL['edu_level']) ?: null,
                            'major' => $get(self::COL['edu_major']) ?: null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    if ($get(self::COL['honor_type']) !== '') {
                        PersonnelHonor::create([
                            'personnel_id' => $personnel->personnel_id,
                            'honor_type' => $get(self::COL['honor_type']),
                            'organization' => $get(self::COL['honor_org']) ?: null,
                            'year_received' => $get(self::COL['honor_year']) ?: null,
                        ]);
                    }

                    if ($get(self::COL['training_course']) !== '') {
                        PersonnelTraining::create([
                            'personnel_id' => $personnel->personnel_id,
                            'project' => $get(self::COL['training_project']) ?: null,
                            'course_name' => $get(self::COL['training_course']),
                            'start_date' => $this->parseThaiDate($get(self::COL['training_start'])),
                            'end_date' => $this->parseThaiDate($get(self::COL['training_end'])),
                        ]);
                    }

                    if ($get(self::COL['license_number']) !== '') {
                        PersonnelLicense::create([
                            'personnel_id' => $personnel->personnel_id,
                            'license_type' => $get(self::COL['license_type']) ?: null,
                            'license_number' => $get(self::COL['license_number']),
                            'license_name' => $get(self::COL['license_name']) ?: null,
                            'issue_date' => $this->parseThaiDate($get(self::COL['license_issue'])),
                            'expiry_date' => $this->parseThaiDate($get(self::COL['license_expiry'])),
                        ]);
                    }

                    if ($get(self::COL['decoration_class']) !== '') {
                        PersonnelDecoration::create([
                            'personnel_id' => $personnel->personnel_id,
                            'year_received' => $get(self::COL['decoration_year']) ?: null,
                            'decoration_class' => $get(self::COL['decoration_class']),
                            'position' => $get(self::COL['decoration_position']) ?: null,
                            'gazette_date' => $this->parseThaiDate($get(self::COL['decoration_gazette_date'])),
                        ]);
                    }

                    if ($this->option('dry-run')) {
                        throw new \RuntimeException('__DRY_RUN_ROLLBACK__');
                    }
                });

                $created++;
            } catch (\RuntimeException $e) {
                if ($e->getMessage() === '__DRY_RUN_ROLLBACK__') {
                    $created++; // นับเป็น "จะสร้าง" ในโหมดทดสอบ
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

    private function createAddressIfPresent(Personnel $personnel, callable $get, string $prefix, string $addressType): void
    {
        $fields = [
            'house_no' => "{$prefix}_house_no", 'moo' => "{$prefix}_moo", 'village' => "{$prefix}_village",
            'soi' => "{$prefix}_soi", 'building_floor' => "{$prefix}_building_floor", 'road' => "{$prefix}_road",
            'province' => "{$prefix}_province", 'district' => "{$prefix}_district",
            'sub_district' => "{$prefix}_sub_district", 'postal_code' => "{$prefix}_postal_code",
        ];

        $values = [];
        $hasData = false;
        foreach ($fields as $dbField => $colKey) {
            $v = $get(self::COL[$colKey]) ?: null;
            if ($v !== null) {
                $hasData = true;
            }
            $values[$dbField] = $v;
        }

        if (!$hasData) {
            return;
        }

        PersonnelAddress::create(array_merge($values, [
            'personnel_id' => $personnel->personnel_id,
            'address_type' => $addressType,
        ]));
    }

    private function mapGender(string $value): ?string
    {
        return match ($value) {
            'ชาย' => 'M',
            'หญิง' => 'F',
            default => $value ?: null,
        };
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
