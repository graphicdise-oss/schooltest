<?php

namespace App\Http\Middleware;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * สลับ "เทอมปัจจุบัน" ให้อัตโนมัติตามวันเริ่มภาคเรียนที่ตั้งไว้ (หน้าตั้งค่าเริ่มต้น/จัดการหลักสูตร)
 * ไม่ต้องเข้ามากดปุ่ม "ตั้งเป็นเทอมปัจจุบัน" เองทุกครั้งที่เปิดเทอมใหม่
 *
 * กติกา: เทอมที่มี start_date ล่าสุดที่ไม่เกินวันนี้ = เทอมปัจจุบัน ถ้าต่างจากที่ตั้งไว้เดิมก็สลับให้
 * (ไม่ต้องพึ่ง end_date เลย เพราะแค่วันเริ่มของเทอมถัดไปมาถึง ก็ถือว่าเทอมนั้น "ล่าสุด" กว่าอยู่แล้ว)
 * เช็คแค่วันละครั้งต่อวัน (cache กันไม่ให้ query ทุก request) และครอบ try/catch กันไม่ให้กระทบ request หลัก
 */
class AutoAdvanceSemester
{
    public function handle(Request $request, Closure $next)
    {
        $cacheKey = 'semester_auto_advance_' . now()->format('Y-m-d');

        if (! Cache::has($cacheKey)) {
            Cache::put($cacheKey, true, now()->endOfDay());
            $this->advanceIfDue();
        }

        return $next($request);
    }

    private function advanceIfDue(): void
    {
        try {
            $today = now()->toDateString();

            $due = Semester::whereNotNull('start_date')
                ->where('start_date', '<=', $today)
                ->orderByDesc('start_date')
                ->first();

            if (! $due || $due->is_current) {
                return;
            }

            Semester::where('is_current', true)->update(['is_current' => false]);
            $due->update(['is_current' => true]);

            // ถ้าเทอมที่สลับไปอยู่คนละปีการศึกษา ให้สลับ "ปีปัจจุบัน" ตามไปด้วย จะได้ไม่ค้าง
            $yearIsCurrent = $due->year_id
                && AcademicYear::where('year_id', $due->year_id)->where('is_current', true)->exists();
            if ($due->year_id && ! $yearIsCurrent) {
                AcademicYear::where('is_current', true)->update(['is_current' => false]);
                AcademicYear::where('year_id', $due->year_id)->update(['is_current' => true]);
            }
        } catch (\Throwable $e) {
            // ห้ามทำให้ request หลักพัง
        }
    }
}
