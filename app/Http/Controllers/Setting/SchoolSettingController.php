<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Academic\Pp2Setting;
use App\Models\Academic\SubjectGroupHead;
use App\Models\Personne\Personnel;
use App\Services\SchoolInfoSync;
use Illuminate\Http\Request;

class SchoolSettingController extends Controller
{
    // หน้ากลางเดียวสำหรับตั้งค่าข้อมูลโรงเรียน/ตราโรงเรียน/ลายเซ็น ใช้ร่วมกันทุกหน้าที่ต้องพิมพ์เอกสาร
    // (ปพ.1/3/5/6/7, ใบระเบียนผลการเรียน, รายงานจัดอันดับคะแนนรายวิชา ฯลฯ) — ก่อนหน้านี้แต่ละหน้ามีของตัวเอง
    // แยกกัน (บางหน้าเก็บลง session ชั่วคราว บางหน้าเก็บ DB ไม่ครบทุกช่อง) ทำให้ตั้งค่าที่หนึ่งแล้วไม่ไปอีกที่
    public function index()
    {
        $setting = Pp2Setting::getInstance();

        $personnels = Personnel::where('status', 'ปฏิบัติงาน')
            ->orderBy('thai_firstname')
            ->get(['personnel_id', 'thai_prefix', 'thai_firstname', 'thai_lastname', 'position']);

        // ตัวเลือกผู้อำนวยการ กรองเฉพาะคนที่ตำแหน่งมีคำว่า "ผู้อำนวยการ" เท่านั้น ไม่ต้องไล่ดูทั้งโรงเรียน
        $directors = $personnels->filter(fn ($p) => $p->position && str_contains($p->position, 'ผู้อำนวยการ'))->values();

        $logoUrl = null;
        foreach (['png', 'jpg', 'jpeg', 'PNG', 'JPG', 'JPEG'] as $ext) {
            $path = public_path('img/pp_1/logo.' . $ext);
            if (file_exists($path)) {
                $logoUrl = asset('img/pp_1/logo.' . $ext) . '?v=' . filemtime($path);
                break;
            }
        }

        $subjectGroupHeads = SubjectGroupHead::all()->keyBy('subject_group');
        $subjectGroups = SubjectGroupHead::groupList();

        return view('settings.school_settings', compact('setting', 'personnels', 'directors', 'logoUrl', 'subjectGroupHeads', 'subjectGroups'));
    }

    public function updateSchool(Request $request)
    {
        $request->validate([
            'school_name'    => 'nullable|string|max:255',
            'affiliation'    => 'nullable|string|max:255',
            'tambon'         => 'nullable|string|max:100',
            'amphoe'         => 'nullable|string|max:100',
            'province'       => 'nullable|string|max:100',
            'education_area' => 'nullable|string|max:255',
        ]);

        $setting = Pp2Setting::getInstance();
        if (!$setting->exists) {
            $setting->save();
        }
        $setting->update($request->only([
            'school_name', 'affiliation', 'tambon', 'amphoe', 'province', 'education_area',
        ]));

        // เชื่อมชื่อโรงเรียนไปยัง SchoolInfoSetting ด้วย (ใช้โดยหน้าสมัครเรียน/ส่งออก Excel/หัวกระดาษรายชื่อ)
        SchoolInfoSync::syncName($request->school_name);

        return redirect()->route('settings.school.index')->with('success', 'บันทึกข้อมูลโรงเรียนสำเร็จ');
    }

    public function updateSignatures(Request $request)
    {
        $request->validate([
            'registrar_personnel_id' => 'nullable|integer',
            'director_personnel_id'  => 'nullable|integer',
            'deputy_academic_personnel_id'  => 'nullable|integer',
            'measurement_head_personnel_id' => 'nullable|integer',
        ]);

        $setting = Pp2Setting::getInstance();
        if (!$setting->exists) {
            $setting->save();
        }

        $registrar = $request->registrar_personnel_id
            ? Personnel::find($request->registrar_personnel_id)
            : null;
        $director = $request->director_personnel_id
            ? Personnel::find($request->director_personnel_id)
            : null;
        $deputyAcademic = $request->deputy_academic_personnel_id
            ? Personnel::find($request->deputy_academic_personnel_id)
            : null;
        $measurementHead = $request->measurement_head_personnel_id
            ? Personnel::find($request->measurement_head_personnel_id)
            : null;

        $setting->update([
            'registrar_personnel_id' => $registrar?->personnel_id,
            'director_personnel_id'  => $director?->personnel_id,
            'deputy_academic_personnel_id'  => $deputyAcademic?->personnel_id,
            'measurement_head_personnel_id' => $measurementHead?->personnel_id,
            'registrar_name' => $registrar
                ? trim(($registrar->thai_prefix ?? '') . $registrar->thai_firstname . ' ' . $registrar->thai_lastname)
                : $request->registrar_name_manual,
            'director_name' => $director
                ? trim(($director->thai_prefix ?? '') . $director->thai_firstname . ' ' . $director->thai_lastname)
                : $request->director_name_manual,
            'deputy_academic_name' => $deputyAcademic
                ? trim(($deputyAcademic->thai_prefix ?? '') . $deputyAcademic->thai_firstname . ' ' . $deputyAcademic->thai_lastname)
                : $request->deputy_academic_name_manual,
            'measurement_head_name' => $measurementHead
                ? trim(($measurementHead->thai_prefix ?? '') . $measurementHead->thai_firstname . ' ' . $measurementHead->thai_lastname)
                : $request->measurement_head_name_manual,
        ]);

        return redirect()->route('settings.school.index')->with('success', 'บันทึกชื่อผู้ลงนามสำเร็จ');
    }

    // หัวหน้ากลุ่มสาระการเรียนรู้ — คนเดียวต่อกลุ่มสาระ ใช้ร่วมกันทุกวิชา/ทุกห้อง/ทุกเทอมในกลุ่มนั้น (เช่น ปพ.5)
    public function updateSubjectGroupHeads(Request $request)
    {
        foreach (SubjectGroupHead::groupList() as $group) {
            $personnelId = $request->input("heads.$group");
            $personnel = $personnelId ? Personnel::find($personnelId) : null;

            SubjectGroupHead::updateOrCreate(
                ['subject_group' => $group],
                [
                    'personnel_id' => $personnel?->personnel_id,
                    'head_name' => $personnel
                        ? trim(($personnel->thai_prefix ?? '') . $personnel->thai_firstname . ' ' . $personnel->thai_lastname)
                        : null,
                ]
            );
        }

        return redirect()->route('settings.school.index')->with('success', 'บันทึกหัวหน้ากลุ่มสาระการเรียนรู้สำเร็จ');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $dir = public_path('img/pp_1');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach (['png', 'jpg', 'jpeg', 'PNG', 'JPG', 'JPEG'] as $ext) {
            $old = $dir . '/logo.' . $ext;
            if (file_exists($old)) unlink($old);
        }

        $ext = strtolower($request->file('logo')->getClientOriginalExtension());
        $request->file('logo')->move($dir, 'logo.' . $ext);

        // เชื่อมโลโก้ไปยัง SchoolInfoSetting ด้วย (ใช้โดยหน้าสมัครเรียน/ส่งออก Excel/หัวกระดาษรายชื่อ)
        SchoolInfoSync::propagateLogoFromStatic($dir . '/logo.' . $ext, $ext);

        return redirect()->route('settings.school.index')->with('success', 'อัปโหลดตราโรงเรียนสำเร็จ');
    }
}
