<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Personne\Personnel;
use App\Models\Personne\PersonnelTraining;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PersonnelReportController extends Controller
{
    // ลำดับคอลัมน์เดียวกันทั้ง export และ import ตั้งใจให้ไฟล์ export เอาไปแก้แล้วอัปโหลดกลับได้เลย
    private const EXPORT_HEADERS = ['ลำดับ', 'รหัส', 'รหัสบัตรประชาชน', 'คำนำหน้า', 'ชื่อ - นามสกุล', 'ตำแหน่ง', 'แผนก', 'อีเมล'];

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

    private function exportStaffList(Request $request)
    {
        $personnels = $this->filteredStaffQuery($request)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายชื่อพนักงาน');

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count(self::EXPORT_HEADERS));
        foreach (self::EXPORT_HEADERS as $i => $header) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}1", $header);
        }
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5482E7']],
        ]);

        $row = 2;
        foreach ($personnels as $i => $p) {
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $p->employee_code ?? '');
            $sheet->setCellValue("C{$row}", $p->id_card_number ?? '');
            $sheet->setCellValue("D{$row}", $p->thai_prefix ?? '');
            $sheet->setCellValue("E{$row}", trim($p->thai_firstname . ' ' . $p->thai_lastname));
            $sheet->setCellValue("F{$row}", $p->position ?? '');
            $sheet->setCellValue("G{$row}", $p->department ?? '');
            $sheet->setCellValue("H{$row}", $p->email ?? '');
            $row++;
        }

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'staff_list') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        $filename = 'รายชื่อพนักงาน_' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    // นำเข้าจากไฟล์ Excel ที่มีคอลัมน์แบบเดียวกับที่ export ออกไป (แก้ในไฟล์ export แล้วอัปโหลดกลับได้เลย)
    // จับคู่ด้วย "รหัส" (employee_code): มีอยู่แล้ว = อัปเดต, ยังไม่มี = สร้างใหม่
    public function importStaffList(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ], [
            'file.required' => 'กรุณาเลือกไฟล์ Excel',
            'file.mimes' => 'ไฟล์ต้องเป็นสกุล .xlsx หรือ .xls เท่านั้น',
        ]);

        $path = $request->file('file')->getRealPath();
        $sheet = IOFactory::load($path)->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $i => $row) {
            if ($i === 1) {
                continue; // แถวหัวตาราง
            }

            $employeeCode = trim((string) ($row['B'] ?? ''));
            $name = trim((string) ($row['E'] ?? ''));

            if ($employeeCode === '' || $name === '') {
                $skipped++;
                continue;
            }

            $nameParts = preg_split('/\s+/', $name, 2);

            $data = [
                'id_card_number'  => trim((string) ($row['C'] ?? '')) ?: null,
                'thai_prefix'     => trim((string) ($row['D'] ?? '')) ?: null,
                'thai_firstname'  => $nameParts[0] ?? '',
                'thai_lastname'   => $nameParts[1] ?? '',
                'position'        => trim((string) ($row['F'] ?? '')) ?: null,
                'department'      => trim((string) ($row['G'] ?? '')) ?: null,
                'email'           => trim((string) ($row['H'] ?? '')) ?: null,
            ];

            $existing = Personnel::where('employee_code', $employeeCode)->first();

            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                Personnel::create($data + [
                    'employee_code' => $employeeCode,
                    'role' => 'user',
                    'status' => 'ปฏิบัติงาน',
                ]);
                $created++;
            }
        }

        return back()->with(
            'success',
            "นำเข้าสำเร็จ — เพิ่มใหม่ {$created} คน, อัปเดต {$updated} คน" . ($skipped > 0 ? ", ข้าม {$skipped} แถว (ไม่มีรหัส/ชื่อ)" : '')
        );
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
