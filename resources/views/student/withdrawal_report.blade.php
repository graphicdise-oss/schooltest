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
    .form-label-sm { font-size: 0.82rem; color: #666; font-weight: 600; margin-bottom: 4px; }
    .form-select-line {
        border: none; border-bottom: 1.5px solid #ccc; border-radius: 0;
        padding: 6px 4px; font-size: 0.88rem; width: 100%;
        background: transparent; outline: none; font-family: inherit;
    }
    .form-select-line:focus { border-bottom-color: #00bcd4; }
    .form-input-line {
        border: none; border-bottom: 1.5px solid #ccc; border-radius: 0;
        padding: 6px 4px; font-size: 0.88rem; width: 100%;
        background: transparent; outline: none; font-family: inherit;
    }
    .form-input-line:focus { border-bottom-color: #00bcd4; }
    .btn-search-teal {
        background: #00bcd4; color: #fff; border: none; border-radius: 4px;
        padding: 9px 28px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-search-teal:hover { background: #00a5bb; }
    .btn-export {
        background: #4caf50; color: #fff; border: none; border-radius: 4px;
        padding: 9px 20px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
        text-decoration: none;
    }
    .btn-export:hover { background: #43a047; color: #fff; text-decoration: none; }
    .wd-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-bottom: 24px; }
    .wd-table th {
        padding: 10px 14px; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;
        font-weight: 600; color: #555; text-align: center; background: #fafafa;
    }
    .wd-table th.left { text-align: left; }
    .wd-table td {
        padding: 10px 14px; border-bottom: 1px solid #f0f2f5;
        color: #444; text-align: center; vertical-align: middle;
    }
    .wd-table td.name-col { text-align: left; }
    .summary-row td {
        text-align: right; font-size: 0.82rem; color: #888;
        padding: 6px 14px; border-bottom: 2px solid #e5e7eb; font-style: italic;
    }
    .grand-summary {
        text-align: right; font-size: 0.9rem; font-weight: 700;
        color: #e53935; padding: 10px 4px;
    }
    .empty-box { text-align: center; color: #aaa; padding: 50px 0; }
    .empty-box i { font-size: 2.5rem; display: block; margin-bottom: 10px; }
    @media print {
        .floating-card:first-child { display: none; }
        .page { padding: 0; }
        body { background: #fff; }
    }
</style>
@endpush

@section('content')
<div class="page">

    <div class="floating-card">
        <div class="floating-icon" style="background:#00bcd4;"><i class="fas fa-search"></i></div>
        <div class="card-header-text">ค้นหา</div>

        <form method="GET" action="{{ route('student-alumni.withdrawal') }}" style="margin-top:24px;">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="form-label-sm">ปีการศึกษา</div>
                    <select name="year_id" class="form-select-line" onchange="this.form.submit()">
                        <option value="">เลือกปี</option>
                        @foreach ($academicYears as $yr)
                            <option value="{{ $yr->year_id }}" {{ $yearId == $yr->year_id ? 'selected' : '' }}>
                                {{ $yr->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">เทอม</div>
                    <select name="semester_id" class="form-select-line">
                        <option value="">ทั้งหมด</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->semester_id }}" {{ $semesterId == $sem->semester_id ? 'selected' : '' }}>
                                {{ $sem->semester_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="form-label-sm">ค้นหาชื่อ / รหัสนักเรียน</div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="พิมพ์ชื่อหรือรหัส..." class="form-input-line">
                </div>
            </div>
            <div style="text-align:center; border-top:1px solid #f0f0f0; padding-top:14px; display:flex; justify-content:center; gap:12px;">
                <button type="submit" class="btn-search-teal"><i class="fas fa-search"></i> ค้นหา</button>
                <a href="{{ route('student-alumni.withdrawal') }}"
                    style="display:inline-flex; align-items:center; gap:6px; background:#fff; color:#666;
                           border:1.5px solid #d0d7de; border-radius:4px; padding:9px 20px;
                           font-size:0.9rem; font-weight:600; text-decoration:none;">
                    <i class="fas fa-redo"></i> ล้างค่า
                </a>
                <button type="button" class="btn-export" onclick="window.print()">
                    <i class="fas fa-print"></i> พิมพ์
                </button>
            </div>
        </form>
    </div>

    <div class="floating-card">
        <div class="floating-icon" style="background:#e53935;"><i class="fas fa-user-minus"></i></div>
        <div class="card-header-text">รายการค้นหา</div>

        <div style="margin-top:20px;">
            @if ($grouped->isEmpty())
                <div class="empty-box">
                    <i class="fas fa-inbox" style="color:#ccc;"></i>
                    <div style="font-size:1rem; color:#555; margin-top:8px;">ไม่พบรายการนักเรียนลาออก</div>
                </div>
            @else
                @foreach ($grouped as $sectionId => $rows)
                    @php
                        $section    = $rows->first()->fromSection;
                        $level      = $section?->level;
                        $sem        = $section?->semester;
                        $enrollDate = $sem?->start_date;
                    @endphp
                    <table class="wd-table">
                        <thead>
                            <tr>
                                <th style="width:60px;">ลำดับ</th>
                                <th style="width:120px;">เลขประจำตัว</th>
                                <th class="left">ชื่อ-นามสกุล</th>
                                <th style="width:160px;">ชั้น/ห้อง</th>
                                <th style="width:130px;">วันที่เข้าเรียน</th>
                                <th style="width:130px;">วันที่ลาออก</th>
                                <th style="width:200px;">สาเหตุการลาออก</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $i => $promo)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $promo->student?->student_code ?? '-' }}</td>
                                    <td class="name-col">
                                        {{ $promo->student?->thai_prefix ?? '' }}{{ $promo->student?->thai_firstname ?? '-' }}
                                        {{ $promo->student?->thai_lastname ?? '' }}
                                    </td>
                                    <td>{{ $level?->name ?? '-' }} / {{ $section?->section_number ?? '-' }}</td>
                                    <td>
                                        @if ($enrollDate)
                                            {{ $enrollDate->format('d/m/') . ($enrollDate->year + 543) }}
                                        @else - @endif
                                    </td>
                                    <td>
                                        @if ($promo->promo_date)
                                            {{ $promo->promo_date->format('d/m/') . ($promo->promo_date->year + 543) }}
                                        @else - @endif
                                    </td>
                                    <td>{{ $promo->remark ?? '-' }}</td>
                                </tr>
                            @endforeach
                            <tr class="summary-row">
                                <td colspan="7">รวมนักเรียนลาออก {{ $rows->count() }} คน</td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach

                <div class="grand-summary">รวมทั้งหมด {{ $withdrawals->count() }} คน</div>
            @endif
        </div>
    </div>

</div>
@endsection