<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
</head>

<body>

    <h2>เข้าสู่ระบบ (สำหรับครู)</h2>

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div>
            <label>ชื่อผู้ใช้งาน:</label>
            <input type="text" name="employee_code" required>
        </div>
        <div>
            <label>รหัสผ่าน:</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <a href="#">ลืมรหัสผ่าน ?</a>
        </div>
        <div>
            <button type="submit">เข้าสู่ระบบ</button>
        </div>
    </form>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

</body>
</html>