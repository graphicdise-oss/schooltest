@extends('layouts.sidebar')

@push('styles')
<style>
    .al-page { padding: 24px 28px; min-height: 100%; }
    .al-breadcrumb { font-size: 0.85rem; color: #999; margin-bottom: 20px; }
    .al-breadcrumb a { color: #4b7ce3; text-decoration: none; }

    .al-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 30px 20px 20px; position: relative;
        margin-top: 50px; margin-bottom: 28px;
    }
    .al-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .al-icon-search { background: #00bcd4; }
    .al-icon-table  { background: #ff9800; }
    .al-card-title {
        margin-left: 90px; font-size: 1.1rem; color: #555; margin-top: -10px;
    }

    .al-search-grid {
        display: grid; grid-template-columns: 1fr 1fr 1fr;
        gap: 16px; margin-top: 20px;
    }
    @media(max-width:768px){ .al-search-grid { grid-template-columns: 1fr; } }
    .al-field label { font-size: 0.82rem; font-weight: 600; color: #555; margin-bottom: 4px; display: block; }
    .al-field input, .al-field select {
        width: 100%; height: 38px; border: 1px solid #ccc; border-radius: 4px;
        padding: 0 12px; font-size: 0.85rem; font-family: inherit; outline: none; box-sizing: border-box;
    }
    .al-field input:focus, .al-field select:focus { border-color: #00bcd4; }
    .al-search-center { display: flex; justify-content: center; margin-top: 20px; gap: 10px; }

    .btn-search {
        background: #00bcd4; color: #fff; border: none; border-radius: 6px;
        padding: 9px 28px; font-size: 0.88rem; font-weight: 600;
        cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-search:hover { background: #00acc1; }
    .btn-reset {
        background: #f5f5f5; color: #666; border: 1px solid #ddd; border-radius: 6px;
        padding: 9px 20px; font-size: 0.88rem; font-weight: 600;
        text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    }

    .al-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; margin-top: 20px; }
    .al-table thead th {
        padding: 12px 10px; font-weight: 600; color: #333;
        border-bottom: 2px solid #eee; background: #fafafa; white-space: nowrap;
    }
    .al-table tbody tr { border-bottom: 1px solid #f5f5f5; }
    .al-table tbody tr:hover { background: #fef9f0; }
    .al-table tbody td { padding: 11px 10px; color: #555; vertical-align: middle; }
    .al-table .no-data { text-align: center; padding: 30px; color: #aaa; }

    .al-badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 0.78rem; font-weight: 600; }
    .badge-จบ  { background: #d1fae5; color: #065f46; }
    .badge-ลาออก { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@section('content')
<div class="al-page">

    <div class="al-breadcrumb">
        ข้อมูลบุคคล &rsaquo; <a href="{{ route('students.index') }}">นักเรียน</a> &rsaquo;
        <span style="color:#ff9800">ข้อมูลศิษย์เก่า</span>
    </div>

    {{-- ค้นหา --}}
    <div class="al-card">
        <div class="al-icon al-icon-search"><i class="bi bi-search"></i></div>
        <div class="al-card-title">ค้นหา</div>

        <form method="GET" action="{{ route('student-alumni.index') }}">
            <div class="al-search-grid">
                <div class="al-field">
                    <label>ชื่อ / รหัสนักเรียน</label>
                    <input type="text" name="search" placeholder="ค้นหาชื่อหรือรหัส" value="{{ request('search') }}">
                </div>
                <div class="al-field">
                    <label>สถานะ</label>
                    <select name="promo_type">
                        <option value="">-- ทั้งหมด --</option>
                        <option value="บันทึกจบ" {{ request('promo_type')=='บันทึกจบ' ? 'selected':'' }}>จบการศึกษา</option>
                        <option value="ลาออก"    {{ request('promo_type')=='ลาออก'    ? 'selected':'' }}>ลาออก</option>
                    </select>
                </div>
                <div class="al-field">
                    <label>ปีการศึกษา</label>
                    <input type="text" name="year" placeholder="เช่น 2566" value="{{ request('year') }}">
                </div>
            </div>
            <div class="al-search-center">
                <button type="submit" class="btn-search"><i class="bi bi-search"></i> ค้นหา</button>
                <a href="{{ route('student-alumni.index') }}" class="btn-reset">
                    <i class="bi bi-arrow-counterclockwise"></i> ล้าง
                </a>
            </div>
        </form>
    </div>

    {{-- ตาราง --}}
    <div class="al-card">
        <div class="al-icon al-icon-table"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="al-card-title">ข้อมูลศิษย์เก่า</div>

        <table class="al-table">
            <thead>
                <tr>
                    <th style="width:55px">ลำดับ</th>
                    <th>รหัสนักเรียน</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>ระดับชั้น</th>
                    <th>ปีการศึกษา</th>
                    <th>สถานะ</th>
                    <th>วันที่</th>
                    <th>หมายเหตุ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alumni as $i => $p)
                @php
                    $student = $p->student;
                    $level   = $p->fromSection?->level?->name ?? '-';
                    $section = $p->fromSection?->section_number ?? '';
                    $year    = $p->fromSection?->semester?->academicYear?->year_name ?? '-';
                    $isJob   = $p->promo_type === 'บันทึกจบ';
                @endphp
                <tr>
                    <td>{{ $alumni->firstItem() + $i }}</td>
                    <td>{{ $student?->student_code ?? '-' }}</td>
                    <td>{{ ($student?->thai_firstname ?? '') . ' ' . ($student?->thai_lastname ?? '') }}</td>
                    <td>{{ $level }}{{ $section ? '/'.$section : '' }}</td>
                    <td>{{ $year }}</td>
                    <td>
                        <span class="al-badge {{ $isJob ? 'badge-จบ' : 'badge-ลาออก' }}">
                            {{ $isJob ? 'จบการศึกษา' : 'ลาออก' }}
                        </span>
                    </td>
                    <td>{{ $p->promo_date ? $p->promo_date->format('d/m/') . ($p->promo_date->year + 543) : '-' }}</td>
                    <td>{{ $p->remark ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="no-data">ไม่พบข้อมูล</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:14px">{{ $alumni->links() }}</div>
    </div>

</div>
@endsection
