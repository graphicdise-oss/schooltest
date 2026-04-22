{{-- resources/views/layouts/sidebar.blade.php --}}
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Tech</title>
    <link rel="icon" type="image/png" href="{{ asset('img/login_pic/graduation_cap.png') }}">

    {{-- Tailwind: ปิด preflight เฉพาะใน main เพื่อไม่ให้ทับ Bootstrap --}}
    <script>
        tailwind.config = {
            corePlugins: { preflight: false }
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Bootstrap CSS (โหลดหลัง Tailwind เพื่อให้ Bootstrap มีความสำคัญสูงกว่าใน main) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- ใส่ styles เพิ่มเติมจาก @push('styles') ได้เลย --}}
    @stack('styles')

    <style>
        :root {
            --main-bg: #bbf0ff;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background-color: var(--main-bg);
        }

        /* ป้องกัน Bootstrap รั่วเข้า sidebar */
        aside * {
            box-sizing: border-box;
        }

        .sidebar-bg {
            background: linear-gradient(180deg, #4479DA 0%, #8EB5FE 100%);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #9bd2e8;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #7cc0e8;
        }

        .active-menu-slide {
            will-change: transform, top, height;
            transform: translateZ(0);
        }

        .active-menu-slide::before {
            content: '';
            position: absolute;
            top: -30px;
            right: 0;
            width: 30px;
            height: 31px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 31'%3E%3Cpath d='M30,31 L30,0 C30,16.568 16.568,30 0,30 L0,31 Z' fill='%23bbf0ff'/%3E%3C/svg%3E");
            pointer-events: none;
        }

        .active-menu-slide::after {
            content: '';
            position: absolute;
            bottom: -30px;
            right: 0;
            width: 30px;
            height: 31px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 31'%3E%3Cpath d='M30,0 L30,31 C30,14.432 16.568,1 0,1 L0,0 Z' fill='%23bbf0ff'/%3E%3C/svg%3E");
            pointer-events: none;
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="w-[250px] sidebar-bg flex flex-col flex-shrink-0 z-20 text-white relative">

        {{-- โปรไฟล์ --}}
        <div class="flex flex-col items-center justify-center pt-10 pb-4">
            <div class="w-[90px] h-[90px] bg-[#d1d5db] rounded-full border-[3px] border-[#92b2f2] shadow-sm mb-3"></div>
            <h3 class="font-bold text-[16px] tracking-wider uppercase">
                {{ Auth::user()->first_name ?? 'USER' }}
                <span class="opacity-70">({{ Auth::user()->role ?? '' }})</span>
            </h3>
            <p class="text-[10px] opacity-80">{{ Auth::user()->email }}</p>
        </div>

        {{-- เมนูหลัก --}}
        <nav x-data="{
            activeItem: 'dashboard',
            hoverItem: null,
            indicatorTop: 0,
            indicatorHeight: 0,
            moveIndicator(el) {
                this.indicatorTop = el.offsetTop;
                this.indicatorHeight = el.offsetHeight;
            },
            resetIndicator() {
                let activeEl = document.getElementById('menu-' + this.activeItem);
                if (activeEl) {
                    this.indicatorTop = activeEl.offsetTop;
                    this.indicatorHeight = activeEl.offsetHeight;
                }
            },
            init() { setTimeout(() => { this.resetIndicator(); }, 50); }
        }" @mouseleave="hoverItem = null; resetIndicator()"
            class="flex-1 overflow-y-auto custom-scrollbar text-[18px] font-medium pl-4 relative">

            <div class="relative w-full pt-[30px] pb-[30px]">

                {{-- แถบ Active Indicator --}}
                <div class="absolute right-0 bg-[#bbf0ff] transition-all duration-300 ease-out z-0 rounded-l-[30px] active-menu-slide"
                    :style="`top: ${indicatorTop}px; height: ${indicatorHeight}px; width: 100%;`">
                </div>

                {{-- Dashboard --}}
                <a id="menu-dashboard" href="#" @mouseenter="moveIndicator($el); hoverItem = 'dashboard'"
                    :class="(hoverItem === 'dashboard' || (hoverItem === null && activeItem === 'dashboard')) ? 'text-[#5282e5] font-bold' : 'text-white hover:bg-white/10'"
                    class="flex items-center py-3 pl-6 transition-colors rounded-l-[30px] mb-2 block relative z-10">
                    <i class="fa-solid fa-border-all w-6 text-center mr-2"></i> Dashboard
                </a>

                {{-- ===== ข้อมูลบุคคล ===== --}}
                <div id="menu-personnel" x-data="{
                    myTop: 0, myArrow: 0,
                    calcPos(el) {
                        let rect = el.getBoundingClientRect();
                        let elCenterY = rect.top + (rect.height / 2);
                        let boxHeight = 450;
                        let boxTop = elCenterY - (boxHeight / 2);
                        if (boxTop < 80) boxTop = 80;
                        if (boxTop + boxHeight > window.innerHeight - 20) boxTop = window.innerHeight - boxHeight - 20;
                        this.myTop = boxTop;
                        this.myArrow = elCenterY - boxTop - 12;
                    }
                }" @mouseenter="moveIndicator($el); hoverItem = 'personnel'; calcPos($el)"
                    class="mb-2 relative z-10 block">

                    <a :class="(hoverItem === 'personnel' || (hoverItem === null && activeItem === 'personnel')) ? 'text-[#5282e5] font-bold' : 'text-white hover:bg-white/10'"
                        class="flex items-center py-3 pl-6 transition-colors w-full cursor-pointer rounded-l-[30px]">
                        <i class="fa-regular fa-user w-6 text-center mr-2"></i> ข้อมูลบุคคล
                    </a>

                    <div x-show="hoverItem === 'personnel'" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2" class="fixed left-[250px] z-[100]"
                        x-bind:style="`top: ${myTop}px`" style="display: none;">

                        <div class="absolute left-[-20px] top-[-200px] w-[40px] h-[1000px] bg-transparent"></div>
                        <div
                            class="ml-[15px] w-[880px] bg-white rounded-[2.5rem] shadow-[0_15px_40px_rgba(0,0,0,0.12)] p-8 flex gap-8 border border-gray-100 relative">
                            <div class="absolute left-[-12px] w-0 h-0 border-t-[12px] border-t-transparent border-b-[12px] border-b-transparent border-r-[14px] border-r-white drop-shadow-sm"
                                x-bind:style="`top: ${myArrow}px`"></div>

                            {{-- คอลัมน์ 1 --}}
                            <div class="flex-1">
                                <div class="mb-5">
                                    <h4 class="font-bold text-[#082b75] text-[16px] mb-2">นักเรียน</h4>
                                    <ul class="space-y-1.5 pl-2">
                                        <li><a href="{{ route('students.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ข้อมูลนักเรียน</a>
                                        </li>
                                        <li><a href="{{ route('personnels.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ข้อมูลผู้ปกครอง</a>
                                        </li>
                                        <li><a href="{{ route('student-types.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ประเภทนักเรียน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ส่งข้อมูล
                                                REGIS</a></li>
                                        <li><a href="{{ route('promotions.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ย้ายห้อง/เลื่อนห้อง/บันทึกจบ</a>
                                        </li>
                                        <a href="{{ route('student-alumni.index') }}"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ข้อมูลศิษย์เก่า</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">นำเข้าข้อมูลศิษย์เก่า</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">อนุมัติการกรอกข้อมูลของนักเรียน</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="mb-5">
                                    <h4 class="font-bold text-[#082b75] text-[16px] mb-2">รายงานนักเรียน</h4>
                                    <ul class="space-y-1.5 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">รายชื่อนักเรียน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">รายงานสถิตินักเรียน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">รายงานลาออก</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">รายงานชื่อนักเรียนใหม่</a>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#082b75] text-[16px] mb-2">ระบบบัตรขออนุญาต</h4>
                                    <ul class="space-y-1.5 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">บันทึกบัตรรายการขออนุญาต</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">รายงานประวัติการขออนุญาต</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            {{-- คอลัมน์ 2 --}}
                            <div class="flex-1">
                                <div class="mb-5">
                                    <h4 class="font-bold text-[#082b75] text-[16px] mb-2">บุคลากร - อาจารย์</h4>
                                    <ul class="space-y-1.5 pl-2">
                                        <li><a href="{{ route('personnels.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ข้อมูลบุคลากร
                                                - อาจารย์</a></li>
                                        <li><a href="{{ route('prefixes.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ตั้งค่าคำนำหน้า</a>
                                        </li>
                                        <li><a href="{{ route('personnel-types.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ตั้งค่าประเภทบุคลากร</a>
                                        </li>
                                        <li><a href="{{ route('positions.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ตั้งค่าตำแหน่ง</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ตั้งค่าแผนก</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ตั้งค่าประเภทการลา</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ตั้งค่าการลา</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ข้อมูลการลา</a>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#082b75] text-[16px] mb-2">รายงานบุคลากร - อาจารย์</h4>
                                    <ul class="space-y-1.5 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">รายงานการมาทำงานใหม่</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">รายชื่อพนักงาน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">รายงานการลา</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">รายงานการอบรม</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            {{-- คอลัมน์ 3 --}}
                            <div class="flex-1">
                                <div class="mb-5">
                                    <h4 class="font-bold text-[#082b75] text-[16px] mb-2">รับสมัครนักเรียนออนไลน์</h4>
                                    <ul class="space-y-1.5 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ข้อมูลนักเรียน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ตั้งค่าฟอร์มรับสมัคร</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ตั้งค่าคำชี้แจง/ระเบียบการ</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ตั้งค่าแผนหลักสูตร</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ตั้งค่าห้องสอบ</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">ตั้งค่าการรับสมัคร</a>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#082b75] text-[16px] mb-2">ระบบบัตรนักเรียน/บุคลากร</h4>
                                    <ul class="space-y-1.5 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">บันทึกบัตรนักเรียน</a>
                                        </li>
                                        <li><a href="{{ route('student-cards.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">
                                                พิมพ์บัตรนักเรียน</a>
                                        </li>

                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">พิมพ์บัตรสอบ</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">พิมพ์บัตรพนักงาน/อาจารย์</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">บันทึก/พิมพ์บัตรผู้ปกครอง</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline transition-colors">บัตรสำรอง</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== วิชาการ ===== --}}
                <div id="menu-academic" x-data="{
                    myTop: 0, myArrow: 0,
                    calcPos(el) {
                        let rect = el.getBoundingClientRect();
                        let elCenterY = rect.top + (rect.height / 2);
                        let boxHeight = 650;
                        let boxTop = elCenterY - (boxHeight / 2);
                        if (boxTop < 80) boxTop = 80;
                        if (boxTop + boxHeight > window.innerHeight - 20) boxTop = window.innerHeight - boxHeight - 20;
                        this.myTop = boxTop;
                        this.myArrow = elCenterY - boxTop - 12;
                    }
                }" @mouseenter="moveIndicator($el); hoverItem = 'academic'; calcPos($el)"
                    class="mb-2 relative z-10 block">

                    <a :class="(hoverItem === 'academic' || (hoverItem === null && activeItem === 'academic')) ? 'text-[#5282e5] font-bold' : 'text-white hover:bg-white/10'"
                        class="flex items-center py-3 pl-6 transition-colors w-full cursor-pointer rounded-l-[30px]">
                        <i class="fa-solid fa-book-open w-6 text-center mr-2"></i> วิชาการ
                    </a>

                    <div x-show="hoverItem === 'academic'" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2" class="fixed left-[250px] z-[100]"
                        x-bind:style="`top: ${myTop}px`" style="display: none;">

                        <div class="absolute left-[-20px] top-[-200px] w-[40px] h-[1000px] bg-transparent"></div>
                        <div
                            class="ml-[15px] w-[880px] bg-white rounded-[2.5rem] shadow-[0_15px_40px_rgba(0,0,0,0.12)] p-8 flex gap-8 border border-gray-100 relative">
                            <div class="absolute left-[-12px] w-0 h-0 border-t-[12px] border-t-transparent border-b-[12px] border-b-transparent border-r-[14px] border-r-white drop-shadow-sm"
                                x-bind:style="`top: ${myArrow}px`"></div>

                            <div class="flex-1">
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">จัดการหลักสูตร</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="{{ route('subjects.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">จัดการรายวิชา</a>
                                        </li>
                                        <li><a href="{{ route('curriculums.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">จัดการหลักสูตร</a>
                                        </li>
                                        <li><a href="{{ route('class-sections.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">จัดการห้องเรียน</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">ตารางสอน</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="{{ route('timetable.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">จัดการตารางสอน</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">ระบบตรวจข้อสอบ</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ระบบตรวจข้อสอบ</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">ระบบสอบออนไลน์</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ระบบสอบออนไลน์</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">ระบบ REGIS (สช.)</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ส่งข้อมูลเกรด
                                                REGIS</a></li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">รายงานวิชาการ</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานการเข้าเรียนรายวิชา</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานไม่มีสิทธิ์สอบ</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานผลการพัฒนาผู้เรียน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานการเรียนปรับพื้นฐาน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานคะแนนเฉลี่ย
                                                2 ภาคเรียน</a></li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานสรุปผลการเรียนรู้</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานนักเรียนไม่ผ่านเกณฑ์</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานนักเรียนสอบซ่อม-รายวิชา</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานนักเรียนสอบซ่อม-รายห้อง</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานจัดอันดับคะแนนรายวิชา</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงาน
                                                SAR</a></li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ใบสำรวจผลการเรียน</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">ห้องเรียนออนไลน์</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">จัดการห้องเรียนออนไลน์</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">เอกสาร ปพ./รบ.</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ระเบียนแสดงผลการเรียน
                                                (ปพ.1)</a></li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ใบประกาศนียบัตร
                                                (ปพ.2)</a></li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">แบบรายงานผู้สำเร็จการศึกษา
                                                (ปพ.3)</a></li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">แบบบันทึกผลการพัฒนาคุณภาพผู้เรียน
                                                (ปพ.5)</a></li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ใบรับรองผลการเรียน</a>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">ระบบอนุบาล</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">บันทึกเกณฑ์น้ำหนักส่วนสูง</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">บันทึกพัฒนาการนักเรียนปฐมวัย</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ตั้งค่าหลักสูตรพัฒนาการนักเรียนปฐมวัย</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">บันทึกคะแนน</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="{{ route('scores.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">บันทึกคะแนนผลการเรียน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">บันทึกความคิดเห็นอาจารย์รายวิชา</a>
                                        </li>
                                        <li><a href="{{ route('grades.index') }}"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">แก้ไขเกรด</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">นำเข้าเกรดสำหรับปพ.1/รบ.1</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">นำเข้า
                                                ONET</a></li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">เทียบโอน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ตั้งค่าระบบบันทึกคะแนน</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== กิจการนักเรียน ===== --}}
                <div id="menu-student_affairs" x-data="{
                    myTop: 0, myArrow: 0,
                    calcPos(el) {
                        let rect = el.getBoundingClientRect();
                        let elCenterY = rect.top + (rect.height / 2);
                        let boxHeight = 450;
                        let boxTop = elCenterY - (boxHeight / 2);
                        if (boxTop < 80) boxTop = 80;
                        if (boxTop + boxHeight > window.innerHeight - 20) boxTop = window.innerHeight - boxHeight - 20;
                        this.myTop = boxTop;
                        this.myArrow = elCenterY - boxTop - 12;
                    }
                }" @mouseenter="moveIndicator($el); hoverItem = 'student_affairs'; calcPos($el)"
                    class="mb-2 relative z-10 block">

                    <a :class="(hoverItem === 'student_affairs' || (hoverItem === null && activeItem === 'student_affairs')) ? 'text-[#5282e5] font-bold' : 'text-white hover:bg-white/10'"
                        class="flex items-center py-3 pl-6 transition-colors w-full cursor-pointer rounded-l-[30px]">
                        <i class="fa-solid fa-graduation-cap w-6 text-center mr-2"></i> กิจการนักเรียน
                    </a>

                    <div x-show="hoverItem === 'student_affairs'" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2" class="fixed left-[250px] z-[100]"
                        x-bind:style="`top: ${myTop}px`" style="display: none;">

                        <div class="absolute left-[-20px] top-[-200px] w-[40px] h-[1000px] bg-transparent"></div>
                        <div
                            class="ml-[15px] w-[880px] bg-white rounded-[2.5rem] shadow-[0_15px_40px_rgba(0,0,0,0.12)] p-8 flex gap-8 border border-gray-100 relative">
                            <div class="absolute left-[-12px] w-0 h-0 border-t-[12px] border-t-transparent border-b-[12px] border-b-transparent border-r-[14px] border-r-white drop-shadow-sm"
                                x-bind:style="`top: ${myArrow}px`"></div>

                            <div class="flex-1">
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">เช็คชื่อ/ลา</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ปรับสถานะการมาเรียน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">จัดการกิจกรรม</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ปรับสถานะการทำกิจกรรม</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ข้อมูลการลา</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ตั้งค่าประเภทการลา</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ตั้งค่าการลา</a>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">แบบประเมิน SDQ</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">สถานะประเมินรายคน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">สรุปสถานะการประเมิน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">สรุปผลการประเมิน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานสรุปผลรายบุคคล</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานสรุปผลรวม</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">ระบบความประพฤติ</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">เพิ่มข้อมูลคะแนนความประพฤติ</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ตัดคะแนนความประพฤติ</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ตั้งค่าการตัดคะแนนความประพฤติ</a>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">บันทึกการเยี่ยมบ้าน</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">บันทึกการเยี่ยมบ้านรายบุคคล</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">สรุปสถานะการเยี่ยมบ้าน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">สรุปผลการเยี่ยมบ้าน</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div>
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">รายงานกิจการนักเรียน</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานการมาโรงเรียน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานการเช็คชื่อรายคาบเรียน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานการมาโรงเรียนสาย</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานคะแนนพฤติกรรม</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานการตัดคะแนนจากคุณครูที่ปรึกษา</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== บริหารทั่วไป ===== --}}
                <div id="menu-general_admin" x-data="{
                    myTop: 0, myArrow: 0,
                    calcPos(el) {
                        let rect = el.getBoundingClientRect();
                        let elCenterY = rect.top + (rect.height / 2);
                        let boxHeight = 550;
                        let boxTop = elCenterY - (boxHeight / 2);
                        if (boxTop < 80) boxTop = 80;
                        if (boxTop + boxHeight > window.innerHeight - 20) boxTop = window.innerHeight - boxHeight - 20;
                        this.myTop = boxTop;
                        this.myArrow = elCenterY - boxTop - 12;
                    }
                }" @mouseenter="moveIndicator($el); hoverItem = 'general_admin'; calcPos($el)"
                    class="mb-2 relative z-10 block">

                    <a :class="(hoverItem === 'general_admin' || (hoverItem === null && activeItem === 'general_admin')) ? 'text-[#5282e5] font-bold' : 'text-white hover:bg-white/10'"
                        class="flex items-center py-3 pl-6 transition-colors w-full cursor-pointer rounded-l-[30px]">
                        <i class="fa-solid fa-users w-6 text-center mr-2"></i> บริหารทั่วไป
                    </a>

                    <div x-show="hoverItem === 'general_admin'" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2" class="fixed left-[250px] z-[100]"
                        x-bind:style="`top: ${myTop}px`" style="display: none;">

                        <div class="absolute left-[-20px] top-[-200px] w-[40px] h-[1000px] bg-transparent"></div>
                        <div
                            class="ml-[15px] w-[880px] bg-white rounded-[2.5rem] shadow-[0_15px_40px_rgba(0,0,0,0.12)] p-8 flex gap-8 border border-gray-100 relative">
                            <div class="absolute left-[-12px] w-0 h-0 border-t-[12px] border-t-transparent border-b-[12px] border-b-transparent border-r-[14px] border-r-white drop-shadow-sm"
                                x-bind:style="`top: ${myArrow}px`"></div>
                            <div class="flex-1">
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">ประชาสัมพันธ์</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ประชาสัมพันธ์</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานประชาสัมพันธ์</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ตั้งค่ากลุ่มประชาสัมพันธ์</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">ระบบรับนักเรียน</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">หน้าแสดงผล</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานรับนักเรียน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ตั้งค่าระบบรับนักเรียน</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">ห้องสมุด</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ลงชื่อเข้าใช้ห้องสมุด</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">จัดการห้องสมุด</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ตั้งค่าหมวดหมู่หนังสือ</a>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#082b75] text-[18px] mb-1">ระบบ School Bus</h4>
                                    <ul class="space-y-1 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ภาพรวม</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">การแจ้งเหตุ</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ตั้งค่าการเดินทาง</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-[#082b75] text-[18px] mb-1">รายงานห้องสมุด</h4>
                                <ul class="space-y-1 pl-2">
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานข้อมูลหนังสือ</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานค้างส่ง</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานยืม-คืนหนังสือ</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานแจ้งชำรุดเสียหาย</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานผู้ยืมหนังสือมากที่สุด</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานเข้าใช้ห้องสมุด
                                            (สถิติ)</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== บัญชี/การเงิน ===== --}}
                <div id="menu-accounting" x-data="{
                    myTop: 0, myArrow: 0,
                    calcPos(el) {
                        let rect = el.getBoundingClientRect();
                        let elCenterY = rect.top + (rect.height / 2);
                        let boxHeight = 560;
                        let boxTop = elCenterY - (boxHeight / 2);
                        if (boxTop < 80) boxTop = 80;
                        if (boxTop + boxHeight > window.innerHeight - 20) boxTop = window.innerHeight - boxHeight - 20;
                        this.myTop = boxTop;
                        this.myArrow = elCenterY - boxTop - 12;
                    }
                }" @mouseenter="moveIndicator($el); hoverItem = 'accounting'; calcPos($el)"
                    class="mb-2 relative z-10 block">

                    <a :class="(hoverItem === 'accounting' || (hoverItem === null && activeItem === 'accounting')) ? 'text-[#5282e5] font-bold' : 'text-white hover:bg-white/10'"
                        class="flex items-center py-3 pl-6 transition-colors w-full cursor-pointer rounded-l-[30px]">
                        <i class="fa-solid fa-coins w-6 text-center mr-2"></i> บัญชี/การเงิน
                    </a>

                    <div x-show="hoverItem === 'accounting'" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2" class="fixed left-[250px] z-[100]"
                        x-bind:style="`top: ${myTop}px`" style="display: none;">

                        <div class="absolute left-[-20px] top-[-200px] w-[40px] h-[1000px] bg-transparent"></div>
                        <div
                            class="ml-[15px] w-[920px] bg-white rounded-[2.5rem] shadow-[0_15px_40px_rgba(0,0,0,0.12)] p-8 flex gap-8 border border-gray-100 relative">
                            <div class="absolute left-[-12px] w-0 h-0 border-t-[12px] border-t-transparent border-b-[12px] border-b-transparent border-r-[14px] border-r-white drop-shadow-sm"
                                x-bind:style="`top: ${myArrow}px`"></div>
                            <div class="flex-1">
                                <h4 class="font-bold text-[#082b75] text-[16px] mb-2">ระบบบัญชีรายรับ</h4>
                                <ul class="space-y-1.5 pl-2">
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายการชำระเงิน</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">กลุ่มรายการชำระเงิน</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">สร้างใบแจ้งหนี้</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ข้อมูลใบแจ้งหนี้นักเรียนใหม่</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">นำเข้าใบแจ้งหนี้</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ใบลดหนี้
                                            / ใบเพิ่มหนี้</a></li>
                                </ul>
                            </div>
                            <div class="flex-1">
                                <div class="mb-4">
                                    <h4 class="font-bold text-[#082b75] text-[16px] mb-2">ระบบบัญชีรายจ่าย</h4>
                                    <ul class="space-y-1.5 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ใบสั่งซื้อ</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">บันทึกรายจ่าย</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">บันทึกสินทรัพย์</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">สินทรัพย์
                                                / สินค้า</a></li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#082b75] text-[16px] mb-2">ระบบเงินเดือน</h4>
                                    <ul class="space-y-1.5 pl-2">
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">ข้อมูลทำเงินเดือน</a>
                                        </li>
                                        <li><a href="#"
                                                class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานเงินเพิ่ม/เงินหัก</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-[#082b75] text-[16px] mb-2">รายงานบัญชี</h4>
                                <ul class="space-y-1.5 pl-2">
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานรายรับ</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานสรุปยอดค้างชำระ</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานลูกหนี้รายห้อง</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานสรุปรายรับ</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">รายงานยกเลิกใบเสร็จ</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">บัญชีรายวัน</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">บัญชีแยกประเภท</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">งบทดลอง</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">งบแสดงฐานะการเงิน</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-[#4b7ce3] text-[16px] hover:text-[#082b75] hover:underline">งบกำไรขาดทุน</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== ร้านค้า/สหกรณ์ ===== --}}
                <a id="menu-store" href="#" @mouseenter="moveIndicator($el); hoverItem = 'store'"
                    :class="(hoverItem === 'store' || (hoverItem === null && activeItem === 'store')) ? 'text-[#5282e5] font-bold' : 'text-white hover:bg-white/10'"
                    class="flex items-center py-3 pl-6 transition-colors rounded-l-[30px] mb-2 block relative z-10">
                    <i class="fa-solid fa-store w-6 text-center mr-2"></i> ร้านค้า/สหกรณ์
                </a>

                {{-- ===== รายงาน ===== --}}
                <a id="menu-reports" href="#" @mouseenter="moveIndicator($el); hoverItem = 'reports'"
                    :class="(hoverItem === 'reports' || (hoverItem === null && activeItem === 'reports')) ? 'text-[#5282e5] font-bold' : 'text-white hover:bg-white/10'"
                    class="flex items-center py-3 pl-6 transition-colors rounded-l-[30px] mb-2 block relative z-10">
                    <i class="fa-regular fa-file-lines w-6 text-center mr-2"></i> รายงาน
                </a>

                {{-- ===== ตั้งค่าเริ่มต้น ===== --}}
                <a id="menu-settings" href="#" @mouseenter="moveIndicator($el); hoverItem = 'settings'"
                    :class="(hoverItem === 'settings' || (hoverItem === null && activeItem === 'settings')) ? 'text-[#5282e5] font-bold' : 'text-white hover:bg-white/10'"
                    class="flex items-center py-3 pl-6 transition-colors rounded-l-[30px] mb-2 block relative z-10">
                    <i class="fa-solid fa-gear w-6 text-center mr-2"></i> ตั้งค่าเริ่มต้น
                </a>

                {{-- ===== ดาวน์โหลด ===== --}}
                <a id="menu-downloads" href="#" @mouseenter="moveIndicator($el); hoverItem = 'downloads'"
                    :class="(hoverItem === 'downloads' || (hoverItem === null && activeItem === 'downloads')) ? 'text-[#5282e5] font-bold' : 'text-white hover:bg-white/10'"
                    class="flex items-center py-3 pl-6 transition-colors rounded-l-[30px] mb-2 block relative z-10">
                    <i class="fa-solid fa-download w-6 text-center mr-2"></i> ดาวน์โหลด
                </a>

                {{-- ===== ถังขยะ ===== --}}
                <a id="menu-trash" href="#" @mouseenter="moveIndicator($el); hoverItem = 'trash'"
                    :class="(hoverItem === 'trash' || (hoverItem === null && activeItem === 'trash')) ? 'text-[#5282e5] font-bold' : 'text-white hover:bg-white/10'"
                    class="flex items-center py-3 pl-6 transition-colors rounded-l-[30px] mb-2 block relative z-10">
                    <i class="fa-solid fa-trash-can w-6 text-center mr-2"></i> ถังขยะ
                </a>

            </div>
        </nav>

        {{-- ปุ่มออกจากระบบ --}}
        <div class="mt-auto pb-6 pl-4 relative z-10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center py-3 pl-6 hover:bg-white/10 rounded-l-[30px] transition-colors w-full text-[18px] font-medium text-left text-white">
                    <i class="fa-solid fa-arrow-right-from-bracket scale-x-[-1] w-6 text-center mr-2"></i>
                    ออกจากระบบ
                </button>
            </form>
        </div>

    </aside>

    {{-- ===== MAIN CONTENT AREA ===== --}}
    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        {{-- Header --}}
        <header class="h-[70px] bg-white flex items-center justify-between px-6 z-10 shadow-sm">
            <div class="flex items-center gap-2 text-[#3b5cb4] font-bold text-[18px] tracking-wide">
                <i class="fa-solid fa-graduation-cap text-[#3b5cb4] text-xl"></i> SCHOOL TECH
            </div>
            <div class="flex items-center gap-4">
                <button class="text-gray-700 hover:text-orange-500 transition">
                    <i class="fa-regular fa-bell text-[22px]"></i>
                </button>
                <span class="text-sm text-gray-600 font-medium">{{ Auth::user()->first_name ?? '' }}</span>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto custom-scrollbar">
            @yield('content')
        </main>

    </div>

    @stack('scripts')
</body>

</html>