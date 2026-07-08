<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รับสมัครนักเรียนออนไลน์ — {{ config('school.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Prompt',sans-serif; background:#eaf4ff; padding:24px 12px; }
        .wrap { max-width:760px; margin:0 auto; }
        .card2 { background:#fff; border-radius:16px; box-shadow:0 6px 24px rgba(8,43,117,.10); padding:26px 28px; margin-bottom:18px; }
        .banner { width:100%; border-radius:16px; margin-bottom:18px; box-shadow:0 6px 24px rgba(8,43,117,.10); display:block; }
        .head { text-align:center; margin-bottom:18px; }
        .head h1 { color:#082b75; font-weight:700; font-size:1.6rem; margin:8px 0 2px; }
        .head p { color:#4b6aa5; margin:0; }
        .sec-title { color:#082b75; font-weight:700; font-size:1.1rem; border-left:4px solid #4b7ce3; padding-left:10px; margin:0 0 14px; }
        .content { white-space:pre-wrap; color:#475569; line-height:1.7; }
        .announce-img { width:100%; border-radius:12px; margin-top:14px; display:block; }
        .reg-table { width:100%; border-collapse:collapse; }
        .reg-table thead th { color:#475569; font-weight:600; padding:12px 14px; border-bottom:1px solid #e6ebf5; font-size:.95rem; }
        .reg-table tbody td { padding:15px 14px; border-bottom:1px solid #eef1f7; color:#334155; vertical-align:middle; }
        .reg-table tbody tr:hover { background:#f7fbff; }
        .reg-file-ic { color:#3b82f6; margin-right:8px; }
        .btn-dl { background:#1cb8d6; color:#fff; border-radius:22px; padding:8px 24px; text-decoration:none; font-size:.9rem; font-weight:500; display:inline-block; white-space:nowrap; transition:.15s; }
        .btn-dl:hover { background:#1596b0; color:#fff; }
        .btn-apply { background:#2563eb; border:none; border-radius:12px; padding:15px; font-weight:700; font-size:1.1rem; width:100%; color:#fff; }
        .btn-apply:hover { background:#1e4fd0; color:#fff; }
        .closed { text-align:center; background:#fff7ed; color:#c2410c; border-radius:12px; padding:16px; font-weight:600; }
        .badge-open { display:inline-block; background:#dcfce7; color:#16a34a; border-radius:20px; padding:5px 16px; font-size:.85rem; font-weight:600; }
    </style>
</head>
<body>
<div class="wrap">

    @if($setting->banner_image)
        <img src="{{ asset('storage/' . $setting->banner_image) }}" class="banner" alt="banner">
    @endif

    <div class="head">
        @if(config('school.logo'))
            <img src="{{ asset(config('school.logo')) }}" alt="logo" style="height:64px;" onerror="this.style.display='none'">
        @endif
        <h1>รับสมัครนักเรียนออนไลน์</h1>
        <p>{{ config('school.name') }}
            @if($setting->academicYear) · ปีการศึกษา {{ $setting->academicYear->year_name }} @endif
        </p>
        @if($setting->isAcceptingNow())
            <div class="mt-2"><span class="badge-open">● เปิดรับสมัคร</span></div>
        @endif
    </div>

    @if(session('error'))
        <div class="alert alert-warning">{{ session('error') }}</div>
    @endif

    {{-- คำชี้แจง / ประชาสัมพันธ์ --}}
    @if($setting->instructions || $setting->levels_note || $images->isNotEmpty())
        <div class="card2">
            <div class="sec-title">คำชี้แจงการรับสมัคร</div>
            @if($setting->instructions)
                <div class="content">{{ $setting->instructions }}</div>
            @endif
            @if($setting->levels_note)
                <p class="mt-3 mb-0"><strong>ระดับชั้นที่เปิดรับ:</strong> {{ $setting->levels_note }}</p>
            @endif
            @foreach($images as $img)
                <img src="{{ asset('storage/' . $img->file_path) }}" class="announce-img" alt="รูปประชาสัมพันธ์">
            @endforeach
        </div>
    @endif

    {{-- ปุ่มสมัคร / ปิดรับ --}}
    <div class="card2">
        @if($setting->isAcceptingNow())
            <a href="{{ route('admission.apply') }}" class="btn btn-primary btn-apply">
                📝 สมัครเรียน
            </a>
        @else
            <div class="closed">
                ⚠️ ขณะนี้ยังไม่เปิดรับสมัคร
                @if($setting->open_date && now()->startOfDay()->lt($setting->open_date))
                    <div class="mt-1" style="font-weight:400;">จะเปิดรับสมัครวันที่ {{ $setting->open_date->format('d/m/') }}{{ $setting->open_date->year + 543 }}</div>
                @elseif($setting->close_date && now()->startOfDay()->gt($setting->close_date))
                    <div class="mt-1" style="font-weight:400;">ปิดรับสมัครแล้วเมื่อ {{ $setting->close_date->format('d/m/') }}{{ $setting->close_date->year + 543 }}</div>
                @endif
            </div>
        @endif
    </div>

    {{-- หลักฐานที่ต้องเตรียม --}}
    @if($setting->required_docs)
        <div class="card2">
            <div class="sec-title">เอกสาร/หลักฐานที่ต้องเตรียม</div>
            <div class="content">{{ $setting->required_docs }}</div>
        </div>
    @endif

    {{-- ระเบียบการ / ไฟล์แนบตามระดับชั้น --}}
    @if($documents->isNotEmpty())
        <div class="card2">
            <div class="sec-title">ระเบียบการรับสมัคร</div>
            <div style="overflow-x:auto;">
                <table class="reg-table">
                    <thead>
                        <tr>
                            <th style="width:60px; text-align:left;">#</th>
                            <th style="text-align:center;">รายการ</th>
                            <th style="width:150px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $i => $doc)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td style="text-align:center;">
                                    <span class="reg-file-ic">📄</span>{{ $doc->title }}
                                </td>
                                <td style="text-align:right;">
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn-dl">ดาวน์โหลด</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
</body>
</html>
