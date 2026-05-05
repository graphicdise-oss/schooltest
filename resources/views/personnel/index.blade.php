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
            background: rgba(0, 0, 0, 0.45);
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
            border-radius: 16px;
            width: 480px;
            max-width: 92vw;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pwd-modal-header {
            text-align: center;
            padding: 24px 24px 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        .pwd-modal-header h5 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            margin: 0 0 4px;
        }

        .pwd-modal-header p {
            font-size: 0.82rem;
            color: #999;
            margin: 0;
        }

        .pwd-modal-body {
            padding: 24px;
        }

        .pwd-field {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .pwd-field:last-child {
            border-bottom: none;
        }

        .pwd-field-label {
            min-width: 130px;
        }

        .pwd-field-label .label-th {
            font-size: 0.88rem;
            font-weight: 700;
            color: #333;
        }

        .pwd-field-label .label-en {
            font-size: 0.72rem;
            color: #aaa;
        }

        .pwd-field-input {
            flex: 1;
        }

        .pwd-field-input input,
        .pwd-field-input select {
            width: 100%;
            height: 40px;
            border: 1.5px solid #e0e0e0;
            border-radius: 10px;
            padding: 0 14px;
            font-size: 0.88rem;
            font-family: inherit;
            color: #333;
            outline: none;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .pwd-field-input input:focus,
        .pwd-field-input select:focus {
            border-color: #00bcd4;
            box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.1);
        }

        .pwd-field-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ปุ่มดูรหัส */
        .btn-toggle-pwd {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
            background: #fff;
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
            height: 38px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #ff9800, #ffa726);
            color: #fff;
            padding: 0 16px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: inherit;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-change-pwd:hover {
            background: linear-gradient(135deg, #e68900, #ff9800);
        }

        /* Footer ปุ่ม */
        .pwd-modal-footer {
            display: flex;
            justify-content: center;
            gap: 12px;
            padding: 16px 24px 24px;
        }

        .btn-pwd-save {
            background: linear-gradient(135deg, #00bcd4, #00acc1);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 36px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(0, 188, 212, 0.3);
        }

        .btn-pwd-save:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 188, 212, 0.35);
        }

        .btn-pwd-cancel {
            background: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 28px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-pwd-cancel:hover {
            background: #c0392b;
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
                <div class="row px-md-4 mt-4">
                    <div class="col-md-3 mb-4 offset-md-1">
                        <label class="form-label fw-bold text-dark mb-0">ประเภทบุคลากร</label>
                        <select name="type" class="form-select input-material text-muted mt-2">
                            <option value="">เลือกประเภทบุคลากร</option>
                            @foreach(['ผู้บริหาร', 'ครู', 'ครูพิเศษ', 'บุคลากร', 'ลูกจ้าง'] as $t)
                                <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-4 offset-md-1">
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
                    <div class="col-md-3 mb-4 offset-md-1">
                        <label class="form-label fw-bold text-dark mb-0">ชื่อ - นามสกุล</label>
                        <input type="text" name="search" class="form-control input-material text-muted mt-2"
                            placeholder="ชื่อ - นามสกุล" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="text-center mb-2 mt-2">
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
                <div class="dropdown" style="margin-top: -15px;">
                    <button class="btn btn-manage dropdown-toggle px-3 py-2 rounded-1" type="button"
                        data-bs-toggle="dropdown">
                        จัดการข้อมูล
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="{{ route('personnels.create') }}"><i
                                    class="bi bi-person-plus me-2"></i>เพิ่มข้อมูลบุคลากร</a></li>
                    </ul>
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
                                <td>{{ $person->status ?? 'ปฏิบัติงาน' }}</td>
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
                <h5>ข้อมูลรหัสผ่าน</h5>
                <p>Password Information</p>
            </div>

            <form id="pwdForm" method="POST">
                @csrf
                @method('PUT')

                <div class="pwd-modal-body">
                    {{-- ชื่อผู้ใช้ --}}
                    <div class="pwd-field">
                        <div class="pwd-field-label">
                            <div class="label-th">ชื่อผู้ใช้</div>
                            <div class="label-en">User</div>
                        </div>
                        <div class="pwd-field-input">
                            <input type="text" name="employee_code" id="modalUsername" placeholder="รหัสพนักงาน">
                        </div>
                    </div>

                    {{-- รหัสผ่าน --}}
                    <div class="pwd-field">
                        <div class="pwd-field-label">
                            <div class="label-th">รหัสผ่าน</div>
                            <div class="label-en">Password</div>
                        </div>
                        <div class="pwd-field-input">
                            <input type="password" name="password" id="modalPassword" placeholder="กรอกรหัสผ่านใหม่">
                        </div>
                        <div class="pwd-field-actions">
                            <button type="button" class="btn-toggle-pwd" onclick="togglePassword()" title="ดู/ซ่อนรหัสผ่าน">
                                <i class="bi bi-eye" id="togglePwdIcon"></i>
                            </button>
                            <button type="button" class="btn-change-pwd" onclick="clearPassword()">
                                <i class="bi bi-arrow-repeat"></i> เปลี่ยนรหัสผ่าน
                            </button>
                        </div>
                    </div>

                    {{-- บทบาท --}}
                    <div class="pwd-field">
                        <div class="pwd-field-label">
                            <div class="label-th">บทบาท</div>
                            <div class="label-en">Role</div>
                        </div>
                        <div class="pwd-field-input">
                            <select name="role" id="modalRole">
                                <option value="">-- เลือก --</option>
                                <option value="admin">admin</option>
                                <option value="teacher">teacher</option>
                                <option value="staff">staff</option>
                                <option value="viewer">viewer</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="pwd-modal-footer">
                    <button type="button" class="btn-pwd-cancel" onclick="closePwdModal()">
                        <i class="bi bi-x-lg"></i> ยกเลิก
                    </button>
                    <button type="submit" class="btn-pwd-save">
                        <i class="bi bi-check-lg"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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