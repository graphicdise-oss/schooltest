<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Tech - Login</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('img/login_pic/graduation_cap.png') }}">

    <style>
        /* สไตล์สำหรับพื้นหลัง */
        body {
            background-color: #f0f8ff;
            background-image: url('{{ asset('img/login_pic/bg_login.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* แต่งสีของ Input */
        .input-bg {
            background-color: #e6ecfa;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">

    <div
        class="w-full max-w-md bg-white/40 backdrop-blur-sm rounded-3xl p-8 shadow-lg relative z-10 border border-white/50">

        <div class="text-center mb-8">
            <div class="flex justify-center items-center gap-2 mb-4">
                <div class="bg-white p-2 rounded-xl shadow-md flex items-center justify-center"
                    style="width: 60px; height: 60px;">
                    <img src="{{ asset('img/login_pic/graduation_cap.png') }}" alt="School Tech Logo"
                        class="max-h-full max-w-full">
                </div>
                <h1 class="text-4xl font-black text-blue-900 tracking-wider shadow-sm"
                    style="text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">
                    SCHOOL <span class="text-blue-500">TECH</span>
                </h1>
            </div>
            <h2 class="text-xl font-bold text-black">เข้าสู่ระบบ (สำหรับครู)</h2>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <i class="fas fa-graduation-cap text-black text-lg"></i>
                </div>
                <input type="text" name="school_name" id="schoolNameField" value="{{ $schoolName }}"
                    placeholder="กรอกชื่อโรงเรียน" autocomplete="off"
                    class="input-bg w-full rounded-full py-4 pl-12 pr-12 text-black text-center focus:outline-none focus:ring-2 focus:ring-blue-400 font-medium text-sm">
                <div id="schoolResults"
                    class="hidden absolute left-0 right-0 mt-2 bg-white border border-gray-200 rounded-2xl shadow-lg overflow-hidden z-20"
                    style="max-height: 220px; overflow-y: auto;">
                </div>
            </div>

            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <i class="fas fa-user text-black text-lg"></i>
                </div>
                <input type="text" name="employee_code" placeholder="ชื่อผู้ใช้งาน" value="{{ old('employee_code') }}"
                    required
                    class="input-bg w-full rounded-full py-4 pl-12 pr-4 text-black focus:outline-none focus:ring-2 focus:ring-blue-400 font-medium">
            </div>

            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <i class="fas fa-lock text-black text-lg"></i>
                </div>
                <input type="password" name="password" placeholder="รหัสผ่าน" required
                    class="input-bg w-full rounded-full py-4 pl-12 pr-4 text-black focus:outline-none focus:ring-2 focus:ring-blue-400 font-medium">
            </div>

            <div class="flex justify-end pt-2 pb-2">
                <a href="#" class="text-sm text-black font-semibold hover:text-blue-600 underline underline-offset-2">
                    ลืมรหัสผ่าน ?
                </a>
            </div>

            <div>
                <button type="submit"
                    class="w-full bg-gradient-to-r from-[#7a73ff] to-[#6055ff] text-white rounded-full py-4 font-bold text-lg shadow-md hover:shadow-lg transition-all duration-300">
                    เข้าสู่ระบบ
                </button>
            </div>
        </form>

        @if ($errors->any())
            <div class="mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative" role="alert">
                <strong class="font-bold">เกิดข้อผิดพลาด!</strong>
                <ul class="list-disc ml-5 mt-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>

    <script>
        (function () {
            var field = document.getElementById('schoolNameField');
            if (!field) return;
            var maxSize = 14, minSize = 9;
            function fitText() {
                field.style.fontSize = maxSize + 'px';
                var guard = 0;
                while (field.scrollWidth > field.clientWidth && parseFloat(field.style.fontSize) > minSize && guard < 20) {
                    field.style.fontSize = (parseFloat(field.style.fontSize) - 0.5) + 'px';
                    guard++;
                }
            }
            fitText();
            window.addEventListener('resize', fitText);
            field.addEventListener('input', fitText);

            // ค้นหาโรงเรียนด้วย keyword — ตอนนี้มีโรงเรียนเดียว แต่โครงสร้างพร้อมรองรับหลายโรงเรียนในอนาคต
            var schools = @json($schools ?? []);
            var results = document.getElementById('schoolResults');

            function renderResults(keyword) {
                var kw = (keyword || '').trim().toLowerCase();
                var matches = kw === '' ? schools : schools.filter(function (s) {
                    return s.toLowerCase().indexOf(kw) !== -1;
                });

                results.innerHTML = '';
                if (matches.length === 0) {
                    var empty = document.createElement('div');
                    empty.className = 'px-4 py-3 text-sm text-gray-400 text-center';
                    empty.textContent = 'ไม่พบโรงเรียนที่ตรงกับคำค้นหา';
                    results.appendChild(empty);
                } else {
                    matches.forEach(function (name) {
                        var item = document.createElement('div');
                        item.className = 'px-4 py-3 text-sm text-black text-center cursor-pointer hover:bg-blue-50';
                        item.textContent = name;
                        item.addEventListener('mousedown', function (e) {
                            e.preventDefault();
                            field.value = name;
                            fitText();
                            results.classList.add('hidden');
                        });
                        results.appendChild(item);
                    });
                }
                results.classList.remove('hidden');
            }

            field.addEventListener('focus', function () { renderResults(field.value); });
            field.addEventListener('input', function () { renderResults(field.value); });
            field.addEventListener('blur', function () {
                setTimeout(function () { results.classList.add('hidden'); }, 100);
            });
        })();
    </script>

</body>

</html>