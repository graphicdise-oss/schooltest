@extends('layouts.sidebar')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/studentdetail/student_index.css') }}?v={{ time() }}">
@endpush

@section('content')
    <div class="si-page">

        {{-- Breadcrumb --}}
        <nav class="si-breadcrumb">
            <a href="#">ข้อมูลบุคคล</a>
            <i class="bi bi-chevron-right"></i>
            <a href="#">นักเรียน</a>
            <i class="bi bi-chevron-right"></i>
            <span>ข้อมูลนักเรียน</span>
        </nav>

        {{-- ===== Search Card ===== --}}
        <div class="si-card">
            <div class="si-card-icon" style="background:#0cc;">
                <i class="bi bi-search"></i>
            </div>
            <div class="si-card-body">
                <h6 class="si-card-title">ค้นหา</h6>
                <form method="GET" action="{{ route('students.index') }}">
                    <div class="si-form-grid">

                        <div class="si-form-row">
                            <label>ปีการศึกษา</label>
                            <select name="academic_year" class="si-select">
                                <option value="">-- เลือก --</option>
                                @for ($y = 2560; $y <= 2570; $y++)
                                    <option value="{{ $y }}" {{ request('academic_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="si-form-row">
                            <label>เทอม</label>
                            <select name="semester" class="si-select">
                                <option value="">-- เลือก --</option>
                                <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>1</option>
                                <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>2</option>
                            </select>
                        </div>

                        <div class="si-form-row">
                            <label>ระดับชั้นเรียน</label>
                            <select name="level_id" class="si-select" id="levelSelect" onchange="filterClassrooms()">
                                <option value="">เลือกระดับชั้นเรียน</option>
                                @foreach($levels as $lv)
                                <option value="{{ $lv->level_id }}" {{ request('level_id') == $lv->level_id ? 'selected' : '' }}>
                                    {{ $lv->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="si-form-row">
                            <label>ชั้นเรียน</label>
                            <select name="section_id" class="si-select" id="classroomSelect">
                                <option value="">เลือกชั้นเรียน</option>
                                @foreach($classrooms as $room)
                                <option value="{{ $room['section_id'] }}"
                                    data-level="{{ $room['level_id'] }}"
                                    {{ request('section_id') == $room['section_id'] ? 'selected' : '' }}>
                                    {{ $room['label'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="si-form-row">
                            <label>ชื่อนักเรียน</label>
                            <input type="text" name="search_name" class="si-input"
                                placeholder="ชื่อ หรือ นามสกุล"
                                value="{{ request('search_name') }}">
                        </div>

                        <div class="si-form-row">
                            <label>รหัสนักเรียน</label>
                            <input type="text" name="search_code" class="si-input"
                                placeholder="รหัสนักเรียน"
                                value="{{ request('search_code') }}">
                        </div>

                        <div class="si-form-row">
                            <label>เลขบัตรประชาชน</label>
                            <input type="text" name="search_idcard" class="si-input"
                                placeholder="เลขบัตรประชาชน 13 หลัก"
                                value="{{ request('search_idcard') }}" maxlength="13">
                        </div>

                        <div class="si-form-row">
                            <label>สถานะนักเรียน</label>
                            <select name="status" class="si-select">
                                <option value="">ทั้งหมด</option>
                                <option value="กำลังศึกษา" {{ request('status') == 'กำลังศึกษา' ? 'selected' : '' }}>กำลังศึกษา</option>
                                <option value="จำหน่าย" {{ request('status') == 'จำหน่าย' ? 'selected' : '' }}>จำหน่าย</option>
                                <option value="ลาออก" {{ request('status') == 'ลาออก' ? 'selected' : '' }}>ลาออก</option>
                                <option value="พ้นสภาพ" {{ request('status') == 'พ้นสภาพ' ? 'selected' : '' }}>พ้นสภาพ</option>
                            </select>
                        </div>

                        <div class="si-form-row">
                            <label>สถานะวันเข้าเรียน</label>
                            <select name="enroll_status" class="si-select">
                                <option value="">นักเรียนทั้งหมดในระบบ</option>
                                <option value="เข้าใหม่" {{ request('enroll_status') == 'เข้าใหม่' ? 'selected' : '' }}>เข้าใหม่</option>
                                <option value="ย้ายมา" {{ request('enroll_status') == 'ย้ายมา' ? 'selected' : '' }}>ย้ายมา</option>
                            </select>
                        </div>

                    </div>

                    <div class="si-search-actions">
                        <button type="submit" class="si-btn-search">
                            <i class="bi bi-search"></i> ค้นหา
                        </button>
                        <a href="{{ route('students.index') }}" class="si-btn-reset">
                            <i class="bi bi-arrow-counterclockwise"></i> ล้างค่า
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== Table Card ===== --}}
        <div class="si-card">
            <div class="si-card-icon" style="background:#f90;">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="si-card-body">
                <div class="si-table-header">
                    <h6 class="si-card-title">ข้อมูลนักเรียน</h6>
                    <div style="display:flex; gap:8px;">
                        <button type="button" class="si-btn-add" style="background:#495057;" onclick="document.getElementById('schoolInfoOverlay').classList.add('active')" title="ตั้งค่าข้อมูลโรงเรียนที่จะแสดงบนแบบฟอร์ม">
                            <i class="bi bi-gear"></i>
                        </button>
                        <a href="{{ route('students.import-template') }}" class="si-btn-add" style="background:#6c757d;">
                            <i class="bi bi-download"></i> ดาวน์โหลดแบบฟอร์ม Excel
                        </a>
                        <button type="button" class="si-btn-add" style="background:#198754;" onclick="document.getElementById('importOverlay').classList.add('active')">
                            <i class="bi bi-upload"></i> นำเข้าจาก Excel
                        </button>
                        <a href="{{ route('students.create') }}" class="si-btn-add">
                            <i class="bi bi-plus-lg"></i> เพิ่มข้อมูลนักเรียน
                        </a>
                    </div>
                </div>

                <div class="si-table-wrap">
                    <table class="si-table" id="students-table">
                        <colgroup>
                            <col class="col-no">
                            <col class="col-number">
                            <col class="col-code">
                            <col class="col-prefix">
                            <col class="col-name">
                            <col class="col-lastname">
                            <col class="col-level">
                            <col class="col-status">
                            <col class="col-date">
                            <col class="col-actions">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>ลำดับ</th>
                                <th>เลขที่ <i class="bi bi-arrow-down-up"></i></th>
                                <th>รหัสนักเรียน<br><span class="si-th-sub">(Username)</span> <i class="bi bi-arrow-down-up"></i></th>
                                <th>คำนำหน้า <i class="bi bi-arrow-down-up"></i></th>
                                <th>ชื่อ <i class="bi bi-arrow-down-up"></i></th>
                                <th>นามสกุล <i class="bi bi-arrow-down-up"></i></th>
                                <th>ชั้น <i class="bi bi-arrow-down-up"></i></th>
                                <th>สถานะ <i class="bi bi-arrow-down-up"></i></th>
                                <th>วันที่เข้าเรียน <i class="bi bi-arrow-down-up"></i></th>
                                <th>จัดการข้อมูล</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $index => $student)
                                <tr>
                                    <td>{{ $students->firstItem() + $index }}</td>
                                    <td>{{ $student->classroom_number ?? '-' }}</td>
                                    <td>{{ $student->student_code ?? '-' }}</td>
                                    <td>{{ $student->thai_prefix ?? '-' }}</td>
                                    <td>{{ $student->thai_firstname ?? '-' }}</td>
                                    <td>{{ $student->thai_lastname ?? '-' }}</td>
                                    <td>{{ $student->level ?? '-' }}</td>
                                    <td>
                                        <span class="si-badge {{ $student->status == 'กำลังศึกษา' ? 'si-badge-active' : 'si-badge-inactive' }}">
                                            {{ $student->status ?? '-' }}
                                        </span>
                                    </td>
                                    <td>{{ $student->created_at ? \Carbon\Carbon::parse($student->created_at)->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <div class="si-actions">
                                            <a href="{{ route('students.show', $student->student_id) }}"
                                                class="si-action-btn si-action-view" title="ดูข้อมูล">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('students.edit', $student->student_id) }}"
                                                class="si-action-btn si-action-edit" title="แก้ไข">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('students.destroy', $student->student_id) }}" method="POST"
                                                onsubmit="return confirmDelete(event, this)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="si-action-btn si-action-delete" title="ลบ">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="si-empty">
                                        <i class="bi bi-inbox"></i>
                                        <div>ไม่พบข้อมูลนักเรียน</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="si-pagination">
                    {{ $students->withQueryString()->links() }}
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function filterClassrooms() {
            const levelId = document.getElementById('levelSelect').value;
            const sel = document.getElementById('classroomSelect');
            Array.from(sel.options).forEach(opt => {
                if (!opt.value) return;
                opt.hidden = levelId && opt.dataset.level != levelId;
            });
            if (levelId && sel.options[sel.selectedIndex] && sel.options[sel.selectedIndex].dataset.level != levelId) {
                sel.value = '';
            }
        }
        document.addEventListener('DOMContentLoaded', filterClassrooms);

        function confirmDelete(e, form) {
            e.preventDefault();
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: 'ข้อมูลนักเรียนจะถูกลบถาวร ไม่สามารถกู้คืนได้',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e53935',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ลบเลย',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
            return false;
        }

        function closeImportModal() {
            document.getElementById('importOverlay').classList.remove('active');
        }
        function submitImportForm() {
            document.getElementById('importSubmitBtn').disabled = true;
            document.getElementById('importSubmitBtn').innerText = 'กำลังนำเข้า... กรุณารอสักครู่ (ไฟล์ใหญ่อาจใช้เวลาหลายนาที ห้ามปิดหน้านี้)';
        }
    </script>

    {{-- Modal ตั้งค่าข้อมูลโรงเรียน (แสดงบนหัวแบบฟอร์ม Excel) --}}
    <div id="schoolInfoOverlay" onclick="if(event.target===this)this.classList.remove('active')"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:2000; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:12px; width:100%; max-width:480px; padding:24px; margin:16px;">
            <h5 style="margin-bottom:4px;"><i class="bi bi-gear"></i> ตั้งค่าข้อมูลโรงเรียน</h5>
            <p style="font-size:0.85rem; color:#777; margin-bottom:16px;">
                ข้อมูลนี้จะแสดงที่หัวแบบฟอร์ม Excel ทุกครั้งที่กด "ดาวน์โหลดแบบฟอร์ม Excel"
            </p>
            <form method="POST" action="{{ route('students.school-info') }}">
                @csrf
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
                    <button type="button" onclick="document.getElementById('schoolInfoOverlay').classList.remove('active')" style="padding:8px 16px; border:1px solid #ccc; border-radius:6px; background:#fff;">ยกเลิก</button>
                    <button type="submit" style="padding:8px 16px; border:none; border-radius:6px; background:#0d6efd; color:#fff;">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal นำเข้าข้อมูลจาก Excel --}}
    <div id="importOverlay" onclick="if(event.target===this)closeImportModal()"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:2000; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:12px; width:100%; max-width:480px; padding:24px; margin:16px;">
            <h5 style="margin-bottom:4px;"><i class="bi bi-upload"></i> นำเข้าข้อมูลนักเรียนจาก Excel</h5>
            <p style="font-size:0.85rem; color:#777; margin-bottom:16px;">
                ใช้ไฟล์ที่ดาวน์โหลดจากปุ่ม "ดาวน์โหลดแบบฟอร์ม Excel" เท่านั้น (ตำแหน่งคอลัมน์ต้องตรงกัน)
            </p>
            <form method="POST" action="{{ route('students.import') }}" enctype="multipart/form-data" onsubmit="submitImportForm()">
                @csrf
                <div style="margin-bottom:14px;">
                    <input type="file" name="file" accept=".xlsx" required class="form-control">
                </div>
                <div style="margin-bottom:8px;">
                    <label><input type="checkbox" name="assign_rooms" value="1"> จัดห้องเรียนให้ตามคอลัมน์ "ชั้น/ห้อง" ในไฟล์</label>
                </div>
                <div style="margin-bottom:8px;">
                    <label><input type="checkbox" name="fill_classroom_number" value="1"> เติม "เลขที่นักเรียน" ให้คนที่เคยนำเข้าไปแล้ว</label>
                </div>
                <div style="margin-bottom:16px;">
                    <label><input type="checkbox" name="dry_run" value="1"> ทดสอบก่อน (dry-run) — ยังไม่บันทึกข้อมูลจริง</label>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" onclick="closeImportModal()" style="padding:8px 16px; border:1px solid #ccc; border-radius:6px; background:#fff;">ยกเลิก</button>
                    <button type="submit" id="importSubmitBtn" style="padding:8px 16px; border:none; border-radius:6px; background:#198754; color:#fff;">เริ่มนำเข้า</button>
                </div>
            </form>
        </div>
    </div>

    @if (session('import_output'))
        <div id="importResultOverlay"
            style="position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:2000; display:flex; align-items:center; justify-content:center;">
            <div style="background:#fff; border-radius:12px; width:100%; max-width:700px; max-height:80vh; padding:24px; margin:16px; display:flex; flex-direction:column;">
                <h5 style="margin-bottom:12px;"><i class="bi bi-clipboard-check"></i> ผลการนำเข้าข้อมูล</h5>
                <pre style="background:#f5f5f5; padding:12px; border-radius:8px; overflow:auto; flex:1; font-size:0.8rem; white-space:pre-wrap;">{{ session('import_output') }}</pre>
                <div style="text-align:right; margin-top:12px;">
                    <button type="button" onclick="document.getElementById('importResultOverlay').remove()"
                        style="padding:8px 16px; border:none; border-radius:6px; background:#0d6efd; color:#fff;">ปิด</button>
                </div>
            </div>
        </div>
    @endif

    <style>
        #importOverlay.active, #schoolInfoOverlay.active { display: flex !important; }
    </style>
@endsection