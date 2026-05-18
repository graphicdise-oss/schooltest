<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ปพ.3 - {{ $section->level->name ?? '' }}/{{ $section->section_number }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'TH Sarabun New', 'Sarabun', sans-serif; font-size: 13px; background: #fff; }

.page {
    width: 297mm;
    height: 210mm;
    margin: 0 auto;
    padding: 7mm 9mm;
    background: #fff;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* Header */
.doc-header { margin-bottom: 3px; flex-shrink: 0; }
.doc-header table { width: 100%; border-collapse: collapse; }
.doc-header td { padding: 0 3px; font-size: 12.5px; }

/* Main table — flex-grow to fill remaining height */
.table-wrap { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.main-table {
    width: 100%; border-collapse: collapse; font-size: 11px;
    margin-top: 4px; table-layout: fixed;
    flex: 1;
}
.main-table th, .main-table td {
    border: 0.5px solid #333;
    padding: 1px 3px;
    vertical-align: middle;
    text-align: center;
    overflow: hidden;
}
.main-table thead th { background: #fff; font-size: 9.5px; font-weight: 700; line-height: 1.2; }
.main-table .th-top { border-bottom: none; }
.main-table .th-bot { border-top: none; }
.main-table td.left { text-align: left; }
.main-table tbody tr { height: 14px; max-height: 14px; }
.main-table tbody td { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-height: 14px; }
.main-table tr.row-top td { border-bottom: 0.5px dotted #aaa; padding-top: 1px; padding-bottom: 0; }
.main-table tr.row-bot td { border-top: none; border-bottom: 0.5px dotted #aaa; padding-top: 0; padding-bottom: 1px; }
.main-table tr.empty-top td { border-bottom: 0.5px dotted #aaa; }
.main-table tr.empty-bot td { border-top: none; border-bottom: 0.5px dotted #aaa; }
.main-table td.no-dot,
.main-table tr.row-top td.no-dot,
.main-table tr.row-bot td.no-dot {
    border-top: 0.5px solid #333 !important;
    border-bottom: 0.5px solid #333 !important;
}
.main-table tr.empty-top td.no-dot {
    border-top: 0.5px solid #333 !important;
    border-bottom: none !important;
}
.main-table tr.empty-bot td.no-dot {
    border-top: none !important;
    border-bottom: 0.5px solid #333 !important;
}

/* Footer */
.footer { flex-shrink: 0; margin-top: 5px; }
.count-table { border-collapse: collapse; font-size: 11.5px; }
.count-table th, .count-table td {
    border: 0.5px solid #333; padding: 2px 10px; text-align: center;
}
.count-table th { font-weight: 700; }

@page { size: A4 landscape; margin: 0; }
@media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .page {
        width: 297mm; height: 210mm;
        padding: 6mm 8mm;
        page-break-after: always;
        page-break-inside: avoid;
    }
    .page:last-child { page-break-after: avoid; }
    .no-print { display: none !important; }
}
</style>
</head>
<body>

@php
$ROWS_PER_PAGE = 20;

$yearName  = $section->semester->academicYear->year_name ?? '';
$termName  = $section->semester->semester_name ?? '';
$levelName = $section->level->name ?? '';
$secNum    = $section->section_number;
$school    = $school ?? config('school');

$thaiMonths = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];

$formatBirthDay = function($date) use ($thaiMonths) {
    if (!$date) return ['', ''];
    try {
        $d = \Carbon\Carbon::parse($date);
        return [$d->day . ' ' . $thaiMonths[$d->month], $d->year + 543];
    } catch (\Exception $e) { return ['', '']; }
};

// แบ่งหน้า แล้วแพดแต่ละหน้าให้ครบ ROWS_PER_PAGE
$rawChunks = $studentSections->chunk($ROWS_PER_PAGE)->values();
$pages = $rawChunks->map(function($chunk) use ($ROWS_PER_PAGE) {
    $arr = $chunk->values()->all();
    while (count($arr) < $ROWS_PER_PAGE) {
        $arr[] = null; // empty row
    }
    return $arr;
});

$approverName = $approver
    ? trim(($approver->thai_prefix ?? '') . $approver->thai_firstname . ' ' . $approver->thai_lastname)
    : '';
$approverPos = $approver?->position ?? 'ผู้อำนวยการ/อาจารย์ใหญ่/ครูใหญ่';
@endphp

<div class="no-print" style="text-align:center;padding:12px;background:#f5f5f5;">
    <button onclick="window.print()" style="background:#43a047;color:#fff;border:none;padding:10px 28px;border-radius:6px;font-size:15px;cursor:pointer;font-family:inherit;">
        🖨️ พิมพ์เอกสาร
    </button>
    <button onclick="window.close()" style="background:#666;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-size:15px;cursor:pointer;font-family:inherit;margin-left:8px;">
        ✕ ปิด
    </button>
</div>

@foreach($pages as $pageIdx => $rows)
<div class="page">

    {{-- หัวเอกสาร --}}
    <div class="doc-header">
        <table>
            <tr>
                <td>สำเร็จการศึกษาภาคเรียนที่ <strong>{{ $termName }}</strong> &nbsp; ปีการศึกษา <strong>{{ $yearName }}</strong> &nbsp; โรงเรียน <strong>{{ $school['name'] ?? '' }}</strong></td>
                <td style="text-align:right;white-space:nowrap;font-size:12px;">หน้า {{ $pageIdx + 1 }}</td>
            </tr>
            <tr>
                <td colspan="2" style="font-size:12px;">
                    ตำบล/แขวง <strong>{{ $school['tambon'] ?? '' }}</strong>
                    &nbsp; อำเภอ/เขต <strong>{{ $school['amphoe'] ?? '' }}</strong>
                    &nbsp; จังหวัด <strong>{{ $school['changwat'] ?? '' }}</strong>
                    &nbsp; สำนักงานเขตพื้นที่การศึกษา <strong>{{ $school['education_area'] ?? '' }}</strong>
                </td>
            </tr>
        </table>
        <div style="text-align:center;font-size:13px;font-weight:700;margin:2px 0 1px;">
            ทะเบียนรายงานผู้สำเร็จการศึกษา ชั้น {{ $levelName }}/{{ $secNum }}
        </div>
    </div>

    {{-- ตารางนักเรียน --}}
    <div class="table-wrap">
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="3" style="width:26px;">ลำดับ<br>ที่</th>
                <th class="th-top" style="width:68px;">เลขประจำตัวนักเรียน</th>
                <th style="width:48px;">ชุดที่ ปพ.1:พ</th>
                <th rowspan="3" style="width:22px;">เลข<br>ที่</th>
                <th class="th-top" style="width:90px;">ชื่อนักเรียน</th>
                <th class="th-top" style="width:58px;">วัน เดือน</th>
                <th class="th-top" style="width:108px;">ชื่อ-ชื่อสกุลบิดา</th>
                <th rowspan="3" style="width:54px;">จำนวนหน่วยกิต<br>รายวิชาที่เรียน/ที่ได้<br>ตลอดหลักสูตร</th>
                <th rowspan="3" style="width:46px;">ผลการประเมิน<br>การอ่าน<br>คิดวิเคราะห์<br>และเขียน</th>
                <th rowspan="3" style="width:46px;">ผลการประเมิน<br>คุณลักษณะ<br>อันพึง<br>ประสงค์</th>
                <th rowspan="3" style="width:46px;">ผลการประเมิน<br>กิจกรรม<br>พัฒนา<br>ผู้เรียน</th>
                <th rowspan="3" style="width:38px;">หมาย<br>เหตุ</th>
            </tr>
            <tr>
                <th class="th-bot" style="font-size:8.5px;">เลขประจำตัวประชาชน</th>
                <th style="font-size:8.5px;">เลขที่ ปพ.1:พ</th>
                <th class="th-bot">ชื่อสกุลนักเรียน</th>
                <th class="th-bot">ปีเกิด</th>
                <th class="th-bot">ชื่อ-ชื่อสกุลมารดา</th>
            </tr>
            <tr>
                <th style="font-size:8.5px;border-top:none;"></th>
                <th style="font-size:8.5px;">เลขที่ ปพ.2:พ</th>
                <th colspan="3" style="border-top:none;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $ss)
            @if($ss !== null)
            @php
                $stu     = $ss->student;
                $sid     = $stu?->student_id;
                $doc     = $docNumbers[$sid] ?? null;
                $pp2     = $pp2Docs[$sid] ?? null;
                $credits = number_format($creditsByStudent[$sid] ?? 0, 1);
                $gpa     = number_format($gpaTotalByStudent[$sid] ?? 0, 2);

                $father = $stu?->families->first(fn($f) => in_array($f->guardian_type ?? $f->family_type ?? '', ['บิดา','พ่อ']));
                $mother = $stu?->families->first(fn($f) => in_array($f->guardian_type ?? $f->family_type ?? '', ['มารดา','แม่']));

                $fatherName = $father ? trim(($father->prefix_th ?? '').' '.($father->first_name_th ?? '').' '.($father->last_name_th ?? '')) : '';
                $motherName = $mother ? trim(($mother->prefix_th ?? '').' '.($mother->first_name_th ?? '').' '.($mother->last_name_th ?? '')) : '';

                [$birthDayMonth, $birthYear] = $formatBirthDay($stu?->date_of_birth);
                $rowNum = ($pageIdx * $ROWS_PER_PAGE) + $i + 1;
            @endphp
            <tr class="row-top">
                <td rowspan="2" style="font-size:12px;font-weight:600;">{{ $rowNum }}</td>
                <td style="font-size:11px;">{{ $stu?->student_code }}</td>
                <td style="font-size:11px;">{{ $doc?->doc_set ?? '' }}</td>
                <td rowspan="2" style="font-size:12px;font-weight:600;">{{ $rowNum }}</td>
                <td class="left" style="font-size:11px;">{{ $stu?->thai_prefix }}{{ $stu?->thai_firstname }}</td>
                <td style="font-size:11px;">{{ $birthDayMonth }}</td>
                <td class="left" style="font-size:11px;">{{ $fatherName }}</td>
                <td rowspan="2" style="font-size:11px;">{{ $credits }}/{{ $credits }}<br><span style="font-size:10px;">{{ $gpa }}</span></td>
                <td rowspan="2"></td>
                <td rowspan="2" class="no-dot"></td>
                <td rowspan="2" class="no-dot"></td>
                <td rowspan="2"></td>
            </tr>
            <tr class="row-bot">
                <td style="font-size:10px;color:#555;border-top:0.5px dotted #999;">{{ $stu?->id_card_number }}</td>
                <td style="font-size:10px;">{{ $doc?->doc_number ?? '' }}@if($pp2?->doc_number) / {{ $pp2->doc_number }}@endif</td>
                <td class="left" style="font-size:11px;">{{ $stu?->thai_lastname }}</td>
                <td style="font-size:11px;">{{ $birthYear }}</td>
                <td class="left" style="font-size:11px;">{{ $motherName }}</td>
            </tr>
            @else
            {{-- แถวว่าง --}}
            <tr class="empty-top">
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td><td class="no-dot"></td><td class="no-dot"></td><td></td>
            </tr>
            <tr class="empty-bot">
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td><td class="no-dot"></td><td class="no-dot"></td><td></td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
    </div>

    {{-- Footer — หน้าสุดท้ายเท่านั้น --}}
    @if($loop->last)
    <div class="footer">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;">
            <div>
                <div style="font-size:11.5px;font-weight:700;margin-bottom:3px;">จำนวนผู้สำเร็จการศึกษา</div>
                <table class="count-table">
                    <tr><th>ชาย</th><th>หญิง</th><th>รวม</th></tr>
                    <tr>
                        <td>{{ $maleCount }}</td>
                        <td>{{ $femaleCount }}</td>
                        <td>{{ $maleCount + $femaleCount }}</td>
                    </tr>
                </table>
            </div>
            <div style="flex:1;font-size:11.5px;">
                <div style="margin-bottom:4px;"><span style="display:inline-block;border-bottom:0.5px solid #333;min-width:190px;">&nbsp;</span>ผู้เขียน/ผู้พิมพ์</div>
                <div style="margin-bottom:4px;"><span style="display:inline-block;border-bottom:0.5px solid #333;min-width:190px;">&nbsp;</span>ผู้ทาน</div>
                <div style="margin-bottom:4px;"><span style="display:inline-block;border-bottom:0.5px solid #333;min-width:190px;">&nbsp;</span>ผู้ตรวจ</div>
                <div><span style="display:inline-block;border-bottom:0.5px solid #333;min-width:190px;">&nbsp;</span>นายทะเบียน</div>
            </div>
            <div style="text-align:center;font-size:11.5px;min-width:190px;">
                <div style="font-weight:700;margin-bottom:4px;">อนุมัติการจบหลักสูตร</div>
                <div style="margin-bottom:2px;">(
                    <span style="border-bottom:0.5px solid #333;min-width:150px;display:inline-block;padding:0 6px;">{{ $approverName }}</span>
                )</div>
                <div style="font-size:10.5px;">{{ $approverPos }}</div>
                <div style="margin-top:4px;font-size:10.5px;">วันที่ <span style="border-bottom:0.5px solid #333;min-width:110px;display:inline-block;">{{ $approveDateFormatted }}</span></div>
            </div>
        </div>
    </div>
    @endif

</div>
@endforeach

</body>
</html>
