@extends('layouts.sidebar')

@push('styles')
<style>
    .ls-page { padding: 24px 28px; min-height: 100%; }
    .ls-breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; margin-bottom: 20px; color: #555; }
    .ls-breadcrumb a { color: #5482e7; text-decoration: none; font-weight: 500; }
    .ls-breadcrumb a:hover { text-decoration: underline; }
    .ls-breadcrumb span { color: #5482e7; font-weight: 600; }
    .ls-breadcrumb i { font-size: 0.7rem; color: #aaa; }

    .ls-card {
        background: #fff; border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        padding: 30px 24px 24px; position: relative;
        margin-top: 50px; margin-bottom: 28px;
    }
    .ls-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .ls-card-header {
        margin-left: 90px; font-size: 1.05rem; color: #444;
        margin-top: -10px; font-weight: 600;
        display: flex; justify-content: space-between; align-items: center;
    }
    .ls-label { font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: 4px; }
    .ls-input {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 8px 12px;
        font-size: 0.88rem; color: #333; width: 100%; font-family: inherit;
        outline: none; transition: border 0.2s; box-sizing: border-box;
    }
    .ls-input:focus { border-color: #5482e7; box-shadow: 0 0 0 3px rgba(84,130,231,0.12); }
    .ls-select {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 8px 12px;
        font-size: 0.88rem; color: #333; width: 100%; font-family: inherit;
        outline: none; background: #fff; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%23666' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center;
        padding-right: 30px; box-sizing: border-box;
    }
    .ls-form-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px 24px; margin-bottom: 22px; }
    .ls-form-row { display: flex; flex-direction: column; gap: 5px; }
    .ls-search-actions { display: flex; justify-content: center; gap: 12px; border-top: 1px solid #f0f3f7; padding-top: 18px; }
    .btn-search {
        background: #5482e7; color: #fff; border: none; border-radius: 6px;
        padding: 10px 36px; font-size: 0.88rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;
    }
    .btn-search:hover { background: #446bca; transform: translateY(-1px); }
    .btn-reset {
        background: #fff; color: #666; border: 1.5px solid #d0d7de; border-radius: 6px;
        padding: 10px 28px; font-size: 0.88rem; font-weight: 600; cursor: pointer;
        font-family: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
    }
    .btn-reset:hover { background: #f5f5f5; color: #333; text-decoration: none; }

    .ls-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.85rem; }
    .ls-table thead th {
        padding: 12px 10px; font-weight: 600; color: #fff;
        background: #5482e7; text-align: center; white-space: nowrap; border: none;
    }
    .ls-table thead th:first-child { border-top-left-radius: 8px; }
    .ls-table thead th:last-child  { border-top-right-radius: 8px; }
    .ls-table tbody tr td { border-bottom: 1px solid #f2f4f8; }
    .ls-table tbody tr:nth-child(even) td { background: #fafcff; }
    .ls-table tbody tr:hover td { background: #eef6ff; }
    .ls-table tbody td { padding: 11px 10px; color: #555; vertical-align: middle; text-align: center; }
    .ls-table tbody td.td-name, .ls-table tbody td.td-course { text-align: left; }
    .hours-badge { font-weight: 700; color: #5482e7; }
    .ls-result-count { font-size: 0.82rem; color: #888; }
    .ls-summary-hours {
        display: inline-flex; align-items: center; gap: 6px;
        background: #eef6ff; color: #3949ab; border-radius: 20px;
        padding: 4px 14px; font-size: 0.82rem; font-weight: 700;
    }
    .ls-pagination { display: flex; justify-content: flex-end; margin-top: 16px; }
    .ls-pagination .page-link { border-radius: 6px !important; font-size: 0.85rem; padding: 6px 12px; }
    .ls-pagination .page-item.active .page-link { background-color: #5482e7; border-color: #5482e7; }
    .ls-empty { text-align: center; color: #aaa; padding: 40px 0; }
    .ls-empty i { font-size: 2rem; display: block; margin-bottom: 8px; }
</style>
@endpush

@section('content')
<div class="ls-page">

    <nav class="ls-breadcrumb">
        <a href="#">ข้อมูลบุคคล</a>
        <i class="bi bi-chevron-right"></i>
        <span>รายงานการอบรม</span>
    </nav>

    <div class="ls-card">
        <div class="ls-icon" style="background: #00bbbb;"><i class="fas fa-search"></i></div>
        <div class="ls-card-header"><strong>ค้นหา</strong></div>

        <form method="GET" action="{{ route('personnel-reports.training') }}" style="margin-top: 24px;">
            <div class="ls-form-grid">
                <div class="ls-form-row">
                    <label class="ls-label">บุคลากร</label>
                    <select name="personnel_id" class="ls-select">
                        <option value="">ทั้งหมด</option>
                        @foreach ($personnels as $p)
                            <option value="{{ $p->personnel_id }}" {{ request('personnel_id') == $p->personnel_id ? 'selected' : '' }}>
                                {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ls-form-row">
                    <label class="ls-label">ประเภทการอบรม</label>
                    <select name="training_type" class="ls-select">
                        <option value="">ทั้งหมด</option>
                        @foreach ($trainingTypes as $type)
                            <option value="{{ $type }}" {{ request('training_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ls-form-row">
                    <label class="ls-label">จากวันที่</label>
                    <input type="date" name="date_from" class="ls-input" value="{{ request('date_from') }}">
                </div>
                <div class="ls-form-row">
                    <label class="ls-label">ถึงวันที่</label>
                    <input type="date" name="date_to" class="ls-input" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="ls-search-actions">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> ค้นหา</button>
                <a href="{{ route('personnel-reports.training') }}" class="btn-reset"><i class="fas fa-redo"></i> ล้างค่า</a>
            </div>
        </form>
    </div>

    <div class="ls-card">
        <div class="ls-icon" style="background: #ff9800;"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="ls-card-header">
            <strong>รายงานการอบรม/ศึกษา/ดูงาน</strong>
            <span style="display:flex; gap:12px; align-items:center;">
                <span class="ls-summary-hours"><i class="fas fa-clock"></i> รวม {{ number_format($totalHours, 1) }} ชั่วโมง</span>
                <span class="ls-result-count">พบ {{ $trainings->total() }} รายการ</span>
            </span>
        </div>

        <div style="margin-top: 24px; overflow-x: auto; border-radius: 8px; border: 1px solid #eaeef2;">
            <table class="ls-table">
                <thead>
                    <tr>
                        <th style="width:50px;">ลำดับ</th>
                        <th>ชื่อ - นามสกุล</th>
                        <th>หลักสูตร/โครงการ</th>
                        <th>ประเภท</th>
                        <th>วันที่เริ่ม</th>
                        <th>วันที่สิ้นสุด</th>
                        <th>ชั่วโมง</th>
                        <th>สถานที่</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trainings as $index => $t)
                        <tr>
                            <td>{{ $trainings->firstItem() + $index }}</td>
                            <td class="td-name">
                                {{ $t->personnel->thai_prefix ?? '' }}{{ $t->personnel->thai_firstname ?? '-' }} {{ $t->personnel->thai_lastname ?? '' }}
                            </td>
                            <td class="td-course">{{ $t->course_name ?? $t->project ?? '-' }}</td>
                            <td>{{ $t->training_type ?? '-' }}</td>
                            <td>{{ $t->start_date ? $t->start_date->format('d/m/Y') : '-' }}</td>
                            <td>{{ $t->end_date ? $t->end_date->format('d/m/Y') : '-' }}</td>
                            <td class="hours-badge">{{ $t->hours ?? '-' }}</td>
                            <td>{{ $t->location ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="ls-empty">
                                <i class="fas fa-inbox"></i>
                                <div>ไม่พบข้อมูลการอบรม</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ls-pagination">{{ $trainings->links() }}</div>
    </div>

</div>
@endsection
