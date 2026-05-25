<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ปพ.7 — {{ $student->thai_firstname }} {{ $student->thai_lastname }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'TH Sarabun New', 'THSarabun', 'Sarabun', 'Tahoma', sans-serif;
    font-size: 21px; color: #000; background: #e0e0e0;
    -webkit-font-smoothing: antialiased;
}
.page {
    width: 210mm; min-height: 297mm;
    margin: 0 auto 10mm; padding: 15mm 20mm 15mm;
    background: #fff;
    box-shadow: 0 0 8px rgba(0,0,0,0.15);
    position: relative;
}
.por-label {
    position: absolute; top: 12mm; right: 14mm;
    font-size: 138%; font-weight: 600;
}
.header {
    text-align: center; margin-bottom: 2mm;
}
.header img {
    width: 33mm; height: 33mm; object-fit: contain;
}
.doc-title {
    font-size: 170%; font-weight: bold; text-align: center;
    margin-bottom: 1mm; line-height: 1.2;
}
.school-name {
    font-size: 28px; font-weight: 600; text-align: center;
    margin-bottom: 0.5mm;
}
.school-addr {
    font-size: 24px; text-align: center; margin-bottom: 2mm;
}
.divider { margin: 1.5mm 0 2.5mm; }

/* Data rows */
.data-row {
    display: flex; align-items: baseline;
    margin-bottom: 1mm; font-size: 21px;
}
.data-row .lbl {
    font-weight: bold; white-space: nowrap; flex-shrink: 0;
    padding-right: 2mm;
}
.data-row .val {
    flex: 1; padding: 0 2mm;
    min-height: 6mm; line-height: 1.1;
}
.data-row .val.no-border { border-bottom: none; }

/* Two-column rows */
.data-row-2col {
    display: flex; align-items: baseline;
    margin-bottom: 1mm; font-size: 21px;
}
.data-row-2col .col {
    display: flex; align-items: baseline; flex: 1;
}
.data-row-2col .col:first-child { flex: 1.4; }
.data-row-2col .lbl {
    font-weight: bold; white-space: nowrap; flex-shrink: 0;
    padding-right: 2mm;
}
.data-row-2col .val {
    flex: 1; padding: 0 2mm;
    min-height: 6mm; line-height: 1.1;
}

/* Result area */
.result-area { margin: 2mm 0 2mm 15mm; }
.result-row {
    display: flex; align-items: baseline;
    margin-bottom: 2mm; font-size: 21px;
}
.result-row .lbl { font-weight: bold; width: 38mm; flex-shrink: 0; }
.result-row .val {
    flex: 1;
    min-height: 6mm; padding: 0 2mm;
}

.issue-line {
    text-align: center; font-size: 21px; font-weight: bold;
    margin: 3mm 0 3mm;
}

/* Sign area */
.sign-area {
    display: flex; align-items: flex-start; gap: 0;
    margin-top: 1mm;
}
.photo-box {
    width: 30mm; height: 40mm;
    border: 1px solid #666;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; color: #999; flex-shrink: 0;
    overflow: hidden;
}
.photo-box img { width: 100%; height: 100%; object-fit: cover; }

.signatures { flex: 1; padding-left: 6mm; }
.sig-block { margin-bottom: 4mm; }
.sig-dots {
    border-bottom: 1px dotted #666;
    width: 70mm; margin: 0 auto 1mm;
    height: 8mm;
}
.sig-name { text-align: center; font-size: 20px; }
.sig-position { text-align: center; font-weight: bold; font-size: 20px; }

/* Registrar below photo */
.registrar-block { margin-top: 3mm; }
.registrar-block .sig-dots {
    width: 70mm; border-bottom: 1px dotted #666;
    height: 8mm; margin: 0 auto 1mm;
}
.registrar-block .sig-name { text-align: center; font-size: 20px; }
.registrar-block .sig-position { text-align: center; font-weight: bold; font-size: 20px; }

.remark {
    margin-top: 4mm; font-size: 18px;
    border-top: 0.5px solid #ccc; padding-top: 2mm;
}

.no-print { padding: 12px; text-align: center; background: #f5f5f5; }
@page { size: A4 portrait; margin: 0; }
@media print {
    body { background: #fff; }
    .page { box-shadow: none; margin: 0; padding: 15mm 20mm 15mm; }
    .no-print { display: none !important; }
}
</style>
</head>
<body>

@php
$school = $school ?? config('school');

$logoSrc = null;
foreach (['png','jpg','jpeg','PNG','JPG'] as $ext) {
    $f = public_path('img/pp_1/logo.' . $ext);
    if (file_exists($f)) {
        $mime = in_array(strtolower($ext),['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
        $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($f));
        break;
    }
}

$photoSrc = null;
if (!empty($student->photo)) {
    $photoFile = public_path('storage/' . $student->photo);
    if (file_exists($photoFile)) {
        $ext = strtolower(pathinfo($photoFile, PATHINFO_EXTENSION));
        $mime = in_array($ext,['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
        $photoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($photoFile));
    }
}

$fatherName = $father
    ? trim(($father->prefix_th ?? $father->thai_prefix ?? '').' '.($father->first_name_th ?? $father->thai_firstname ?? '').' '.($father->last_name_th ?? $father->thai_lastname ?? ''))
    : '';
$motherName = $mother
    ? trim(($mother->prefix_th ?? $mother->thai_prefix ?? '').' '.($mother->first_name_th ?? $mother->thai_firstname ?? '').' '.($mother->last_name_th ?? $mother->thai_lastname ?? ''))
    : '';

$fullName = ($student->thai_prefix ?? '') . ($student->thai_firstname ?? '') . ' ' . ($student->thai_lastname ?? '');
@endphp

<div class="no-print">
    <button onclick="window.print()" style="background:#43a047;color:#fff;border:none;padding:10px 28px;border-radius:6px;font-size:15px;cursor:pointer;font-family:inherit;">
        🖨️ พิมพ์เอกสาร
    </button>
    <button onclick="window.close()" style="background:#666;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-size:15px;cursor:pointer;font-family:inherit;margin-left:8px;">
        ✕ ปิด
    </button>
</div>

<div class="page">
    <div class="por-label">ปพ.๗</div>

    {{-- Logo --}}
    <div class="header">
        @if($logoSrc)
        <img src="{{ $logoSrc }}" alt="logo">
        @endif
    </div>

    {{-- Title --}}
    <div class="doc-title">ใบรับรองการเป็นนักเรียน</div>

    {{-- School info --}}
    <div class="school-name">{{ $school['name'] ?? '' }}</div>
    <div class="school-addr">
        สำนักงานศึกษาธิการจังหวัด{{ $school['changwat'] ?? '' }}
        &nbsp;&nbsp; อำเภอ{{ $school['amphoe'] ?? '' }}
        &nbsp;&nbsp; จังหวัด{{ $school['changwat'] ?? '' }}
    </div>

    <div class="divider"></div>

    {{-- แถว 1: ชื่อ + เลขประจำตัวนักเรียน --}}
    <div class="data-row-2col">
        <div class="col" style="flex:1.8;">
            <span class="lbl">ขอรับรองสถานภาพการเรียนของ</span>
            <span class="val">{{ $fullName }}</span>
        </div>
        <div class="col" style="flex:1; padding-left:4mm;">
            <span class="lbl">เลขประจำตัวนักเรียน</span>
            <span class="val">{{ $student->student_code ?? '' }}</span>
        </div>
    </div>

    {{-- แถว 2: เลขบัตร + วันเกิด --}}
    <div class="data-row-2col">
        <div class="col" style="flex:1.5;">
            <span class="lbl">เลขประจำตัวประชาชน</span>
            <span class="val">{{ $student->id_card_number ?? '' }}</span>
        </div>
        <div class="col" style="flex:1; padding-left:4mm;">
            <span class="lbl">เกิดวันที่</span>
            <span class="val">{{ $dobFormatted }}</span>
        </div>
    </div>

    {{-- แถว 3: บิดา + มารดา --}}
    <div class="data-row-2col">
        <div class="col" style="flex:1.2;">
            <span class="lbl">ชื่อ-นามสกุลบิดา</span>
            <span class="val">{{ $fatherName }}</span>
        </div>
        <div class="col" style="flex:1; padding-left:4mm;">
            <span class="lbl">ชื่อ-นามสกุลมารดา</span>
            <span class="val">{{ $motherName }}</span>
        </div>
    </div>

    {{-- แถว 4: เป็นนักเรียนของ --}}
    <div class="data-row" style="margin-top:1mm;">
        <span class="lbl">เป็นนักเรียนของ</span>
        <span class="val no-border">
            กำลังศึกษาชั้น &nbsp;{{ $levelSection }}
            @if($section?->program) &nbsp;&nbsp;{{ $section->program }} @endif
            &nbsp;&nbsp;&nbsp; ปีการศึกษา &nbsp;{{ $yearName }}
        </span>
    </div>

    {{-- ผลการเรียน + ความประพฤติ --}}
    <div class="result-area">
        <div class="result-row">
            <span class="lbl">ผลการเรียน</span>
            <span class="val">{{ $gradeResult }}</span>
        </div>
        <div class="result-row">
            <span class="lbl">ความประพฤติ</span>
            <span class="val">{{ $behavior }}</span>
        </div>
    </div>

    {{-- วันออกให้ --}}
    <div class="issue-line">
        ออกให้ ณ วันที่ {{ $issueDateFormatted }}
    </div>

    {{-- รูปและลายเซ็น --}}
    <div class="sign-area">
        <div>
            <div class="photo-box">
                @if($photoSrc)
                    <img src="{{ $photoSrc }}" alt="photo">
                @else
                    รูปถ่าย
                @endif
            </div>
        </div>

        <div class="signatures">
            {{-- ผู้อำนวยการ --}}
            <div class="sig-block" style="margin-top:8mm;">
                <div class="sig-dots"></div>
                <div class="sig-name">( {{ $school['director_name'] ?? '' }} )</div>
                <div class="sig-position">{{ $school['director_position'] ?? 'ผู้อำนวยการ' }}</div>
            </div>
        </div>
    </div>

    {{-- นายทะเบียน --}}
    <div class="registrar-block">
        <div class="sig-dots"></div>
        <div class="sig-name">( {{ $school['registrar_name'] ?? '' }} )</div>
        <div class="sig-position">{{ $school['registrar_position'] ?? 'นายทะเบียน' }}</div>
    </div>

    <div class="remark">
        หมายเหตุ : เอกสารสำคัญฉบับนี้มีอายุการใช้งาน ๑๒๐ วัน
    </div>
</div>

</body>
</html>
