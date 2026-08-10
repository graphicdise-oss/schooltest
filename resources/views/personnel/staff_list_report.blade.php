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
    .ls-form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px 24px; margin-bottom: 22px; }
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
    .ls-table tbody td.td-name { text-align: left; }
    .staff-photo {
        width: 34px; height: 34px; border-radius: 50%; object-fit: cover;
        display: inline-block; vertical-align: middle; box-shadow: 0 1px 4px rgba(0,0,0,.15);
    }
    .staff-photo-placeholder {
        width: 34px; height: 34px; border-radius: 50%; background: #eef2f9; color: #9aa7bd;
        display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem;
    }
    .status-badge {
        display: inline-block; border-radius: 20px; padding: 3px 12px;
        font-size: 0.76rem; font-weight: 700;
    }
    .status-active { background: #e8f5e9; color: #2e7d32; }
    .status-other { background: #f1f3f5; color: #666; }
    .ls-result-count { font-size: 0.82rem; color: #888; }
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
        <span>รายชื่อพนักงาน</span>
    </nav>

    <div class="ls-card">
        <div class="ls-icon" style="background: #00bbbb;"><i class="fas fa-search"></i></div>
        <div class="ls-card-header"><strong>ค้นหา</strong></div>

        <form method="GET" action="{{ route('personnel-reports.staff-list') }}" style="margin-top: 24px;">
            <div class="ls-form-grid">
                <div class="ls-form-row">
                    <label class="ls-label">ชื่อ - นามสกุล / รหัส</label>
                    <input type="text" name="search" class="ls-input" placeholder="ค้นหา ชื่อ/รหัส" value="{{ request('search') }}">
                </div>
                <div class="ls-form-row">
                    <label class="ls-label">แผนก</label>
                    <select name="department" class="ls-select">
                        <option value="">ทั้งหมด</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ls-form-row">
                    <label class="ls-label">สถานะ</label>
                    <select name="status" class="ls-select">
                        <option value="">ทั้งหมด</option>
                        <option value="ปฏิบัติงาน" {{ request('status') == 'ปฏิบัติงาน' ? 'selected' : '' }}>ปฏิบัติงาน</option>
                        <option value="ลาออก" {{ request('status') == 'ลาออก' ? 'selected' : '' }}>ลาออก</option>
                    </select>
                </div>
            </div>
            <div class="ls-search-actions">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> ค้นหา</button>
                <a href="{{ route('personnel-reports.staff-list') }}" class="btn-reset"><i class="fas fa-redo"></i> ล้างค่า</a>
            </div>
        </form>
    </div>

    <div class="ls-card">
        <div class="ls-icon" style="background: #3f51b5;"><i class="fas fa-users"></i></div>
        <div class="ls-card-header">
            <strong>รายชื่อพนักงาน</strong>
            <span class="ls-result-count">พบ {{ $personnels->total() }} คน</span>
        </div>

        <div style="margin-top: 24px; overflow-x: auto; border-radius: 8px; border: 1px solid #eaeef2;">
            <table class="ls-table">
                <thead>
                    <tr>
                        <th style="width:50px;">ลำดับ</th>
                        <th style="width:50px;">รูป</th>
                        <th style="width:100px;">รหัส</th>
                        <th>ชื่อ - นามสกุล</th>
                        <th>ตำแหน่ง</th>
                        <th>แผนก</th>
                        <th>เบอร์โทร</th>
                        <th>อีเมล</th>
                        <th style="width:110px;">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($personnels as $index => $p)
                        <tr>
                            <td>{{ $personnels->firstItem() + $index }}</td>
                            <td>
                                @if($p->personnel_image)
                                    <img src="{{ asset('storage/' . $p->personnel_image) }}" class="staff-photo" alt="">
                                @else
                                    <span class="staff-photo-placeholder"><i class="fas fa-user"></i></span>
                                @endif
                            </td>
                            <td>{{ $p->employee_code ?? '-' }}</td>
                            <td class="td-name">{{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}</td>
                            <td>{{ $p->position ?? '-' }}</td>
                            <td>{{ $p->department ?? '-' }}</td>
                            <td>{{ $p->phone ?? '-' }}</td>
                            <td>{{ $p->email ?? '-' }}</td>
                            <td>
                                <span class="status-badge {{ $p->status === 'ปฏิบัติงาน' ? 'status-active' : 'status-other' }}">
                                    {{ $p->status ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="ls-empty">
                                <i class="fas fa-inbox"></i>
                                <div>ไม่พบข้อมูลพนักงาน</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ls-pagination">{{ $personnels->links() }}</div>
    </div>

</div>
@endsection
