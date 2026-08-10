<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Personne\Personnel;
use App\Models\Personne\PersonnelTraining;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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

    private function exportStaffList(Request $request)
    {
        $personnels = $this->filteredStaffQuery($request)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายชื่อพนักงาน');

        $headers = ['ลำดับ', 'รหัส', 'คำนำหน้า', 'ชื่อ - นามสกุล', 'ตำแหน่ง', 'แผนก', 'อีเมล'];
        foreach ($headers as $i => $header) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}1", $header);
        }
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5482E7']],
        ]);

        $row = 2;
        foreach ($personnels as $i => $p) {
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $p->employee_code ?? '-');
            $sheet->setCellValue("C{$row}", $p->thai_prefix ?? '-');
            $sheet->setCellValue("D{$row}", trim($p->thai_firstname . ' ' . $p->thai_lastname));
            $sheet->setCellValue("E{$row}", $p->position ?? '-');
            $sheet->setCellValue("F{$row}", $p->department ?? '-');
            $sheet->setCellValue("G{$row}", $p->email ?? '-');
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'staff_list') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        $filename = 'รายชื่อพนักงาน_' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
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
