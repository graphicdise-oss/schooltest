<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Leave\LeaveRequest;
use App\Models\Leave\LeaveType;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LeavePersonnelController extends Controller
{
    public function index(Request $request)
    {
        $fiscalYear = (int) $request->get('fiscal_year', now()->year + 543);
        $department = $request->get('department', '');
        $searchName = $request->get('search_name', '');
        $dateFrom   = $request->get('date_from', '');
        $dateTo     = $request->get('date_to', '');

        $yearAD  = $fiscalYear - 543;
        $startAD = $dateFrom ?: "{$yearAD}-01-01";
        $endAD   = $dateTo   ?: "{$yearAD}-12-31";

        if ($request->query('export') === 'excel') {
            return $this->export($department, $searchName, $startAD, $endAD, $fiscalYear);
        }

        $departments = Personnel::whereNotNull('department')->distinct()->orderBy('department')->pluck('department');
        $leaveTypes  = LeaveType::where('is_active', true)->orderBy('id')->get();

        $personnels = Personnel::query()
            ->when($department, fn($q) => $q->where('department', $department))
            ->when($searchName, fn($q) => $q->where(function ($q2) use ($searchName) {
                $q2->where('thai_firstname', 'like', "%{$searchName}%")
                   ->orWhere('thai_lastname',  'like', "%{$searchName}%")
                   ->orWhere('employee_code',  'like', "%{$searchName}%");
            }))
            ->orderBy('thai_firstname')
            ->paginate(20)
            ->withQueryString();

        $personnelIds = $personnels->pluck('personnel_id');

        $summaryRows = LeaveRequest::whereBetween('start_date', [$startAD, $endAD])
            ->whereIn('requester_id', $personnelIds)
            ->where('status', 'อนุมัติ')
            ->select('requester_id', 'leave_type_key', DB::raw('SUM(num_days) as total_days'))
            ->groupBy('requester_id', 'leave_type_key')
            ->get();

        $leaveSummary = $summaryRows->groupBy('requester_id');

        return view('leave.personnel_index', compact(
            'personnels', 'leaveSummary', 'leaveTypes', 'departments',
            'fiscalYear', 'department', 'searchName', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Export รายการคำขอลาแต่ละครั้ง (ใครลา วันไหน ประเภทไหน สถานะอะไร) เป็น Excel
     * ใช้ตัวกรองเดียวกับหน้าค้นหา (ปี พ.ศ. / แผนก / ชื่อ / ช่วงวันที่)
     */
    private function export(string $department, string $searchName, string $startAD, string $endAD, int $fiscalYear)
    {
        $personnelIds = Personnel::query()
            ->when($department, fn ($q) => $q->where('department', $department))
            ->when($searchName, fn ($q) => $q->where(function ($q2) use ($searchName) {
                $q2->where('thai_firstname', 'like', "%{$searchName}%")
                    ->orWhere('thai_lastname', 'like', "%{$searchName}%")
                    ->orWhere('employee_code', 'like', "%{$searchName}%");
            }))
            ->pluck('personnel_id');

        $requests = LeaveRequest::with(['requester', 'leaveType'])
            ->whereIn('requester_id', $personnelIds)
            ->whereBetween('start_date', [$startAD, $endAD])
            ->orderBy('start_date')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายงานการลา');

        $headers = ['ลำดับ', 'รหัสพนักงาน', 'ชื่อ - นามสกุล', 'แผนก', 'ประเภทการลา', 'วันที่เริ่ม', 'วันที่สิ้นสุด', 'จำนวนวัน', 'สถานะ', 'เหตุผล'];
        foreach ($headers as $i => $header) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}1", $header);
        }
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5482E7']],
        ]);
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setWidth(16);
        }
        $sheet->getColumnDimension('C')->setWidth(24);
        $sheet->getColumnDimension('J')->setWidth(30);

        $row = 2;
        foreach ($requests as $i => $r) {
            $p = $r->requester;
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $p->employee_code ?? '-');
            $sheet->setCellValue("C{$row}", trim(($p->thai_prefix ?? '') . ($p->thai_firstname ?? '') . ' ' . ($p->thai_lastname ?? '')));
            $sheet->setCellValue("D{$row}", $p->department ?? '-');
            $sheet->setCellValue("E{$row}", $r->leaveType->leave_type_name ?? $r->leave_type_key);
            $sheet->setCellValue("F{$row}", optional($r->start_date)->format('d/m/Y'));
            $sheet->setCellValue("G{$row}", optional($r->end_date)->format('d/m/Y'));
            $sheet->setCellValue("H{$row}", $r->num_days);
            $sheet->setCellValue("I{$row}", $r->status);
            $sheet->setCellValue("J{$row}", $r->reason);
            $row++;
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'leave_report') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        $filename = "รายงานการลา_{$fiscalYear}_" . now()->format('Ymd_His') . '.xlsx';

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    public function show(Request $request, $personnelId)
    {
        $fiscal_year = (int) $request->get('fiscal_year', now()->year + 543);
        $yearAD      = $fiscal_year - 543;

        $personnel  = Personnel::findOrFail($personnelId);
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('id')->get();

        $requests = LeaveRequest::with(['leaveType', 'reviewer', 'requester'])
            ->where('requester_id', $personnelId)
            ->whereYear('start_date', $yearAD)
            ->orderByDesc('request_date')
            ->get();

        $summary = $requests->where('status', 'อนุมัติ')
            ->groupBy('leave_type_key')
            ->map(fn($g) => $g->sum('num_days'));

        return view('leave.personnel_show', compact('personnel', 'requests', 'leaveTypes', 'summary', 'fiscal_year'));
    }
}