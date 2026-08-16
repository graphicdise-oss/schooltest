<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>พิมพ์บัตรนักเรียน</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background: #e0e0e0;
        font-family: 'Sarabun', 'Tahoma', sans-serif;
        padding: 20px;
    }

    .cards-wrap {
        display: flex; flex-wrap: wrap; gap: 16px;
        justify-content: center;
    }

    /* ===== บัตรนักเรียน ===== */
    .id-card {
        width: 54mm; min-height: 88mm;
        background: #fff;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.25);
        display: flex; flex-direction: column;
        font-size: 9pt;
    }

    /* Header */
    .card-head {
        background: linear-gradient(135deg, #1a5c38, #2d8a57);
        padding: 6px 8px;
        display: flex; align-items: center; gap: 6px;
    }
    .card-head .logo {
        width: 28px; height: 28px; object-fit: contain; flex-shrink: 0;
    }
    .card-head-text { color: #fff; line-height: 1.3; }
    .card-head-text .school-th { font-size: 6.5pt; font-weight: 700; }

    /* Body */
    .card-body {
        flex: 1; padding: 8px 10px;
        display: flex; flex-direction: column; align-items: center;
        background: #fff;
    }
    .card-photo {
        width: 26mm; height: 32mm;
        object-fit: cover; border: 2px solid #ddd;
        margin: 6px 0 8px;
    }
    .card-photo-placeholder {
        width: 26mm; height: 32mm;
        background: #f0f0f0; border: 2px solid #ddd;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px; color: #bbb; margin: 6px 0 8px;
    }
    .card-name-th {
        font-size: 9.5pt; font-weight: 700; color: #1a5c38;
        text-decoration: underline; text-align: center;
        margin-bottom: 2px; line-height: 1.4;
    }
    .card-name-en {
        font-size: 8pt; color: #333; text-align: center; margin-bottom: 2px;
    }
    .card-code {
        font-size: 9pt; color: #333; text-align: center; margin-bottom: 6px;
    }

    /* Footer */
    .card-foot {
        padding: 4px 10px 8px;
        text-align: center;
    }
    .card-sign-line {
        border-top: 1px solid #333; width: 36mm; margin: 0 auto 2px;
    }
    .card-sign-name { font-size: 7pt; color: #333; }
    .card-sign-pos  { font-size: 6.5pt; color: #555; margin-bottom: 5px; }
    .card-dates {
        display: flex; justify-content: space-between;
        font-size: 6pt; color: #444;
        border-top: 1px solid #eee; padding-top: 4px; margin-top: 2px;
    }

    /* Print styles */
    @media print {
        body { background: #fff; padding: 0; margin: 0; }
        .no-print { display: none !important; }
        .cards-wrap { gap: 8mm; padding: 5mm; }
        .id-card { box-shadow: none; break-inside: avoid; }
        @page { margin: 10mm; }
    }
</style>
</head>
<body>

{{-- ปุ่ม (ซ่อนตอนพิมพ์) --}}
<div class="no-print" style="text-align:center;margin-bottom:16px;">
    <button onclick="window.print()"
        style="background:#4caf50;color:#fff;border:none;border-radius:8px;padding:10px 32px;font-size:1rem;font-weight:700;cursor:pointer;">
        🖨️ พิมพ์บัตร
    </button>
    <button onclick="window.close()"
        style="background:#f5f5f5;color:#666;border:1px solid #ddd;border-radius:8px;padding:10px 24px;font-size:1rem;cursor:pointer;margin-left:10px;">
        ✕ ปิด
    </button>
</div>

@php
    $cardLogoPath = \App\Models\SchoolInfoSetting::getInstance()->logo_path;
    $cardLogoSrc  = $cardLogoPath ? asset('storage/' . $cardLogoPath) : asset('img/pp_1/logo.png');
@endphp

<div class="cards-wrap">
    @foreach($students as $idx => $row)
    @php
        $s  = $row['student'];
        $ss = $row['ss'];
        $level   = $ss?->classSection?->level?->name ?? '';
        $section = $ss?->classSection?->section_number ?? '';
        $year    = $ss?->classSection?->semester?->academicYear?->year_name ?? '';

        $issueDate  = now()->locale('th')->translatedFormat('d/m/') . (now()->year + 543);
        $expireDate = now()->addYears(1)->locale('th')->translatedFormat('d/m/') . (now()->addYears(1)->year + 543);

        $nameTh = ($s->thai_prefix ?? '') . ($s->thai_firstname ?? '') . ' ' . ($s->thai_lastname ?? '');
        $nameEn = trim(($s->english_firstname ?? '') . ' ' . ($s->english_lastname ?? ''));
    @endphp

    <div class="id-card">

        {{-- Header --}}
        <div class="card-head">
            <img src="{{ $cardLogoSrc }}" class="logo" alt="logo" onerror="this.style.display='none'">
            <div class="card-head-text">
                <div class="school-th">{{ $school['name'] ?? config('school.name') }}</div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">
            @if($s->student_image)
                <img src="{{ asset('storage/' . $s->student_image) }}" class="card-photo" alt="รูปนักเรียน">
            @else
                <div class="card-photo-placeholder">👤</div>
            @endif

            <div class="card-name-th">{{ $nameTh }}</div>
            @if($nameEn)
            <div class="card-name-en">{{ $nameEn }}</div>
            @endif
            <div class="card-code">{{ $s->student_code }}</div>
        </div>

        {{-- Footer --}}
        <div class="card-foot">
            <div class="card-sign-line"></div>
            <div class="card-sign-name">(ผู้อำนวยการโรงเรียน)</div>
            <div class="card-sign-pos">ผู้อำนวยการโรงเรียน</div>
            <div class="card-dates">
                <span>วันที่ออกบัตร : {{ $issueDate }}</span>
                <span>วันหมดอายุ : {{ $expireDate }}</span>
            </div>
        </div>

    </div>
    @endforeach
</div>

<script>
// auto print
window.onload = function() {
    setTimeout(() => window.print(), 800);
};
</script>
</body>
</html>
