<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\Semester;
use App\Models\Academic\StudentSection;
use App\Services\ExcelSchoolHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentStatController extends Controller
{
    public function index(Request $request)
    {
        $yearId     = $request->get('year_id');
        $semesterId = $request->get('semester_id');
        $levelId    = $request->get('level_id', '');
        $reportType = $request->get('report_type', 'gender');

        $academicYears = AcademicYear::orderByDesc('year_name')->get();

        // auto-default เฉพาะตอนโหลดหน้าแรก ไม่มี year_id ใน URL
        if (!$request->has('year_id')) {
            $currentYear = AcademicYear::where('is_current', true)->first() ?? $academicYears->first();
            $yearId      = $currentYear?->year_id;
            $defaultSem  = Semester::where('year_id', $yearId)->where('is_current', true)->first()
                ?? Semester::where('year_id', $yearId)->orderBy('semester_name')->first();
            $semesterId  = $defaultSem?->semester_id;
        }

        $semesters = $yearId
            ? Semester::where('year_id', $yearId)->orderBy('semester_name')->get()
            : collect();

        $levels = Level::orderBy('sort_order')->get();

        $grouped = $yearId ? $this->buildGrouped($yearId, $semesterId, $levelId, $levels) : collect();

        return view('student.student_stat', compact(
            'academicYears', 'semesters', 'levels',
            'yearId', 'semesterId', 'levelId',
            'reportType', 'grouped'
        ));
    }

    // สร้างไฟล์ Excel ของตารางสถิติที่กำลังดูอยู่ (ใช้ตัวกรองชุดเดียวกับหน้าจอ)
    public function exportExcel(Request $request)
    {
        $yearId     = $request->get('year_id');
        $semesterId = $request->get('semester_id');
        $levelId    = $request->get('level_id', '');

        if (!$yearId) {
            return redirect()->route('student-stat.index')->with('error', 'กรุณาเลือกปีการศึกษาก่อน export');
        }

        $year   = AcademicYear::find($yearId);
        $semester = $semesterId ? Semester::find($semesterId) : null;
        $levels = Level::orderBy('sort_order')->get();

        $grouped = $this->buildGrouped($yearId, $semesterId, $levelId, $levels);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('สถิตินักเรียน');

        $totalCols = 5; // A..E
        $hasLogo = ExcelSchoolHeader::apply($sheet, $totalCols, 'สถิตินักเรียน');

        $subtitle = 'ปีการศึกษา ' . ($year->year_name ?? '-') . ($semester ? ' เทอม ' . $semester->semester_name : ' ทุกเทอม');
        $sheet->mergeCells('A5:E5');
        $sheet->setCellValue('A5', $subtitle);
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headerRow = 6;
        $headers = ['ระดับชั้น', 'จำนวนห้องเรียน', 'ชาย', 'หญิง', 'รวมนักเรียน'];
        foreach ($headers as $i => $h) {
            $col = chr(ord('A') + $i);
            $sheet->setCellValue("{$col}{$headerRow}", $h);
        }
        $sheet->getStyle("A{$headerRow}:E{$headerRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF2F8']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        $row = $headerRow + 1;
        $grandRooms = 0; $grandMale = 0; $grandFemale = 0; $grandTotal = 0;

        foreach ($grouped as $groupName => $rows) {
            $subRooms = $rows->sum('rooms');
            $subMale = $rows->sum('male');
            $subFemale = $rows->sum('female');
            $subTotal = $rows->sum('total');

            foreach ($rows->sortBy('sort_order') as $r) {
                $sheet->setCellValue("A{$row}", $r['level_name']);
                $sheet->setCellValue("B{$row}", $r['rooms']);
                $sheet->setCellValue("C{$row}", $r['male']);
                $sheet->setCellValue("D{$row}", $r['female']);
                $sheet->setCellValue("E{$row}", $r['total']);
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E5E5']]],
                ]);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $row++;
            }

            $sheet->setCellValue("A{$row}", 'รวม');
            $sheet->setCellValue("B{$row}", $subRooms);
            $sheet->setCellValue("C{$row}", $subMale);
            $sheet->setCellValue("D{$row}", $subFemale);
            $sheet->setCellValue("E{$row}", $subTotal);
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '3B82F6']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E7FF']]],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $row++;

            $grandRooms += $subRooms;
            $grandMale += $subMale;
            $grandFemale += $subFemale;
            $grandTotal += $subTotal;
        }

        $sheet->setCellValue("A{$row}", 'รวมทั้งหมด');
        $sheet->setCellValue("B{$row}", $grandRooms);
        $sheet->setCellValue("C{$row}", $grandMale);
        $sheet->setCellValue("D{$row}", $grandFemale);
        $sheet->setCellValue("E{$row}", $grandTotal);
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        ExcelSchoolHeader::setColumnWidth($sheet, 'A', 22, $hasLogo);
        ExcelSchoolHeader::setColumnWidth($sheet, 'B', 16, $hasLogo);
        ExcelSchoolHeader::setColumnWidth($sheet, 'C', 12, $hasLogo);
        ExcelSchoolHeader::setColumnWidth($sheet, 'D', 12, $hasLogo);
        ExcelSchoolHeader::setColumnWidth($sheet, 'E', 14, $hasLogo);

        $filename = 'สถิตินักเรียน_' . ($year->year_name ?? '') . ($semester ? '_เทอม' . $semester->semester_name : '') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $tmpPath = tempnam(sys_get_temp_dir(), 'student_stat') . '.xlsx';
        $writer->save($tmpPath);

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    // สร้างข้อมูลสถิติ (จำนวนห้อง/ชาย/หญิง/รวม) แยกตามระดับชั้น จัดกลุ่มตาม level_group — ใช้ร่วมกันทั้งหน้าจอและ Excel
    private function buildGrouped($yearId, $semesterId, $levelId, $levels)
    {
        $stats = collect();

        $sectionQuery = ClassSection::real()->with('level')
            ->whereHas('semester', fn($q) => $q->where('year_id', $yearId))
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->when($levelId, fn($q) => $q->where('level_id', $levelId))
            ->get();

        $roomCount  = $sectionQuery->groupBy('level_id')->map(fn($g) => $g->count());
        $sectionIds = $sectionQuery->pluck('section_id');

        $studentStats = StudentSection::whereIn('student_sections.section_id', $sectionIds)
            ->join('students', 'student_sections.student_id', '=', 'students.student_id')
            ->join('class_sections', 'student_sections.section_id', '=', 'class_sections.section_id')
            ->select(
                'class_sections.level_id',
                DB::raw("SUM(CASE WHEN students.gender = 'M' THEN 1 ELSE 0 END) as male_count"),
                DB::raw("SUM(CASE WHEN students.gender = 'F' THEN 1 ELSE 0 END) as female_count"),
                DB::raw('COUNT(student_sections.id) as total')
            )
            ->groupBy('class_sections.level_id')
            ->get()
            ->keyBy('level_id');

        $levelMap = $levels->keyBy('level_id');

        foreach ($sectionQuery->pluck('level_id')->unique() as $lvId) {
            $lv  = $levelMap->get($lvId);
            $stu = $studentStats->get($lvId);
            $stats->push([
                'level_id'    => $lvId,
                'level_name'  => $lv?->name ?? '-',
                'level_group' => $lv?->level_group ?? 'อื่นๆ',
                'sort_order'  => $lv?->sort_order ?? 99,
                'rooms'       => $roomCount->get($lvId, 0),
                'male'        => $stu?->male_count ?? 0,
                'female'      => $stu?->female_count ?? 0,
                'total'       => $stu?->total ?? 0,
            ]);
        }

        return $stats->sortBy('sort_order')->groupBy('level_group');
    }
}