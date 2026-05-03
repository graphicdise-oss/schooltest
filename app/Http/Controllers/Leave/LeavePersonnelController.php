<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Personne\Personnel;
use App\Models\Leave\Leave;
use App\Models\Leave\LeaveTypeQuota;
use Illuminate\Http\Request;

class LeavePersonnelController extends Controller
{
    public function index(Request $request)
    {
        $fiscalYear  = $request->input('fiscal_year', now()->year + 543);
        $department  = $request->input('department');
        $searchName  = $request->input('search_name');
        $dateFrom    = $request->input('date_from');
        $dateTo      = $request->input('date_to');

        $leaveTypes = LeaveTypeQuota::orderBy('sort_order')->get();

        $query = Personnel::query()
            ->select('personnels.*')
            ->where('personnels.status', 'ทำงาน');

        if ($department) {
            $query->where('personnels.department', $department);
        }

        if ($searchName) {
            $query->where(function ($q) use ($searchName) {
                $q->where('thai_firstname', 'like', "%{$searchName}%")
                  ->orWhere('thai_lastname',  'like', "%{$searchName}%")
                  ->orWhere('employee_code',  'like', "%{$searchName}%");
            });
        }

        $personnels = $query->orderBy('thai_firstname')->paginate(50)->withQueryString();

        // Load leave summary per person
        $personnelIds = $personnels->pluck('personnel_id')->toArray();

        $leaveYear = $fiscalYear - 543; // แปลงเป็น ค.ศ.
        $leaveSummaryQuery = Leave::query()
            ->whereIn('personnel_id', $personnelIds)
            ->where('status', 'approved')
            ->where('fiscal_year', $fiscalYear);

        if ($dateFrom) {
            $leaveSummaryQuery->whereDate('start_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $leaveSummaryQuery->whereDate('end_date', '<=', $dateTo);
        }

        $leaveSummary = $leaveSummaryQuery
            ->selectRaw('personnel_id, leave_type_key, SUM(days_count) as total_days')
            ->groupBy('personnel_id', 'leave_type_key')
            ->get()
            ->groupBy('personnel_id');

        $departments = Personnel::distinct()->pluck('department')->filter()->sort()->values();

        return view('leave.personnel_leave', compact(
            'personnels', 'leaveTypes', 'leaveSummary',
            'fiscalYear', 'department', 'searchName', 'dateFrom', 'dateTo',
            'departments'
        ));
    }

    public function show($personnelId, Request $request)
    {
        $personnel  = Personnel::where('personnel_id', $personnelId)->firstOrFail();
        $fiscalYear = $request->input('fiscal_year', now()->year + 543);

        $leaves = Leave::where('personnel_id', $personnelId)
            ->where('fiscal_year', $fiscalYear)
            ->orderByDesc('start_date')
            ->paginate(20)
            ->withQueryString();

        $leaveTypes = LeaveTypeQuota::orderBy('sort_order')->get();

        $summary = Leave::where('personnel_id', $personnelId)
            ->where('fiscal_year', $fiscalYear)
            ->where('status', 'approved')
            ->selectRaw('leave_type_key, SUM(days_count) as total_days')
            ->groupBy('leave_type_key')
            ->pluck('total_days', 'leave_type_key');

        return view('leave.personnel_leave_detail', compact(
            'personnel', 'leaves', 'leaveTypes', 'summary', 'fiscalYear'
        ));
    }
}
