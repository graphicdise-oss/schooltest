<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Library\LibraryBook;
use App\Models\Library\LibraryLoan;
use App\Models\Library\LibraryDamageReport;
use App\Models\Student;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryLoanController extends Controller
{
    // ค้นหาผู้ยืม (นักเรียน/บุคลากร) ด้วยรหัสหรือชื่อ — ใช้แบบ AJAX จากหน้าจัดการห้องสมุด
    public function searchBorrower(Request $request)
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

    // ยืมหนังสือ
    public function issue(Request $request)
    {
        $request->validate([
            'book_id'       => 'required|exists:library_books,id',
            'borrower_type' => 'required|in:student,personnel',
            'borrower_id'   => 'required',
            'due_at'        => 'required|date',
        ]);

        $book = LibraryBook::findOrFail($request->book_id);
        if ($book->available_copies < 1) {
            return back()->with('error', 'หนังสือเล่มนี้ถูกยืมหมดแล้ว');
        }

        DB::transaction(function () use ($request, $book) {
            LibraryLoan::create([
                'book_id'       => $book->id,
                'borrower_type' => $request->borrower_type,
                'student_id'    => $request->borrower_type === 'student' ? $request->borrower_id : null,
                'personnel_id'  => $request->borrower_type === 'personnel' ? $request->borrower_id : null,
                'borrowed_at'   => now()->toDateString(),
                'due_at'        => $request->due_at,
                'status'        => 'ยืมอยู่',
            ]);
            $book->decrement('available_copies');
        });

        return back()->with('success', 'บันทึกการยืมหนังสือสำเร็จ');
    }

    // รับคืนหนังสือ
    public function returnBook($id)
    {
        $loan = LibraryLoan::with('book')->findOrFail($id);
        if ($loan->status !== 'ยืมอยู่') {
            return back()->with('error', 'รายการนี้ถูกดำเนินการไปแล้ว');
        }

        DB::transaction(function () use ($loan) {
            $loan->update(['status' => 'คืนแล้ว', 'returned_at' => now()->toDateString()]);
            $loan->book?->increment('available_copies');
        });

        return back()->with('success', 'รับคืนหนังสือสำเร็จ');
    }

    // แจ้งชำรุด/สูญหาย (แทนการรับคืนปกติ)
    public function reportDamage(Request $request, $id)
    {
        $request->validate([
            'status'      => 'required|in:ชำรุด,สูญหาย',
            'description' => 'nullable|string|max:500',
        ]);

        $loan = LibraryLoan::with('book')->findOrFail($id);
        if ($loan->status !== 'ยืมอยู่') {
            return back()->with('error', 'รายการนี้ถูกดำเนินการไปแล้ว');
        }

        DB::transaction(function () use ($loan, $request) {
            $loan->update(['status' => $request->status, 'returned_at' => now()->toDateString()]);

            LibraryDamageReport::create([
                'book_id'     => $loan->book_id,
                'loan_id'     => $loan->id,
                'description' => $request->description,
                'status'      => 'รอดำเนินการ',
                'reported_at' => now()->toDateString(),
            ]);

            // เอาออกจากจำนวนที่หมุนเวียนได้ถาวร (ชำรุด/สูญหาย = ตัดออกจากสต็อก)
            if ($loan->book) {
                $loan->book->decrement('total_copies');
            }
        });

        return back()->with('success', 'บันทึกรายงานชำรุด/สูญหายสำเร็จ');
    }
}
