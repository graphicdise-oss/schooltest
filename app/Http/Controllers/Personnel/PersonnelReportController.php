<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Personne\Personnel;
use App\Models\Personne\PersonnelAddress;
use App\Models\Personne\PersonnelDecoration;
use App\Models\Personne\PersonnelEducation;
use App\Models\Personne\PersonnelHonor;
use App\Models\Personne\PersonnelLicense;
use App\Models\Personne\PersonnelPosition;
use App\Models\Personne\PersonnelTraining;
use App\Services\PersonnelExcelTemplate;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PersonnelReportController extends Controller
{
    private function filteredStaffQuery(Request $request)
    {
        $query = Personnel::query()->orderBy('thai_firstname');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(
                fn($q) => $q->where('thai_firstname', 'like', "%$s%")
                    ->orWhere('thai_lastname', 'like', "%$s%")
                    ->orWhere('employee_code', 'like', "%$s%")
            );
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    // รายชื่อพนักงาน — รายงานแบบดูอย่างเดียว (ต่างจาก personnels.index ที่ใช้จัดการ/แก้ไข)
    public function staffList(Request $request)
    {
        if ($request->query('export') === 'excel') {
            return $this->exportStaffList($request);
        }

        $personnels = $this->filteredStaffQuery($request)->paginate(20)->withQueryString();

        $departments = Personnel::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('personnel.staff_list_report', compact('personnels', 'departments'));
    }

    // ใช้เทมเพลตเดียวกับ "แบบฟอร์มนำเข้าข้อมูลบุคลากร" ทุกคอลัมน์ (PersonnelExcelTemplate) ทั้ง export และ import
    // เพื่อให้เอาไฟล์ที่ export ออกไป แก้ไข แล้วอัปโหลดกลับเข้ามาได้เลยโดยไม่ต้องมีฟอร์มคนละแบบ
    private function exportStaffList(Request $request)
    {
        $personnels = $this->filteredStaffQuery($request)
            ->with(['addresses', 'positionDetail', 'educations', 'honors', 'trainings', 'licenses', 'decorations'])
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายชื่อพนักงาน');

        [$totalCols] = PersonnelExcelTemplate::buildHeaderRows($sheet);
        $col = fn(string $key) => Coordinate::stringFromColumnIndex(PersonnelExcelTemplate::COL[$key]);

        $row = 8;
        foreach ($personnels as $i => $p) {
            $regAddr = $p->addresses->firstWhere('address_type', 'registered');
            $curAddr = $p->addresses->firstWhere('address_type', 'current');
            $posDetail = $p->positionDetail;
            $edu = $p->educations->first();
            $honor = $p->honors->first();
            $training = $p->trainings->first();
            $license = $p->licenses->first();
            $decoration = $p->decorations->first();

            $sheet->setCellValue("A{$row}", $i + 1);
            $this->setRow($sheet, $row, [
                'personnel_type' => $p->personnel_type, 'employee_code' => $p->employee_code,
                'position' => $p->position, 'department' => $p->department,
                'gender' => $p->gender === 'M' ? 'ชาย' : ($p->gender === 'F' ? 'หญิง' : $p->gender),
                'thai_prefix' => $p->thai_prefix, 'thai_firstname' => $p->thai_firstname, 'thai_lastname' => $p->thai_lastname,
                'eng_firstname' => $p->eng_firstname, 'eng_lastname' => $p->eng_lastname,
                'id_card_number' => $p->id_card_number, 'passport_number' => $p->passport_number,
                'passport_country' => $p->passport_country,
                'date_of_birth' => PersonnelExcelTemplate::toThaiDate($p->date_of_birth),
                'blood_group' => $p->blood_group, 'nationality' => $p->nationality,
                'ethnicity' => $p->ethnicity, 'religion' => $p->religion,
                'phone' => $p->phone, 'email' => $p->email, 'schedule' => $p->schedule,

                'reg_house_no' => $regAddr?->house_no, 'reg_moo' => $regAddr?->moo, 'reg_village' => $regAddr?->village,
                'reg_soi' => $regAddr?->soi, 'reg_building_floor' => $regAddr?->building_floor, 'reg_road' => $regAddr?->road,
                'reg_province' => $regAddr?->province, 'reg_district' => $regAddr?->district,
                'reg_sub_district' => $regAddr?->sub_district, 'reg_postal_code' => $regAddr?->postal_code,

                'cur_house_no' => $curAddr?->house_no, 'cur_moo' => $curAddr?->moo, 'cur_village' => $curAddr?->village,
                'cur_soi' => $curAddr?->soi, 'cur_building_floor' => $curAddr?->building_floor, 'cur_road' => $curAddr?->road,
                'cur_province' => $curAddr?->province, 'cur_district' => $curAddr?->district,
                'cur_sub_district' => $curAddr?->sub_district, 'cur_postal_code' => $curAddr?->postal_code,

                'work_status' => $posDetail?->work_status, 'level' => $posDetail?->level,
                'school_start_date' => PersonnelExcelTemplate::toThaiDate($posDetail?->school_start_date),
                'appointment_date' => PersonnelExcelTemplate::toThaiDate($posDetail?->appointment_date),
                'salary' => $posDetail?->salary,
                'government_start_date' => PersonnelExcelTemplate::toThaiDate($posDetail?->government_start_date),
                'position_allowance' => $posDetail?->position_allowance,
                'academic_allowance' => $posDetail?->academic_allowance,
                'retirement_date' => PersonnelExcelTemplate::toThaiDate($posDetail?->retirement_date),

                'edu_start_year' => $edu?->start_year, 'edu_end_year' => $edu?->end_year,
                'edu_level' => $edu?->education_level, 'edu_major' => $edu?->major, 'edu_institution' => $edu?->institution,

                'honor_type' => $honor?->honor_type, 'honor_org' => $honor?->organization, 'honor_year' => $honor?->year_received,

                'training_project' => $training?->project, 'training_course' => $training?->course_name,
                'training_start' => PersonnelExcelTemplate::toThaiDate($training?->start_date),
                'training_end' => PersonnelExcelTemplate::toThaiDate($training?->end_date),

                'license_type' => $license?->license_type, 'license_number' => $license?->license_number,
                'license_name' => $license?->license_name,
                'license_issue' => PersonnelExcelTemplate::toThaiDate($license?->issue_date),
                'license_expiry' => PersonnelExcelTemplate::toThaiDate($license?->expiry_date),

                'decoration_year' => $decoration?->year_received, 'decoration_class' => $decoration?->decoration_class,
                'decoration_position' => $decoration?->position,
                'decoration_gazette_date' => PersonnelExcelTemplate::toThaiDate($decoration?->gazette_date),
            ], $col);

            $row++;
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'staff_list') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        $filename = 'รายชื่อพนักงาน_' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    private function setRow($sheet, int $row, array $values, callable $col): void
    {
        foreach ($values as $key => $value) {
            if (!array_key_exists($key, PersonnelExcelTemplate::COL)) {
                continue;
            }
            $sheet->setCellValue("{$col($key)}{$row}", $value ?? '');
        }
    }

    // นำเข้าจากไฟล์ Excel เทมเพลตเดียวกับที่ export ออกไป (แก้ไฟล์แล้วอัปโหลดกลับได้เลย ไม่ต้องมีฟอร์มแยก)
    // จับคู่ด้วย "รหัสพนักงาน": มีอยู่แล้ว = อัปเดตข้อมูลทุกส่วน, ยังไม่มี = สร้างใหม่
    public function importStaffList(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ], [
            'file.required' => 'กรุณาเลือกไฟล์ Excel',
            'file.mimes' => 'ไฟล์ต้องเป็นสกุล .xlsx หรือ .xls เท่านั้น',
        ]);

        $sheet = IOFactory::load($request->file('file')->getRealPath())->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $get = fn(int $row, string $key) => trim((string) ($sheet->getCell(Coordinate::stringFromColumnIndex(PersonnelExcelTemplate::COL[$key]) . $row)->getValue() ?? ''));
        $getDate = fn(int $row, string $key) => PersonnelExcelTemplate::fromExcelDateCell(
            $sheet->getCell(Coordinate::stringFromColumnIndex(PersonnelExcelTemplate::COL[$key]) . $row)
        );

        $created = 0;
        $updated = 0;
        $skipped = 0;

        for ($row = 8; $row <= $highestRow; $row++) {
            $employeeCode = $get($row, 'employee_code');
            $firstname = $get($row, 'thai_firstname');

            if ($employeeCode === '' && $firstname === '') {
                continue; // แถวว่าง
            }
            if ($employeeCode === '') {
                $skipped++;
                continue;
            }

            $data = [
                'personnel_type' => $get($row, 'personnel_type') ?: null,
                'position' => $get($row, 'position') ?: null,
                'department' => $get($row, 'department') ?: null,
                'gender' => $this->mapGender($get($row, 'gender')),
                'thai_prefix' => $get($row, 'thai_prefix') ?: null,
                'thai_firstname' => $firstname ?: null,
                'thai_lastname' => $get($row, 'thai_lastname') ?: null,
                'eng_firstname' => $get($row, 'eng_firstname') ?: null,
                'eng_lastname' => $get($row, 'eng_lastname') ?: null,
                'id_card_number' => $get($row, 'id_card_number') ?: null,
                'passport_number' => $get($row, 'passport_number') ?: null,
                'passport_country' => $get($row, 'passport_country') ?: null,
                'date_of_birth' => $getDate($row, 'date_of_birth'),
                'blood_group' => $get($row, 'blood_group') ?: null,
                'nationality' => $get($row, 'nationality') ?: null,
                'ethnicity' => $get($row, 'ethnicity') ?: null,
                'religion' => $get($row, 'religion') ?: null,
                'phone' => $get($row, 'phone') ?: null,
                'email' => $get($row, 'email') ?: null,
                'schedule' => $get($row, 'schedule') ?: null,
            ];

            $personnel = Personnel::where('employee_code', $employeeCode)->first();

            if ($personnel) {
                $personnel->update($data);
                $updated++;
            } else {
                $personnel = Personnel::create($data + [
                    'employee_code' => $employeeCode,
                    'role' => 'user',
                    'status' => 'ปฏิบัติงาน',
                    'created_by' => 'personnel-reports.staff-list.import',
                ]);
                $created++;
            }

            $this->upsertAddress($personnel, $get, $row, 'reg', 'registered');
            $this->upsertAddress($personnel, $get, $row, 'cur', 'current');

            if ($get($row, 'work_status') !== '' || $get($row, 'salary') !== '') {
                PersonnelPosition::updateOrCreate(['personnel_id' => $personnel->personnel_id], [
                    'work_status' => $get($row, 'work_status') ?: null,
                    'level' => $get($row, 'level') ?: null,
                    'school_start_date' => $getDate($row, 'school_start_date'),
                    'appointment_date' => $getDate($row, 'appointment_date'),
                    'salary' => $get($row, 'salary') ?: null,
                    'government_start_date' => $getDate($row, 'government_start_date'),
                    'position_allowance' => $get($row, 'position_allowance') ?: null,
                    'academic_allowance' => $get($row, 'academic_allowance') ?: null,
                    'retirement_date' => $getDate($row, 'retirement_date'),
                ]);
            }

            if ($get($row, 'edu_institution') !== '') {
                PersonnelEducation::updateOrCreate(['personnel_id' => $personnel->personnel_id], [
                    'institution' => $get($row, 'edu_institution') ?: null,
                    'start_year' => $get($row, 'edu_start_year') ?: null,
                    'end_year' => $get($row, 'edu_end_year') ?: null,
                    'education_level' => $get($row, 'edu_level') ?: null,
                    'major' => $get($row, 'edu_major') ?: null,
                ]);
            }

            if ($get($row, 'honor_type') !== '') {
                PersonnelHonor::updateOrCreate(['personnel_id' => $personnel->personnel_id], [
                    'honor_type' => $get($row, 'honor_type'),
                    'organization' => $get($row, 'honor_org') ?: null,
                    'year_received' => $get($row, 'honor_year') ?: null,
                ]);
            }

            if ($get($row, 'training_course') !== '') {
                PersonnelTraining::updateOrCreate(['personnel_id' => $personnel->personnel_id], [
                    'project' => $get($row, 'training_project') ?: null,
                    'course_name' => $get($row, 'training_course'),
                    'start_date' => $getDate($row, 'training_start'),
                    'end_date' => $getDate($row, 'training_end'),
                ]);
            }

            if ($get($row, 'license_number') !== '') {
                PersonnelLicense::updateOrCreate(['personnel_id' => $personnel->personnel_id], [
                    'license_type' => $get($row, 'license_type') ?: null,
                    'license_number' => $get($row, 'license_number'),
                    'license_name' => $get($row, 'license_name') ?: null,
                    'issue_date' => $getDate($row, 'license_issue'),
                    'expiry_date' => $getDate($row, 'license_expiry'),
                ]);
            }

            if ($get($row, 'decoration_class') !== '') {
                PersonnelDecoration::updateOrCreate(['personnel_id' => $personnel->personnel_id], [
                    'year_received' => $get($row, 'decoration_year') ?: null,
                    'decoration_class' => $get($row, 'decoration_class'),
                    'position' => $get($row, 'decoration_position') ?: null,
                    'gazette_date' => $getDate($row, 'decoration_gazette_date'),
                ]);
            }
        }

        return back()->with(
            'success',
            "นำเข้าสำเร็จ — เพิ่มใหม่ {$created} คน, อัปเดต {$updated} คน" . ($skipped > 0 ? ", ข้าม {$skipped} แถว (ไม่มีรหัสพนักงาน)" : '')
        );
    }

    private function upsertAddress(Personnel $personnel, callable $get, int $row, string $prefix, string $addressType): void
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
            $v = $get($row, $colKey) ?: null;
            if ($v !== null) {
                $hasData = true;
            }
            $values[$dbField] = $v;
        }

        if (!$hasData) {
            return;
        }

        PersonnelAddress::updateOrCreate(
            ['personnel_id' => $personnel->personnel_id, 'address_type' => $addressType],
            $values
        );
    }

    private function mapGender(string $value): ?string
    {
        return match ($value) {
            'ชาย' => 'M',
            'หญิง' => 'F',
            default => $value ?: null,
        };
    }

    // รายงานการอบรม — รวมข้อมูลการอบรม/ศึกษา/ดูงานของบุคลากรทุกคนไว้ที่เดียว
    public function trainingReport(Request $request)
    {
        $query = PersonnelTraining::with('personnel')->orderByDesc('start_date');

        if ($request->filled('personnel_id')) {
            $query->where('personnel_id', $request->personnel_id);
        }

        if ($request->filled('training_type')) {
            $query->where('training_type', $request->training_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('start_date', '<=', $request->date_to);
        }

        $totalHours = (clone $query)->sum('hours');

        $trainings = $query->paginate(20)->withQueryString();

        $personnels = Personnel::orderBy('thai_firstname')->get(['personnel_id', 'thai_prefix', 'thai_firstname', 'thai_lastname']);
        $trainingTypes = PersonnelTraining::whereNotNull('training_type')
            ->where('training_type', '!=', '')
            ->distinct()
            ->orderBy('training_type')
            ->pluck('training_type');

        return view('personnel.training_report', compact('trainings', 'personnels', 'trainingTypes', 'totalHours'));
    }
}
