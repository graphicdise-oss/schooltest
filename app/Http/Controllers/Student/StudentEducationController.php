<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentEducation;

class StudentEducationController extends Controller
{
    // แสดงฟอร์ม
    public function edit($id)
    {
        $student = (object)['id' => $id]; // เปลี่ยนเป็น query จาก DB ทีหลัง
        return view('students.edit', compact('student'));
    }

    // รับข้อมูลจากฟอร์มแล้วบันทึก
    public function store(Request $request, $id)
    {
        StudentEducation::create([
            'student_id'      => $id,
            'country_type'    => $request->country_type,
            'school_name'     => $request->school_name,
            'gpa'             => $request->gpa,
            'credit'          => $request->credit,
            'graduation_year' => $request->graduation_year,
            'education_level' => $request->education_level,
            'transfer_reason' => $request->transfer_reason,
            'education_type'  => $request->education_type,
            'country_city'    => $request->country_city,
        ]);

        return redirect()->back()->with('success', 'บันทึกสำเร็จ');
    }
}