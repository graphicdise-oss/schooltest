<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\Promotion;
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
}
