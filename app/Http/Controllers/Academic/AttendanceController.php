<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\TeachingAssign;
use App\Models\Academic\StudentSection;
use App\Models\Academic\ClassAttendance;
use App\Models\Academic\Semester;
use App\Models\Academic\Subject;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AttendanceController extends Controller
{
    // เลือกวิชา-ห้องที่จะเช็คชื่อ
    public function index(Request $request)
    {
        $semesterId  = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $subjectId   = $request->subject_id;
        $personnelId = $request->personnel_id;

        $semesters = Semester::with('academicYear')->orderedByRecency()->get();
        $subjects  = Subject::where('is_active', true)->orderBy('code')->get();
        $teachers  = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();

        $query = TeachingAssign::with(['personnel', 'subject', 'classSection.level'])
            ->where('semester_id', $semesterId)
            ->orderBy('section_id');

        if ($subjectId)   $query->where('subject_id', $subjectId);
        if ($personnelId) $query->where('personnel_id', $personnelId);

        $assigns = $query->get()->map(function ($a) {
            $a->attendance_days = ClassAttendance::where('assign_id', $a->assign_id)
                ->distinct('class_date')->count('class_date');
            return $a;
        });

        return view('academic.attendance_index', compact(
            'assigns', 'semesters', 'subjects', 'teachers', 'semesterId', 'subjectId', 'personnelId'
        ));
    }

    // หน้าเช็คชื่อของวิชา-ห้องนั้น (เลือกวันที่)
    public function mark(Request $request, $assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level', 'classSection.semester.academicYear'])
            ->findOrFail($assignId);

        $user = auth()->user();
        if (!$user->isAdmin() && $user->personnel_id !== $assign->personnel_id) {
            return redirect()->route('attendance.index')->with('error', 'คุณไม่มีสิทธิ์เช็คชื่อวิชานี้ (เฉพาะครูประจำวิชาเท่านั้น)');
        }

        $semester = $assign->classSection->semester;
        $date = $request->get('date', now()->toDateString());
        if ($semester->start_date && $date < $semester->start_date->toDateString()) $date = $semester->start_date->toDateString();
        if ($semester->end_date && $date > $semester->end_date->toDateString())   $date = $semester->end_date->toDateString();

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $existing = ClassAttendance::where('assign_id', $assignId)
            ->where('class_date', $date)
            ->get()->keyBy('student_id');

        $recentDates = ClassAttendance::where('assign_id', $assignId)
            ->selectRaw('class_date, count(*) as total')
            ->groupBy('class_date')
            ->orderByDesc('class_date')
            ->limit(10)
            ->get();

        return view('academic.attendance_mark', compact('assign', 'students', 'date', 'existing', 'recentDates'));
    }

    public function store(Request $request, $assignId)
    {
        $assign = TeachingAssign::findOrFail($assignId);

        $user = auth()->user();
        if (!$user->isAdmin() && $user->personnel_id !== $assign->personnel_id) {
            return redirect()->route('attendance.index')->with('error', 'คุณไม่มีสิทธิ์เช็คชื่อวิชานี้');
        }

        $request->validate(['date' => 'required|date']);
        $statuses = $request->input('status', []);

        foreach ($statuses as $studentId => $status) {
            if (!in_array($status, ClassAttendance::STATUSES, true)) continue;
            ClassAttendance::updateOrCreate(
                ['assign_id' => $assignId, 'student_id' => $studentId, 'class_date' => $request->date],
                ['status' => $status]
            );
        }

        return redirect()
            ->route('attendance.mark', ['assign' => $assignId, 'date' => $request->date])
            ->with('success', 'บันทึกการเช็คชื่อสำเร็จ');
    }

    // ดาวน์โหลดแบบฟอร์ม Excel สำหรับเช็คชื่อออฟไลน์ (ช่วงวันที่ที่เลือก)
    public function exportTemplate(Request $request, $assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level'])->findOrFail($assignId);

        $user = auth()->user();
        if (!$user->isAdmin() && $user->personnel_id !== $assign->personnel_id) {
            return redirect()->route('attendance.index')->with('error', 'คุณไม่มีสิทธิ์ดาวน์โหลดแบบฟอร์มวิชานี้ (เฉพาะครูประจำวิชาเท่านั้น)');
        }

        $from = $request->get('from', now()->toDateString());
        $to = $request->get('to', now()->addDays(6)->toDateString());
        if ($to < $from) {
            [$from, $to] = [$to, $from];
        }
        $dates = collect(\Carbon\CarbonPeriod::create($from, $to));
        if ($dates->count() > 62) {
            return redirect()->route('attendance.index')->with('error', 'ช่วงวันที่ที่เลือกกว้างเกินไป (สูงสุด 62 วันต่อครั้ง) กรุณาเลือกช่วงให้แคบลง');
        }

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $existing = ClassAttendance::where('assign_id', $assignId)
            ->whereBetween('class_date', [$from, $to])
            ->get()
            ->groupBy(fn($r) => $r->student_id . '|' . $r->class_date->format('Y-m-d'));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('เช็คชื่อ');
        $this->buildAttendanceSheet($sheet, $assign, $students, $dates, $existing);

        $tmpPath = tempnam(sys_get_temp_dir(), 'attendance_template') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);
        $filename = 'แบบฟอร์มเช็คชื่อ_' . $assign->subject->code . '_' . $assign->classSection->full_name . '_' . now()->format('Ymd_His') . '.xlsx';
        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    // นำเข้าไฟล์ Excel ที่กรอกเช็คชื่อออฟไลน์แล้ว
    public function importExcel(Request $request)
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

        $options = ['file' => $fullPath];
        if ($request->boolean('dry_run')) {
            $options['--dry-run'] = true;
        }

        Artisan::call('import:attendance', $options);
        $output = Artisan::output();

        @unlink($fullPath);

        return back()->with('attendance_import_output', $output);
    }

    // สร้างชีต Excel สำหรับเช็คชื่อออฟไลน์ 1 วิชา-ห้อง — แถวนักเรียน x คอลัมน์วันที่
    private function buildAttendanceSheet($sheet, TeachingAssign $assign, $students, $dates, $existing): void
    {
        $totalCols = 3 + $dates->count();
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);

        $this->writeAttLabeledRow($sheet, $totalCols, 1, 'วิชา', $assign->subject->code . ' — ' . $assign->subject->name_th);
        $this->writeAttLabeledRow($sheet, $totalCols, 2, 'ห้อง', $assign->classSection->full_name);
        $this->writeAttLabeledRow($sheet, $totalCols, 3, 'ครูผู้สอน', trim(($assign->personnel->thai_prefix ?? '') . ($assign->personnel->thai_firstname ?? '') . ' ' . ($assign->personnel->thai_lastname ?? '')));
        $this->writeAttLabeledRow($sheet, $totalCols, 4, 'รหัสอ้างอิง (ห้ามแก้ไข)', (string) $assign->assign_id);

        $sheet->mergeCells("A6:{$lastCol}6");
        $sheet->setCellValue('A6', 'กรอกสถานะในแต่ละวันที่: ' . implode(' / ', ClassAttendance::STATUSES) . ' — เว้นว่างไว้ถ้าวันนั้นไม่มีเรียน/ไม่เช็คชื่อ (เลือกจากลิสต์ในช่องได้เลย)');
        $sheet->getStyle('A6')->applyFromArray(['font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '777777']]]);

        $headerRow = 8;
        $sheet->setCellValue("A{$headerRow}", 'เลขที่');
        $sheet->setCellValue("B{$headerRow}", 'รหัสนักเรียน');
        $sheet->setCellValue("C{$headerRow}", 'ชื่อ-สกุล');
        foreach ($dates->values() as $i => $date) {
            $col = Coordinate::stringFromColumnIndex(4 + $i);
            $cell = $sheet->getCell("{$col}{$headerRow}");
            $cell->setValue(\PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($date->toDateTime()));
            $cell->getStyle()->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        }
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10.5],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF2F8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B8C1']]],
        ]);

        $row = $headerRow + 1;
        foreach ($students as $ss) {
            $student = $ss->student;
            $sheet->setCellValue("A{$row}", $ss->student_number);
            $sheet->setCellValue("B{$row}", $student->student_code ?? '');
            $sheet->setCellValue("C{$row}", trim(($student->thai_prefix ?? '') . ($student->thai_firstname ?? '') . ' ' . ($student->thai_lastname ?? '')));

            foreach ($dates->values() as $i => $date) {
                $col = Coordinate::stringFromColumnIndex(4 + $i);
                $key = $student->student_id . '|' . $date->format('Y-m-d');
                $prior = $existing->get($key)?->first()?->status ?? '';
                $cell = $sheet->getCell("{$col}{$row}");
                $cell->setValue($prior);

                $validation = $cell->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_WARNING);
                $validation->setAllowBlank(true);
                $validation->setShowDropDown(true);
                $validation->setShowErrorMessage(true);
                $validation->setErrorTitle('ค่าไม่ถูกต้อง');
                $validation->setError('เลือกจากลิสต์: ' . implode(', ', ClassAttendance::STATUSES));
                $validation->setFormula1('"' . implode(',', ClassAttendance::STATUSES) . '"');
            }
            $row++;
        }

        $sheet->getStyle("A{$headerRow}:{$lastCol}{$row}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDE3EA']]],
        ]);
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(26);
        foreach ($dates->values() as $i => $date) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(4 + $i))->setWidth(11);
        }
        $sheet->freezePane('D' . ($headerRow + 1));
    }

    private function writeAttLabeledRow($sheet, int $totalCols, int $row, string $label, string $value): void
    {
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);

        $sheet->setCellValue("A{$row}", $label);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10.5],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF2F8']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B8C1']]],
        ]);

        $sheet->mergeCells("B{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("B{$row}", $value);
        $sheet->getStyle("B{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10.5],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B8C1']]],
        ]);
    }
}
