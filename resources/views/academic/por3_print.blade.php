<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ปพ.3 - {{ $section->level->name ?? '' }}/{{ $section->section_number }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'TH Sarabun New', 'Sarabun', sans-serif; font-size: 13px; background: #fff; }

.page {
    width: 297mm; min-height: 210mm;
    margin: 0 auto; padding: 8mm 10mm;
    background: #fff;
}

/* Header */
.doc-header { margin-bottom: 4px; }
.doc-header table { width: 100%; border-collapse: collapse; }
.doc-header td { padding: 0 4px; font-size: 13px; }

/* Main table */
.main-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 6px; }
.main-table th, .main-table td {
    border: 0.5px solid #333;
    padding: 2px 3px;
    vertical-align: middle;
    text-align: center;
}
.main-table thead th { background: #fff; font-size: 10px; font-weight: 700; }
.main-table .th-top { border-bottom: none; }
.main-table .th-bot { border-top: none; }
.main-table td.left { text-align: left; }
.main-table td.dotted { border-bottom: 0.5px dotted #555 !important; border-top: none !important; border-left: none !important; border-right: none !important; }
.main-table tr.row-top td { border-bottom: none; padding-bottom: 0; }
.main-table tr.row-bot td { border-top: none; padding-top: 0; }

/* Footer */
.footer { margin-top: 8px; }
.count-table { border-collapse: collapse; font-size: 12px; }
.count-table th, .count-table td {
    border: 0.5px solid #333; padding: 3px 10px; text-align: center;
}
.count-table th { font-weight: 700; }

.sign-area { display: flex; justify-content: space-between; margin-top: 6px; }
.sign-col { flex: 1; font-size: 12px; }
.sign-line { border-bottom: 0.5px solid #333; width: 180px; margin: 0 auto 3px; }
.sign-label { text-align: center; font-size: 11px; }

.approver-box { text-align: center; font-size: 12px; flex: 1; }
.approver-name { border-bottom: 0.5px solid #333; min-width: 200px; display: inline-block; padding: 0 20px; margin-bottom: 3px; font-size: 12px; }

@page { size: A4 landscape; margin: 0; }
@media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .page { width: 100%; padding: 6mm 8mm; page-break-after: always; }
    .page:last-child { page-break-after: avoid; }
    .no-print { display: none !important; }
}
</style>
</head>
<body>

@php
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

// แบ่งนักเรียนเป็นหน้าๆ ละ 20 คน
$chunks = $studentSections->chunk(20);
$totalStudents = $studentSections->count();
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

@foreach($chunks as $pageIdx => $chunk)
<div class="page">

    {{-- หัวเอกสาร --}}
    <div class="doc-header">
        <table>
            <tr>
                <td>สำเร็จการศึกษาภาคเรียนที่ <strong>{{ $termName }}</strong> &nbsp; ปีการศึกษา <strong>{{ $yearName }}</strong> &nbsp; โรงเรียน <strong>{{ $school['name'] ?? '' }}</strong></td>
                <td style="text-align:right;white-space:nowrap;">หน้า {{ $pageIdx + 1 }}</td>
            </tr>
            <tr>
                <td colspan="2">
                    ตำบล/แขวง <strong>{{ $school['tambon'] ?? '' }}</strong>
                    &nbsp; อำเภอ/เขต <strong>{{ $school['amphoe'] ?? '' }}</strong>
                    &nbsp; จังหวัด <strong>{{ $school['changwat'] ?? '' }}</strong>
                    &nbsp; สำนักงานเขตพื้นที่การศึกษา <strong>{{ $school['education_area'] ?? '' }}</strong>
                </td>
            </tr>
        </table>
        <div style="text-align:center;font-size:14px;font-weight:700;margin:4px 0 2px;">
            ทะเบียนรายงานผู้สำเร็จการศึกษา ชั้น {{ $levelName }}/{{ $secNum }}
        </div>
    </div>

    {{-- ตารางนักเรียน --}}
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="3" style="width:28px;">ลำดับ<br>ที่</th>
                <th colspan="1" class="th-top" style="width:70px;">เลขประจำตัวนักเรียน</th>
                <th style="width:50px;">ชุดที่ ปพ.1:พ</th>
                <th rowspan="3" style="width:22px;">เลข<br>ที่</th>
                <th class="th-top" style="width:90px;">ชื่อนักเรียน</th>
                <th class="th-top" style="width:60px;">วัน เดือน</th>
                <th class="th-top" style="width:110px;">ชื่อ-ชื่อสกุลบิดา</th>
                <th rowspan="3" style="width:55px;">จำนวนหน่วยกิต<br>รายวิชาที่เรียน/ที่ได้<br>ตลอดหลักสูตร</th>
                <th rowspan="3" style="width:48px;">ผลการประเมิน<br>การอ่าน<br>คิดวิเคราะห์<br>และเขียน</th>
                <th rowspan="3" style="width:48px;">ผลการประเมิน<br>คุณลักษณะ<br>อันพึง<br>ประสงค์</th>
                <th rowspan="3" style="width:48px;">ผลการประเมิน<br>กิจกรรม<br>พัฒนา<br>ผู้เรียน</th>
                <th rowspan="3" style="width:40px;">หมาย<br>เหตุ</th>
            </tr>
            <tr>
                <th class="th-bot" style="font-size:9px;">เลขประจำตัวประชาชน</th>
                <th style="font-size:9px;">เลขที่ ปพ.1:พ</th>
                <th class="th-bot" style="width:90px;">ชื่อสกุลนักเรียน</th>
                <th class="th-bot" style="width:60px;">ปีเกิด</th>
                <th class="th-bot" style="width:110px;">ชื่อ-ชื่อสกุลมารดา</th>
            </tr>
            <tr>
                <th style="font-size:9px;border-top:none;"></th>
                <th style="font-size:9px;">เลขที่ ปพ.2:พ</th>
                <th colspan="3" style="border-top:none;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($chunk as $i => $ss)
            @php
                $stu    = $ss->student;
                $sid    = $stu?->student_id;
                $doc    = $docNumbers[$sid] ?? null;
                $pp2    = $pp2Docs[$sid] ?? null;
                $credits = number_format($creditsByStudent[$sid] ?? 0, 1);
                $gpa     = number_format($gpaTotalByStudent[$sid] ?? 0, 2);

                $father = $stu?->families->first(fn($f) => in_array($f->guardian_type ?? $f->family_type ?? '', ['บิดา','พ่อ']));
                $mother = $stu?->families->first(fn($f) => in_array($f->guardian_type ?? $f->family_type ?? '', ['มารดา','แม่']));

                $fatherName = $father ? trim(($father->prefix_th ?? '') . ($father->first_name_th ?? '') . ' ' . ($father->last_name_th ?? '')) : '';
                $motherName = $mother ? trim(($mother->prefix_th ?? '') . ($mother->first_name_th ?? '') . ' ' . ($mother->last_name_th ?? '')) : '';

                [$birthDayMonth, $birthYear] = $formatBirthDay($stu?->date_of_birth);
                $rowNum = ($pageIdx * 20) + $i + 1;
            @endphp
            {{-- แถวบน --}}
            <tr class="row-top">
                <td rowspan="2" style="font-size:12px;font-weight:600;">{{ $rowNum }}</td>
                <td style="font-size:11px;">{{ $stu?->student_code }}</td>
                <td style="font-size:11px;">{{ $doc?->doc_set ?? '' }}</td>
                <td rowspan="2" style="font-size:12px;font-weight:600;">{{ $rowNum }}</td>
                <td class="left" style="font-size:11px;">{{ $stu?->thai_prefix }}{{ $stu?->thai_firstname }}</td>
                <td style="font-size:11px;">{{ $birthDayMonth }}</td>
                <td class="left" style="font-size:11px;">{{ $fatherName }}</td>
                <td rowspan="2">{{ $credits }}/{{ $credits }}</td>
                <td rowspan="2"></td>
                <td rowspan="2"></td>
                <td rowspan="2"></td>
                <td rowspan="2"></td>
            </tr>
            {{-- แถวล่าง --}}
            <tr class="row-bot">
                <td style="font-size:10px;color:#555;border-top:0.5px dotted #999;">{{ $stu?->id_card_number }}</td>
                <td style="font-size:10px;">
                    {{ $doc?->doc_number ?? '' }}
                    @if($pp2?->doc_number) / {{ $pp2->doc_number }} @endif
                </td>
                <td class="left" style="font-size:11px;">{{ $stu?->thai_lastname }}</td>
                <td style="font-size:11px;">{{ $birthYear }}</td>
                <td class="left" style="font-size:11px;">{{ $motherName }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer --}}
    @if($loop->last)
    <div class="footer">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:20px;margin-top:8px;">
            {{-- จำนวนผู้สำเร็จ --}}
            <div>
                <div style="font-size:12px;font-weight:700;margin-bottom:4px;">จำนวนผู้สำเร็จการศึกษา</div>
                <table class="count-table">
                    <tr><th>ชาย</th><th>หญิง</th><th>รวม</th></tr>
                    <tr>
                        <td>{{ $maleCount }}</td>
                        <td>{{ $femaleCount }}</td>
                        <td>{{ $maleCount + $femaleCount }}</td>
                    </tr>
                </table>
            </div>

            {{-- ลายเซ็น --}}
            <div style="flex:1;font-size:12px;">
                <div style="margin-bottom:6px;">
                    <span style="display:inline-block;border-bottom:0.5px solid #333;min-width:200px;">&nbsp;</span>ผู้เขียน/ผู้พิมพ์
                </div>
                <div style="margin-bottom:6px;">
                    <span style="display:inline-block;border-bottom:0.5px solid #333;min-width:200px;">&nbsp;</span>ผู้ทาน
                </div>
                <div style="margin-bottom:6px;">
                    <span style="display:inline-block;border-bottom:0.5px solid #333;min-width:200px;">&nbsp;</span>ผู้ตรวจ
                </div>
                <div>
                    <span style="display:inline-block;border-bottom:0.5px solid #333;min-width:200px;">&nbsp;</span>นายทะเบียน
                </div>
            </div>

            {{-- อนุมัติการจบหลักสูตร --}}
            <div style="text-align:center;font-size:12px;min-width:200px;">
                <div style="font-weight:700;margin-bottom:6px;">อนุมัติการจบหลักสูตร</div>
                <div style="margin-bottom:3px;">(
                    <span style="border-bottom:0.5px solid #333;min-width:160px;display:inline-block;padding:0 8px;">
                        {{ $approverName }}
                    </span>
                )</div>
                <div style="font-size:11px;">{{ $approverPos }}</div>
                <div style="margin-top:6px;font-size:11px;">
                    วันที่ <span style="border-bottom:0.5px solid #333;min-width:120px;display:inline-block;">{{ $approveDateFormatted }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endforeach

</body>
</html>
