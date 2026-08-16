@extends('layouts.sidebar')

@push('styles')
    <style>
        body {
            background-color: #f4f6f9;
        }

        .breadcrumb-custom a {
            color: #00bcd4;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .breadcrumb-custom a:hover {
            text-decoration: underline;
        }

        .breadcrumb-custom i {
            color: #888;
            margin: 0 8px;
            font-size: 0.8rem;
        }

        .floating-card {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 30px 20px 20px;
            position: relative;
            margin-top: 50px;
            border: none;
        }

        .floating-icon {
            position: absolute;
            top: -25px;
            left: 20px;
            width: 70px;
            height: 70px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .bg-cyan-custom {
            background-color: #00bcd4;
        }

        .bg-orange-custom {
            background-color: #ff9800;
        }

        .card-header-text {
            margin-left: 90px;
            font-size: 1.25rem;
            color: #555;
            margin-top: -10px;
        }

        .input-material {
            border: none;
            border-bottom: 1px solid #ccc;
            border-radius: 0;
            padding-left: 0;
            box-shadow: none !important;
            background-color: transparent;
        }

        .input-material:focus {
            border-bottom-color: #00bcd4;
            border-bottom-width: 2px;
        }

        .table>thead>tr>th {
            border-bottom: 2px solid #eee;
            color: #333;
            font-weight: 500;
            white-space: nowrap;
            padding-bottom: 15px;
        }

        .table>tbody>tr>td {
            vertical-align: middle;
            color: #555;
            border-bottom: 1px solid #f5f5f5;
            padding: 15px 10px;
        }

        .text-purple {
            color: #9b59b6;
            font-size: 1.1rem;
        }

        .text-danger-custom {
            color: #e74c3c;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .btn-manage {
            background-color: #4caf50;
            color: white;
            border: none;
            font-size: 0.95rem;
        }

        .btn-manage:hover {
            background-color: #45a049;
            color: white;
        }

        /* ===== Modal Overlay ===== */
        .pwd-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(20, 30, 40, 0.55);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .pwd-overlay.active {
            display: flex;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* ===== Modal Card ===== */
        .pwd-modal {
            background: #fff;
            border-radius: 14px;
            width: 440px;
            max-width: 92vw;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: slideUp 0.25s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pwd-modal-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 24px;
            background: #00bcd4;
            color: #fff;
        }

        .pwd-modal-header .pwd-header-icon {
            width: 44px;
            height: 44px;
            min-width: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .pwd-modal-header h5 {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
        }

        .pwd-modal-header p {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.85);
            margin: 2px 0 0;
        }

        .pwd-modal-body {
            padding: 22px 24px 6px;
        }

        .pwd-field {
            margin-bottom: 18px;
        }

        .pwd-field-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #444;
            margin-bottom: 6px;
        }

        .pwd-field-label i {
            color: #00bcd4;
        }

        .pwd-field-input {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pwd-field-input input,
        .pwd-field-input select {
            flex: 1;
            width: 100%;
            height: 42px;
            border: 1.5px solid #e3e7ea;
            border-radius: 8px;
            padding: 0 14px;
            font-size: 0.88rem;
            font-family: inherit;
            color: #333;
            outline: none;
            background: #fafbfc;
            transition: border-color 0.2s, background 0.2s;
            box-sizing: border-box;
        }

        .pwd-field-input input:focus,
        .pwd-field-input select:focus {
            border-color: #00bcd4;
            background: #fff;
        }

        .pwd-field-hint {
            font-size: 0.76rem;
            color: #999;
            margin-top: 5px;
        }

        /* ปุ่มดูรหัส */
        .btn-toggle-pwd {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 8px;
            border: 1.5px solid #e3e7ea;
            background: #fafbfc;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #888;
            transition: all 0.15s;
        }

        .btn-toggle-pwd:hover {
            border-color: #00bcd4;
            color: #00bcd4;
        }

        /* ปุ่มเปลี่ยนรหัส */
        .btn-change-pwd {
            border: none;
            background: none;
            color: #ff9800;
            padding: 0;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-family: inherit;
        }

        .btn-change-pwd:hover {
            text-decoration: underline;
        }

        /* Footer ปุ่ม */
        .pwd-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px 22px;
        }

        .btn-pwd-save,
        .btn-pwd-cancel {
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-size: 0.87rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: opacity 0.15s;
        }

        .btn-pwd-save {
            background: #00bcd4;
            color: #fff;
        }

        .btn-pwd-save:hover {
            opacity: 0.9;
        }

        .btn-pwd-cancel {
            background: #f1f3f5;
            color: #666;
        }

        .btn-pwd-cancel:hover {
            background: #e6e9eb;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4 py-3">

        {{-- Breadcrumb --}}
        <div class="breadcrumb-custom mb-2">
            <a href="#">ข้อมูลบุคคล</a>
            <i class="bi bi-chevron-right"></i>
            <a href="#">บุคลากร - อาจารย์</a>
            <i class="bi bi-chevron-right"></i>
            <a href="#" style="text-decoration: underline;">ข้อมูลบุคลากร - อาจารย์</a>
        </div>

        {{-- ส่วนค้นหา --}}
        <div class="floating-card">
            <div class="floating-icon bg-cyan-custom"><i class="bi bi-search"></i></div>
            <div class="card-header-text mb-4">ค้นหา</div>
            <form action="{{ route('personnels.index') }}" method="GET">
                <div class="row g-4 px-md-4 mt-2">
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label class="form-label fw-bold text-dark mb-0">ประเภทบุคลากร</label>
                        <select name="type" class="form-select input-material text-muted mt-2">
                            <option value="">เลือกประเภทบุคลากร</option>
                            @foreach(['ผู้บริหาร', 'ครู', 'ครูพิเศษ', 'บุคลากร', 'ลูกจ้าง'] as $t)
                                <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label class="form-label fw-bold text-dark mb-0">แผนก</label>
                        <select name="department" class="form-select input-material text-muted mt-2">
                            <option value="">ทั้งหมด</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label class="form-label fw-bold text-dark mb-0">ระดับ</label>
                        <select id="personnelSectionLevel" class="form-select input-material text-muted mt-2" onchange="filterPersonnelSectionsByLevel()">
                            <option value="">ทุกระดับชั้น</option>
                            @foreach ($sections->pluck('level')->filter()->unique('level_id')->sortBy('sort_order') as $lv)
                                <option value="{{ $lv->level_id }}">{{ $lv->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label class="form-label fw-bold text-dark mb-0">ห้อง (ครูประจำชั้น)</label>
                        <select name="section_id" id="personnelSectionSelect" class="form-select input-material text-muted mt-2">
                            <option value="">ทั้งหมด</option>
                            @foreach ($sections as $sec)
                                <option value="{{ $sec->section_id }}" data-level="{{ $sec->level_id }}" {{ request('section_id') == $sec->section_id ? 'selected' : '' }}>
                                    {{ $sec->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label class="form-label fw-bold text-dark mb-0">รหัสบุคลากร</label>
                        <input type="text" name="employee_code" class="form-control input-material text-muted mt-2"
                            placeholder="รหัสบุคลากร" value="{{ request('employee_code') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label class="form-label fw-bold text-dark mb-0">ชื่อ - นามสกุล</label>
                        <input type="text" name="search" class="form-control input-material text-muted mt-2"
                            placeholder="ชื่อ - นามสกุล" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="text-center mb-2 mt-4">
                    <button type="submit" class="btn bg-cyan-custom text-white px-4 py-2 rounded-1">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                </div>
            </form>
        </div>

        {{-- ตารางข้อมูลบุคลากร --}}
        <div class="floating-card mb-5">
            <div class="floating-icon bg-orange-custom"><i class="bi bi-person-fill"></i></div>
            <div class="d-flex justify-content-between align-items-center mb-4 card-header-text">
                <div>ข้อมูลบุคลากร</div>
                <div class="d-flex gap-2" style="margin-top: -15px;">
                    <button type="button" class="btn px-3 py-2 rounded-1" style="background:#495057; color:#fff;"
                        onclick="document.getElementById('personnelSchoolInfoOverlay').classList.add('active')"
                        title="ตั้งค่าข้อมูลโรงเรียนที่จะแสดงบนแบบฟอร์ม">
                        <i class="bi bi-gear"></i>
                    </button>
                    <a href="{{ route('personnels.import-template') }}" class="btn btn-secondary px-3 py-2 rounded-1">
                        <i class="bi bi-download me-1"></i> ดาวน์โหลดแบบฟอร์ม Excel
                    </a>
                    <button type="button" class="btn btn-success px-3 py-2 rounded-1"
                        onclick="document.getElementById('personnelImportOverlay').classList.add('active')">
                        <i class="bi bi-upload me-1"></i> นำเข้าจาก Excel
                    </button>
                    <a href="{{ route('personnels.create') }}" class="btn btn-manage px-3 py-2 rounded-1">
                        <i class="bi bi-person-plus me-1"></i> เพิ่มข้อมูลบุคลากร
                    </a>
                </div>
            </div>

            <div class="table-responsive px-2 mt-4">
                <table class="table table-hover text-center align-middle">
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>ประเภทบุคลากร</th>
                            <th>รหัสพนักงาน<br><small class="text-muted fw-normal">(Username)</small></th>
                            <th>คำนำหน้า</th>
                            <th>ชื่อ</th>
                            <th>นามสกุล</th>
                            <th>เบอร์โทร</th>
                            <th>วันเกิด</th>
                            <th>สถานะ</th>
                            <th>จัดการข้อมูล</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($personnels as $index => $person)
                            <tr>
                                <td>{{ $personnels->firstItem() + $index }}.</td>
                                <td>{{ $person->personnel_type ?? '-' }}</td>
                                <td>{{ $person->employee_code ?? '-' }}</td>
                                <td>{{ $person->thai_prefix ?? '' }}</td>
                                <td>{{ $person->thai_firstname ?? '-' }}</td>
                                <td>{{ $person->thai_lastname ?? '-' }}</td>
                                <td>{{ $person->phone ?? '-' }}</td>
                                <td>{{ $person->date_of_birth ? \Carbon\Carbon::parse($person->date_of_birth)->addYears(543)->format('d/m/Y') : '-' }}
                                </td>
                                @php
                                    $statusValue = $person->status ?? 'ปฏิบัติงาน';
                                    $statusStyle = match (true) {
                                        str_contains($statusValue, 'ปฏิบัติงาน') => ['bg' => '#e6f7ee', 'text' => '#1e8a4c'],
                                        str_contains($statusValue, 'ลาออก'), str_contains($statusValue, 'ไล่ออก') => ['bg' => '#fdecea', 'text' => '#c0392b'],
                                        str_contains($statusValue, 'พัก') => ['bg' => '#fff4e5', 'text' => '#b8720a'],
                                        str_contains($statusValue, 'เกษียณ') => ['bg' => '#eef0f2', 'text' => '#6c757d'],
                                        default => ['bg' => '#eef0f2', 'text' => '#495057'],
                                    };
                                @endphp
                                <td>
                                    <span style="display:inline-block; padding:4px 14px; border-radius:20px; font-size:0.85rem; font-weight:600; white-space:nowrap; background:{{ $statusStyle['bg'] }}; color:{{ $statusStyle['text'] }};">
                                        {{ $statusValue }}
                                    </span>
                                </td>
                                <td>
                                    {{-- แก้ไข --}}
                                    <a href="{{ route('personnels.edit', $person->personnel_id) }}"
                                        class="text-purple mx-1 text-decoration-none" title="แก้ไข">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    {{-- กุญแจ → เปิด Modal --}}
                                    <a href="javascript:void(0)" class="text-purple mx-1 text-decoration-none"
                                        title="จัดการรหัสผ่าน"
                                        onclick="openPwdModal({{ $person->personnel_id }}, '{{ $person->employee_code ?? '' }}', '{{ $person->role ?? '' }}')">
                                        <i class="bi bi-key-fill" style="transform: rotate(135deg); display: inline-block;"></i>
                                    </a>

                                    {{-- ลบ --}}
                                    <form action="{{ route('personnels.destroy', $person->personnel_id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('ยืนยันการลบข้อมูลบุคลากรท่านนี้?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-danger-custom mx-1 p-0" title="ลบ"
                                            style="background:none; border:none;">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                                    ยังไม่มีข้อมูลบุคลากรในระบบ
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end px-3">
                {{ $personnels->withQueryString()->links() }}
            </div>
        </div>
    </div>

    {{-- ===== Modal จัดการรหัสผ่าน (กลางจอ) ===== --}}
    <div class="pwd-overlay" id="pwdOverlay" onclick="closePwdModal(event)">
        <div class="pwd-modal" onclick="event.stopPropagation()">
            <div class="pwd-modal-header">
                <div class="pwd-header-icon"><i class="bi bi-shield-lock"></i></div>
                <div>
                    <h5>จัดการบัญชีผู้ใช้งาน</h5>
                    <p>ตั้งรหัสผ่านและสิทธิ์การเข้าใช้ระบบ</p>
                </div>
            </div>

            <form id="pwdForm" method="POST">
                @csrf
                @method('PUT')

                <div class="pwd-modal-body">
                    {{-- ชื่อผู้ใช้ --}}
                    <div class="pwd-field">
                        <div class="pwd-field-label"><i class="bi bi-person-badge"></i> รหัสพนักงาน (ใช้ล็อกอิน)</div>
                        <div class="pwd-field-input">
                            <input type="text" name="employee_code" id="modalUsername" placeholder="รหัสพนักงาน">
                        </div>
                    </div>

                    {{-- รหัสผ่าน --}}
                    <div class="pwd-field">
                        <div class="pwd-field-label"><i class="bi bi-key"></i> รหัสผ่าน</div>
                        <div class="pwd-field-input">
                            <input type="password" name="password" id="modalPassword" placeholder="กรอกรหัสผ่านใหม่">
                            <button type="button" class="btn-toggle-pwd" onclick="togglePassword()" title="ดู/ซ่อนรหัสผ่าน">
                                <i class="bi bi-eye" id="togglePwdIcon"></i>
                            </button>
                        </div>
                        <div class="pwd-field-hint">
                            <button type="button" class="btn-change-pwd" onclick="clearPassword()">
                                <i class="bi bi-arrow-repeat"></i> ล้างช่องเพื่อตั้งรหัสผ่านใหม่
                            </button>
                        </div>
                    </div>

                    {{-- บทบาท --}}
                    <div class="pwd-field">
                        <div class="pwd-field-label"><i class="bi bi-shield-check"></i> บทบาท (Role)</div>
                        <div class="pwd-field-input">
                            <select name="role" id="modalRole">
                                <option value="">-- เลือก --</option>
                                @if(auth()->user()->isSuperAdmin())
                                    <option value="superadmin">superadmin (ผู้ดูแลสูงสุด)</option>
                                @endif
                                <option value="admin">admin (ผู้ดูแลระบบ)</option>
                                <option value="user">user (ผู้ใช้ทั่วไป)</option>
                            </select>
                        </div>
                        <div class="pwd-field-hint">
                            สิทธิ์เข้าถึงเมนูของ "user" กำหนดแยกได้ที่ <strong>ประเภทบุคลากร</strong> ของแต่ละคน
                        </div>
                    </div>
                </div>

                <div class="pwd-modal-footer">
                    <button type="button" class="btn-pwd-cancel" onclick="closePwdModal()">ยกเลิก</button>
                    <button type="submit" class="btn-pwd-save">
                        <i class="bi bi-check-lg"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Modal ตั้งค่าข้อมูลโรงเรียน (แสดงบนหัวแบบฟอร์ม Excel — ใช้ร่วมกับแบบฟอร์มนักเรียน) ===== --}}
    <div id="personnelSchoolInfoOverlay" onclick="if(event.target===this)this.classList.remove('active')"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:2000; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:12px; width:100%; max-width:480px; padding:24px; margin:16px;">
            <h5 style="margin-bottom:4px;"><i class="bi bi-gear"></i> ตั้งค่าข้อมูลโรงเรียน</h5>
            <p style="font-size:0.85rem; color:#777; margin-bottom:16px;">
                ข้อมูลนี้จะแสดงที่หัวแบบฟอร์ม Excel ทุกครั้งที่กด "ดาวน์โหลดแบบฟอร์ม Excel" (ใช้ค่าเดียวกับแบบฟอร์มนักเรียน)
            </p>
            <form method="POST" action="{{ route('students.school-info') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:10px;">
                    <label style="font-size:0.83rem;font-weight:600;color:#555;display:block;margin-bottom:4px;">ตราโรงเรียน</label>
                    @if($schoolInfo->logo_path)
                        <div style="margin-bottom:6px;">
                            <img src="{{ asset('storage/' . $schoolInfo->logo_path) }}" alt="ตราโรงเรียน" style="height:50px;">
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/*" class="form-control">
                </div>
                <div style="margin-bottom:10px;">
                    <label style="font-size:0.83rem;font-weight:600;color:#555;display:block;margin-bottom:4px;">ชื่อโรงเรียน</label>
                    <input type="text" name="school_name" value="{{ old('school_name', $schoolInfo->school_name) }}" class="form-control">
                </div>
                <div style="margin-bottom:10px;">
                    <label style="font-size:0.83rem;font-weight:600;color:#555;display:block;margin-bottom:4px;">โทรศัพท์</label>
                    <input type="text" name="phone" value="{{ old('phone', $schoolInfo->phone) }}" class="form-control">
                </div>
                <div style="margin-bottom:10px;">
                    <label style="font-size:0.83rem;font-weight:600;color:#555;display:block;margin-bottom:4px;">โทรสาร</label>
                    <input type="text" name="fax" value="{{ old('fax', $schoolInfo->fax) }}" class="form-control">
                </div>
                <div style="margin-bottom:10px;">
                    <label style="font-size:0.83rem;font-weight:600;color:#555;display:block;margin-bottom:4px;">เว็บไซต์</label>
                    <input type="text" name="website" value="{{ old('website', $schoolInfo->website) }}" class="form-control">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:0.83rem;font-weight:600;color:#555;display:block;margin-bottom:4px;">อีเมล์</label>
                    <input type="text" name="email" value="{{ old('email', $schoolInfo->email) }}" class="form-control">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" onclick="document.getElementById('personnelSchoolInfoOverlay').classList.remove('active')" style="padding:8px 16px; border:1px solid #ccc; border-radius:6px; background:#fff;">ยกเลิก</button>
                    <button type="submit" style="padding:8px 16px; border:none; border-radius:6px; background:#0d6efd; color:#fff;">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Modal นำเข้าข้อมูลบุคลากรจาก Excel ===== --}}
    <div id="personnelImportOverlay" onclick="if(event.target===this)closePersonnelImportModal()"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:2000; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:12px; width:100%; max-width:480px; padding:24px; margin:16px;">
            <h5 style="margin-bottom:4px;"><i class="bi bi-upload"></i> นำเข้าข้อมูลบุคลากรจาก Excel</h5>
            <p style="font-size:0.85rem; color:#777; margin-bottom:16px;">
                ใช้ไฟล์ที่ดาวน์โหลดจากปุ่ม "ดาวน์โหลดแบบฟอร์ม Excel" เท่านั้น (ตำแหน่งคอลัมน์ต้องตรงกัน)
            </p>
            <form method="POST" action="{{ route('personnels.import') }}" enctype="multipart/form-data" onsubmit="submitPersonnelImportForm()">
                @csrf
                <div style="margin-bottom:14px;">
                    <input type="file" name="file" accept=".xlsx" required class="form-control">
                </div>
                <div style="margin-bottom:16px;">
                    <label><input type="checkbox" name="dry_run" value="1"> ทดสอบก่อน (dry-run) — ยังไม่บันทึกข้อมูลจริง</label>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" onclick="closePersonnelImportModal()" style="padding:8px 16px; border:1px solid #ccc; border-radius:6px; background:#fff;">ยกเลิก</button>
                    <button type="submit" id="personnelImportSubmitBtn" style="padding:8px 16px; border:none; border-radius:6px; background:#198754; color:#fff;">เริ่มนำเข้า</button>
                </div>
            </form>
        </div>
    </div>

    @if (session('import_output'))
        <div id="personnelImportResultOverlay"
            style="position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:2000; display:flex; align-items:center; justify-content:center;">
            <div style="background:#fff; border-radius:12px; width:100%; max-width:700px; max-height:80vh; padding:24px; margin:16px; display:flex; flex-direction:column;">
                <h5 style="margin-bottom:12px;"><i class="bi bi-clipboard-check"></i> ผลการนำเข้าข้อมูล</h5>
                <pre style="background:#f5f5f5; padding:12px; border-radius:8px; overflow:auto; flex:1; font-size:0.8rem; white-space:pre-wrap;">{{ session('import_output') }}</pre>
                <div style="text-align:right; margin-top:12px;">
                    <button type="button" onclick="document.getElementById('personnelImportResultOverlay').remove()"
                        style="padding:8px 16px; border:none; border-radius:6px; background:#0d6efd; color:#fff;">ปิด</button>
                </div>
            </div>
        </div>
    @endif

    <style>
        #personnelImportOverlay.active, #personnelSchoolInfoOverlay.active { display: flex !important; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterPersonnelSectionsByLevel() {
            const levelId = document.getElementById('personnelSectionLevel').value;
            const roomSelect = document.getElementById('personnelSectionSelect');
            let currentStillVisible = false;
            Array.from(roomSelect.options).forEach(opt => {
                if (!opt.value) { opt.hidden = false; return; }
                const matches = !levelId || opt.dataset.level === levelId;
                opt.hidden = !matches;
                if (matches && opt.selected) currentStillVisible = true;
            });
            if (!currentStillVisible) roomSelect.value = '';
        }

        function closePersonnelImportModal() {
            document.getElementById('personnelImportOverlay').classList.remove('active');
        }
        function submitPersonnelImportForm() {
            document.getElementById('personnelImportSubmitBtn').disabled = true;
            document.getElementById('personnelImportSubmitBtn').innerText = 'กำลังนำเข้า... กรุณารอสักครู่ (ไฟล์ใหญ่อาจใช้เวลาหลายนาที ห้ามปิดหน้านี้)';
        }

        function openPwdModal(id, username, role) {
            document.getElementById('pwdForm').action = '/schooltest/public/personnels/' + id + '/credentials';
            document.getElementById('modalUsername').value = username;
            document.getElementById('modalPassword').value = '';
            document.getElementById('modalRole').value = role || '';
            document.getElementById('pwdOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closePwdModal(e) {
            if (e && e.target !== e.currentTarget) return;
            document.getElementById('pwdOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        function togglePassword() {
            const pwd = document.getElementById('modalPassword');
            const icon = document.getElementById('togglePwdIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                pwd.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        function clearPassword() {
            const pwd = document.getElementById('modalPassword');
            pwd.value = '';
            pwd.type = 'text';
            pwd.focus();
            pwd.setAttribute('placeholder', 'กรอกรหัสผ่านใหม่ที่นี่');
            document.getElementById('togglePwdIcon').className = 'bi bi-eye-slash';
        }

        // กด Esc ปิด modal
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closePwdModal();
        });
    </script>

    @if(session('success'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: '{{ session("success") }}', timer: 2000, showConfirmButton: false });
        </script>
    @endif
@endsection