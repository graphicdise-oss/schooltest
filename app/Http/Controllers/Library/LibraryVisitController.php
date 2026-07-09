<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Library\LibraryVisit;
use App\Models\Student;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;

class LibraryVisitController extends Controller
{
    public function index()
    {
        $todayVisits = LibraryVisit::with(['student', 'personnel'])
            ->whereDate('visited_at', now()->toDateString())
            ->orderByDesc('visited_at')
            ->get();

        return view('library.checkin_index', compact('todayVisits'));
    }

    // ค้นหาคนสำหรับลงชื่อเข้าใช้ (AJAX)
    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if ($q === '') return response()->json([]);

        $students = Student::where('status', 'กำลังศึกษา')
            ->where(function ($qq) use ($q) {
                $qq->where('student_code', 'like', "%$q%")
                   ->orWhere('thai_firstname', 'like', "%$q%")
                   ->orWhere('thai_lastname', 'like', "%$q%");
            })->limit(8)->get(['student_id', 'student_code', 'thai_prefix', 'thai_firstname', 'thai_lastname']);

        $personnels = Personnel::where('status', 'ปฏิบัติงาน')
            ->where(function ($qq) use ($q) {
                $qq->where('employee_code', 'like', "%$q%")
                   ->orWhere('thai_firstname', 'like', "%$q%")
                   ->orWhere('thai_lastname', 'like', "%$q%");
            })->limit(8)->get(['personnel_id', 'employee_code', 'thai_prefix', 'thai_firstname', 'thai_lastname']);

        $results = $students->map(fn($s) => [
                'type' => 'student', 'id' => $s->student_id, 'code' => $s->student_code,
                'name' => trim($s->thai_prefix . $s->thai_firstname . ' ' . $s->thai_lastname),
            ])->concat($personnels->map(fn($p) => [
                'type' => 'personnel', 'id' => $p->personnel_id, 'code' => $p->employee_code,
                'name' => trim($p->thai_prefix . $p->thai_firstname . ' ' . $p->thai_lastname),
            ]));

        return response()->json($results->values());
    }

    public function store(Request $request)
    {
        $request->validate([
            'visitor_type' => 'required|in:student,personnel',
            'visitor_id'   => 'required',
        ]);

        LibraryVisit::create([
            'visitor_type' => $request->visitor_type,
            'student_id'   => $request->visitor_type === 'student' ? $request->visitor_id : null,
            'personnel_id' => $request->visitor_type === 'personnel' ? $request->visitor_id : null,
            'purpose'      => $request->input('purpose'),
            'visited_at'   => now(),
        ]);

        return back()->with('success', 'ลงชื่อเข้าใช้ห้องสมุดสำเร็จ');
    }
}
