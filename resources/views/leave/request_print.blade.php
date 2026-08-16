<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ใบลา — {{ $personnel->thai_firstname ?? '' }} {{ $personnel->thai_lastname ?? '' }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'TH Sarabun New', 'THSarabun', 'Sarabun', 'Tahoma', sans-serif;
    font-size: 20px; color: #000; background: #e0e0e0;
    -webkit-font-smoothing: antialiased;
}
.page {
    width: 210mm; min-height: 297mm;
    margin: 0 auto 10mm; padding: 18mm 20mm;
    background: #fff;
    box-shadow: 0 0 8px rgba(0,0,0,0.15);
}
.header { text-align: center; margin-bottom: 4mm; }
.header img { width: 22mm; height: 22mm; object-fit: contain; margin-bottom: 2mm; }
.doc-title { font-size: 150%; font-weight: bold; text-align: center; margin-bottom: 1mm; }
.school-name { font-size: 105%; text-align: center; margin-bottom: 6mm; }

.write-line { text-align: right; margin-bottom: 4mm; }
.subject-line { margin-bottom: 3mm; }
.subject-line .lbl { font-weight: bold; display: inline-block; width: 30mm; }
.addressee-line { margin-bottom: 4mm; }

p.body-text { line-height: 2; text-indent: 12mm; margin-bottom: 3mm; }
.underline { display: inline-block; border-bottom: 1px dotted #333; min-width: 40mm; padding: 0 2mm; text-align: center; }

.sign-area { margin-top: 10mm; text-align: center; }
.sign-dots { border-bottom: 1px dotted #666; width: 70mm; margin: 0 auto 1mm; height: 8mm; }
.sign-name { text-align: center; }
.sign-position { text-align: center; }
.sign-date { text-align: center; margin-top: 1mm; }

.approve-box {
    margin-top: 10mm; border-top: 1px solid #999; padding-top: 4mm;
}
.approve-box .approve-title { font-weight: bold; margin-bottom: 2mm; }

.stats-box {
    margin-top: 6mm; border: 1px solid #999; border-radius: 4px; padding: 3mm 4mm; font-size: 90%;
}
.stats-box .stats-title { font-weight: bold; margin-bottom: 2mm; }

.no-print { padding: 12px; text-align: center; background: #f5f5f5; }
@page { size: A4 portrait; margin: 0; }
@media print {
    body { background: #fff; }
    .page { box-shadow: none; margin: 0; padding: 18mm 20mm; }
    .no-print { display: none !important; }
}
</style>
</head>
<body>

@php
$logoSrc = null;
$uploadedLogoPath = \App\Models\SchoolInfoSetting::getInstance()->logo_path;
if ($uploadedLogoPath) {
    $f = storage_path('app/public/' . $uploadedLogoPath);
    if (file_exists($f)) {
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        $mime = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
        $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($f));
    }
}
if (!$logoSrc) {
    foreach (['png','jpg','jpeg','PNG','JPG'] as $ext) {
        $f = public_path('img/pp_1/logo.' . $ext);
        if (file_exists($f)) {
            $mime = in_array(strtolower($ext),['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
            $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($f));
            break;
        }
    }
}

$thMonths = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
             'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
$requestDate = \Carbon\Carbon::parse($request->request_date);

$requesterName = trim(($personnel->thai_prefix ?? '') . ($personnel->thai_firstname ?? '') . ' ' . ($personnel->thai_lastname ?? ''));
$contactParts = array_filter([
    $request->contact_house ? 'บ้านเลขที่ ' . $request->contact_house : null,
    $request->contact_road,
    $request->contact_subdistrict ? 'ตำบล/แขวง ' . $request->contact_subdistrict : null,
    $request->contact_district ? 'อำเภอ/เขต ' . $request->contact_district : null,
    $request->contact_province ? 'จังหวัด ' . $request->contact_province : null,
]);
$contactAddress = implode(' ', $contactParts);
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
    <div class="header">
        @if($logoSrc)
        <img src="{{ $logoSrc }}" alt="logo">
        @endif
    </div>
    <div class="doc-title">แบบใบลา{{ $request->leaveType->leave_type_name ?? '' }}</div>
    <div class="school-name">{{ $school['name'] ?? '' }}</div>

    <div class="write-line">
        เขียนที่ ................................................<br>
        วันที่ {{ $requestDate->format('d') }}
        เดือน {{ $thMonths[(int) $requestDate->format('n')] }}
        พ.ศ. {{ $requestDate->format('Y') + 543 }}
    </div>

    <div class="subject-line"><span class="lbl">เรื่อง</span> ขอลา{{ $request->leaveType->leave_type_name ?? '' }}</div>
    <div class="addressee-line">
        เรียน ผู้อำนวยการ{{ $school['name'] ?? '' }}
    </div>

    <p class="body-text">
        ข้าพเจ้า <span class="underline">{{ $requesterName }}</span>
        ตำแหน่ง <span class="underline">{{ $personnel->position ?? '-' }}</span>
        สังกัด <span class="underline">{{ $personnel->department ?? '-' }}</span>
    </p>

    <p class="body-text">
        มีความประสงค์ขอลา{{ $request->leaveType->leave_type_name ?? '' }}
        เนื่องจาก <span class="underline" style="min-width:80mm;">{{ $request->reason ?: '-' }}</span>
    </p>

    <p class="body-text">
        ตั้งแต่วันที่ <span class="underline">{{ \Carbon\Carbon::parse($request->start_date)->addYears(543)->format('d/m/Y') }}</span>
        ถึงวันที่ <span class="underline">{{ \Carbon\Carbon::parse($request->end_date)->addYears(543)->format('d/m/Y') }}</span>
        มีกำหนด <span class="underline">{{ number_format($request->num_days, 1) }}</span> วัน
        ({{ $request->leave_period ?? 'ทั้งวัน' }})
    </p>

    <p class="body-text">
        ในระหว่างลาจะติดต่อข้าพเจ้าได้ที่
        <span class="underline" style="min-width:100mm;">{{ $contactAddress ?: '-' }}</span>
        โทร <span class="underline">{{ $request->contact_phone ?: '-' }}</span>
    </p>

    <div class="sign-area">
        <div class="sign-dots"></div>
        <div class="sign-name">( {{ $requesterName }} )</div>
        <div class="sign-position">ผู้ยื่นคำร้อง</div>
    </div>

    <div class="approve-box">
        <div class="approve-title">ความเห็นผู้บังคับบัญชาชั้นต้น</div>
        <div style="min-height:8mm; border-bottom:1px dotted #999; margin-bottom:2mm;">{{ $request->approver1_comment ?? '' }}</div>
        <div class="sign-area" style="margin-top:6mm;">
            <div class="sign-dots"></div>
            <div class="sign-name">
                ( {{ $request->approver1 ? trim(($request->approver1->thai_prefix ?? '') . $request->approver1->thai_firstname . ' ' . $request->approver1->thai_lastname) : '..............................' }} )
            </div>
            @if($request->approver1_date)
                <div class="sign-date">วันที่ {{ \Carbon\Carbon::parse($request->approver1_date)->addYears(543)->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    <div class="approve-box">
        <div class="approve-title">ความเห็นผู้อนุมัติ</div>
        <div style="min-height:8mm; border-bottom:1px dotted #999; margin-bottom:2mm;">{{ $request->approver2_comment ?? '' }}</div>
        <div class="sign-area" style="margin-top:6mm;">
            <div class="sign-dots"></div>
            <div class="sign-name">
                ( {{ $request->approver2 ? trim(($request->approver2->thai_prefix ?? '') . $request->approver2->thai_firstname . ' ' . $request->approver2->thai_lastname) : (($school['director_name'] ?? '') ?: '..............................') }} )
            </div>
            <div class="sign-position">{{ $school['director_position'] ?? 'ผู้อำนวยการ' }}</div>
            @if($request->approver2_date)
                <div class="sign-date">วันที่ {{ \Carbon\Carbon::parse($request->approver2_date)->addYears(543)->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    @if($stats->isNotEmpty())
    <div class="stats-box">
        <div class="stats-title">สถิติการลาที่อนุมัติแล้วในปีนี้</div>
        @foreach($stats as $typeKey => $total)
            {{ \App\Models\Leave\LeaveType::where('leave_type_key', $typeKey)->value('leave_type_name') ?? $typeKey }}: {{ number_format($total, 1) }} วัน &nbsp;&nbsp;
        @endforeach
    </div>
    @endif
</div>

</body>
</html>
