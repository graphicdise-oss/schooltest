<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'เกิดข้อผิดพลาด') - School Tech</title>
    <link rel="icon" type="image/png" href="{{ asset('img/login_pic/graduation_cap.png') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', 'Prompt', Tahoma, sans-serif; padding: 20px;
            background-color: #f0f8ff;
            background-image: url('{{ asset('img/login_pic/bg_login.png') }}');
            background-size: cover; background-position: center; background-repeat: no-repeat;
        }
        .err-card {
            width: 100%; max-width: 460px; background: rgba(255,255,255,0.6);
            backdrop-filter: blur(6px); border-radius: 28px; padding: 44px 36px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.12); border: 1px solid rgba(255,255,255,0.7);
            text-align: center;
        }
        .err-icon { font-size: 60px; line-height: 1; margin-bottom: 14px; }
        .err-code { font-size: 13px; font-weight: 700; letter-spacing: 2px; color: #6055ff; margin-bottom: 10px; }
        .err-title { font-size: 21px; font-weight: 800; color: #1e293b; margin: 0 0 10px; }
        .err-message { font-size: 14px; color: #475569; margin: 0 0 28px; line-height: 1.7; }
        .err-btn {
            display: inline-block; background: linear-gradient(135deg, #7a73ff, #6055ff);
            color: #fff; text-decoration: none; padding: 13px 32px; border-radius: 999px;
            font-weight: 700; font-size: 14px; box-shadow: 0 4px 14px rgba(96,85,255,0.35);
        }
        .err-btn:hover { box-shadow: 0 6px 18px rgba(96,85,255,0.45); }
    </style>
</head>
<body>
    <div class="err-card">
        <div class="err-icon">@yield('icon', '⚠️')</div>
        <div class="err-code">@yield('code')</div>
        <h1 class="err-title">@yield('title')</h1>
        <p class="err-message">@yield('message')</p>
        <a href="{{ url('/') }}" class="err-btn" onclick="@yield('button_onclick', 'if (window.history.length > 1) { event.preventDefault(); window.history.back(); }')">@yield('button', 'ย้อนกลับ')</a>
    </div>
</body>
</html>
