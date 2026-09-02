<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\StudentSection;
use App\Models\StudentPickupNotice;
use Illuminate\Http\Request;

// ฝั่งโรงเรียนดูรายการที่ผู้ปกครองแจ้งการรับ-ส่งเข้ามาเอง (ดู ParentPortalController::pickupNotices
// สำหรับฝั่งผู้ปกครอง) ค่าเริ่มต้นดูของ "วันนี้" เพื่อให้ครู/เจ้าหน้าที่หน้าประตูเช็คได้เร็ว
class PickupNoticeController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->filled('date') ? $request->date : now()->format('Y-m-d');
        $levelId = $request->level_id;

        $levels = Level::orderBy('sort_order')->get();

        $query = StudentPickupNotice::with(['student.studentSections' => function ($q) {
                $q->where('status', 'กำลังศึกษา')->with('classSection.level');
            }])
            ->whereDate('notice_date', $date);

        $notices = $query->orderBy('pickup_time')->get();

        if ($levelId) {
            $notices = $notices->filter(function ($n) use ($levelId) {
                $section = $n->student?->studentSections->first()?->classSection;
                return $section && (string) $section->level_id === (string) $levelId;
            })->values();
        }

        return view('student.pickup_notices_index', compact('notices', 'date', 'levels', 'levelId'));
    }
}
