<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use App\Models\Admission\AdmissionSetting;
use App\Models\Admission\AdmissionApplication;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Level;
use App\Models\Student;
use App\Models\StudentFamily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmissionController extends Controller
{
    /* ===================== ส่วนสาธารณะ (ไม่ต้อง login) ===================== */

    public function form()
    {
        $setting = AdmissionSetting::getOrCreate();
        $levels  = Level::orderBy('sort_order')->get();
        return view('admission.form', compact('setting', 'levels'));
    }

    public function submit(Request $request)
    {
        $setting = AdmissionSetting::getOrCreate();
        if (!$setting->isAcceptingNow()) {
            return redirect()->route('admission.form')
                ->with('error', 'ขณะนี้ปิดรับสมัคร');
        }

        $data = $request->validate([
            'thai_prefix'    => 'required|string|max:50',
            'thai_firstname' => 'required|string|max:100',
            'thai_lastname'  => 'required|string|max:100',
            'gender'         => 'required|string|max:20',
            'id_card_number' => 'required|digits:13|unique:students,id_card_number',
            'date_of_birth'  => 'required|date',
            'religion'       => 'nullable|string|max:50',
            'phone'          => 'nullable|string|max:20',
            'level_id'       => 'nullable|exists:levels,level_id',
            // ผู้ปกครอง
            'g_prefix'       => 'nullable|string|max:50',
            'g_firstname'    => 'nullable|string|max:100',
            'g_lastname'     => 'nullable|string|max:100',
            'g_phone'        => 'nullable|string|max:20',
            'g_relationship' => 'nullable|string|max:50',
        ], [
            'id_card_number.unique' => 'เลขบัตรประชาชนนี้เคยสมัคร/มีในระบบแล้ว',
            'id_card_number.digits' => 'เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก',
        ]);

        DB::transaction(function () use ($data, $request, $setting) {
            // 1) สร้างนักเรียน สถานะ รอการตรวจสอบ
            $student = Student::create([
                'thai_prefix'    => $data['thai_prefix'],
                'thai_firstname' => $data['thai_firstname'],
                'thai_lastname'  => $data['thai_lastname'],
                'gender'         => $data['gender'],
                'id_card_number' => $data['id_card_number'],
                'date_of_birth'  => $data['date_of_birth'],
                'nationality'    => $request->input('nationality', 'ไทย'),
                'ethnicity'      => $request->input('ethnicity', 'ไทย'),
                'religion'       => $data['religion'] ?? null,
                'status'         => 'รอการตรวจสอบ',
                'created_by'     => 'สมัครออนไลน์',
            ]);

            // 2) ผู้ปกครอง (ถ้ากรอก)
            if (!empty($data['g_firstname'])) {
                StudentFamily::create([
                    'student_id'    => $student->student_id,
                    'guardian_type' => 'ผู้ปกครอง',
                    'prefix_th'     => $data['g_prefix'] ?? null,
                    'first_name_th' => $data['g_firstname'],
                    'last_name_th'  => $data['g_lastname'] ?? null,
                    'phone_mobile'  => $data['g_phone'] ?? null,
                    'relationship'  => $data['g_relationship'] ?? null,
                ]);
            }

            // 3) ข้อมูลการสมัคร
            AdmissionApplication::create([
                'student_id'      => $student->student_id,
                'year_id'         => $setting->year_id,
                'level_id'        => $data['level_id'] ?? null,
                'applicant_phone' => $data['phone'] ?? null,
                'status'          => 'รอการตรวจสอบ',
            ]);
        });

        return redirect()->route('admission.success');
    }

    public function success()
    {
        return view('admission.success');
    }

    /* ===================== ส่วนผู้ดูแล (ต้อง login) ===================== */

    public function settings()
    {
        $setting = AdmissionSetting::getOrCreate();
        $years   = AcademicYear::orderByDesc('year_name')->get();
        return view('admission.settings', compact('setting', 'years'));
    }

    public function saveSettings(Request $request)
    {
        $setting = AdmissionSetting::getOrCreate();
        $setting->update([
            'is_open'      => $request->boolean('is_open'),
            'year_id'      => $request->input('year_id') ?: null,
            'open_date'    => $request->input('open_date') ?: null,
            'close_date'   => $request->input('close_date') ?: null,
            'instructions' => $request->input('instructions'),
            'levels_note'  => $request->input('levels_note'),
        ]);
        return back()->with('success', 'บันทึกการตั้งค่ารับสมัครสำเร็จ');
    }

    public function applicants(Request $request)
    {
        $status = $request->get('status', 'รอการตรวจสอบ');

        $applications = AdmissionApplication::with(['student', 'level', 'academicYear'])
            ->when($status !== 'ทั้งหมด', fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->get();

        return view('admission.applicants', compact('applications', 'status'));
    }

    public function approve($id)
    {
        $app = AdmissionApplication::with('student')->findOrFail($id);
        $app->update(['status' => 'ผ่าน']);
        if ($app->student) {
            $app->student->update(['status' => 'กำลังศึกษา']);
        }
        return back()->with('success', 'อนุมัติผู้สมัครสำเร็จ (เปลี่ยนสถานะเป็น กำลังศึกษา)');
    }

    public function reject($id)
    {
        $app = AdmissionApplication::with('student')->findOrFail($id);
        $app->update(['status' => 'ไม่ผ่าน']);
        if ($app->student) {
            $app->student->update(['status' => 'ไม่ผ่าน']);
        }
        return back()->with('success', 'ทำเครื่องหมายไม่ผ่านเรียบร้อย');
    }
}
