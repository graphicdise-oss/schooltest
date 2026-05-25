<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ปพ.7 — {{ $student->thai_firstname }} {{ $student->thai_lastname }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'TH Sarabun New', 'Sarabun', 'Tahoma', sans-serif;
    font-size: 16px; color: #000; background: #e0e0e0;
    -webkit-font-smoothing: antialiased;
}
.page {
    width: 210mm; min-height: 297mm;
    margin: 0 auto 10mm; padding: 15mm 20mm 12mm;
    background: #fff;
    box-shadow: 0 0 8px rgba(0,0,0,0.15);
    position: relative;
    display: flex; flex-direction: column;
}
.por-label {
    position: absolute; top: 10mm; right: 14mm;
    font-size: 14px; font-weight: 600;
}
.header { text-align: center; margin-bottom: 4mm; }
.header img { width: 28mm; height: 28mm; object-fit: contain; margin-bottom: 3mm; }
.doc-title {
    font-size: 26px; font-weight: 700; text-align: center;
    margin-bottom: 4mm;
}
.school-name {
    font-size: 17px; font-weight: 600; text-align: center;
    margin-bottom: 1.5mm;
}
.school-addr {
    font-size: 15px; text-align: center; margin-bottom: 5mm;
}
.divider { border-top: 1px solid #000; margin: 2mm 0 4mm; }

.info-table { width: 100%; border-collapse: collapse; font-size: 16px; margin-bottom: 3mm; }
.info-table td { padding: 1.5mm 0; vertical-align: baseline; }
.info-table .lbl { font-weight: 700; white-space: nowrap; padding-right: 2mm; }
.info-table .val {
    padding: 0 2mm 0; min-width: 30mm;
}
.info-table .val-inline { padding-right: 4mm; }

.full-row { display: flex; align-items: baseline; gap: 2mm; margin-bottom: 3mm; font-size: 16px; }
.full-row .lbl { font-weight: 700; white-space: nowrap; flex-shrink: 0; }
.full-row .val { flex: 1; padding: 0 2mm; }

.result-area { margin: 5mm 0 5mm 20mm; font-size: 16px; }
.result-row { display: flex; align-items: baseline; gap: 4mm; margin-bottom: 4mm; }
.result-row .lbl { font-weight: 700; width: 35mm; }
.result-row .val { min-width: 40mm; padding: 0 2mm; }

.issue-line {
    text-align: center; font-size: 17px; font-weight: 700;
    margin: 6mm 0 8mm;
}

.sign-area {
    display: flex; align-items: flex-start; gap: 0;
    margin-top: 4mm;
}
.photo-box {
    width: 35mm; height: 45mm;
    border: 1px solid #666;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; color: #999; flex-shrink: 0;
    overflow: hidden;
}
.photo-box img { width: 100%; height: 100%; object-fit: cover; }

.signatures { flex: 1; padding-left: 10mm; }
.sig-block { margin-bottom: 8mm; }
.sig-dots {
    border-bottom: 1px dotted #666;
    width: 70mm; margin: 0 auto 1mm;
    height: 8mm;
}
.sig-name { text-align: center; font-size: 15px; }
.sig-position { text-align: center; font-weight: 700; font-size: 15px; }

.remark {
    margin-top: 8mm; font-size: 14px;
    border-top: 0.5px solid #ccc; padding-top: 3mm;
}

.no-print { padding: 12px; text-align: center; background: #f5f5f5; }
@page { size: A4 portrait; margin: 0; }
@media print {
    body { background: #fff; }
    .page { box-shadow: none; margin: 0; padding: 15mm 20mm 12mm; }
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
        &nbsp; อำเภอ{{ $school['amphoe'] ?? '' }}
        &nbsp; จังหวัด{{ $school['changwat'] ?? '' }}
    </div>

    <div class="divider"></div>

    {{-- แถว 1: ชื่อ + เลขประจำตัวนักเรียน --}}
    <table class="info-table">
        <tr>
            <td class="lbl" style="width:72mm;">ขอรับรองสถานภาพการเรียนของ</td>
            <td class="val" style="width:70mm;">{{ $fullName }}</td>
            <td class="lbl" style="padding-left:4mm;white-space:nowrap;">เลขประจำตัวนักเรียน</td>
            <td class="val">{{ $student->student_code ?? '' }}</td>
        </tr>
    </table>

    {{-- แถว 2: เลขบัตร + วันเกิด --}}
    <table class="info-table">
        <tr>
            <td class="lbl" style="width:52mm;">เลขประจำตัวประชาชน</td>
            <td class="val" style="width:55mm;">{{ $student->id_card_number ?? '' }}</td>
            <td class="lbl" style="padding-left:4mm;white-space:nowrap;"><strong>เกิดวันที่</strong></td>
            <td style="padding-left:2mm;">{{ $dobFormatted }}</td>
        </tr>
    </table>

    {{-- แถว 3: บิดา + มารดา --}}
    <table class="info-table" style="margin-top:1mm;">
        <tr>
            <td class="lbl" style="width:42mm;">ชื่อ - นามสกุลบิดา</td>
            <td class="val" style="width:70mm;">{{ $fatherName }}</td>
            <td class="lbl" style="padding-left:4mm;white-space:nowrap;">ชื่อ - นามสกุลมารดา</td>
            <td class="val">{{ $motherName }}</td>
        </tr>
    </table>

    {{-- แถว 4: เป็นนักเรียนของ --}}
    <div class="full-row" style="margin-top:2mm;">
        <span class="lbl">เป็นนักเรียนของ</span>
        <span class="val">
            {{ $school['name'] ?? '' }}
            กำลังศึกษาชั้น {{ $levelSection }}
            @if($section?->program) {{ $section->program }} @endif
            ปีการศึกษา {{ $yearName }}
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
            <div class="sig-block" style="margin-top:6mm;">
                <div class="sig-dots"></div>
                <div class="sig-name">( {{ $school['director_name'] ?? '' }} )</div>
                <div class="sig-position">{{ $school['director_position'] ?? 'ผู้อำนวยการ' }}</div>
            </div>
        </div>
    </div>

    {{-- นายทะเบียน --}}
    <div style="margin-top:6mm;">
        <div class="sig-dots" style="width:70mm;border-bottom:1px dotted #666;height:8mm;"></div>
        <div class="sig-name">( {{ $school['registrar_name'] ?? '' }} )</div>
        <div class="sig-position">{{ $school['registrar_position'] ?? 'นายทะเบียน' }}</div>
    </div>

    <div class="remark">
        หมายเหตุ : เอกสารสำคัญฉบับนี้มีอายุการใช้งาน ๑๒๐ วัน
    </div>
</div>

</body>
</html>
