@extends('layouts.sidebar')

@push('styles')
<style>
    .pp3-page {
        padding: 24px 28px;
        min-height: 100%;
    }
    .pp3-card {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 30px 20px 20px;
        position: relative;
        margin-top: 50px;
        margin-bottom: 28px;
    }
    .pp3-icon {
        position: absolute;
        top: -25px; left: 20px;
        width: 70px; height: 70px;
        border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        background: #43a047;
    }
    .pp3-header {
        margin-left: 90px;
        font-size: 1.15rem;
        color: #555;
        margin-top: -10px;
    }
    .pp3-header h5 {
        font-size: 1.3rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 2px;
    }
    .form-label {
        font-weight: 600;
        color: #444;
        margin-bottom: 4px;
        display: block;
        font-size: 0.9rem;
    }
    .form-control, .form-select {
        border-radius: 4px;
        border: 1px solid #ccc;
        padding: 7px 10px;
        width: 100%;
    }
    .form-control:focus, .form-select:focus {
        border-color: #43a047;
        outline: none;
        box-shadow: 0 0 0 2px rgba(67,160,71,0.15);
    }
    .btn-search {
        background: #43a047;
        color: #fff;
        border: none;
        padding: 8px 20px;
        border-radius: 4px;
        font-size: 0.95rem;
        cursor: pointer;
        white-space: nowrap;
    }
    .btn-search:hover { background: #2e7d32; }
    .row { display: flex; flex-wrap: wrap; margin: 0 -8px; }
    .col-md-3, .col-md-4 { padding: 0 8px; margin-bottom: 12px; }
    .col-md-3 { width: 25%; }
    .col-md-4 { width: 33.33%; }
    @media (max-width: 768px) {
        .col-md-3, .col-md-4 { width: 100%; }
    }
    .student-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
        margin-top: 12px;
    }
    .student-table th {
        padding: 10px 12px;
        border-bottom: 2px solid #ddd;
        text-align: left;
        background: #43a047;
        color: #fff;
        font-weight: 600;
    }
    .student-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    .student-table tbody tr:hover { background: #f9fbff; }

    /* Print dropdown */
    .btn-print-wrap { position: relative; display: inline-block; }
    .btn-print-main {
        background: #00bcd4; color: #fff; border: none; border-radius: 6px 0 0 6px;
        padding: 6px 14px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 5px;
    }
    .btn-print-caret {
        background: #0097a7; color: #fff; border: none; border-radius: 0 6px 6px 0;
        padding: 6px 10px; font-size: 0.82rem; cursor: pointer;
        border-left: 1px solid rgba(255,255,255,0.3);
    }
    .btn-print-dropdown {
        display: none; position: absolute; top: 100%; right: 0;
        background: #fff; border-radius: 6px; min-width: 140px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15); z-index: 100; margin-top: 4px;
    }
    .btn-print-dropdown.open { display: block; }
    .btn-print-dropdown a {
        display: block; padding: 10px 16px; font-size: 0.88rem; color: #333;
        text-decoration: none;
    }
    .btn-print-dropdown a:hover { background: #f5f5f5; }
    .alert-success {
        background: #e8f5e9; border: 1px solid #a5d6a7; color: #2e7d32;
        border-radius: 4px; padding: 10px 16px; margin-bottom: 16px;
    }
</style>
@endpush

@section('content')
<div class="pp3-page">
    <div class="pp3-card">
        <div class="pp3-icon">🎓</div>
        <div class="pp3-header">
            <h5>แบบรายงานผู้สำเร็จการศึกษา (ปพ.3)</h5>
            <span style="color:#888;font-size:0.9rem;">เลือกปีการศึกษา ระดับชั้น และห้องเรียนที่ต้องการ</span>
        </div>

        <hr style="margin:20px 0;">

        @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
        @endif

        {{-- Filter --}}
        <form method="GET" action="{{ route('por3.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">ปีการศึกษา</label>
                    <select name="year_id" class="form-select" onchange="this.form.submit()">
                        @foreach($academicYears as $year)
                        <option value="{{ $year->year_id }}" {{ $yearId == $year->year_id ? 'selected' : '' }}>
                            {{ $year->year_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ระดับชั้น</label>
                    <select name="level_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- เลือกระดับ --</option>
                        @foreach($levels as $level)
                        <option value="{{ $level->level_id }}" {{ $levelId == $level->level_id ? 'selected' : '' }}>
                            {{ $level->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ห้องเรียน</label>
                    <select name="section_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- เลือกห้อง --</option>
                        @foreach($sections as $sec)
                        <option value="{{ $sec->section_id }}" {{ $sectionId == $sec->section_id ? 'selected' : '' }}>
                            {{ $sec->level?->name }}/{{ $sec->section_number }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ค้นหา</label>
                    <div style="display:flex;gap:6px;">
                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ชื่อ / รหัส">
                        <button type="submit" class="btn-search">🔍</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Table --}}
        @if($students->count() > 0)
        <table class="student-table">
            <thead>
                <tr>
                    <th style="width:60px">ลำดับ</th>
                    <th>รหัสนักเรียน</th>
                    <th>บัตรประชาชน</th>
                    <th>ชื่อ - นามสกุล</th>
                    <th>ระดับชั้น/ห้อง</th>
                    <th style="text-align:center;min-width:200px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $ss)
                @php
                    $stu = $ss->student;
                    $sec = $ss->classSection;
                    $fullName = ($stu?->thai_prefix ?? '') . ($stu?->thai_firstname ?? '') . ' ' . ($stu?->thai_lastname ?? '');
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $stu?->student_code }}</td>
                    <td>{{ $stu?->id_card_number ?? '-' }}</td>
                    <td>{{ $fullName }}</td>
                    <td>{{ $sec?->level?->name }}/{{ $sec?->section_number }}</td>
                    <td style="text-align:center;">
                        <div class="btn-print-wrap" id="pw{{ $i }}">
                            <button class="btn-print-main" onclick="toggleDrop('pw{{ $i }}')">
                                <i class="bi bi-printer"></i> เตรียมพิมพ์ ปพ.3
                            </button>
                            <button class="btn-print-caret" onclick="toggleDrop('pw{{ $i }}')">
                                <i class="bi bi-caret-down-fill" style="font-size:0.7rem;"></i>
                            </button>
                            <div class="btn-print-dropdown">
                                <a href="#" onclick="alert('PDF กำลังพัฒนา');return false;">
                                    <i class="bi bi-file-earmark-pdf" style="color:#e53935;"></i> PDF
                                </a>
                                <a href="#" onclick="alert('Excel กำลังพัฒนา');return false;">
                                    <i class="bi bi-file-earmark-excel" style="color:#43a047;"></i> Excel
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @elseif($sectionId)
        <p style="color:#aaa;margin-top:12px;">ไม่พบนักเรียนที่มีสถานะ "จบการศึกษา" ในห้องนี้</p>
        @else
        <p style="color:#aaa;margin-top:12px;">เลือกปีการศึกษา ระดับชั้น และห้องเรียนเพื่อแสดงรายชื่อ</p>
        @endif
    </div>
</div>

<script>
function toggleDrop(id) {
    const wrap = document.getElementById(id);
    const drop = wrap.querySelector('.btn-print-dropdown');
    const open = drop.classList.contains('open');
    document.querySelectorAll('.btn-print-dropdown.open').forEach(d => d.classList.remove('open'));
    if (!open) drop.classList.add('open');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.btn-print-wrap')) {
        document.querySelectorAll('.btn-print-dropdown.open').forEach(d => d.classList.remove('open'));
    }
});
</script>
@endsection
