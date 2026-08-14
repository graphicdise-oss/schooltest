<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ปพ.3 - {{ $section->full_name }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'TH Sarabun PSK', 'TH Sarabun IT9', 'TH Sarabun New', 'Sarabun', sans-serif; font-size: 14px; background: #fff; }

.page {
    width: 297mm;
    height: 210mm;
    margin: 0 auto;
    background: #fff;
    position: relative;
    overflow: hidden;
}
/* ย่อเนื้อหาทั้งหน้าลงเหลือ 92% แล้วจัดกึ่งกลาง แทนการเว้น padding แต่ละด้านเอง
   วิธีนี้ทำให้ระยะห่างจากขอบกระดาษเท่ากันทุกด้านโดยอัตโนมัติ ปรับแค่ตัวเลขเดียว (scale) ก็พอ */
.page-inner {
    width: 100%;
    height: 100%;
    padding: 3mm 3mm 20mm;
    display: flex;
    flex-direction: column;
    transform: scale(0.92);
    transform-origin: center center;
}

/* Header */
.doc-header { margin-bottom: 2px; flex-shrink: 0; font-size: 16px; }
.doc-header .fill { font-weight: 700; border-bottom: 0.5px dotted #333; padding: 0 4px; }

/* Main table — flex-grow to fill remaining height */
.table-wrap { display: flex; flex-direction: column; }
.main-table {
    width: 100%; border-collapse: collapse; font-size: 14px;
    margin-top: 2px; table-layout: fixed;
}
.main-table th, .main-table td {
    border: 0.5px solid #333;
    padding: 1px 3px;
    vertical-align: middle;
    text-align: center;
    overflow: hidden;
}
.main-table thead th { background: #fff; font-size: 16px; font-weight: 700; line-height: 1; }

.main-table td.left { text-align: left; }
.main-table tbody tr { height: 13px; max-height: 13px; }
.main-table tbody td { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-height: 13px; }
.main-table tr.row-top td { border-bottom: 0.5px dotted #aaa; padding-top: 1px; padding-bottom: 0; }
.main-table tr.row-bot td { border-top: none; border-bottom: 0.5px dotted #aaa; padding-top: 0; padding-bottom: 1px; }
/* แถวสุดท้ายของตารางแต่ละหน้า ปิดด้วยเส้นทึบแทนเส้นประ (คอลัมน์ rowspan="2" ที่เป็น .no-dot
   ได้เส้นทึบอยู่แล้วจากกฎด้านล่าง จึงไม่ปิดทั้งแถวบนของคู่ ไม่งั้นเส้นประกลางระเบียนสุดท้ายจะหาย
   ส่วนคอลัมน์ rowspan="2" อื่น (.rs-plain เช่น ลำดับที่/จำนวนหน่วยกิต) ไม่มีเส้นประกลาง ต้องปิดด้วย) */
.main-table tbody tr:last-child td {
    border-bottom: 0.5px solid #333 !important;
}
.main-table tbody tr:nth-last-child(2) td.rs-plain {
    border-bottom: 0.5px solid #333 !important;
}
.main-table td.no-dot,
.main-table tr.row-top td.no-dot,
.main-table tr.row-bot td.no-dot {
    border-top: 0.5px solid #333 !important;
    border-bottom: 0.5px solid #333 !important;
}

/* Footer */
.footer { flex-shrink: 0; margin-top: 8px; }
.count-table { border-collapse: collapse; font-size: 14px; }
.count-table th, .count-table td {
    border: 0.5px solid #333; padding: 2px 10px; text-align: center;
}
.count-table th { font-weight: 700; }

@page { size: A4 landscape; margin: 0; }
@media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .page {
        width: 297mm; height: 210mm;
        page-break-after: always;
    }
    .page:last-child { page-break-after: avoid; }
    .no-print { display: none !important; }
}
</style>
</head>
<body>

@php
// พิมพ์แบบสองหน้า (duplex): หน้าคี่ = ด้านหน้าแผ่น (หัวเต็ม + ท้ายกระดาษ, จุได้ 10 แถว)
//                            หน้าคู่ = ด้านหลังแผ่น (หัวย่อ ไม่มีท้ายกระดาษ, จุได้ 12 แถว)
// ตามฟอร์มทางการ ปพ.3 หน้าแรกของแต่ละแผ่นกระดาษต้องมีหัวโรงเรียน+ลงนามครบ ส่วนหน้าหลังมีแค่หัวย่อ
$FRONT_ROWS = 10;
$BACK_ROWS  = 12;

$yearName  = $section->semester->academicYear->year_name ?? '';
$termName  = $section->semester->semester_name ?? '';
$school    = $school ?? config('school');

$thaiMonths = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];

$formatBirthDay = function($date) use ($thaiMonths) {
    if (!$date) return ['', ''];
    try {
        $d = \Carbon\Carbon::parse($date);
        return [$d->day . ' ' . $thaiMonths[$d->month], $d->year + 543];
    } catch (\Exception $e) { return ['', '']; }
};

// จัดนักเรียนลงหน้าหน้า-หน้าหลังสลับกันไปเรื่อย ๆ จนหมด
$allRows = $studentSections->values();
$total = $allRows->count();

$pages = collect();
$offset = 0;
while ($offset < $total) {
    $take = min($FRONT_ROWS, $total - $offset);
    $pages->push(['type' => 'front', 'rows' => $allRows->slice($offset, $take)->values(), 'startNum' => $offset]);
    $offset += $take;

    if ($offset >= $total) break;

    $take = min($BACK_ROWS, $total - $offset);
    $pages->push(['type' => 'back', 'rows' => $allRows->slice($offset, $take)->values(), 'startNum' => $offset]);
    $offset += $take;
}

if ($pages->isEmpty()) {
    $pages->push(['type' => 'front', 'rows' => collect(), 'startNum' => 0]);
}

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

@foreach($pages as $pageIdx => $page)
<div class="page">
<div class="page-inner">

    {{-- หัวเอกสาร: หน้าหน้า (คี่) = หัวเต็ม, หน้าหลัง (คู่) = หัวย่อ --}}
    @if($page['type'] === 'front')
    <div class="doc-header">
        <div style="display:flex;align-items:baseline;text-align:left;white-space:nowrap;">
            <div>สำเร็จการศึกษาภาคเรียนที่ <span class="fill">{{ $termName }}</span> &nbsp; ปีการศึกษา <span class="fill">{{ $yearName }}</span> &nbsp; โรงเรียน <span class="fill">{{ $school['name'] ?? '' }}</span></div>
            <div style="margin-left:auto;padding-left:8px;font-size:16px;">หน้า {{ $pageIdx + 1 }}</div>
        </div>
        <div style="text-align:left;white-space:nowrap;font-size:16px;">
            ตำบล/แขวง <span class="fill">{{ $school['tambon'] ?? '' }}</span>
            &nbsp; อำเภอ/เขต <span class="fill">{{ $school['amphoe'] ?? '' }}</span>
            &nbsp; จังหวัด <span class="fill">{{ $school['changwat'] ?? '' }}</span>
            &nbsp; สำนักงานเขตพื้นที่การศึกษา <span class="fill">{{ $school['education_area'] ?? '' }}</span>
        </div>
    </div>
    @else
    <div class="doc-header">
        <div style="display:flex;align-items:baseline;text-align:left;white-space:nowrap;">
            <div>แบบรายงานผู้สำเร็จการศึกษา ภาคเรียนที่ <span class="fill">{{ $termName }}</span> &nbsp; ปีการศึกษา <span class="fill">{{ $yearName }}</span></div>
            <div style="margin-left:auto;padding-left:8px;font-size:16px;">หน้า {{ $pageIdx + 1 }}</div>
        </div>
    </div>
    @endif

    {{-- ตารางนักเรียน --}}
    <div class="table-wrap">
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" style="width:26px;">ลำดับ<br>ที่</th>
                <th class="th-top" style="width:78px;">เลขประจำตัวนักเรียน</th>
                <th class="th-top" style="width:32px;">ชุดที่ ปพ.1:พ</th>
                <th rowspan="2" style="width:22px;">เลขที่<br>ปพ.2:พ</th>
                <th class="th-top" style="width:90px;">ชื่อนักเรียน</th>
                <th class="th-top" style="width:64px;">วัน เดือน</th>
                <th class="th-top" style="width:108px;">ชื่อ-ชื่อสกุลบิดา</th>
                <th class="th-top" style="width:54px;">จำนวนหน่วยกิต<br>รายวิชาที่เรียน/ที่ได้</th>
                <th rowspan="2" style="width:46px;">ผลการประเมิน<br>การอ่าน<br>คิดวิเคราะห์<br>และเขียน</th>
                <th rowspan="2" style="width:46px;">ผลการประเมิน<br>คุณลักษณะ<br>อันพึง<br>ประสงค์</th>
                <th rowspan="2" style="width:46px;">ผลการประเมิน<br>กิจกรรม<br>พัฒนา<br>ผู้เรียน</th>
                <th rowspan="2" style="width:38px;">หมาย<br>เหตุ</th>
            </tr>
            <tr>
                <th class="th-bot" style="font-size:16px;">เลขประจำตัวประชาชน</th>
                <th class="th-bot" style="font-size:16px;">เลขที่ ปพ.1:พ</th>
                <th class="th-bot">ชื่อสกุลนักเรียน</th>
                <th class="th-bot">ปีเกิด</th>
                <th class="th-bot">ชื่อ-ชื่อสกุลมารดา</th>
                <th class="th-bot">ผลการเรียนเฉลี่ย<br>ตลอดหลักสูตร</th>
            </tr>
        </thead>
        <tbody>
            @foreach($page['rows'] as $i => $ss)
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
                $rowNum = $page['startNum'] + $i + 1;
            @endphp
            <tr class="row-top">
                <td rowspan="2" class="rs-plain" style="font-size:14px;font-weight:600;">{{ $rowNum }}</td>
                <td style="font-size:14px;">{{ $stu?->student_code }}</td>
                <td style="font-size:14px;">{{ $doc?->doc_set ?? '' }}</td>
                <td rowspan="2" class="rs-plain" style="font-size:14px;font-weight:600;">{{ $rowNum }}</td>
                <td style="font-size:14px;">{{ $stu?->thai_prefix }}{{ $stu?->thai_firstname }}</td>
                <td style="font-size:14px;">{{ $birthDayMonth }}</td>
                <td style="font-size:14px;">{{ $fatherName }}</td>
                <td rowspan="2" class="rs-plain" style="font-size:14px;">{{ $credits }}/{{ $credits }}<br><span style="font-size:14px;">{{ $gpa }}</span></td>
                <td rowspan="2" class="no-dot"></td>
                <td rowspan="2" class="no-dot"></td>
                <td rowspan="2" class="no-dot"></td>
                <td></td>
            </tr>
            <tr class="row-bot">
                <td style="font-size:14px;color:#555;border-top:0.5px dotted #999;">{{ $stu?->id_card_number }}</td>
                <td style="font-size:14px;">{{ $doc?->doc_number ?? '' }}@if($pp2?->doc_number) / {{ $pp2->doc_number }}@endif</td>
                <td style="font-size:14px;">{{ $stu?->thai_lastname }}</td>
                <td style="font-size:14px;">{{ $birthYear }}</td>
                <td style="font-size:14px;">{{ $motherName }}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    {{-- Footer — เฉพาะหน้าหน้า (ด้านหน้าแผ่นกระดาษ) --}}
    @if($page['type'] === 'front')
    <div class="footer">
        <div style="display:flex;align-items:flex-start;">
            <div style="flex:1;text-align:center;">
                <div style="font-size:14px;font-weight:700;margin-bottom:3px;">จำนวนผู้สำเร็จการศึกษา</div>
                <table class="count-table" style="margin:0 auto;">
                    <tr><th>ชาย</th><th>หญิง</th><th>รวม</th></tr>
                    <tr>
                        <td>{{ $maleCount }}</td>
                        <td>{{ $femaleCount }}</td>
                        <td>{{ $maleCount + $femaleCount }}</td>
                    </tr>
                </table>
            </div>
            <div style="flex:1;font-size:14px;text-align:center;">
                <div style="width:280px;margin:0 auto 2px;text-align:left;white-space:nowrap;"><span style="display:inline-block;border-bottom:0.5px dotted #333;width:190px;">&nbsp;</span>ผู้เขียน/ผู้พิมพ์</div>
                <div style="width:280px;margin:0 auto 2px;text-align:left;white-space:nowrap;"><span style="display:inline-block;border-bottom:0.5px dotted #333;width:190px;">&nbsp;</span>ผู้ทาน</div>
                <div style="width:280px;margin:0 auto 2px;text-align:left;white-space:nowrap;"><span style="display:inline-block;border-bottom:0.5px dotted #333;width:190px;">&nbsp;</span>ผู้ตรวจ</div>
                <div style="width:280px;margin:0 auto;text-align:left;white-space:nowrap;"><span style="display:inline-block;border-bottom:0.5px dotted #333;width:190px;">&nbsp;</span>นายทะเบียน</div>
            </div>
            <div style="flex:1;text-align:center;font-size:14px;">
                <div style="font-weight:700;margin-bottom:2px;">อนุมัติการจบหลักสูตร</div>
                <div style="margin-bottom:2px;">(
                    <span style="border-bottom:0.5px dotted #333;min-width:150px;display:inline-block;padding:0 6px;">{{ $approverName }}</span>
                )</div>
                <div style="font-size:14px;">{{ $approverPos }}</div>
                <div style="margin-top:4px;font-size:14px;">วันที่ <span style="border-bottom:0.5px dotted #333;min-width:110px;display:inline-block;">{{ $approveDateFormatted }}</span></div>
            </div>
        </div>
    </div>
    @endif

</div>
</div>
@endforeach

</body>
</html>
