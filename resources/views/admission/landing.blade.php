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
        .doc-item { display:flex; align-items:center; gap:12px; padding:11px 14px; border:1px solid #e6ebf5; border-radius:10px; margin-bottom:9px; text-decoration:none; color:#082b75; transition:.15s; }
        .doc-item:hover { background:#f2f6ff; border-color:#bcd0f5; }
        .doc-item .ic { color:#dc2626; font-size:20px; }
        .grp-label { color:#2563eb; font-weight:600; font-size:.92rem; margin:14px 0 8px; }
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
    @if($setting->instructions)
        <div class="card2">
            <div class="sec-title">คำชี้แจงการรับสมัคร</div>
            <div class="content">{{ $setting->instructions }}</div>
            @if($setting->levels_note)
                <p class="mt-3 mb-0"><strong>ระดับชั้นที่เปิดรับ:</strong> {{ $setting->levels_note }}</p>
            @endif
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
            <div class="sec-title">ระเบียบการรับสมัคร (ดาวน์โหลด)</div>
            @php $groups = $documents->groupBy(fn($d) => $d->level->name ?? 'ทั่วไป'); @endphp
            @foreach($groups as $groupName => $docs)
                <div class="grp-label">{{ $groupName }}</div>
                @foreach($docs as $doc)
                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="doc-item">
                        <span class="ic">📄</span>
                        <span>{{ $doc->title }}</span>
                    </a>
                @endforeach
            @endforeach
        </div>
    @endif

</div>
</body>
</html>
