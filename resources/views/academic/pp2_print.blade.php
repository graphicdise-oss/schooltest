<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ป.พ.2 - {{ $studentSection->student?->thai_firstname }} {{ $studentSection->student?->thai_lastname }}</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<style>
@font-face {
    font-family: TFArluck;
    src: url('/Fonts/TF-Arluck.woff') format('woff');
}
@font-face {
    font-family: THDanViVek;
    src: url('/Fonts/TH_Dan_Vi_Vek_ver_1.03.woff') format('woff');
}

* { box-sizing: border-box; }
body {
    font-family: TFArluck, 'Sarabun', sans-serif;
    font-size: 14px;
    margin: 0; padding: 0;
    background: #fff;
}

@page {
    size: 21cm 14.5cm;
    margin: 0;
}

@media print {
    html, body { width: 21cm; height: 14.5cm; }
    .no-print { display: none !important; }
    .page-break { page-break-after: always; }
}

.font-tfarluck { font-family: TFArluck, 'Sarabun', sans-serif; }
.font-thdanvivek { font-family: THDanViVek, 'Sarabun', sans-serif; }

.pp2-page {
    width: 21cm;
    min-height: 14.5cm;
    padding: 0.4cm 0.6cm;
    position: relative;
    overflow: hidden;
}

.title-line {
    text-align: center;
    font-family: TFArluck, 'Sarabun', sans-serif;
    font-size: 18px;
    font-weight: bold;
    line-height: 1.4;
    margin-bottom: 2px;
}
.sub-title-line {
    text-align: center;
    font-family: TFArluck, 'Sarabun', sans-serif;
    font-size: 14px;
    line-height: 1.4;
    margin-bottom: 6px;
}

.input-data {
    font-family: THDanViVek, 'Sarabun', sans-serif;
    border-bottom: 1px dotted #333;
    display: inline-block;
    min-width: 30px;
    text-align: center;
    line-height: 1.2;
    padding: 0 4px;
}

.label-text {
    font-family: TFArluck, 'Sarabun', sans-serif;
}

.row-line {
    margin-bottom: 4px;
    line-height: 1.8;
}

.no-print-bar {
    background: #1a237e;
    color: #fff;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-family: TFArluck, 'Sarabun', sans-serif;
    font-size: 14px;
}
.no-print-bar button {
    background: #fff;
    color: #1a237e;
    border: none;
    border-radius: 4px;
    padding: 5px 16px;
    font-family: TFArluck, 'Sarabun', sans-serif;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
}
.no-print-bar button:hover { background: #e8eaf6; }

.sig-line {
    border-bottom: 1px solid #333;
    display: inline-block;
    min-width: 120px;
    margin: 0 4px;
}
</style>
</head>
<body>

{{-- Control Bar (no-print) --}}
<div class="no-print-bar no-print">
    <button onclick="window.print()">🖨 พิมพ์</button>
    <button onclick="window.close()">✕ ปิด</button>
    <span style="margin-left:8px; opacity:0.8;">ใบ ป.พ.2 — ขนาดกระดาษ 21 × 14.5 ซม.</span>
</div>

@php
    $student = $studentSection->student;
    $section = $studentSection->classSection;
    $level   = $section?->level;
    $sem     = $section?->semester;
    $year    = $sem?->academicYear;

    $thaiMonths = [
        1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',
        5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',
        9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม',
    ];

    function toThaiNum($n) {
        $map = ['๐','๑','๒','๓','๔','๕','๖','๗','๘','๙'];
        return preg_replace_callback('/\d/', fn($m) => $map[$m[0]], (string)$n);
    }

    $issuedDate = $doc?->issued_date ?? now();
    $issuedDay   = toThaiNum($issuedDate->day);
    $issuedMonth = $thaiMonths[$issuedDate->month];
    $issuedYear  = toThaiNum($issuedDate->year + 543);

    $birthDate   = $student?->birth_date ? \Carbon\Carbon::parse($student->birth_date) : null;
    $birthDay    = $birthDate ? toThaiNum($birthDate->day) : '....';
    $birthMonth  = $birthDate ? $thaiMonths[$birthDate->month] : '....';
    $birthYear   = $birthDate ? toThaiNum($birthDate->year + 543) : '....';

    $levelName = $level?->name ?? '';
    $docNumber = $doc?->doc_number ?? '';

    // Certificate text per level
    $certText = '';
    if (str_contains($levelName, 'ป.6') || str_contains($levelName, 'ประถม')) {
        $certText = 'สำเร็จการศึกษาตามหลักสูตรการศึกษาขั้นพื้นฐาน พุทธศักราช ๒๕๕๑ ระดับประถมศึกษาปีที่ ๖';
    } elseif (str_contains($levelName, 'ม.3') || $levelName === 'ม.3') {
        $certText = 'สำเร็จการศึกษาตามหลักสูตรการศึกษาขั้นพื้นฐาน พุทธศักราช ๒๕๕๑ ระดับมัธยมศึกษาปีที่ ๓';
    } elseif (str_contains($levelName, 'ม.6') || $levelName === 'ม.6') {
        $certText = 'สำเร็จการศึกษาตามหลักสูตรการศึกษาขั้นพื้นฐาน พุทธศักราช ๒๕๕๑ ระดับมัธยมศึกษาปีที่ ๖';
    } else {
        $certText = 'สำเร็จการศึกษาตามหลักสูตรการศึกษาขั้นพื้นฐาน พุทธศักราช ๒๕๕๑';
    }

    $fullName = ($student?->thai_prefix ?? '') . ($student?->thai_firstname ?? '') . ' ' . ($student?->thai_lastname ?? '');
    $studentCode = $student?->student_code ?? '';
    $yearName = $year?->year_name ?? '';
@endphp

{{-- หน้า 1: ใบ ป.พ.2 --}}
<div class="pp2-page">

    <div class="title-line">กระทรวงศึกษาธิการ</div>
    <div class="sub-title-line">ป.พ.2 หลักฐานแสดงผลการเรียน</div>

    <div class="row-line">
        <span class="label-text">เลขที่ </span>
        <span class="input-data">{{ $docNumber ?: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' }}</span>
        <span class="label-text"> &nbsp;&nbsp; วันที่ออกเอกสาร </span>
        <span class="input-data">{{ $issuedDay }}</span>
        <span class="label-text"> เดือน </span>
        <span class="input-data">{{ $issuedMonth }}</span>
        <span class="label-text"> พ.ศ. </span>
        <span class="input-data">{{ $issuedYear }}</span>
    </div>

    <div class="row-line">
        <span class="label-text">ข้าพเจ้าขอรับรองว่า </span>
        <span class="input-data" style="min-width:200px;">{{ $fullName }}</span>
        <span class="label-text"> เลขประจำตัวนักเรียน </span>
        <span class="input-data">{{ $studentCode }}</span>
    </div>

    <div class="row-line">
        <span class="label-text">วันเกิด </span>
        <span class="input-data">{{ $birthDay }}</span>
        <span class="label-text"> เดือน </span>
        <span class="input-data">{{ $birthMonth }}</span>
        <span class="label-text"> พ.ศ. </span>
        <span class="input-data">{{ $birthYear }}</span>
        <span class="label-text"> &nbsp; เชื้อชาติ </span>
        <span class="input-data">{{ $student?->nationality ?? 'ไทย' }}</span>
        <span class="label-text"> สัญชาติ </span>
        <span class="input-data">{{ $student?->citizenship ?? 'ไทย' }}</span>
        <span class="label-text"> ศาสนา </span>
        <span class="input-data">{{ $student?->religion ?? 'พุทธ' }}</span>
    </div>

    <div class="row-line">
        <span class="label-text">ได้ </span>
        <span class="input-data" style="min-width:420px;">{{ $certText }}</span>
    </div>

    <div class="row-line">
        <span class="label-text">ปีการศึกษา </span>
        <span class="input-data">{{ $yearName }}</span>
        <span class="label-text"> จากโรงเรียน </span>
        <span class="input-data" style="min-width:200px;">{{ config('app.school_name', '...') }}</span>
    </div>

    <div class="row-line">
        <span class="label-text">สังกัด </span>
        <span class="input-data" style="min-width:300px;"></span>
    </div>

    {{-- ลายเซ็น --}}
    <div style="margin-top:18px; display:flex; justify-content:space-between;">
        <div style="text-align:center; width:45%;">
            <div class="label-text" style="margin-bottom:30px;">ลงชื่อ</div>
            <div style="border-top:1px solid #333; padding-top:4px;">
                <div class="label-text">(............................................)</div>
                <div class="label-text">ผู้อำนวยการโรงเรียน</div>
            </div>
        </div>
        <div style="text-align:center; width:45%;">
            <div class="label-text" style="margin-bottom:30px;">ลงชื่อ</div>
            <div style="border-top:1px solid #333; padding-top:4px;">
                <div class="label-text">(............................................)</div>
                <div class="label-text">เจ้าหน้าที่ทะเบียน</div>
            </div>
        </div>
    </div>

    <div style="position:absolute; bottom:0.3cm; right:0.6cm; font-size:11px; color:#999;" class="label-text">
        พิมพ์วันที่ {{ now()->day }}/{{ now()->month }}/{{ now()->year + 543 }}
    </div>
</div>

<div class="page-break"></div>

{{-- หน้า 2: ผลการเรียน / ลายเซ็น --}}
<div class="pp2-page">
    <div class="title-line">ผลการเรียน</div>
    <div style="margin-top:8px; font-family: TFArluck, 'Sarabun', sans-serif; font-size:13px; color:#888; text-align:center;">
        (หน้านี้สำหรับกรอกผลการเรียนรายวิชา)
    </div>
    <div style="margin-top:60px; display:flex; justify-content:space-between;">
        <div style="text-align:center; width:45%;">
            <div class="label-text" style="margin-bottom:40px;">ลงชื่อ</div>
            <div style="border-top:1px solid #333; padding-top:4px;">
                <div class="label-text">(............................................)</div>
                <div class="label-text">ผู้อำนวยการโรงเรียน</div>
                <div class="label-text" style="font-size:12px; color:#777;">วันที่ ......../......../.........</div>
            </div>
        </div>
        <div style="text-align:center; width:45%;">
            <div class="label-text" style="margin-bottom:40px;">ลงชื่อ</div>
            <div style="border-top:1px solid #333; padding-top:4px;">
                <div class="label-text">(............................................)</div>
                <div class="label-text">เจ้าหน้าที่ทะเบียน</div>
                <div class="label-text" style="font-size:12px; color:#777;">วันที่ ......../......../.........</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
