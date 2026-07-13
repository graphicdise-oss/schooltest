<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบผู้ปกครอง — {{ config('school.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Prompt',sans-serif; background:#eaf4ff; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px; }
        .lg-card { background:#fff; border-radius:18px; box-shadow:0 8px 30px rgba(8,43,117,.14); padding:32px 30px; width:100%; max-width:400px; }
        .lg-head { text-align:center; margin-bottom:20px; }
        .lg-head img { height:60px; margin-bottom:8px; }
        .lg-head h1 { color:#082b75; font-weight:700; font-size:1.25rem; margin:0; }
        .lg-head p { color:#4b6aa5; font-size:.9rem; margin:2px 0 0; }
        .form-label { font-weight:500; color:#334155; font-size:.9rem; }
        .btn-login { background:#2563eb; border:none; border-radius:12px; padding:12px; font-weight:700; width:100%; color:#fff; }
        .btn-login:hover { background:#1e4fd0; color:#fff; }
        .hint { font-size:.82rem; color:#64748b; margin-top:14px; text-align:center; line-height:1.6; }
    </style>
</head>
<body>
<div class="lg-card">
    <div class="lg-head">
        <img src="{{ asset(config('school.logo')) }}" alt="logo" onerror="this.style.display='none'">
        <h1>เข้าสู่ระบบผู้ปกครอง</h1>
        <p>{{ config('school.name') }}</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger py-2">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('parent.login.submit') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">รหัสนักเรียน</label>
            <input type="text" name="student_code" class="form-control" value="{{ old('student_code') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">รหัสผ่าน</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
    </form>

    <div class="hint">
        ใช้ครั้งแรก: รหัสผ่าน คือ <strong>เลขบัตรประชาชนนักเรียน 13 หลัก</strong><br>
        เข้าสู่ระบบครั้งแรกแล้วสามารถเปลี่ยนรหัสผ่านเองได้ในเมนู "เปลี่ยนรหัสผ่าน"
    </div>
</div>
</body>
</html>
