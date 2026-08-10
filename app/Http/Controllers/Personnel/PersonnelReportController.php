<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Personne\Personnel;
use App\Models\Personne\PersonnelTraining;
use Illuminate\Http\Request;

class PersonnelReportController extends Controller
{
    // รายชื่อพนักงาน — รายงานแบบดูอย่างเดียว (ต่างจาก personnels.index ที่ใช้จัดการ/แก้ไข)
    public function staffList(Request $request)
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

        $personnels = $query->paginate(20)->withQueryString();

        $departments = Personnel::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('personnel.staff_list_report', compact('personnels', 'departments'));
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
