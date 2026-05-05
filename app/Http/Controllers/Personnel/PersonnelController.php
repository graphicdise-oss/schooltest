<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Personne\Personnel;
use App\Models\Personne\PersonnelAddress;
use App\Models\Personne\PersonnelEducation;
use App\Models\Personne\PersonnelHonor;
use App\Models\Personne\PersonnelTraining;
use App\Models\Personne\PersonnelToeic;
use App\Models\Personne\PersonnelPosition;
use App\Models\Personne\PersonnelLicense;
use App\Models\Personne\PersonnelDecoration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class PersonnelController extends Controller
{
    // ===== หน้าสร้างใหม่ =====
    public function create()
    {
        return view('personnel.detail');
    }

    // ===== บันทึกข้อมูลใหม่ (Tab 1: ประวัติส่วนตัว + ที่อยู่) =====
    public function store(Request $request)
    {
        $request->validate([
            'thai_firstname' => 'required',
            'thai_lastname' => 'required',
            'employee_code' => 'nullable|unique:personnels,employee_code',
        ], [
            'thai_firstname.required' => 'กรุณากรอกชื่อภาษาไทย',
            'thai_lastname.required' => 'กรุณากรอกนามสกุลภาษาไทย',
            'employee_code.unique' => 'รหัสพนักงานนี้มีในระบบแล้ว กรุณาตรวจสอบอีกครั้ง',
        ]);
        $data = $request->except(['_token', 'addresses', 'personnel_image']);

        // กรองเอาเฉพาะ scalar
        $personnelData = [];
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $personnelData[$key] = $value;
            }
        }

        if ($request->hasFile('personnel_image')) {
            $personnelData['personnel_image'] = $request->file('personnel_image')->store('personnels', 'public');
        }

        $personnelData['created_by'] = Auth::user()->name ?? 'system';
        $personnel = Personnel::create($personnelData);

        // บันทึกที่อยู่
        if ($request->has('addresses')) {
            foreach ($request->input('addresses') as $type => $addr) {
                $addr['personnel_id'] = $personnel->personnel_id;
                PersonnelAddress::create($addr);
            }
        }

        return redirect()->route('personnels.edit', $personnel->personnel_id)
            ->with('success', 'บันทึกข้อมูลบุคลากรสำเร็จ');
    }

    // ===== หน้าแก้ไข (โหลดทุก Tab) =====
    public function edit($id)
    {
        $personnel = Personnel::with([
            'addresses',
            'educations',
            'honors',
            'trainings',
            'toeics',
            'positionDetail',
            'licenses',
            'decorations'
        ])->where('personnel_id', $id)->firstOrFail();

        return view('personnel.detail', compact('personnel'));
    }

    // ===== อัปเดตข้อมูลส่วนตัว + ที่อยู่ =====
    public function update(Request $request, $id)
    {
        $personnel = Personnel::where('personnel_id', $id)->firstOrFail();

        $request->validate([
            'thai_firstname' => 'required',
            'thai_lastname' => 'required',
        ]);

        $data = $request->except(['_token', '_method', 'addresses', 'personnel_image']);
        $personnelData = [];
        foreach ($data as $key => $value) {
            if (!is_array($value))
                $personnelData[$key] = $value;
        }

        if ($request->hasFile('personnel_image')) {
            $personnelData['personnel_image'] = $request->file('personnel_image')->store('personnels', 'public');
        }

        $personnel->update($personnelData);

        if ($request->has('addresses')) {
            foreach ($request->input('addresses') as $type => $addr) {
                PersonnelAddress::updateOrCreate(
                    ['personnel_id' => $personnel->personnel_id, 'address_type' => $addr['address_type']],
                    $addr
                );
            }
        }

        return redirect()->back()->with('success', 'อัปเดตข้อมูลส่วนตัวสำเร็จ');
    }

    // ===== Tab 2: ข้อมูลการศึกษา =====
    public function storeEducation(Request $request)
    {
        $request->validate(['personnel_id' => 'required|exists:personnels,personnel_id']);
        PersonnelEducation::create($request->except('_token'));
        return redirect()->back()->with('success', 'บันทึกข้อมูลการศึกษาสำเร็จ');
    }

    public function updateEducation(Request $request, $id)
    {
        PersonnelEducation::where('education_id', $id)->firstOrFail()->update($request->except(['_token', '_method']));
        return redirect()->back()->with('success', 'อัปเดตข้อมูลการศึกษาสำเร็จ');
    }

    public function destroyEducation($id)
    {
        PersonnelEducation::where('education_id', $id)->firstOrFail()->delete();
        return redirect()->back()->with('success', 'ลบข้อมูลการศึกษาสำเร็จ');
    }

    // ===== เกียรติคุณ =====
    public function storeHonor(Request $request)
    {
        $request->validate(['personnel_id' => 'required|exists:personnels,personnel_id']);
        PersonnelHonor::create($request->except('_token'));
        return redirect()->back()->with('success', 'บันทึกข้อมูลเกียรติคุณสำเร็จ');
    }

    public function updateHonor(Request $request, $id)
    {
        PersonnelHonor::where('honor_id', $id)->firstOrFail()->update($request->except(['_token', '_method']));
        return redirect()->back()->with('success', 'อัปเดตข้อมูลเกียรติคุณสำเร็จ');
    }

    public function destroyHonor($id)
    {
        PersonnelHonor::where('honor_id', $id)->firstOrFail()->delete();
        return redirect()->back()->with('success', 'ลบข้อมูลเกียรติคุณสำเร็จ');
    }

    // ===== อบรม/ศึกษา/ดูงาน =====
    public function storeTraining(Request $request)
    {
        $request->validate(['personnel_id' => 'required|exists:personnels,personnel_id']);
        PersonnelTraining::create($request->except('_token'));
        return redirect()->back()->with('success', 'บันทึกข้อมูลการอบรมสำเร็จ');
    }

    public function updateTraining(Request $request, $id)
    {
        PersonnelTraining::where('training_id', $id)->firstOrFail()->update($request->except(['_token', '_method']));
        return redirect()->back()->with('success', 'อัปเดตข้อมูลการอบรมสำเร็จ');
    }

    public function destroyTraining($id)
    {
        PersonnelTraining::where('training_id', $id)->firstOrFail()->delete();
        return redirect()->back()->with('success', 'ลบข้อมูลการอบรมสำเร็จ');
    }

    // ===== TOEIC =====
    public function storeToeic(Request $request)
    {
        $request->validate(['personnel_id' => 'required|exists:personnels,personnel_id']);
        PersonnelToeic::create($request->except('_token'));
        return redirect()->back()->with('success', 'บันทึกข้อมูล TOEIC สำเร็จ');
    }

    public function updateToeic(Request $request, $id)
    {
        PersonnelToeic::where('toeic_id', $id)->firstOrFail()->update($request->except(['_token', '_method']));
        return redirect()->back()->with('success', 'อัปเดตข้อมูล TOEIC สำเร็จ');
    }

    public function destroyToeic($id)
    {
        PersonnelToeic::where('toeic_id', $id)->firstOrFail()->delete();
        return redirect()->back()->with('success', 'ลบข้อมูล TOEIC สำเร็จ');
    }

    // ===== Tab 3: ตำแหน่งงาน =====
    public function storePosition(Request $request)
    {
        $request->validate(['personnel_id' => 'required|exists:personnels,personnel_id']);
        PersonnelPosition::updateOrCreate(
            ['personnel_id' => $request->personnel_id],
            $request->except('_token')
        );
        return redirect()->back()->with('success', 'บันทึกข้อมูลตำแหน่งงานสำเร็จ');
    }

    // ===== Tab 4: ใบอนุญาตประกอบวิชาชีพ =====
    public function storeLicense(Request $request)
    {
        $request->validate(['personnel_id' => 'required|exists:personnels,personnel_id']);
        PersonnelLicense::create($request->except('_token'));
        return redirect()->back()->with('success', 'บันทึกใบอนุญาตสำเร็จ');
    }

    public function updateLicense(Request $request, $id)
    {
        PersonnelLicense::where('license_id', $id)->firstOrFail()->update($request->except(['_token', '_method']));
        return redirect()->back()->with('success', 'อัปเดตใบอนุญาตสำเร็จ');
    }

    public function destroyLicense($id)
    {
        PersonnelLicense::where('license_id', $id)->firstOrFail()->delete();
        return redirect()->back()->with('success', 'ลบใบอนุญาตสำเร็จ');
    }

    // ===== เครื่องราชฯ =====
    public function storeDecoration(Request $request)
    {
        $request->validate(['personnel_id' => 'required|exists:personnels,personnel_id']);
        PersonnelDecoration::create($request->except('_token'));
        return redirect()->back()->with('success', 'บันทึกเครื่องราชฯ สำเร็จ');
    }

    public function updateDecoration(Request $request, $id)
    {
        PersonnelDecoration::where('decoration_id', $id)->firstOrFail()->update($request->except(['_token', '_method']));
        return redirect()->back()->with('success', 'อัปเดตเครื่องราชฯ สำเร็จ');
    }

    public function destroyDecoration($id)
    {
        PersonnelDecoration::where('decoration_id', $id)->firstOrFail()->delete();
        return redirect()->back()->with('success', 'ลบเครื่องราชฯ สำเร็จ');
    }


    // แสดงหน้าตารางข้อมูลอาจารย์ทั้งหมด
    // ==========================================
    // เปลี่ยนจาก

    // เป็น
    public function index(Request $request)
    {
        $query = \App\Models\Personne\Personnel::orderBy('personnel_id', 'desc');

        if ($request->filled('type')) {
            $query->where('personnel_type', $request->type);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(
                fn($q) =>
                $q->where('thai_firstname', 'like', "%$s%")
                    ->orWhere('thai_lastname', 'like', "%$s%")
                    ->orWhere('employee_code', 'like', "%$s%")
            );
        }

        $departments = \App\Models\Personne\Personnel::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        $personnels = $query->paginate(20);
        return view('personnel.index', compact('personnels', 'departments'));


    }


    // เพิ่มตรงท้ายก่อนปิด class
    public function updateCredentials(Request $request, $id)
    {
        $personnel = Personnel::where('personnel_id', $id)->firstOrFail();

        $data = [];

        if ($request->filled('employee_code')) {
            $data['employee_code'] = $request->employee_code;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->filled('role')) {
            $data['role'] = $request->role;
        }

        if (!empty($data)) {
            $personnel->update($data);
        }

        return redirect()->back()->with('success', 'อัปเดตข้อมูลรหัสผ่านสำเร็จ');
    }


    // ===== ลบข้อมูลบุคลากร =====
    public function destroy($id)
    {
        $personnel = \App\Models\Personne\Personnel::where('personnel_id', $id)->firstOrFail();
        $personnel->delete();

        return redirect()->route('personnels.index')->with('success', 'ลบข้อมูลบุคลากรเรียบร้อยแล้ว');
    }


}

