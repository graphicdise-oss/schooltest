<?php

namespace App\Http\Middleware;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
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
 *
 * ถ้าเทอมที่ถึงกำหนดเป็น "เทอม 2" และยังไม่มีห้องเรียนเลย จะคัดลอกห้อง+แผนการเรียนจากเทอม 1
 * ปีเดียวกันมาสร้างให้อัตโนมัติด้วย (ตรรกะเดียวกับเมนู "เปิดภาคเรียน 2" ที่กดเองได้) — ทำเฉพาะเทอม 2
 * เท่านั้นตามที่ผู้ใช้ระบุ ไม่ทำกับเทอม 1 ของปีถัดไป เพราะปีใหม่ต้องตั้งค่าอย่างอื่นเองอยู่ดี (ไม่คัดลอก
 * ตารางสอน/รายชื่อนักเรียน เหมือนเมนูเดิมทุกประการ — ห้องเทอม 2 จะว่างเปล่า รอจัดตารางสอน/ลงทะเบียนทีหลัง)
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

            if (! $due) {
                return;
            }

            if ($due->semester_name === '2' && ! ClassSection::real()->where('semester_id', $due->semester_id)->exists()) {
                $this->copyTerm1SectionsToTerm2($due);
            }

            if ($due->is_current) {
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

    private function copyTerm1SectionsToTerm2(Semester $semester2): void
    {
        $semester1 = Semester::where('year_id', $semester2->year_id)->where('semester_name', '1')->first();
        if (! $semester1) {
            return;
        }

        $sources = ClassSection::real()->where('semester_id', $semester1->semester_id)->get();

        foreach ($sources as $source) {
            $alreadyExists = ClassSection::where('semester_id', $semester2->semester_id)
                ->where('level_id', $source->level_id)
                ->where('section_number', $source->section_number)
                ->exists();
            if ($alreadyExists) {
                continue;
            }

            ClassSection::create([
                'semester_id'         => $semester2->semester_id,
                'level_id'            => $source->level_id,
                'section_number'      => $source->section_number,
                'study_plan'          => $source->study_plan,
                'homeroom_teacher_id' => $source->homeroom_teacher_id,
                'max_students'        => $source->max_students,
                'curriculum_id'       => $source->curriculum_id,
                'lunch_start'         => $source->lunch_start,
                'lunch_end'           => $source->lunch_end,
            ]);
        }
    }
}
