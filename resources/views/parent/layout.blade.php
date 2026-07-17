<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ระบบผู้ปกครอง') — {{ config('school.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/login_pic/graduation_cap.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Prompt',sans-serif; background:#eaf4ff; margin:0; }
        .pp-topbar { background:#fff; box-shadow:0 2px 10px rgba(8,43,117,.08); padding:10px 0; margin-bottom:22px; }
        .pp-topbar .wrap { max-width:960px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; padding:0 16px; flex-wrap:wrap; gap:8px; }
        .pp-brand { display:flex; align-items:center; gap:10px; }
        .pp-brand img { height:36px; }
        .pp-brand span { color:#082b75; font-weight:700; }
        .pp-nav { display:flex; gap:4px; flex-wrap:wrap; }
        .pp-nav a { color:#475569; text-decoration:none; padding:8px 14px; border-radius:10px; font-size:.92rem; font-weight:500; }
        .pp-nav a.active, .pp-nav a:hover { background:#eef4ff; color:#2563eb; }
        .pp-logout button { background:#fee2e2; color:#b91c1c; border:none; border-radius:10px; padding:8px 16px; font-size:.9rem; font-weight:600; }
        .pp-wrap { max-width:960px; margin:0 auto 40px; padding:0 16px; }
        .pp-card { background:#fff; border-radius:16px; box-shadow:0 6px 24px rgba(8,43,117,.10); padding:24px 26px; margin-bottom:18px; }
        .pp-title { color:#082b75; font-weight:700; font-size:1.15rem; border-left:4px solid #4b7ce3; padding-left:10px; margin:0 0 16px; }
    </style>
    @stack('styles')
</head>
<body>

@php($ppStudent = auth('parent')->user())
<div class="pp-topbar">
    <div class="wrap">
        <div class="pp-brand">
            <img src="{{ asset('img/login_pic/graduation_cap.png') }}" alt="logo" onerror="this.style.display='none'">
            <span>ระบบผู้ปกครอง — {{ config('school.name') }}</span>
        </div>
        @if($ppStudent)
        <div class="pp-nav">
            <a href="{{ route('parent.dashboard') }}" class="{{ request()->routeIs('parent.dashboard') ? 'active' : '' }}"><i class="bi bi-house"></i> หน้าหลัก</a>
            <a href="{{ route('parent.grades') }}" class="{{ request()->routeIs('parent.grades') ? 'active' : '' }}"><i class="bi bi-mortarboard"></i> ผลการเรียน</a>
            <a href="{{ route('parent.timetable') }}" class="{{ request()->routeIs('parent.timetable') ? 'active' : '' }}"><i class="bi bi-table"></i> ตารางเรียน</a>
            <a href="{{ route('parent.calendar') }}" class="{{ request()->routeIs('parent.calendar') ? 'active' : '' }}"><i class="bi bi-calendar3"></i> ปฏิทิน/วันหยุด</a>
            <a href="{{ route('parent.contact') }}" class="{{ request()->routeIs('parent.contact') ? 'active' : '' }}"><i class="bi bi-person-lines-fill"></i> ติดต่อครูประจำชั้น</a>
            <a href="{{ route('parent.change-password') }}" class="{{ request()->routeIs('parent.change-password') ? 'active' : '' }}"><i class="bi bi-key"></i> เปลี่ยนรหัสผ่าน</a>
        </div>
        <div class="pp-logout">
            <form method="POST" action="{{ route('parent.logout') }}">
                @csrf
                <button type="submit"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</button>
            </form>
        </div>
        @endif
    </div>
</div>

<div class="pp-wrap">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-warning">{{ session('error') }}</div>
    @endif

    @yield('content')
</div>

</body>
</html>
