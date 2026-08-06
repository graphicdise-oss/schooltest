<?php

namespace App\Http\Middleware;

use App\Models\ActivityTimestamp;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * บันทึก "ครั้งแรก" ที่แต่ละคนบันทึก/แก้ไขข้อมูลในแต่ละหน้าของระบบ (ทั่วทั้งระบบ ไม่ต้องแก้ทีละ controller)
 * เก็บแค่ครั้งแรกต่อคนต่อหน้าเท่านั้น (กันข้อมูลบวมจากการกดบันทึกซ้ำๆ) ดูได้ที่เมนู "ไทม์สแตมป์" (เฉพาะ admin/superadmin)
 */
class TrackFirstActivity
{
    private const GROUP_LABELS = [
        'academic-years' => 'ปีการศึกษา/ภาคเรียน',
        'admission' => 'รับสมัครนักเรียนออนไลน์',
        'admissions' => 'จัดการรับสมัครนักเรียน',
        'assessments' => 'แบบประเมิน',
        'attendance' => 'เช็คชื่อ/การมาเรียน',
        'change-request' => 'คำร้องขอแก้ไขข้อมูล',
        'class-roster' => 'ทะเบียนนักเรียนรายห้อง',
        'class-sections' => 'จัดห้องเรียน',
        'curriculums' => 'หลักสูตร/แผนการเรียน',
        'departments' => 'แผนก/ฝ่ายงาน',
        'exam-rooms' => 'จัดห้องสอบ',
        'grades' => 'บันทึกเกรด/ผลการเรียน',
        'holidays' => 'วันหยุด',
        'leave' => 'การลา (บุคลากร)',
        'leave-settings' => 'ตั้งค่าการลา',
        'library' => 'ห้องสมุด',
        'onet' => 'O-NET',
        'parent' => 'ระบบผู้ปกครอง',
        'personnel-types' => 'ประเภทบุคลากร',
        'personnels' => 'ข้อมูลบุคลากร',
        'por1' => 'ปพ.1',
        'por3' => 'ปพ.3',
        'por5' => 'ปพ.5',
        'por6' => 'ปพ.6',
        'por7' => 'ปพ.7',
        'positions' => 'ตำแหน่ง',
        'pp2' => 'ปพ.2',
        'prefixes' => 'คำนำหน้าชื่อ',
        'programs' => 'หลักสูตร (กลุ่มแผน)',
        'promotions' => 'เลื่อนชั้น/จบการศึกษา',
        'scores' => 'บันทึกคะแนน',
        'student-alumni' => 'ทำเนียบศิษย์เก่า',
        'student-cards' => 'บัตรนักเรียน',
        'student-stat' => 'สถิตินักเรียน',
        'student-types' => 'ประเภทนักเรียน',
        'students' => 'ข้อมูลนักเรียน',
        'subjects' => 'รายวิชา',
        'timetable' => 'ตารางสอน',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $this->maybeLog($request, $response);

        return $response;
    }

    private function maybeLog(Request $request, $response): void
    {
        if (!$request->isMethod('post') && !$request->isMethod('put') && !$request->isMethod('patch') && !$request->isMethod('delete')) {
            return;
        }

        $user = Auth::user();
        if (!$user || !method_exists($user, 'isAdmin')) {
            return;
        }

        $routeName = $request->route()?->getName();
        if (!$routeName || $routeName === 'activity-timestamps.index') {
            return;
        }

        $status = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 200;
        if ($status >= 400) {
            return;
        }

        // การ redirect กลับพร้อม validation error ก็ยังถือเป็น 302 ปกติ เช็คจาก session errors bag เพิ่ม กันนับเป็น "บันทึกสำเร็จ" ทั้งที่ error
        $errors = $request->session()->get('errors');
        if ($errors && method_exists($errors, 'any') && $errors->any()) {
            return;
        }

        if (ActivityTimestamp::where('personnel_id', $user->personnel_id)->where('route_name', $routeName)->exists()) {
            return;
        }

        $group = explode('.', $routeName)[0];

        // การบันทึก activity log ต้องไม่ทำให้ request หลักพังเด็ดขาด (เช่น ชนกันตอนกดพร้อมกัน 2 แท็บ) ครอบ try/catch ไว้
        try {
            ActivityTimestamp::create([
                'personnel_id' => $user->personnel_id,
                'personnel_name' => trim(($user->thai_firstname ?? '') . ' ' . ($user->thai_lastname ?? '')),
                'employee_code' => $user->employee_code ?? null,
                'route_name' => $routeName,
                'page_label' => self::GROUP_LABELS[$group] ?? $group,
                'method' => $request->method(),
                'url' => $request->path(),
                'first_recorded_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // เงียบไว้ (เช่น unique constraint ชนกันตอนกดซ้อนกันพอดี) — ไม่ให้กระทบ request หลัก
        }
    }
}
