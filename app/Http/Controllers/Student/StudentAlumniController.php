<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Promotion;
use App\Models\Academic\Semester;
use Illuminate\Http\Request;

class StudentAlumniController extends Controller
{
    public function index(Request $request)
    {
        $query = Promotion::with([
            'student',
            'fromSection.level',
            'fromSection.semester.academicYear',
        ])
        ->whereIn('promo_type', ['บันทึกจบ', 'ลาออก'])
        ->orderBy('promo_date', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('student', function ($q) use ($s) {
                $q->where('student_code', 'like', "%{$s}%")
                  ->orWhere('thai_firstname', 'like', "%{$s}%")
                  ->orWhere('thai_lastname', 'like', "%{$s}%");
            });
        }

        if ($request->filled('promo_type')) {
            $query->where('promo_type', $request->promo_type);
        }

        if ($request->filled('year')) {
            $query->whereHas('fromSection.semester.academicYear', function ($q) use ($request) {
                $q->where('year_name', 'like', '%' . $request->year . '%');
            });
        }

        $alumni = $query->paginate(20)->withQueryString();

        return view('student.student_alumni_index', compact('alumni'));
    }

    public function withdrawalReport(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $yearId        = $request->get('year_id');
        $semesterId    = $request->get('semester_id');

        if (!$request->has('year_id')) {
            $currentYear = AcademicYear::where('is_current', true)->first() ?? $academicYears->first();
            $yearId      = $currentYear?->year_id;
            $defaultSem  = Semester::where('year_id', $yearId)->where('is_current', true)->first()
                ?? Semester::where('year_id', $yearId)->orderBy('semester_name')->first();
            $semesterId  = $defaultSem?->semester_id;
        }

        $semesters = $yearId
            ? Semester::where('year_id', $yearId)->orderBy('semester_name')->get()
            : collect();

        $query = Promotion::with(['student', 'fromSection.level', 'fromSection.semester'])
            ->where('promo_type', 'ลาออก')
            ->whereHas('fromSection.semester', function ($q) use ($yearId, $semesterId) {
                if ($yearId)     $q->where('year_id', $yearId);
                if ($semesterId) $q->where('semester_id', $semesterId);
            })
            ->orderBy('promo_date', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('student', fn($q) => $q
                ->where('student_code', 'like', "%$s%")
                ->orWhere('thai_firstname', 'like', "%$s%")
                ->orWhere('thai_lastname', 'like', "%$s%")
            );
        }

        $withdrawals = $query->get();
        $grouped     = $withdrawals->groupBy('from_section_id');

        return view('student.withdrawal_report', compact(
            'academicYears', 'semesters',
            'yearId', 'semesterId',
            'grouped', 'withdrawals'
        ));
    }
}
