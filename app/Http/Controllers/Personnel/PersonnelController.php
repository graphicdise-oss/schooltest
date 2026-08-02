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
use App\Models\SchoolInfoSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


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
        $schoolInfo = SchoolInfoSetting::getInstance();
        return view('personnel.index', compact('personnels', 'departments', 'schoolInfo'));


    }


    // เพิ่มตรงท้ายก่อนปิด class
    // บันทึกเฉพาะรูปบุคลากร (AJAX จากปุ่มข้างรูป)
    public function updatePhoto(Request $request, $id)
    {
        $personnel = Personnel::where('personnel_id', $id)->firstOrFail();

        $path = null;
        $cropped = $request->input('image_cropped');
        if ($cropped && preg_match('#^data:image/(\w+);base64,#', $cropped, $m)) {
            $ext    = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
            $binary = base64_decode(substr($cropped, strpos($cropped, ',') + 1));
            if ($binary !== false) {
                $path = 'personnels/' . uniqid('per_') . '.' . $ext;
                Storage::disk('public')->put($path, $binary);
            }
        } elseif ($request->hasFile('personnel_image')) {
            $path = $request->file('personnel_image')->store('personnels', 'public');
        }

        if (!$path) {
            return response()->json(['ok' => false, 'message' => 'ไม่พบรูปภาพ'], 422);
        }
        $personnel->update(['personnel_image' => $path]);
        return response()->json(['ok' => true, 'url' => asset('storage/' . $path)]);
    }

    public function updateCredentials(Request $request, $id)
    {
        $actor = Auth::user();

        // เฉพาะผู้ดูแลระบบ (admin/superadmin) เท่านั้นที่ตั้งบัญชี/บทบาทได้
        if (!$actor || !$actor->isAdmin()) {
            abort(403, 'เฉพาะผู้ดูแลระบบเท่านั้นที่ตั้งค่าบัญชีได้');
        }

        $personnel = Personnel::where('personnel_id', $id)->firstOrFail();

        // ปกป้องบัญชี superadmin: admin ธรรมดาแก้ไม่ได้ (แก้ได้เฉพาะ superadmin)
        if ($personnel->role === 'superadmin' && !$actor->isSuperAdmin()) {
            abort(403, 'ไม่มีสิทธิ์แก้ไขบัญชีผู้ดูแลระบบสูงสุด');
        }

        $data = [];

        if ($request->filled('employee_code')) {
            $data['employee_code'] = $request->employee_code;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->filled('role')) {
            // เฉพาะ superadmin เท่านั้นที่ตั้งบทบาทเป็น superadmin ได้
            if ($request->role === 'superadmin' && !$actor->isSuperAdmin()) {
                abort(403, 'เฉพาะผู้ดูแลระบบสูงสุดเท่านั้นที่ตั้ง superadmin ได้');
            }
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

    // หัวตารางแบบฟอร์ม Excel นำเข้าข้อมูลบุคลากร ตรงตามตำแหน่งคอลัมน์ที่ import:personnel อ่าน (แถวที่ 7)
    private const IMPORT_TEMPLATE_HEADERS = [
        'ลำดับ', 'ประเภทบุคลากร', 'รหัสพนักงาน', 'ตำแหน่ง', 'แผนก', 'เพศ', 'คำนำหน้านาม', 'ชื่อ', 'นามสกุล',
        'ชื่อ(ภาษาอังกฤษ)', 'นามสกุล(ภาษาอังกฤษ)', 'ยอดเงินคงเหลือ', 'เลขบัตรประชาชน', 'เลขที่หนังสือเดินทาง',
        'ประเทศหนังสือเดินทาง', 'วันเกิด', 'กรุ๊ปเลือด', 'สัญชาติ', 'เชื้อชาติ', 'ศาสนา', 'สถานภาพสมรส',
        'ชื่อคู่สมรส', 'นามสกุลคู่สมรส', 'หมายเลขโทรศัพท์', 'อีเมล์', 'ตารางเวลา', 'ใช้ระบบ Biometric', 'รหัสลายนิ้วมือ',
        // ที่อยู่ตามทะเบียนบ้าน (29-38)
        'บ้านเลขที่', 'หมู่ที่', 'หมู่บ้าน', 'ซอย', 'อาคาร/ชั้น', 'ถนน', 'จังหวัด', 'เขต/อำเภอ', 'แขวง/ตำบล', 'รหัสไปรษณีย์',
        // ที่อยู่ที่ติดต่อได้ (39-48)
        'บ้านเลขที่', 'หมู่ที่', 'หมู่บ้าน', 'ซอย', 'อาคาร/ชั้น', 'ถนน', 'จังหวัด', 'เขต/อำเภอ', 'แขวง/ตำบล', 'รหัสไปรษณีย์',
        // ตำแหน่งงาน (49-59)
        'สถานภาพการทำงาน', 'ระดับ', 'วันที่ปฏิบัติงานในสถานศึกษา', 'วันสั่งบรรจุ', 'เงินเดือน', 'วันเริ่มปฏิบัติราชการ',
        'เงินประจำตำแหน่ง', 'จำนวนเงินวิทยฐานะ', 'วันครบเกษียณอายุ', 'รายได้สุทธิรวม', 'อายุงานเหลือ',
        // ข้อมูลครอบครัว (60-66)
        'ลำดับ', 'ความสัมพันธ์', 'คำนำหน้า', 'ชื่อ', 'นามสกุล', 'วันเกิด', 'สถานภาพ',
        // ข้อมูลการศึกษา (67-72)
        'ลำดับ', 'ปีที่', 'ปีที่', 'ระดับการศึกษา', 'วิชาเอก', 'สถาบันการศึกษา',
        // ข้อมูลเกียรติคุณ (73-76)
        'ลำดับ', 'ประเภทเกียรติคุณ', 'หน่วยงาน', 'ปีที่ได้รับ',
        // ประวัติการศึกษา อบรม ดูงาน (77-81)
        'ลำดับ', 'โครงการ', 'ชื่อหลักสูตรอบรม', 'ว/ด/ป ที่เริ่ม', 'ว/ด/ป ที่สิ้น',
        // ใบอนุญาตประกอบวิชาชีพ (82-87)
        'ลำดับ', 'ประเภทใบอนุญาต', 'เลขที่ใบประกอบ', 'ชื่อใบประกอบ', 'ว/ด/ป ออกใบประกอบ', 'ว/ด/ป หมดอายุ',
        // ประวัติการรับเครื่องราชฯ (88-92)
        'ลำดับ', 'ปีที่ได้รับ', 'ชั้นเครื่องราชฯ', 'ตำแหน่ง', 'ลงวันที่',
    ];

    // ป้ายกลุ่มคอลัมน์ (แถวที่ 6) -> [คอลัมน์เริ่มต้น 1-based => ป้ายกลุ่ม]
    private const IMPORT_TEMPLATE_GROUPS = [
        2 => 'ประวัติส่วนตัว',
        29 => 'ที่อยู่ตามทะเบียนบ้าน',
        39 => 'ที่อยู่ที่ติดต่อได้',
        49 => 'ตำแหน่งงาน',
        60 => 'ข้อมูลครอบครัว',
        67 => 'ข้อมูลการศึกษา',
        73 => 'ข้อมูลเกียรติคุณ',
        77 => 'ประวัติการศึกษา อบรม ดูงาน',
        82 => 'ใบอนุญาตประกอบวิชาชีพ',
        88 => 'ประวัติการรับเครื่องราชฯ',
    ];

    /**
     * ดาวน์โหลดไฟล์ Excel เปล่าไว้กรอกข้อมูลบุคลากร (ตำแหน่งคอลัมน์ตรงกับที่ import:personnel อ่าน)
     */
    public function importTemplate()
    {
        $info = SchoolInfoSetting::getInstance();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายชื่อพนักงาน');

        $sheet->setCellValue('B1', $info->school_name ?: 'แบบฟอร์มนำเข้าข้อมูลบุคลากร');
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);

        if ($info->logo_path && Storage::disk('public')->exists($info->logo_path)) {
            $sheet->getRowDimension(1)->setRowHeight(60);
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('ตราโรงเรียน');
            $drawing->setPath(Storage::disk('public')->path($info->logo_path));
            $drawing->setHeight(75);
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        }

        $contact1 = trim(collect([
            $info->phone ? "โทรศัพท์ : {$info->phone}" : null,
            $info->fax ? "โทรสาร : {$info->fax}" : null,
        ])->filter()->implode('  '));
        if ($contact1 !== '') {
            $sheet->setCellValue('B3', $contact1);
        }

        $contact2 = trim(collect([
            $info->website,
            $info->email ? "อีเมล์ : {$info->email}" : null,
        ])->filter()->implode('  '));
        if ($contact2 !== '') {
            $sheet->setCellValue('B4', $contact2);
        }

        $sheet->setCellValue('B5', 'กรอกข้อมูลบุคลากรเริ่มจากแถวที่ 8 เป็นต้นไป (แถวที่ 1-7 ห้ามลบ/ห้ามแก้) — ต้องมีรหัสพนักงานทุกแถว');

        foreach (self::IMPORT_TEMPLATE_GROUPS as $startCol => $groupLabel) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol);
            $sheet->setCellValue("{$col}6", $groupLabel);
            $sheet->getStyle("{$col}6")->getFont()->setBold(true);
        }

        foreach (self::IMPORT_TEMPLATE_HEADERS as $i => $header) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}7", $header);
            $sheet->getStyle("{$col}7")->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setWidth(14);
        }
        $sheet->freezePane('A8');

        $tmpPath = tempnam(sys_get_temp_dir(), 'personnel_template') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, 'แบบฟอร์มนำเข้าข้อมูลบุคลากร.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * รับไฟล์ Excel ที่กรอกข้อมูลบุคลากรมา แล้วรันคำสั่งนำเข้าข้อมูลให้
     */
    public function importUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ], [
            'file.required' => 'กรุณาเลือกไฟล์ Excel',
            'file.mimes' => 'ไฟล์ต้องเป็นสกุล .xlsx เท่านั้น',
        ]);

        set_time_limit(0); // ไฟล์ใหญ่อาจใช้เวลาหลายนาที

        $path = $request->file('file')->store('imports');
        $fullPath = Storage::path($path);

        $options = ['file' => $fullPath];
        if ($request->boolean('dry_run')) {
            $options['--dry-run'] = true;
        }

        Artisan::call('import:personnel', $options);
        $output = Artisan::output();

        @unlink($fullPath);

        return back()->with('import_output', $output);
    }
}

