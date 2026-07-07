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
                    <a href="{{ route('students.create') }}" class="si-btn-add">
                        <i class="bi bi-plus-lg"></i> เพิ่มข้อมูลนักเรียน
                    </a>
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
    </script>
@endsection