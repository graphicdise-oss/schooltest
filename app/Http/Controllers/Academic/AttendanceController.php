<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\TeachingAssign;
use App\Models\Academic\StudentSection;
use App\Models\Academic\ClassAttendance;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Semester;
use App\Models\Academic\Subject;
use App\Models\Personne\Personnel;
use App\Services\ExcelSchoolHeader;
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
        $sectionId   = $request->section_id;

        $semesters = Semester::with('academicYear')->orderedByRecency()->get();
        $subjects  = Subject::where('is_active', true)->orderBy('code')->get();
        $teachers  = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();

        $query = TeachingAssign::with(['personnel', 'subject', 'classSection.level'])
            ->where('semester_id', $semesterId)
            ->orderBy('section_id');

        if ($subjectId)   $query->where('subject_id', $subjectId);
        if ($personnelId) $query->where('personnel_id', $personnelId);
        if ($sectionId)   $query->where('section_id', $sectionId);

        $assigns = $query->get()->map(function ($a) {
            $a->attendance_days = ClassAttendance::where('assign_id', $a->assign_id)
                ->distinct('class_date')->count('class_date');
            return $a;
        });

        $sections = ClassSection::with('level')
            ->where('semester_id', $semesterId)
            ->orderBy('level_id')->orderBy('section_number')
            ->get();

        return view('academic.attendance_index', compact(
            'assigns', 'semesters', 'subjects', 'teachers', 'semesterId', 'subjectId', 'personnelId', 'sections', 'sectionId'
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

        $dates = $this->monthDaysWithSchoolFlag($year, $mon, $semester?->year_id, $semester?->start_date, $semester?->end_date);

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $existing = collect();
        if ($dates->isNotEmpty()) {
            $existing = ClassAttendance::where('assign_id', $assignId)
                ->whereBetween('class_date', [$dates->first()->date->format('Y-m-d'), $dates->last()->date->format('Y-m-d')])
                ->get()
                ->keyBy(fn ($r) => $r->student_id . '|' . $r->class_date->format('Y-m-d'));
        }

        return view('academic.attendance_mark', compact('assign', 'students', 'dates', 'existing', 'monthValue'));
    }

    public function store(Request $request, $assignId)
    {
        $assign = TeachingAssign::with('semester')->findOrFail($assignId);

        $user = auth()->user();
        if (!$user->isAdmin() && $user->personnel_id !== $assign->personnel_id) {
            return redirect()->route('attendance.index')->with('error', 'คุณไม่มีสิทธิ์เช็คชื่อวิชานี้');
        }

        // วันเรียนจริงที่ "ผ่านไปแล้ว" (ไม่รวมวันหยุด/เสาร์-อาทิตย์ และไม่รวมวันในอนาคตที่ยังไม่ถึง) ของเดือนที่กำลังบันทึก
        // ใช้ตัดสินว่าช่องไหนเข้าเงื่อนไข "ครูลืมเช็ค" ควรเติม ขาด ให้อัตโนมัติ — วันหยุด/วันอนาคตเว้นว่างได้ตามปกติ
        $pastSchoolDays = [];
        $monthValue = $request->input('month');
        if ($monthValue && preg_match('/^(\d{4})-(\d{1,2})$/', $monthValue, $m)) {
            $semester = $assign->semester;
            $today = now()->format('Y-m-d');
            foreach ($this->monthDaysWithSchoolFlag((int) $m[1], (int) $m[2], $semester?->year_id, $semester?->start_date, $semester?->end_date) as $d) {
                if ($d->is_school_day && $d->date->format('Y-m-d') <= $today) {
                    $pastSchoolDays[$d->date->format('Y-m-d')] = true;
                }
            }
        }

        // status[วันที่][student_id] = สถานะ — ช่องไหนเว้นว่างในวันเรียนจริงที่ผ่านมาแล้ว ถือว่าครูลืมเช็ค เติม "ขาด"
        // ให้อัตโนมัติ (แก้ไขทีหลังได้) ส่วนวันหยุด/วันในอนาคตที่เว้นว่างไว้ ข้ามเงียบๆ เหมือนเดิม ไม่บันทึกอะไร
        $grid = $request->input('status', []);
        $saved = 0;
        foreach ($grid as $date => $byStudent) {
            if (!is_array($byStudent)) continue;
            foreach ($byStudent as $studentId => $status) {
                $status = trim((string) $status);
                if ($status === '' && isset($pastSchoolDays[$date])) {
                    $status = 'ขาด';
                }
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
            $allDays = $this->monthDaysWithSchoolFlag($ym['year'], $ym['month'], $semester?->year_id, $semStart, $semEnd);
            if ($allDays->isEmpty() || !$allDays->contains(fn ($d) => $d->is_school_day)) {
                continue;
            }

            $sheet = $firstSheet ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $firstSheet = false;
            $sheet->setTitle($this->uniqueMonthTitle($ym['year'], $ym['month'], $usedTitles));

            $existing = ClassAttendance::where('assign_id', $assignId)
                ->whereBetween('class_date', [$allDays->first()->date->format('Y-m-d'), $allDays->last()->date->format('Y-m-d')])
                ->get()
                ->groupBy(fn($r) => $r->student_id . '|' . $r->class_date->format('Y-m-d'));

            $this->buildAttendanceSheet($sheet, $assign, $students, $allDays, $existing, $ym['year'], $ym['month']);
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

    // ทุกวันของเดือนนั้น "รวมเสาร์-อาทิตย์" ด้วย (ครอบด้วยช่วงเปิดเทอมจริง ถ้ามี กันเผลอสร้างคอลัมน์วันที่นอกเทอม)
    // พร้อมบอกว่าวันไหนเป็นวันเรียนจริง — ใช้ทั้งหน้าเช็คชื่อออนไลน์และไฟล์ Excel ให้คอลัมน์วันที่ครบทุกวันไม่กระโดดข้าม
    // วันหยุด (เสาร์-อาทิตย์/วันหยุดตามปฏิทิน) จะโชว์คำว่า "วันหยุด" แทนช่องกรอกสถานะ แต่ยังเลือกเปลี่ยนได้เผื่อมีเรียนจริง
    private function monthDaysWithSchoolFlag(int $year, int $month, $yearId, $semStart, $semEnd)
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
            $isHoliday = $holidays->contains(function ($h) use ($d) {
                if (!$h->start_date) return false;
                return $d->between($h->start_date, $h->end_date ?? $h->start_date);
            });
            $days->push((object) [
                'date' => $d->copy(),
                'is_school_day' => !$d->isWeekend() && !$isHoliday,
            ]);
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

    // สร้างชีต Excel สำหรับเช็คชื่อออฟไลน์ 1 วิชา-ห้อง — เลย์เอาต์เดียวกับ "ตรวจเช็คการเข้าเรียน" (buildRoomSummarySheet):
    // คอลัมน์วันที่เป็นเลขวันของเดือน (ไม่ใช่วันที่เต็ม) + แถวสรุป "รวม/ขาด/ร้อยละ" ต่อคน + "มาเรียนรวม"/"ขาดเรียนรวม"
    // ท้ายตาราง แต่เว้น 3 แถวว่างไว้ก่อนถึงแถวสรุป (มีดรอปดาวน์พร้อมกรอกด้วย) เผื่อมีนักเรียนเข้าใหม่ระหว่างเดือน
    private function buildAttendanceSheet($sheet, TeachingAssign $assign, $students, $dates, $existing, int $year, int $month): void
    {
        $n = $dates->count();
        $schoolDayCount = $dates->filter(fn ($d) => $d->is_school_day)->count();
        $firstDateCol = 4; // D
        $lastDateCol = 3 + $n;
        $sumCol = $lastDateCol + 1;
        $absentCol = $sumCol + 1;
        $pctCol = $absentCol + 1;
        $lastCol = Coordinate::stringFromColumnIndex($pctCol);
        $dateColLetter = fn (int $i) => Coordinate::stringFromColumnIndex($firstDateCol + $i);
        $datesList = $dates->values();

        $teacherName = trim(($assign->personnel->thai_prefix ?? '') . ($assign->personnel->thai_firstname ?? '') . ' ' . ($assign->personnel->thai_lastname ?? ''));

        // แถบหัวชื่อโรงเรียน + ตราโรงเรียน (แถว 1-4) เดียวกับไฟล์ Excel อื่นๆ ในระบบ ตั้งค่าตราโรงเรียน/ข้อมูลติดต่อ
        // ได้ที่เมนู "ข้อมูลนักเรียน" > นำเข้าข้อมูล (ปุ่มตั้งค่าหัวฟอร์ม)
        $hasLogo = ExcelSchoolHeader::apply($sheet, $pctCol);

        $sheet->mergeCells("A5:{$lastCol}5");
        $sheet->setCellValue('A5', 'แบบฟอร์มเช็คชื่อ   ' . $assign->subject->code . ' — ' . $assign->subject->name_th . '   ห้อง ' . $assign->classSection->full_name . '   ครูผู้สอน ' . $teacherName);
        $sheet->getStyle('A5')->applyFromArray(['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

        $sheet->mergeCells("A6:{$lastCol}6");
        $sheet->setCellValue('A6', 'เดือน ' . self::THAI_MONTHS_SHORT[$month] . ' ปี ' . ($year + 543));
        $sheet->getStyle('A6')->applyFromArray(['font' => ['bold' => true, 'size' => 10.5], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

        $sheet->setCellValue('A7', 'รหัสอ้างอิง (ห้ามแก้ไข)');
        $sheet->setCellValue('B7', (string) $assign->assign_id);
        $sheet->setCellValue('A8', 'เดือนที่อ้างอิง (ห้ามแก้ไข)');
        $sheet->setCellValue('B8', sprintf('%04d-%02d', $year, $month));
        $sheet->getStyle('A7:B8')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '595959']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F5']],
        ]);

        $headerRow = 9;
        $sheet->setCellValue("A{$headerRow}", 'เลขที่');
        $sheet->setCellValue("B{$headerRow}", 'เลขประจำตัวนักเรียน');
        $sheet->setCellValue("C{$headerRow}", 'ชื่อ - สกุล');
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($sumCol) . $headerRow, "รวม {$schoolDayCount} คาบ");
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($absentCol) . $headerRow, 'ขาด');
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($pctCol) . $headerRow, 'เข้าเรียนร้อยละ');
        foreach ($datesList as $i => $d) {
            $sheet->setCellValue($dateColLetter($i) . $headerRow, (int) $d->date->format('j'));
        }
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF2F8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B8C1']]],
        ]);
        // แถวหัวตารางเอง: คอลัมน์วันหยุด (เสาร์-อาทิตย์/วันหยุดตามปฏิทิน) แรเงาเข้มกว่าปกติเล็กน้อยให้สังเกตง่าย
        foreach ($datesList as $i => $d) {
            if (!$d->is_school_day) {
                $sheet->getStyle($dateColLetter($i) . $headerRow)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
                ]);
            }
        }

        $firstDataRow = $headerRow + 1;
        $row = $firstDataRow;
        $firstDateColLetter = $dateColLetter(0);
        $lastDateColLetter = $dateColLetter($n - 1);

        $applyStatusValidation = function ($cell) {
            $validation = $cell->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_WARNING);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('ค่าไม่ถูกต้อง');
            $validation->setError('เลือกจากลิสต์: ' . implode(', ', ClassAttendance::STATUSES));
            $validation->setFormula1('"' . implode(',', ClassAttendance::STATUSES) . '"');
        };

        foreach ($students as $ss) {
            $student = $ss->student;
            $sheet->setCellValue("A{$row}", $ss->student_number);
            $sheet->setCellValue("B{$row}", $student->student_code ?? '');
            $sheet->setCellValue("C{$row}", trim(($student->thai_prefix ?? '') . ($student->thai_firstname ?? '') . ' ' . ($student->thai_lastname ?? '')));

            foreach ($datesList as $i => $d) {
                $col = $dateColLetter($i);
                $cell = $sheet->getCell("{$col}{$row}");
                $key = $student->student_id . '|' . $d->date->format('Y-m-d');
                $prior = $existing->get($key)?->first()?->status ?? '';
                $cell->setValue($prior);
                $applyStatusValidation($cell);
            }

            $rowRange = "{$firstDateColLetter}{$row}:{$lastDateColLetter}{$row}";
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($sumCol) . $row, "=COUNTIF({$rowRange},\"มา\")");
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($absentCol) . $row, "=COUNTIF({$rowRange},\"ขาด\")");
            $sumCell = Coordinate::stringFromColumnIndex($sumCol) . $row;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($pctCol) . $row, "=IF({$schoolDayCount}=0,0,({$sumCell}*100)/{$schoolDayCount})");
            $row++;
        }
        $lastDataRow = $row - 1;

        // เว้นแถวว่างไว้ 3 แถวเผื่อมีนักเรียนเข้าใหม่ — มีดรอปดาวน์/สูตรสรุปรายแถวพร้อมใช้ทันทีที่กรอกชื่อ
        for ($k = 0; $k < 3; $k++) {
            foreach ($datesList as $i => $d) {
                $applyStatusValidation($sheet->getCell($dateColLetter($i) . $row));
            }
            $rowRange = "{$firstDateColLetter}{$row}:{$lastDateColLetter}{$row}";
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($sumCol) . $row, "=COUNTIF({$rowRange},\"มา\")");
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($absentCol) . $row, "=COUNTIF({$rowRange},\"ขาด\")");
            $sumCell = Coordinate::stringFromColumnIndex($sumCol) . $row;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($pctCol) . $row, "=IF({$schoolDayCount}=0,0,({$sumCell}*100)/{$schoolDayCount})");
            $row++;
        }
        $lastBufferRow = $row - 1;

        $sheet->setCellValue("A{$row}", 'มาเรียนรวม');
        $sheet->mergeCells("A{$row}:C{$row}");
        foreach ($datesList as $i => $d) {
            $col = $dateColLetter($i);
            $sheet->setCellValue("{$col}{$row}", "=COUNTIF({$col}{$firstDataRow}:{$col}{$lastBufferRow},\"มา\")");
        }
        $row++;
        $sheet->setCellValue("A{$row}", 'ขาดเรียนรวม');
        $sheet->mergeCells("A{$row}:C{$row}");
        foreach ($datesList as $i => $d) {
            $col = $dateColLetter($i);
            $sheet->setCellValue("{$col}{$row}", "=COUNTIF({$col}{$firstDataRow}:{$col}{$lastBufferRow},\"ขาด\")");
        }

        $sheet->getStyle("A{$headerRow}:{$lastCol}{$row}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDE3EA']]],
        ]);
        $sheet->getStyle("A{$firstDataRow}:{$lastCol}{$row}")->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle("C{$firstDataRow}:C{$lastDataRow}")->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]]);

        ExcelSchoolHeader::setColumnWidth($sheet, 'A', 7, $hasLogo);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(24);
        for ($i = 0; $i < $n; $i++) {
            $sheet->getColumnDimension($dateColLetter($i))->setWidth(5);
        }
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($sumCol))->setWidth(9);
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($absentCol))->setWidth(9);
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($pctCol))->setWidth(11);
        $sheet->freezePane($firstDateColLetter . $firstDataRow);
    }
}
