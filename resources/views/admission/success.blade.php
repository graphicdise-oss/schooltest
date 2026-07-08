<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสำเร็จ — {{ config('school.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Prompt',sans-serif; background:#eaf4ff; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; padding:20px; }
        .box { background:#fff; border-radius:18px; box-shadow:0 8px 30px rgba(8,43,117,.12); padding:44px 34px; text-align:center; max-width:480px; }
        .check { width:84px; height:84px; border-radius:50%; background:#dcfce7; color:#16a34a; font-size:44px; display:flex; align-items:center; justify-content:center; margin:0 auto 18px; }
        h1 { color:#082b75; font-weight:700; font-size:1.5rem; margin:0 0 8px; }
        p { color:#475569; margin:0 0 6px; }
        .status { display:inline-block; margin-top:14px; background:#fff7ed; color:#c2410c; border-radius:20px; padding:6px 18px; font-weight:600; }
        a { display:inline-block; margin-top:22px; color:#2563eb; text-decoration:none; font-weight:600; }
    </style>
</head>
<body>
    <div class="box">
        <div class="check">✓</div>
        <h1>ส่งใบสมัครสำเร็จ</h1>
        <p>ระบบได้รับข้อมูลการสมัครของคุณเรียบร้อยแล้ว</p>
        <p>ทางโรงเรียนจะตรวจสอบและติดต่อกลับ</p>
        <div class="status">สถานะ: รอการตรวจสอบ</div>
        <br>
        <a href="{{ route('admission.form') }}">← สมัครเพิ่มอีกคน</a>
    </div>
</body>
</html>
