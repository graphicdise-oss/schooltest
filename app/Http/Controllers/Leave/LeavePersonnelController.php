<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Leave\LeaveRequest;
use App\Models\Leave\LeaveType;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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