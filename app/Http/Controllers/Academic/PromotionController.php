<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Promotion;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    // หน้าหลัก ย้ายห้อง/เลื่อนชั้น/บันทึกจบ (3 Tabs)
    public function index(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $semesters = Semester::with('academicYear')->orderBy('semester_id', 'desc')->get();
        $levels = Level::orderBy('sort_order')->get();

        $fromSections = ClassSection::with(['level', 'studentSections.student'])
            ->where('semester_id', $semesterId)
            ->orderBy('level_id')->orderBy('section_number')->get();

        // ห้องปลายทาง (เทอมถัดไป ถ้ามี)
        $nextSemester = Semester::where('semester_id', '>', $semesterId)->orderBy('semester_id')->first();
        $toSections = $nextSemester
            ? ClassSection::with('level')->where('semester_id', $nextSemester->semester_id)->orderBy('level_id')->orderBy('section_number')->get()
            : collect();

        return view('academic.promotions', compact('semesters', 'levels', 'fromSections', 'toSections', 'semesterId', 'nextSemester'));
    }

    // ย้ายห้อง (เทอมเดียวกัน)
    public function transfer(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'to_section_id' => 'required|exists:class_sections,section_id',
            'from_section_id' => 'required|exists:class_sections,section_id',
        ]);

        $toSection = ClassSection::findOrFail($request->to_section_id);
        $lastNumber = StudentSection::where('section_id', $toSection->section_id)->max('student_number') ?? 0;

        foreach ($request->student_ids as $studentId) {
            // ลบจากห้องเดิม
            StudentSection::where('student_id', $studentId)->where('section_id', $request->from_section_id)->delete();

            // เพิ่มเข้าห้องใหม่
            $lastNumber++;
            StudentSection::create([
                'student_id' => $studentId,
                'section_id' => $toSection->section_id,
                'student_number' => $lastNumber,
                'status' => 'กำลังศึกษา',
            ]);

            // บันทึกประวัติ
            Promotion::create([
                'student_id' => $studentId,
                'from_section_id' => $request->from_section_id,
                'to_section_id' => $toSection->section_id,
                'promo_type' => 'ย้ายห้อง',
                'promo_date' => now(),
                'created_by' => Auth::user()->thai_firstname ?? 'system',
            ]);
        }

        return redirect()->back()->with('success', 'ย้ายห้องสำเร็จ ' . count($request->student_ids) . ' คน');
    }

    // เลื่อนชั้น (เทอมถัดไป)
    public function promote(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'to_section_id' => 'required|exists:class_sections,section_id',
            'from_section_id' => 'required|exists:class_sections,section_id',
        ]);

        $toSection = ClassSection::findOrFail($request->to_section_id);
        $lastNumber = StudentSection::where('section_id', $toSection->section_id)->max('student_number') ?? 0;

        foreach ($request->student_ids as $studentId) {
            // อัปเดตสถานะห้องเดิม
            StudentSection::where('student_id', $studentId)
                ->where('section_id', $request->from_section_id)
                ->update(['status' => 'เลื่อนชั้น']);

            // เพิ่มเข้าห้องใหม่
            $lastNumber++;
            StudentSection::create([
                'student_id' => $studentId,
                'section_id' => $toSection->section_id,
                'student_number' => $lastNumber,
                'status' => 'กำลังศึกษา',
            ]);

            Promotion::create([
                'student_id' => $studentId,
                'from_section_id' => $request->from_section_id,
                'to_section_id' => $toSection->section_id,
                'promo_type' => 'เลื่อนชั้น',
                'promo_date' => now(),
                'created_by' => Auth::user()->thai_firstname ?? 'system',
            ]);
        }

        return redirect()->back()->with('success', 'เลื่อนชั้นสำเร็จ ' . count($request->student_ids) . ' คน');
    }

    // บันทึกสำเร็จการศึกษา
    public function graduate(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'from_section_id' => 'required|exists:class_sections,section_id',
        ]);

        foreach ($request->student_ids as $studentId) {
            // อัปเดตสถานะห้อง
            StudentSection::where('student_id', $studentId)
                ->where('section_id', $request->from_section_id)
                ->update(['status' => 'จบการศึกษา']);

            // อัปเดตสถานะนักเรียน
            Student::where('student_id', $studentId)->update(['status' => 'จำหน่าย']);

            Promotion::create([
                'student_id' => $studentId,
                'from_section_id' => $request->from_section_id,
                'to_section_id' => null,
                'promo_type' => 'บันทึกจบ',
                'promo_date' => now(),
                'remark' => $request->remark,
                'created_by' => Auth::user()->thai_firstname ?? 'system',
            ]);
        }

        return redirect()->back()->with('success', 'บันทึกจบการศึกษาสำเร็จ ' . count($request->student_ids) . ' คน');
    }

    // ประวัติการเลื่อนชั้น/ย้าย
    public function history(Request $request)
    {
        $query = Promotion::with(['student', 'fromSection.level', 'toSection.level']);

        if ($request->filled('student_id')) $query->where('student_id', $request->student_id);
        if ($request->filled('type')) $query->where('promo_type', $request->type);

        $promotions = $query->orderBy('promo_date', 'desc')->paginate(30);

        return view('academic.promotion_history', compact('promotions'));
    }
}