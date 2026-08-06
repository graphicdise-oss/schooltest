@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:22px; margin-bottom:20px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
    .filter-row { display:flex; gap:14px; flex-wrap:wrap; align-items:flex-end; }
    .filter-field label { display:block; font-size:.85rem; color:#555; font-weight:600; margin-bottom:5px; }
    .filter-input, .filter-select {
        border:1px solid #d0d7e5; border-radius:6px; padding:9px 12px; font-size:.9rem;
        color:#333; font-family:inherit; outline:none; min-width:200px; background:#fff;
    }
    .filter-input:focus, .filter-select:focus { border-color:#4b7ce3; }
    .btn-find { background:#00bcd4; color:#fff; border:none; border-radius:6px; padding:9px 24px; font-weight:600; cursor:pointer; }
    .btn-find:hover { background:#00a5bb; }
    table { width:100%; border-collapse:collapse; font-size:.9rem; }
    thead th { background:#f2f6ff; color:#082b75; font-weight:600; padding:11px 12px; text-align:left; white-space:nowrap; }
    tbody td { padding:10px 12px; border-bottom:1px solid #f0f2f7; color:#444; vertical-align:middle; }
    tbody tr:hover { background:#f7faff; }
    .badge2 { padding:3px 12px; border-radius:20px; font-size:.8rem; font-weight:600; }
    .b-active { background:#dcfce7; color:#16a34a; }
    .b-other { background:#fee2e2; color:#dc2626; }
    .empty { text-align:center; color:#94a3b8; padding:30px 0; }
    .count-pill { background:#eef4ff; color:#2563eb; border-radius:20px; padding:4px 14px; font-size:.85rem; font-weight:600; }
</style>
@endpush

@section('content')
@php
    $thMonths = [1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',
                 7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.'];
    $thDate = function ($d) use ($thMonths) {
        if (!$d) return '-';
        return $d->day . ' ' . $thMonths[$d->month] . ' ' . ($d->year + 543);
    };
@endphp
<div class="page">
    <nav class="breadcrumb-custom mb-3">
        <a href="#">รายงานนักเรียน</a><i class="bi bi-chevron-right"></i>
        <span style="color:#555;">รายงานชื่อนักเรียนใหม่</span>
    </nav>

    {{-- ตัวกรอง --}}
    <div class="card2">
        <div class="card-title"><i class="fas fa-user-plus" style="color:#4b7ce3;"></i> รายงานชื่อนักเรียนใหม่</div>
        <form method="GET" action="{{ route('students.new-report') }}" class="filter-row">
            <div class="filter-field">
                <label>วันที่เข้าเรียน (จาก)</label>
                <input type="date" name="date_from" class="filter-input" value="{{ $dateFrom }}">
            </div>
            <div class="filter-field">
                <label>ถึง</label>
                <input type="date" name="date_to" class="filter-input" value="{{ $dateTo }}">
            </div>
            <div class="filter-field">
                <label>ระดับชั้น</label>
                <select name="level_id" class="filter-select">
                    <option value="">ทุกระดับชั้น</option>
                    @foreach($levels as $lv)
                        <option value="{{ $lv->level_id }}" {{ (string)$levelId === (string)$lv->level_id ? 'selected' : '' }}>{{ $lv->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label>ค้นหา (ชื่อ/รหัส)</label>
                <input type="text" name="search" class="filter-input" value="{{ $search }}" placeholder="ชื่อ หรือ รหัสนักเรียน">
            </div>
            <div class="filter-field">
                <button type="submit" class="btn-find"><i class="fas fa-search"></i> ค้นหา</button>
            </div>
        </form>
    </div>

    {{-- ตาราง --}}
    <div class="card2">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
            <div class="card-title" style="margin:0;"><i class="fas fa-list" style="color:#4b7ce3;"></i> รายชื่อนักเรียนใหม่</div>
            <span class="count-pill">รวมนักเรียนเข้าใหม่ {{ number_format($rows->count()) }} คน</span>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th style="width:55px;">ลำดับ</th>
                        <th style="width:120px;">เลขประจำตัว</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th style="width:110px;">ชั้น/ห้อง</th>
                        <th style="width:130px;">วันที่เข้าเรียน</th>
                        <th>โรงเรียนเดิม</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $r)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $r->code ?: '-' }}</td>
                            <td>{{ $r->name ?: '-' }}</td>
                            <td>{{ $r->room }}</td>
                            <td>{{ $thDate($r->enroll_date) }}</td>
                            <td>{{ $r->previous_school ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                <i class="fas fa-inbox"></i>
                                @if(!$hasAnyData)
                                    ยังไม่มีข้อมูลนักเรียนที่ถูกจัดเข้าห้องเรียนในระบบเลย (ตรวจสอบว่านำเข้า/จัดห้องนักเรียนแล้วหรือยัง)
                                @else
                                    ไม่พบนักเรียนตามเงื่อนไขที่เลือก ลองล้างตัวกรองดู
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
