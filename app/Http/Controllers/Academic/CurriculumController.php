<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Curriculum;
use App\Models\Academic\CurriculumSubject;
use App\Models\Academic\Subject;
use App\Models\Academic\Level;
use App\Models\Academic\Program;
use App\Models\Academic\ClassSection;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CurriculumController extends Controller
{
    // แบบฟอร์มเปล่าสำหรับ "นำเข้าจาก Excel" — คอลัมน์ตรงตามที่ ImportCurriculumPlanFromExcel อ่านจริง (อิงตำแหน่ง A-S)
    public function downloadTemplate()
    {
        $headers = [
            'A' => 'รหัสวิชา', 'B' => 'ชื่อวิชา', 'C' => 'ประเภทรายวิชา', 'D' => 'กลุ่มสาระการเรียนรู้',
            'E' => 'หน่วยกิต', 'F' => 'ภาคเรียน', 'G' => 'ชม./สัปดาห์', 'H' => 'ชม./ปี',
            'O' => 'เลขบัตร ปชช. ครูผู้สอน 1', 'P' => 'เลขบัตร ปชช. ครูผู้สอน 2', 'Q' => 'เลขบัตร ปชช. ครูผู้สอน 3',
            'R' => 'เลขบัตร ปชช. ครูผู้สอน 4', 'S' => 'เลขบัตร ปชช. ครูผู้สอน 5',
        ];
        $sample = [
            'A' => 'ท33101', 'B' => 'ภาษาไทย 5', 'C' => 'รายวิชาพื้นฐาน', 'D' => 'ภาษาไทย',
            'E' => '1.0', 'F' => '1', 'G' => '2', 'H' => '40',
            'O' => '1234567890123',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PlanCourses');

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}1", $label);
        }
        $sheet->getStyle('A1:S1')->getFont()->setBold(true);
        $sheet->getStyle('A1:S1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F0FE');
        foreach (range('A', 'S') as $col) {
            $sheet->getColumnDimension($col)->setWidth(18);
        }

        foreach ($sample as $col => $val) {
            $sheet->setCellValue("{$col}2", $val);
        }

        $sheet->setCellValue('B4', 'ประเภทรายวิชา (คอลัมน์ C) กรอกได้: รายวิชาพื้นฐาน / รายวิชาเพิ่มเติม / กิจกรรมพัฒนาผู้เรียน');
        $sheet->setCellValue('B5', 'ภาคเรียน (คอลัมน์ F) กรอก 1 หรือ 2 — ถ้าเรียนทั้งปีเว้นว่างไว้');
        $sheet->setCellValue('B6', 'คอลัมน์ O-S คือเลขบัตรประชาชนครูผู้สอน กรอกได้สูงสุด 5 คน (เว้นว่างช่องที่ไม่ใช้)');
        $sheet->setCellValue('B7', 'วิชาที่ยังไม่มีในระบบจะถูกสร้างใหม่ วิชาที่มีอยู่แล้วจะอัปเดตข้อมูลให้ตรงกับไฟล์นี้');
        $sheet->getStyle('B4:B7')->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF888888'));

        $tmpPath = tempnam(sys_get_temp_dir(), 'curriculum_template') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, 'แบบฟอร์มนำเข้ารายวิชา_PlanCourses.xlsx')->deleteFileAfterSend(true);
    }

    // ตรวจ return_to ที่ส่งมาจากหน้าที่ผู้ใช้มาจริงๆ (เช่น /programs หรือ /programs/{id}/plans) ก่อนเอาไปใช้
    // เป็นปลายทางของปุ่ม "ย้อนกลับ" — รับเฉพาะ URL ของแอปเราเอง กัน open redirect ไปโดเมนอื่น
    private function sanitizeReturnTo(?string $url): ?string
    {
        if (!$url) {
            return null;
        }
        return str_starts_with($url, url('/')) ? $url : null;
    }

    public function byYear($year)
    {
        $curriculums = Curriculum::with(['level', 'curriculumSubjects'])
            ->where('year_applied', $year)
            ->orderBy('curriculum_id')->get();
        $levels = Level::orderBy('sort_order')->get();
        return view('academic.curriculum_by_year', compact('curriculums', 'year', 'levels'));
    }

    public function copy(Request $request, $id)
    {
        $original = Curriculum::with('curriculumSubjects')->findOrFail($id);
        $new = $original->replicate();

        // ถ้าระบุปีการศึกษาใหม่มา (เช่น คัดลอกไปปีหน้า) ให้ใช้ปีนั้นแทน คงชื่อเดิมไว้ (คนละปีชื่อซ้ำกันได้)
        // ถ้าไม่ได้ระบุปี ถือว่าคัดลอกในปีเดิม ต้องเติม "(คัดลอก)" กันชื่อซ้ำกันเป๊ะๆ ในปีเดียวกัน
        $targetYear = trim((string) $request->input('year_applied', ''));
        $copyingToNewYear = $targetYear !== '' && $targetYear !== $original->year_applied;
        if ($copyingToNewYear) {
            $new->year_applied = $targetYear;
        } else {
            $new->name = $original->name . ' (คัดลอก)';
        }
        $new->save();

        foreach ($original->curriculumSubjects as $cs) {
            $new->curriculumSubjects()->create([
                'subject_id'     => $cs->subject_id,
                'semester_type'  => $cs->semester_type,
                'is_required'    => $cs->is_required,
                'personnel_id'   => $cs->personnel_id,
                'credits'        => $cs->credits,
                'hours_per_year' => $cs->hours_per_year,
                'hours_per_week' => $cs->hours_per_week,
            ]);
        }

        if ($copyingToNewYear) {
            $returnTo = $this->sanitizeReturnTo($request->input('return_to'));
            return redirect()->route('curriculums.edit', array_filter(['id' => $new->curriculum_id, 'return_to' => $returnTo]))
                ->with('success', "คัดลอกแผนการเรียนไปปีการศึกษา {$targetYear} สำเร็จ");
        }
        return redirect()->back()->with('success', 'คัดลอกแผนการเรียนสำเร็จ');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        $cur = Curriculum::create($request->only(['name', 'level_id', 'year_applied', 'description']) + [
            'program_id' => $request->program_id ?: null,
        ]);
        $returnTo = $this->sanitizeReturnTo($request->input('return_to'));
        return redirect()->route('curriculums.edit', array_filter(['id' => $cur->curriculum_id, 'return_to' => $returnTo]))
            ->with('success', 'สร้างหลักสูตรสำเร็จ');
    }

    public function edit(Request $request, $id)
    {
        $curriculum = Curriculum::with(['curriculumSubjects.subject', 'curriculumSubjects.personnel', 'curriculumSubjects.teachers'])->findOrFail($id);
        $levels     = Level::orderBy('sort_order')->get();
        $programs   = Program::orderBy('name')->get();
        $subjects   = Subject::where('is_active', true)->orderBy('code')->get();
        $personnels = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();
        $returnTo   = $this->sanitizeReturnTo($request->query('return_to'));
        // เผื่อไม่มี return_to ส่งมา (เช่น กด "แก้ไข" จากที่อื่น) ใช้หลักสูตรของแผนนี้เอง คำนวณปลายทาง
        // fallback ของปุ่ม "ย้อนกลับ" ให้ถูกต้อง (พาไปหน้า "แผน" ของหลักสูตรนั้น ไม่ใช่ /programs เฉยๆ)
        $program    = $curriculum->program_id ? Program::find($curriculum->program_id) : null;
        return view('academic.curriculum_form', compact('curriculum', 'levels', 'programs', 'program', 'subjects', 'personnels', 'returnTo'));
    }

    public function update(Request $request, $id)
    {
        $cur = Curriculum::findOrFail($id);
        $cur->update($request->only(['name', 'level_id', 'year_applied', 'description']) + [
            'program_id' => $request->program_id ?: null,
        ]);
        return redirect()->back()->with('success', 'แก้ไขหลักสูตรสำเร็จ');
    }

    public function destroy(Request $request, $id)
    {
        Curriculum::findOrFail($id)->delete();
        $returnTo = $this->sanitizeReturnTo($request->input('return_to'));
        return ($returnTo ? redirect($returnTo) : redirect()->back())->with('success', 'ลบหลักสูตรสำเร็จ');
    }

    // อ่าน personnel_ids[] จากฟอร์ม (สูงสุด 3 ช่องจากหน้าเว็บ) กรองค่าว่าง/ซ้ำออก
    private function teacherIdsFromRequest(Request $request): array
    {
        return collect($request->input('personnel_ids', []))
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
    }

    public function addSubject(Request $request, $id)
    {
        $request->validate(['subject_id' => 'required|exists:subjects,subject_id']);
        $teacherIds = $this->teacherIdsFromRequest($request);
        $cs = CurriculumSubject::firstOrCreate(
            ['curriculum_id' => $id, 'subject_id' => $request->subject_id],
            [
                'semester_type'  => $request->semester_type ?? 'both',
                'is_required'    => $request->boolean('is_required', true),
                'personnel_id'   => $teacherIds[0] ?? null,
                'credits'        => $request->credits !== null && $request->credits !== '' ? $request->credits : null,
                'hours_per_year' => $request->hours_per_year !== null && $request->hours_per_year !== '' ? $request->hours_per_year : null,
                'hours_per_week' => $request->hours_per_week !== null && $request->hours_per_week !== '' ? $request->hours_per_week : null,
            ]
        );
        $cs->teachers()->sync($teacherIds);
        return redirect()->back()->with('success', 'เพิ่มวิชาในหลักสูตรสำเร็จ');
    }

    public function updateSubject(Request $request, $id, $csId)
    {
        $cs = CurriculumSubject::where('id', $csId)->where('curriculum_id', $id)->firstOrFail();
        $teacherIds = $this->teacherIdsFromRequest($request);
        $cs->update([
            'semester_type'  => $request->semester_type ?? 'both',
            'is_required'    => $request->boolean('is_required', true),
            'personnel_id'   => $teacherIds[0] ?? null,
            'credits'        => $request->credits !== null && $request->credits !== '' ? $request->credits : null,
            'hours_per_year' => $request->hours_per_year !== null && $request->hours_per_year !== '' ? $request->hours_per_year : null,
            'hours_per_week' => $request->hours_per_week !== null && $request->hours_per_week !== '' ? $request->hours_per_week : null,
        ]);
        $cs->teachers()->sync($teacherIds);
        return redirect()->back()->with('success', 'แก้ไขวิชาสำเร็จ');
    }

   public function removeSubject($id, $csId)
    {
        CurriculumSubject::where('id', $csId)->where('curriculum_id', $id)->delete();
        return redirect()->back()->with('success', 'ลบวิชาออกจากหลักสูตรสำเร็จ');
    }

    // เปิด/ปิดใช้งานวิชานี้เฉพาะในแผนนี้ (ไม่กระทบวิชากลางหรือแผนอื่น) — ปิดแล้วจะไม่ถูกเสนอเป็นตัวเลือกตอนจัดตารางสอน
    public function toggleSubject($id, $csId)
    {
        $cs = CurriculumSubject::where('id', $csId)->where('curriculum_id', $id)->firstOrFail();
        $cs->update(['is_active' => !$cs->is_active]);
        return redirect()->back()->with('success', $cs->is_active ? 'เปิดใช้งานวิชาแล้ว' : 'ปิดใช้งานวิชาแล้ว');
    }

    // นำเข้ารายวิชา (พร้อมครูผู้สอนหลายคน จับคู่ด้วยเลขบัตรประชาชน) เข้าแผนนี้จากไฟล์ Excel รูปแบบ PlanCourses
    public function importSubjects(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ], [
            'file.required' => 'กรุณาเลือกไฟล์ Excel',
            'file.mimes' => 'ไฟล์ต้องเป็นสกุล .xlsx เท่านั้น',
        ]);

        set_time_limit(0);

        $path = $request->file('file')->store('imports');
        $fullPath = Storage::path($path);

        $options = ['curriculum_id' => $id, 'file' => $fullPath];
        if ($request->boolean('dry_run')) {
            $options['--dry-run'] = true;
        }

        Artisan::call('import:curriculum-plan', $options);
        $output = Artisan::output();

        @unlink($fullPath);

        return back()->with('curriculum_import_output', $output);
    }
}