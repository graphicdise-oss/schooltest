<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Library\LibraryBook;
use App\Models\Library\LibraryCategory;
use App\Models\Library\LibraryLoan;
use App\Models\Library\LibraryDamageReport;
use App\Models\Library\LibraryVisit;
use Illuminate\Http\Request;

class LibraryReportController extends Controller
{
    // รายงานข้อมูลหนังสือ
    public function books(Request $request)
    {
        $search     = $request->get('search', '');
        $categoryId = $request->get('category_id', '');

        $books = LibraryBook::with('category')
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%"))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->orderBy('title')
            ->get();

        $categories = LibraryCategory::orderBy('name')->get();

        $summary = [
            'titles'    => $books->count(),
            'copies'    => $books->sum('total_copies'),
            'available' => $books->sum('available_copies'),
            'on_loan'   => $books->sum(fn($b) => $b->total_copies - $b->available_copies),
        ];

        return view('library.reports_books', compact('books', 'categories', 'search', 'categoryId', 'summary'));
    }

    // รายงานค้างส่ง
    public function overdue()
    {
        $loans = LibraryLoan::with(['book', 'student', 'personnel'])
            ->where('status', 'ยืมอยู่')
            ->where('due_at', '<', now()->startOfDay())
            ->orderBy('due_at')
            ->get();

        return view('library.reports_overdue', compact('loans'));
    }

    // รายงานยืม-คืนหนังสือ
    public function loans(Request $request)
    {
        $status = $request->get('status', '');
        $from   = $request->get('from', '');
        $to     = $request->get('to', '');

        $loans = LibraryLoan::with(['book', 'student', 'personnel'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($from, fn($q) => $q->whereDate('borrowed_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('borrowed_at', '<=', $to))
            ->orderByDesc('borrowed_at')
            ->get();

        return view('library.reports_loans', compact('loans', 'status', 'from', 'to'));
    }

    // รายงานแจ้งชำรุดเสียหาย
    public function damage(Request $request)
    {
        $status = $request->get('status', '');

        $reports = LibraryDamageReport::with(['book', 'loan.student', 'loan.personnel'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('reported_at')
            ->get();

        return view('library.reports_damage', compact('reports', 'status'));
    }

    public function damageResolve(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:ซ่อมแล้ว,จำหน่ายออก']);
        $report = LibraryDamageReport::findOrFail($id);
        $report->update(['status' => $request->status, 'resolved_at' => now()->toDateString()]);
        return back()->with('success', 'อัปเดตสถานะสำเร็จ');
    }

    // รายงานผู้ยืมหนังสือมากที่สุด
    public function topBorrowers(Request $request)
    {
        $from = $request->get('from', '');
        $to   = $request->get('to', '');

        $loans = LibraryLoan::with(['student', 'personnel'])
            ->when($from, fn($q) => $q->whereDate('borrowed_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('borrowed_at', '<=', $to))
            ->get();

        $ranking = $loans->groupBy(fn($l) => $l->borrower_type . '-' . ($l->student_id ?? $l->personnel_id))
            ->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'name'  => $first->borrower_name,
                    'code'  => $first->borrower_code,
                    'type'  => $first->borrower_type === 'student' ? 'นักเรียน' : 'บุคลากร',
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->take(20);

        return view('library.reports_top_borrowers', compact('ranking', 'from', 'to'));
    }

    // รายงานเข้าใช้ห้องสมุด (สถิติ)
    public function visits(Request $request)
    {
        $from = $request->get('from', now()->subDays(6)->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $visits = LibraryVisit::with(['student', 'personnel'])
            ->whereDate('visited_at', '>=', $from)
            ->whereDate('visited_at', '<=', $to)
            ->orderByDesc('visited_at')
            ->get();

        $byDate = $visits->groupBy(fn($v) => $v->visited_at->format('Y-m-d'))
            ->map(fn($g) => $g->count())
            ->sortKeys();

        return view('library.reports_visits', compact('visits', 'byDate', 'from', 'to'));
    }
}
