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

    // หน้าเช็คชื่อของวิชา-ห้องนั้น — ตารางรายเดือน (นักเรียน x วันเรียนของเดือนนั้น)
    // ตัดวันเสาร์-อาทิตย์และวันหยุดตามปฏิทินออกให้อัตโนมัติ เหมือนแบบฟอร์ม Excel
    public function mark(Request $request, $assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level', 'semester.academicYear'])
            ->findOrFail($assignId);

        $user = auth()->user();
        if (!$user->isAdmin() && $user->personnel_id !== $assign->personnel_id) {
            return redirect()->route('attendance.index')->with('error', 'คุณไม่มีสิทธิ์เช็คชื่อวิชานี้ (เฉพาะครูประจำวิชาเท่านั้น)');
        }

        $semester = $assign->semester;
        $month = $request->get('month');
        if ($month && preg_match('/^(\d{4})-(\d{1,2})$/', $month, $m)) {
            [$year, $mon] = [(int) $m[1], (int) $m[2]];
        } else {
            [$year, $mon] = [(int) now()->year, (int) now()->month];
        }
        $monthValue = sprintf('%04d-%02d', $year, $mon);

        $dates = $this->schoolDaysInMonth($year, $mon, $semester?->year_id, $semester?->start_date, $semester?->end_date);

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $existing = collect();
        if ($dates->isNotEmpty()) {
            $existing = ClassAttendance::where('assign_id', $assignId)
                ->whereBetween('class_date', [$dates->first()->format('Y-m-d'), $dates->last()->format('Y-m-d')])
                ->get()
                ->keyBy(fn ($r) => $r->student_id . '|' . $r->class_date->format('Y-m-d'));
        }

        return view('academic.attendance_mark', compact('assign', 'students', 'dates', 'existing', 'monthValue'));
    }

    public function store(Request $request, $assignId)
    {
        $assign = TeachingAssign::findOrFail($assignId);

        $user = auth()->user();
        if (!$user->isAdmin() && $user->personnel_id !== $assign->personnel_id) {
            return redirect()->route('attendance.index')->with('error', 'คุณไม่มีสิทธิ์เช็คชื่อวิชานี้');
        }

        // status[วันที่][student_id] = สถานะ — ช่องไหนเว้นว่าง/ไม่ใช่สถานะที่รู้จัก ข้ามเงียบๆ (ไม่บันทึก ไม่ error)
        $grid = $request->input('status', []);
        $saved = 0;
        foreach ($grid as $date => $byStudent) {
            if (!is_array($byStudent)) continue;
            foreach ($byStudent as $studentId => $status) {
                if (!in_array($status, ClassAttendance::STATUSES, true)) continue;
                ClassAttendance::updateOrCreate(
                    ['assign_id' => $assignId, 'student_id' => $studentId, 'class_date' => $date],
                    ['status' => $status]
                );
                $saved++;
            }
        }

        return redirect()
            ->route('attendance.mark', ['assign' => $assignId, 'month' => $request->input('month')])
            ->with('success', "บันทึกการเช็คชื่อสำเร็จ ({$saved} ช่อง)");
    }

    private const THAI_MONTHS_SHORT = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

    // ดาวน์โหลดแบบฟอร์ม Excel สำหรับเช็คชื่อออฟไลน์ — เลือกได้ทีละเดือน หรือทุกเดือนที่มีในเทอม (?all=1)
    // ตัดวันเสาร์-อาทิตย์และวันหยุดตามปฏิทิน (Holiday) ของปีการศึกษานั้นออกให้อัตโนมัติ
    public function exportTemplate(Request $request, $assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level', 'semester'])->findOrFail($assignId);

        $user = auth()->user();
        if (!$user->isAdmin() && $user->personnel_id !== $assign->personnel_id) {
            return redirect()->route('attendance.index')->with('error', 'คุณไม่มีสิทธิ์ดาวน์โหลดแบบฟอร์มวิชานี้ (เฉพาะครูประจำวิชาเท่านั้น)');
        }

        $semester = $assign->semester;
        $semStart = $semester?->start_date;
        $semEnd = $semester?->end_date;

        if ($request->boolean('all')) {
            $months = $this->monthsInRange($semStart, $semEnd);
            if (empty($months)) {
                return redirect()->route('attendance.index')->with('error', 'ไม่พบช่วงวันที่ของภาคเรียนนี้ (ยังไม่ได้ตั้งวันเริ่ม/สิ้นสุดเทอม) ไม่สามารถสร้างแบบฟอร์มทุกเดือนได้');
            }
            $filenameSuffix = 'ทุกเดือน';
        } else {
            $month = $request->get('month');
            if ($month && preg_match('/^(\d{4})-(\d{1,2})$/', $month, $m)) {
                $months = [['year' => (int) $m[1], 'month' => (int) $m[2]]];
            } else {
                $months = [['year' => (int) now()->year, 'month' => (int) now()->month]];
            }
            $filenameSuffix = sprintf('%04d-%02d', $months[0]['year'], $months[0]['month']);
        }

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $spreadsheet = new Spreadsheet();
        $firstSheet = true;
        $usedTitles = [];
        foreach ($months as $ym) {
            $dates = $this->schoolDaysInMonth($ym['year'], $ym['month'], $semester?->year_id, $semStart, $semEnd);
            if ($dates->isEmpty()) {
                continue;
            }

            $sheet = $firstSheet ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $firstSheet = false;
            $sheet->setTitle($this->uniqueMonthTitle($ym['year'], $ym['month'], $usedTitles));

            $existing = ClassAttendance::where('assign_id', $assignId)
                ->whereBetween('class_date', [$dates->first()->format('Y-m-d'), $dates->last()->format('Y-m-d')])
                ->get()
                ->groupBy(fn($r) => $r->student_id . '|' . $r->class_date->format('Y-m-d'));

            $this->buildAttendanceSheet($sheet, $assign, $students, $dates, $existing);
        }

        if ($firstSheet) {
            return redirect()->route('attendance.index')->with('error', 'ไม่พบวันเรียนในช่วงที่เลือก (อาจอยู่นอกภาคเรียน หรือตรงกับวันหยุดทั้งหมด)');
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'attendance_template') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);
        $roomLabel = str_replace(['/', '\\'], '-', $assign->classSection->full_name);
        $filename = 'แบบฟอร์มเช็คชื่อ_' . $assign->subject->code . '_' . $roomLabel . '_' . $filenameSuffix . '.xlsx';
        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    // รายการ [ปี, เดือน] ของทุกเดือนปฏิทินที่ทับกับช่วง $start..$end (ใช้ตอน export ทุกเดือน)
    private function monthsInRange($start, $end): array
    {
        if (!$start || !$end) {
            return [];
        }
        $cursor = \Carbon\Carbon::parse($start)->startOfMonth();
        $endMonth = \Carbon\Carbon::parse($end)->startOfMonth();
        $months = [];
        while ($cursor->lte($endMonth)) {
            $months[] = ['year' => $cursor->year, 'month' => $cursor->month];
            $cursor->addMonth();
        }
        return $months;
    }

    // วันเรียนจริงของเดือนนั้น — ตัดวันเสาร์-อาทิตย์ + วันหยุดตามปฏิทิน (Holiday ของปีการศึกษานี้) ออก
    // แล้วครอบด้วยช่วงเปิดเทอมจริง (ถ้ามี) กันเผลอสร้างคอลัมน์วันที่นอกเทอม
    private function schoolDaysInMonth(int $year, int $month, $yearId, $semStart, $semEnd)
    {
        $monthStart = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        if ($semStart) {
            $s = \Carbon\Carbon::parse($semStart)->startOfDay();
            if ($monthStart->lt($s)) $monthStart = $s->copy();
        }
        if ($semEnd) {
            $e = \Carbon\Carbon::parse($semEnd)->startOfDay();
            if ($monthEnd->gt($e)) $monthEnd = $e->copy();
        }
        if ($monthStart->gt($monthEnd)) {
            return collect();
        }

        $holidays = $yearId ? \App\Models\Holiday::where('year_id', $yearId)->get() : collect();

        $days = collect();
        for ($d = $monthStart->copy(); $d->lte($monthEnd); $d->addDay()) {
            if ($d->isWeekend()) continue;
            $isHoliday = $holidays->contains(function ($h) use ($d) {
                if (!$h->start_date) return false;
                return $d->between($h->start_date, $h->end_date ?? $h->start_date);
            });
            if ($isHoliday) continue;
            $days->push($d->copy());
        }
        return $days;
    }

    private function uniqueMonthTitle(int $year, int $month, array &$usedTitles): string
    {
        $title = self::THAI_MONTHS_SHORT[$month] . ($year + 543);
        $base = $title;
        $n = 2;
        while (isset($usedTitles[$title])) {
            $title = $base . "-{$n}";
            $n++;
        }
        $usedTitles[$title] = true;
        return $title;
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
        $sheet->setCellValue('A6', 'กรอกสถานะในแต่ละวันที่: ' . implode(' / ', ClassAttendance::STATUSES) . ' — ตัดวันเสาร์-อาทิตย์และวันหยุดตามปฏิทินออกให้แล้ว เว้นว่างไว้ได้ถ้าวันนั้นไม่เช็คชื่อ (เลือกจากลิสต์ในช่องได้เลย)');
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
