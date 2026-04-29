<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentAddress;
use App\Models\StudentEducation;
use App\Models\StudentFamily; // <-- 1. เพิ่มบรรทัดนี้เพื่อเรียกใช้ Model ครอบครัว
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentHealth;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;

class StudentController extends Controller
{
    // 1. เปิดหน้าฟอร์มเพิ่มข้อมูลใหม่
    public function create()
    {
        $academicYears = AcademicYear::orderBy('year_id', 'desc')->get();
        $levels = Level::orderBy('sort_order')->get();
        $semesters = Semester::orderBy('semester_id', 'desc')->get();
        $sections = ClassSection::with(['level', 'semester'])->get();
        $currentSection = null;

        return view('student.studentdetail', compact(
            'academicYears',
            'levels',
            'semesters',
            'sections',
            'currentSection'
        ));
    }


    // 2. บันทึกข้อมูลนักเรียน + ที่อยู่ (ตอนสร้างใหม่)
    public function store(Request $request)
    {
        // ตรวจสอบข้อมูลเบื้องต้น
        $request->validate([
            'student_code' => 'nullable|unique:students,student_code',
            'id_card_number' => 'required|size:13|unique:students,id_card_number',
            'gender' => 'required',
            'thai_prefix' => 'required',
            'thai_firstname' => 'required',
            'thai_lastname' => 'required',
            'date_of_birth' => 'required|date',
            'nationality' => 'required',
            'ethnicity' => 'required',
        ]);

        // กรองเอาเฉพาะข้อมูลที่เป็นข้อความหรือตัวเลข (กันพวก Array ขยะหลุดเข้ามา)
        $rawStudentData = $request->except(['_token', 'addresses', 'student_image', 'semester_id', 'section_id', 'year_id', 'level_id']);
        $studentData = [];

        foreach ($rawStudentData as $key => $value) {
            if (!is_array($value)) {
                $studentData[$key] = $value;
            }
        }

        // จัดการอัปโหลดรูปภาพ
        if ($request->hasFile('student_image')) {
            $studentData['student_image'] = $request->file('student_image')->store('students', 'public');
        }

        $studentData['created_by'] = Auth::user()->name ?? 'system';

        // บันทึกลงตาราง students
        $student = Student::create($studentData);
        // บันทึกชั้นเรียน
        if ($request->filled('section_id')) {
            $lastNo = StudentSection::where('section_id', $request->section_id)->max('student_number') ?? 0;
            StudentSection::create([
                'student_id' => $student->student_id,
                'section_id' => $request->section_id,
                'student_number' => $lastNo + 1,
                'status' => 'กำลังศึกษา',
            ]);
        }

        // บันทึกข้อมูลที่อยู่ (วนลูป Registered และ Current)
        if ($request->has('addresses')) {
            foreach ($request->input('addresses') as $type => $addressData) {
                // ใส่ ID นักเรียนเป็น Foreign Key
                $addressData['student_id'] = $student->student_id;
                StudentAddress::create($addressData);
            }
        }

        // เปลี่ยนเส้นทางไปหน้าแก้ไข เพื่อส่ง $student_id ไปปลดล็อก Tab อื่นๆ
        return redirect()->route('students.edit', $student->student_id)->with('success', 'บันทึกข้อมูลส่วนตัวและที่อยู่สำเร็จ');
    }

    // 3. เปิดหน้าฟอร์มสำหรับแก้ไข (และปลดล็อก Tab)
    public function edit($id)
    {
        $student = Student::with(['addresses', 'education', 'families'])
            ->where('student_id', $id)->firstOrFail();

        $academicYears = AcademicYear::orderBy('year_id', 'desc')->get();
        $levels = Level::orderBy('sort_order')->get();
        $semesters = Semester::orderBy('semester_id', 'desc')->get();
        $sections = ClassSection::with(['level', 'semester'])->get();
        $currentSection = StudentSection::where('student_id', $id)
            ->where('status', 'กำลังศึกษา')->first();

        return view('student.studentdetail', compact(
            'student',
            'academicYears',
            'levels',
            'semesters',
            'sections',
            'currentSection'
        ));
    }

// 4. อัปเดตข้อมูลนักเรียน + ที่อยู่ (กรณีกดบันทึกซ้ำ)
    public function update(Request $request, $id)
    {
        $student = Student::where('student_id', $id)->firstOrFail();

        // ตรวจสอบข้อมูล โดยละเว้นการเช็กซ้ำของตัวเอง (ใส่ .' . $id . ',student_id ต่อท้าย)
        $request->validate([
            'student_code'    => 'nullable|unique:students,student_code,' . $id . ',student_id',
            'id_card_number'  => 'required|size:13|unique:students,id_card_number,' . $id . ',student_id',
            'gender'          => 'required',
            'thai_prefix'     => 'required',
            'thai_firstname'  => 'required',
            'thai_lastname'   => 'required',
            'date_of_birth'   => 'required|date',
            'nationality'     => 'required',
            'ethnicity'       => 'required',
        ], [
            'student_code.unique'    => 'รหัสนักเรียนนี้มีในระบบแล้ว กรุณาตรวจสอบอีกครั้ง',
            'id_card_number.required'=> 'กรุณากรอกเลขบัตรประชาชน',
            'id_card_number.size'    => 'เลขบัตรประชาชนต้องมี 13 หลักเท่านั้น',
            'id_card_number.unique'  => 'เลขบัตรประชาชนนี้มีในระบบแล้ว กรุณาตรวจสอบอีกครั้ง',
            'gender.required'        => 'กรุณาเลือกเพศ',
            'thai_prefix.required'   => 'กรุณาเลือกคำนำหน้า',
            'thai_firstname.required'=> 'กรุณากรอกชื่อภาษาไทย',
            'thai_lastname.required' => 'กรุณากรอกนามสกุลภาษาไทย',
            'date_of_birth.required' => 'กรุณากรอกวันเกิด',
            'nationality.required'   => 'กรุณากรอกสัญชาติ',
            'ethnicity.required'     => 'กรุณากรอกเชื้อชาติ',
        ]);
        $rawStudentData = $request->except([
            '_token',
            '_method',
            'addresses',
            'student_image',
            'section_id',
            'year_id',
            'semester_id',
            'level_id'
        ]);


        $studentData = [];

        foreach ($rawStudentData as $key => $value) {
            if (!is_array($value)) {
                $studentData[$key] = $value;
            }
        }

        if ($request->hasFile('student_image')) {
            $studentData['student_image'] = $request->file('student_image')->store('students', 'public');
        }

        // อัปเดตข้อมูลหลัก
        $student->update($studentData);

        // อัปเดตชั้นเรียน
        if ($request->filled('section_id')) {
            $existing = StudentSection::where('student_id', $id)
                ->where('status', 'กำลังศึกษา')->first();

            if ($existing) {
                $existing->update(['section_id' => $request->section_id]);
            } else {
                $lastNo = StudentSection::where('section_id', $request->section_id)->max('student_number') ?? 0;
                StudentSection::create([
                    'student_id' => $id,
                    'section_id' => $request->section_id,
                    'student_number' => $lastNo + 1,
                    'status' => 'กำลังศึกษา',
                ]);
            }
        }

        // อัปเดตข้อมูลที่อยู่
        if ($request->has('addresses')) {
            foreach ($request->input('addresses') as $type => $addressData) {
                StudentAddress::updateOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'address_type' => $addressData['address_type']
                    ],
                    $addressData
                );
            }
        }

        return redirect()->back()->with('success', 'อัปเดตข้อมูลส่วนตัวและที่อยู่สำเร็จ');
    }

    // 5. ฟังก์ชันสำหรับบันทึกข้อมูลการศึกษา (Tab 2)
    public function storeEducation(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id'
        ]);

        $eduData = $request->except(['_token']);

        // ใช้ updateOrCreate เพื่อให้เซฟซ้ำ/อัปเดต ได้ในปุ่มเดียว
        StudentEducation::updateOrCreate(
            ['student_id' => $request->student_id],
            $eduData
        );

        return redirect()->back()->with('success', 'บันทึกข้อมูลทางการศึกษาสำเร็จ');
    }

    // ==========================================
    // 6. เพิ่มใหม่: ฟังก์ชันสำหรับบันทึกข้อมูลครอบครัว (Tab 3)
    // ==========================================
    public function storeFamily(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'guardian_type' => 'required|string|in:บิดา,มารดา,ผู้ปกครอง',
        ]);

        // เงื่อนไขค้นหา: ต้องตรงทั้ง student_id และ guardian_type (บิดา/มารดา/ผู้ปกครอง)
        $matchThese = [
            'student_id' => $request->student_id,
            'guardian_type' => $request->guardian_type,
        ];

        // เอาข้อมูลที่เหลือมาบันทึก
        $dataToSave = $request->except(['_token', 'student_id', 'guardian_type']);

        // อัปเดตหรือสร้างใหม่
        StudentFamily::updateOrCreate($matchThese, $dataToSave);

        return redirect()->back()->with('success', "บันทึกข้อมูล {$request->guardian_type} สำเร็จ");
    }

    // ==========================================
    // 7. ฟังก์ชันสำหรับบันทึกข้อมูลสุขภาพ (Tab 4)
    // ==========================================
    public function storeHealth(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id'
        ]);

        $healthData = $request->except(['_token']);

        // ใช้ updateOrCreate เพื่อให้เซฟซ้ำ/อัปเดต ได้ในปุ่มเดียว
        StudentHealth::updateOrCreate(
            ['student_id' => $request->student_id],
            $healthData
        );

        return redirect()->back()->with('success', 'บันทึกข้อมูลสุขภาพสำเร็จ');
    }
}

