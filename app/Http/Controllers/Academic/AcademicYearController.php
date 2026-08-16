<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    // === จัดการปีการศึกษา ===
    public function storeYear(Request $request)
    {
        $request->validate(['year_name' => 'required|unique:academic_years,year_name']);
        
        // ถ้าติ๊กว่าเป็นปีปัจจุบัน ให้ยกเลิกปีอื่นทั้งหมดก่อน
        if ($request->boolean('is_current')) {
            AcademicYear::query()->update(['is_current' => false]);
        }

        AcademicYear::create([
            'year_name' => $request->year_name,
            'is_current' => $request->boolean('is_current', false)
        ]);

        return redirect()->back()->with('success', 'เพิ่มปีการศึกษาสำเร็จ');
    }

    public function setYearCurrent($id)
    {
        AcademicYear::query()->update(['is_current' => false]);
        AcademicYear::findOrFail($id)->update(['is_current' => true]);
        return redirect()->back()->with('success', 'ตั้งเป็นปีการศึกษาปัจจุบันสำเร็จ');
    }

    public function destroyYear($id)
    {
        // ตรวจสอบก่อนลบว่ามีเทอมผูกอยู่ไหม (ป้องกัน Error)
        $year = AcademicYear::with('semesters')->findOrFail($id);
        if($year->semesters->count() > 0) {
            return redirect()->back()->with('error', 'ไม่สามารถลบได้ เนื่องจากมีภาคเรียนผูกอยู่ กรุณาลบภาคเรียนข้างในออกก่อน');
        }
        $year->delete();
        return redirect()->back()->with('success', 'ลบปีการศึกษาสำเร็จ');
    }

    // === จัดการเทอม/ภาคเรียน ===
    // === จัดการเทอม/ภาคเรียน ===
    public function storeSemester(Request $request)
    {
        // เพิ่มการตรวจสอบห้ามชื่อเทอมซ้ำในปีการศึกษาเดียวกัน
        $request->validate([
            'year_id' => 'required|exists:academic_years,year_id',
            'semester_name' => [
                'required',
                \Illuminate\Validation\Rule::unique('semesters')->where(function ($query) use ($request) {
                    return $query->where('year_id', $request->year_id);
                })
            ],
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ], [
            // ข้อความแจ้งเตือนเมื่อเจอเทอมซ้ำ
            'semester_name.unique' => 'มีชื่อภาคเรียนนี้ ในปีการศึกษานี้อยู่แล้วครับ (ห้ามซ้ำ)',
            'end_date.after_or_equal' => 'วันจบเรียนต้องไม่ก่อนวันเริ่มเรียน',
        ]);

        // ถ้าติ๊กว่าเป็นเทอมปัจจุบัน ให้ยกเลิกเทอมอื่นทั้งหมดก่อน
        if ($request->boolean('is_current')) {
            \App\Models\Academic\Semester::query()->update(['is_current' => false]);
        }

        \App\Models\Academic\Semester::create([
            'year_id' => $request->year_id,
            'semester_name' => $request->semester_name,
            'is_current' => $request->boolean('is_current', false),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->back()->with('success', 'เพิ่มภาคเรียนสำเร็จ');
    }

    public function setSemesterCurrent($id)
    {
        Semester::query()->update(['is_current' => false]);
        Semester::findOrFail($id)->update(['is_current' => true]);
        return redirect()->back()->with('success', 'ตั้งเป็นภาคเรียนปัจจุบันสำเร็จ');
    }

    // ตั้ง/แก้ วันเริ่ม-สิ้นสุดภาคเรียน — ใช้เป็นฐานคำนวณตารางเช็คชื่อ ปพ.5 (ไม่มีวันที่ = ตารางเช็คชื่อว่างเปล่า)
    public function updateSemesterDates(Request $request, $id)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ], [
            'end_date.after_or_equal' => 'วันจบเรียนต้องไม่ก่อนวันเริ่มเรียน',
        ]);

        Semester::findOrFail($id)->update([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->back()->with('success', 'บันทึกวันเริ่ม-สิ้นสุดภาคเรียนสำเร็จ');
    }

    public function destroySemester($id)
    {
        Semester::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบภาคเรียนสำเร็จ');
    }
}