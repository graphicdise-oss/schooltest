@extends('layouts.sidebar')

@push('styles')
<style>
    body { background: #f4f6f9; }
    .page { padding: 24px 28px; }

    .floating-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 30px 20px 20px; position: relative;
        margin-top: 50px; margin-bottom: 28px;
    }
    .floating-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .card-header-text {
        margin-left: 90px; font-size: 1.1rem; color: #555;
        margin-top: -10px; font-weight: 600;
    }
    .breadcrumb-custom a { color: #00bcd4; text-decoration: none; }
    .breadcrumb-custom a:hover { text-decoration: underline; }
    .breadcrumb-custom i { color: #aaa; margin: 0 6px; font-size: 0.75rem; }

    .form-label-sm { font-size: 0.82rem; color: #666; font-weight: 600; margin-bottom: 4px; }
    .form-select-line, .form-input-line {
        border: none; border-bottom: 1.5px solid #ccc;
        border-radius: 0; padding: 6px 4px; font-size: 0.88rem;
        width: 100%; background: transparent; outline: none; font-family: inherit;
    }
    .form-select-line:focus, .form-input-line:focus { border-bottom-color: #00bcd4; }

    .btn-search-teal {
        background: #00bcd4; color: #fff; border: none; border-radius: 4px;
        padding: 9px 28px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-search-teal:hover { background: #00a5bb; }

    .btn-import {
        background: #4caf50; color: #fff; border: none; border-radius: 4px;
        padding: 8px 20px; font-size: 0.88rem; font-weight: 700; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-import:hover { background: #43a047; }
    .btn-import:disabled { background: #ccc; cursor: not-allowed; }

    /* ตาราง */
    .imp-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
    .imp-table thead th {
        background: #fff; border-bottom: 2px solid #e8e8e8;
        padding: 10px 8px; color: #444; font-weight: 600; white-space: nowrap;
        position: sticky; top: 0; z-index: 1;
    }
    .imp-table tbody td {
        padding: 10px 8px; border-bottom: 1px solid #f3f3f3;
        vertical-align: middle; color: #555;
    }
    .imp-table tbody tr:hover td { background: #f8fffe; }
    .imp-table tbody tr.selected td { background: #e8f5e9; }

    .badge-grad { background: #e3f2fd; color: #1565c0; border-radius: 12px; padding: 3px 10px; font-size: 0.78rem; font-weight: 600; white-space:nowrap; }
    .badge-leave { background: #fff3e0; color: #e65100; border-radius: 12px; padding: 3px 10px; font-size: 0.78rem; font-weight: 600; white-space:nowrap; }

    /* dropdown ในแถว */
    .row-select {
        border: 1px solid #d0d7e5; border-radius: 4px; padding: 5px 6px;
        font-size: 0.82rem; color: #333; font-family: inherit; outline: none;
        width: 100%; min-width: 100px; background: #fff;
    }
    .row-select:focus { border-color: #4caf50; }
    .row-select:disabled { background: #f5f5f5; color: #aaa; }

    .select-all-wrap { display: flex; align-items: center; gap: 6px; font-size: 0.82rem; color: #666; }
    .count-badge { background: #4caf50; color: #fff; border-radius: 12px; padding: 2px 10px; font-size: 0.8rem; font-weight: 700; }
    .empty-row td { text-align: center; color: #aaa; padding: 40px 0; }
    .empty-row i { font-size: 2rem; display: block; margin-bottom: 8px; }
</style>
@endpush

@section('content')
<div class="page">

    <nav class="breadcrumb-custom mb-3" style="font-size:0.88rem; display:flex; align-items:center; gap:4px;">
        <a href="{{ route('student-alumni.index') }}">ข้อมูลศิษย์เก่า</a>
        <i class="bi bi-chevron-right"></i>
        <span style="color:#555;">นำเข้าข้อมูลศิษย์เก่า</span>
    </nav>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ค้นหา --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#00bcd4;"><i class="fas fa-search"></i></div>
        <div class="card-header-text">ค้นหา</div>

        <form method="GET" action="{{ route('student-alumni.import') }}" id="searchForm" style="margin-top:24px;">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="form-label-sm">ปีการศึกษา</div>
                    <select name="year_id" class="form-select-line" onchange="this.form.submit()">
                        <option value="">ทั้งหมด</option>
                        @foreach ($academicYears as $yr)
                            <option value="{{ $yr->year_id }}" {{ $yearId == $yr->year_id ? 'selected' : '' }}>
                                {{ $yr->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">เทอม</div>
                    <select name="semester_id" class="form-select-line" onchange="this.form.submit()">
                        <option value="">ทั้งหมด</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->semester_id }}" {{ $semesterId == $sem->semester_id ? 'selected' : '' }}>
                                {{ $sem->semester_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">ระดับชั้นเรียน</div>
                    <select name="level_id" class="form-select-line" onchange="this.form.submit()">
                        <option value="">เลือกระดับชั้นเรียน</option>
                        @foreach ($levels as $lv)
                            <option value="{{ $lv->level_id }}" {{ $levelId == $lv->level_id ? 'selected' : '' }}>
                                {{ $lv->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">ชั้นเรียน</div>
                    <select name="section_id" class="form-select-line" onchange="this.form.submit()">
                        <option value="">เลือกชั้นเรียน</option>
                        @foreach ($sections as $sec)
                            <option value="{{ $sec->section_id }}" {{ $sectionId == $sec->section_id ? 'selected' : '' }}>
                                {{ $sec->level->name ?? '' }}/{{ $sec->section_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-label-sm">ชื่อ-นามสกุล</div>
                    <input type="text" name="search" class="form-input-line" placeholder="ชื่อนักเรียน/รหัสนักเรียน" value="{{ $search }}">
                </div>
            </div>
            <div style="text-align:center; border-top:1px solid #f0f0f0; padding-top:14px;">
                <button type="submit" class="btn-search-teal"><i class="fas fa-search"></i> ค้นหา</button>
            </div>
        </form>
    </div>

    {{-- ตารางนำเข้า --}}
    <form method="POST" action="{{ route('student-alumni.import.store') }}" id="importForm">
    @csrf

    <div class="floating-card">
        <div class="floating-icon" style="background:#f59e0b;"><i class="fas fa-user-graduate"></i></div>
        <div class="card-header-text" style="display:flex; justify-content:space-between; align-items:center;">
            <span>ข้อมูลนักเรียน</span>
            <button type="submit" class="btn-import" id="importBtn" disabled>
                <i class="fas fa-file-import"></i> นำเข้าข้อมูล
            </button>
        </div>

        <div style="margin-top:20px; overflow-x:auto;">
            <table class="imp-table">
                <thead>
                    <tr>
                        <th style="width:40px;">
                            <input type="checkbox" id="selectAll" title="เลือกทั้งหมด">
                        </th>
                        <th style="width:50px;">ลำดับ</th>
                        <th style="width:60px;">เลขที่</th>
                        <th>รหัสนักเรียน</th>
                        <th>ชื่อ - นามสกุล</th>
                        <th>ปีการศึกษา</th>
                        <th>ชั้น</th>
                        <th>สถานะ</th>
                        <th style="min-width:110px;">
                            ระบุชั้น<br><span style="font-size:0.7rem;color:#aaa;">ระบุชั้น</span>
                        </th>
                        <th style="min-width:150px;">
                            ห้อง<br><span style="font-size:0.7rem;color:#aaa;">ระบุห้อง</span>
                        </th>
                        <th>หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumni as $i => $promo)
                        @php
                            $std = $promo->student;
                            $sec = $promo->fromSection;
                            $yr  = $sec?->semester?->academicYear;
                        @endphp
                        <tr id="row_{{ $std->student_id ?? $i }}">
                            <td style="text-align:center;">
                                <input type="checkbox" name="student_ids[]"
                                       value="{{ $std->student_id }}"
                                       class="row-check"
                                       onchange="toggleRow(this)">
                            </td>
                            <td style="text-align:center;">{{ $alumni->firstItem() + $i }}.</td>
                            <td style="text-align:center;">
                                {{ optional(optional($std)->studentSections->first())->student_number ?? '-' }}
                            </td>
                            <td>{{ $std->student_code ?? '-' }}</td>
                            <td>
                                {{ ($std->thai_prefix ?? '') . ($std->thai_firstname ?? '') . ' ' . ($std->thai_lastname ?? '') }}
                            </td>
                            <td style="text-align:center;">{{ $yr?->year_name ?? '-' }}</td>
                            <td style="text-align:center;">
                                {{ $sec?->level?->name ?? '-' }}
                                @if ($sec) /{{ $sec->section_number }} @endif
                            </td>
                            <td style="text-align:center;">
                                @if ($promo->promo_type === 'บันทึกจบ')
                                    <span class="badge-grad">สำเร็จการศึกษา</span>
                                @else
                                    <span class="badge-leave">ลาออก</span>
                                @endif
                            </td>
                            {{-- ระบุชั้นใหม่ --}}
                            <td>
                                <select class="row-select level-sel"
                                        data-student="{{ $std->student_id }}"
                                        onchange="loadSections(this)"
                                        disabled>
                                    <option value="">-- ชั้น --</option>
                                    @foreach ($newLevels as $lv)
                                        <option value="{{ $lv->level_id }}">{{ $lv->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            {{-- ระบุห้องใหม่ --}}
                            <td>
                                <select class="row-select section-sel"
                                        name="new_section_id[{{ $std->student_id }}]"
                                        data-student="{{ $std->student_id }}"
                                        disabled>
                                    <option value="">-- ห้อง --</option>
                                </select>
                            </td>
                            <td style="font-size:0.78rem; color:#888;">{{ $promo->remark ?? '' }}</td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="11">
                                <i class="fas fa-inbox"></i>
                                <div>ไม่พบข้อมูลศิษย์เก่า</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; padding-top:12px; border-top:1px solid #f0f0f0;">
            <div class="select-all-wrap">
                <span>เลือกแล้ว</span>
                <span class="count-badge" id="selectedCount">0</span>
                <span>คน</span>
            </div>
            {{ $alumni->withQueryString()->links() }}
        </div>
    </div>

    </form>

</div>

@push('scripts')
<script>
    // ข้อมูล sections จาก server จัดกลุ่มตาม level_id
    const allSections = @json($allSections);

    // เลือกทั้งหมด
    document.getElementById('selectAll').addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach(cb => {
            cb.checked = this.checked;
            toggleRow(cb);
        });
    });

    function toggleRow(cb) {
        const studentId = cb.value;
        const row = cb.closest('tr');
        const levelSel   = row.querySelector('.level-sel');
        const sectionSel = row.querySelector('.section-sel');

        if (cb.checked) {
            row.classList.add('selected');
            levelSel.disabled   = false;
            sectionSel.disabled = false;
        } else {
            row.classList.remove('selected');
            levelSel.disabled   = true;
            sectionSel.disabled = true;
            levelSel.value      = '';
            sectionSel.innerHTML = '<option value="">-- ห้อง --</option>';
        }

        updateCount();
    }

    function loadSections(levelSel) {
        const levelId    = levelSel.value;
        const row        = levelSel.closest('tr');
        const studentId  = levelSel.dataset.student;
        const sectionSel = row.querySelector('.section-sel');

        sectionSel.innerHTML = '<option value="">-- ห้อง --</option>';

        const sections = allSections[levelId] || [];
        sections.forEach(sec => {
            const name = (sec.level?.name || '') + '/' + sec.section_number;
            sectionSel.innerHTML += `<option value="${sec.section_id}">${name}</option>`;
        });
    }

    function updateCount() {
        const count = document.querySelectorAll('.row-check:checked').length;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('importBtn').disabled = count === 0;
    }
</script>
@endpush
@endsection