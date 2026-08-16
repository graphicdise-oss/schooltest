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
use App\Services\ExcelSchoolHeader;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CurriculumController extends Controller
{
    // แบบฟอร์มเปล่าสำหรับ "นำเข้าจาก Excel" — คอลัมน์ตรงตามที่ ImportCurriculumPlanFromExcel อ่านจริง (อิงตำแหน่ง A-I)
    // เรียงคอลัมน์ให้ตรงกับลำดับตาราง "จัดการวิชาเรียน" ในหน้าเว็บ (รหัสวิชา/หน่วยกิต/ชื่อวิชา/ครูผู้สอน/เทอม/ชม.ปี/ชม.สัปดาห์/ประเภท)
    // ครูผู้สอนกรอกได้แค่ 1 คนหลัก — คนที่เหลือ (ถ้ามีหลายคนสอนร่วม) ให้ไปเพิ่มทีหลังผ่านหน้าเว็บ (ปุ่มจัดการข้อมูล > แก้ไข)
    // ใช้หัวฟอร์ม/แถบคำแนะนำแบบเดียวกับแบบฟอร์มนำเข้าอื่นๆ ในระบบ (ExcelSchoolHeader: หัวโรงเรียนแถว 1-4, คำแนะนำแถว 5,
    // หัวตารางแถว 6, ข้อมูลเริ่มแถว 7) — แถวข้อมูลต้องตรงกับที่ ImportCurriculumPlanFromExcel::parseFile() อ่านจริง ถ้าแก้เลย์เอาต์ตรงนี้ต้องแก้คู่กันที่นั่นด้วย
    public function downloadTemplate()
    {
        $headers = [
            'A' => 'รหัสวิชา', 'B' => 'หน่วยกิต', 'C' => 'ชื่อวิชา', 'D' => 'ชื่อ-นามสกุลครูผู้สอน',
            'E' => 'ภาคเรียน', 'F' => 'ชม./ปี', 'G' => 'ชม./สัปดาห์', 'H' => 'ประเภทรายวิชา', 'I' => 'กลุ่มสาระการเรียนรู้',
        ];

        $totalCols = Coordinate::columnIndexFromString('I'); // 9 คอลัมน์ (A-I) เรียงต่อกันหมด ไม่มีคอลัมน์ว่างคั่น
        $lastCol = 'I';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PlanCourses');

        $hasLogo = ExcelSchoolHeader::apply($sheet, $totalCols, null);
        ExcelSchoolHeader::applyInstructionRow(
            $sheet, $totalCols,
            'แบบฟอร์มนำเข้ารายวิชาของแผนการเรียน — กรอกข้อมูลเริ่มแถวที่ 7 (แถว 1-6 ห้ามลบ/ห้ามแก้) | '
            . 'คอลัมน์ D กรอกชื่อ-นามสกุลครูผู้สอนหลัก 1 คน (จะใส่คำนำหน้าหรือไม่ใส่ก็ได้ ต้องสะกดตรงกับที่มีในระบบ) — ครูร่วมสอนคนอื่นเพิ่มทีหลังผ่านหน้าเว็บได้ | '
            . 'คอลัมน์ E (ภาคเรียน) กรอก 1 หรือ 2 เว้นว่าง=ทั้งปี | คอลัมน์ H กรอกได้: รายวิชาพื้นฐาน/รายวิชาเพิ่มเติม/กิจกรรมพัฒนาผู้เรียน | '
            . 'วิชาที่มีอยู่แล้วจะอัปเดตข้อมูลให้ตรงกับไฟล์นี้'
        );

        $headerRow = 6;
        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}{$headerRow}", $label);
            ExcelSchoolHeader::setColumnWidth($sheet, $col, 18, $hasLogo);
        }
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF2F8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B8C1']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // เผื่อแถวเปล่าไว้ให้กรอก 30 แถว (ตีเส้นไว้ล่วงหน้าให้กรอกง่าย ไม่ใส่ข้อมูลตัวอย่างเพราะเสี่ยงถูกนำเข้าจริงถ้าลืมลบ)
        $firstDataRow = $headerRow + 1;
        $lastDataRow = $firstDataRow + 29;
        $sheet->getStyle("A{$firstDataRow}:{$lastCol}{$lastDataRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        $sheet->freezePane("A{$firstDataRow}");

        $tmpPath = tempnam(sys_get_temp_dir(), 'curriculum_template') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, 'แบบฟอร์มนำเข้ารายวิชา_PlanCourses.xlsx')->deleteFileAfterSend(true);
    }

    // แบบฟอร์มเปล่าสำหรับ "มอบหมายครูผู้สอน" — เบากว่าแบบฟอร์มนำเข้ารายวิชาเต็ม เพราะรับแค่ 3 คอลัมน์
    // (รหัสวิชา/ชื่อครู/ภาคเรียน) ไม่แตะชื่อวิชา หน่วยกิต ชั่วโมง หรือประเภทเลย — ดึงข้อมูลวิชาจากของจริงในระบบเสมอ
    // ต้องตรงกับที่ ImportCurriculumTeacherAssignFromExcel::parseFile() อ่านจริง ถ้าแก้เลย์เอาต์ตรงนี้ต้องแก้คู่กันที่นั่นด้วย
    public function downloadAssignTemplate()
    {
        $headers = [
            'A' => 'รหัสวิชา', 'B' => 'ชื่อ-นามสกุลครูผู้สอน', 'C' => 'ภาคเรียน',
        ];

        $totalCols = Coordinate::columnIndexFromString('C');
        $lastCol = 'C';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('AssignTeacher');

        $hasLogo = ExcelSchoolHeader::apply($sheet, $totalCols, null);
        ExcelSchoolHeader::applyInstructionRow(
            $sheet, $totalCols,
            'แบบฟอร์มมอบหมายครูผู้สอน — สำหรับวิชาที่มีอยู่แล้วในระบบเท่านั้น กรอกข้อมูลเริ่มแถวที่ 7 (แถว 1-6 ห้ามลบ/ห้ามแก้) | '
            . 'คอลัมน์ A กรอกรหัสวิชาที่มีอยู่ในระบบแล้วเท่านั้น (วิชาที่ยังไม่มีในระบบจะถูกข้าม) | '
            . 'คอลัมน์ B กรอกชื่อ-นามสกุลครูผู้สอนหลัก 1 คน (จะใส่คำนำหน้าหรือไม่ใส่ก็ได้ ต้องสะกดตรงกับที่มีในระบบ) | '
            . 'คอลัมน์ C (ภาคเรียน) กรอก 1 หรือ 2 เว้นว่าง=ทั้งปี | '
            . 'ไฟล์นี้จะไม่แก้ไขชื่อวิชา หน่วยกิต ชั่วโมง หรือประเภทรายวิชา — ข้อมูลเหล่านั้นดึงจากของเดิมในระบบเสมอ'
        );

        $headerRow = 6;
        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}{$headerRow}", $label);
            ExcelSchoolHeader::setColumnWidth($sheet, $col, 24, $hasLogo);
        }
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF2F8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B8C1']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        $firstDataRow = $headerRow + 1;
        $lastDataRow = $firstDataRow + 29;
        $sheet->getStyle("A{$firstDataRow}:{$lastCol}{$lastDataRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        $sheet->freezePane("A{$firstDataRow}");

        $tmpPath = tempnam(sys_get_temp_dir(), 'curriculum_assign_template') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, 'แบบฟอร์มมอบหมายครูผู้สอน_AssignTeacher.xlsx')->deleteFileAfterSend(true);
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

        $file = $request->file('file');
        if (!$file || !$file->isValid()) {
            return back()->with('curriculum_import_output', 'อัปโหลดไฟล์ไม่สำเร็จ กรุณาเลือกไฟล์ใหม่แล้วลองอีกครั้ง');
        }

        $fullPath = null;
        try {
            $fullPath = $this->moveUploadedImportFile($file);

            $options = ['curriculum_id' => $id, 'file' => $fullPath];
            if ($request->boolean('dry_run')) {
                $options['--dry-run'] = true;
            }

            Artisan::call('import:curriculum-plan', $options);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            report($e);
            return back()->with('curriculum_import_output', "เกิดข้อผิดพลาดระหว่างนำเข้า: {$e->getMessage()}");
        } finally {
            if ($fullPath) {
                @unlink($fullPath);
            }
        }

        return back()->with('curriculum_import_output', $output);
    }

    public function importAssign(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ], [
            'file.required' => 'กรุณาเลือกไฟล์ Excel',
            'file.mimes' => 'ไฟล์ต้องเป็นสกุล .xlsx เท่านั้น',
        ]);

        set_time_limit(0);

        $file = $request->file('file');
        if (!$file || !$file->isValid()) {
            return back()->with('curriculum_import_output', 'อัปโหลดไฟล์ไม่สำเร็จ กรุณาเลือกไฟล์ใหม่แล้วลองอีกครั้ง');
        }

        $fullPath = null;
        try {
            $fullPath = $this->moveUploadedImportFile($file);

            $options = ['curriculum_id' => $id, 'file' => $fullPath];
            if ($request->boolean('dry_run')) {
                $options['--dry-run'] = true;
            }

            Artisan::call('import:curriculum-assign', $options);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            report($e);
            return back()->with('curriculum_import_output', "เกิดข้อผิดพลาดระหว่างนำเข้า: {$e->getMessage()}");
        } finally {
            if ($fullPath) {
                @unlink($fullPath);
            }
        }

        return back()->with('curriculum_import_output', $output);
    }

    // ย้ายไฟล์ที่อัปโหลดไปเก็บที่ storage/app/private/imports ด้วย move_uploaded_file() ตรงๆ (ผ่าน UploadedFile::move())
    // แทนการใช้ Storage::disk()->putFileAs()/file->store() เพราะบางเครื่อง (โดยเฉพาะ Windows ที่มีโปรแกรมป้องกันไวรัส
    // สแกนไฟล์ temp แบบ real-time) ไฟล์ temp ต้นทางอาจถูกสแกน/ล็อกไว้ชั่วขณะจน getRealPath() คืนค่าว่าง ทำให้ fopen()
    // ภายใน store() พังด้วย "Path cannot be empty" — move_uploaded_file() ยืนยันแหล่งที่มาจาก $_FILES ตรงๆ ทนทานกว่า
    private function moveUploadedImportFile(\Illuminate\Http\UploadedFile $file): string
    {
        $dir = storage_path('app/private/imports');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = uniqid('import_', true) . '.' . ($file->getClientOriginalExtension() ?: 'xlsx');

        return $file->move($dir, $filename)->getPathname();
    }
}