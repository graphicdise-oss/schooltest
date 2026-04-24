@extends('layouts.sidebar')
@push('styles')
<style>
.cy-page { padding: 24px 28px; }

.cy-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 20px 20px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.cy-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.cy-icon-year { background: #5c6bc0; }
.cy-card-header {
    margin-left: 90px; display: flex; align-items: center;
    justify-content: space-between; margin-top: -8px; margin-bottom: 20px;
}
.cy-card-title { font-size: 1.05rem; color: #555; }

.cy-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.cy-table thead th {
    padding: 12px 14px; background: #5c6bc0; color: #fff;
    font-weight: 600; text-align: left; font-size: 0.85rem;
}
.cy-table thead th:first-child { border-radius: 6px 0 0 0; }
.cy-table thead th:last-child  { border-radius: 0 6px 0 0; text-align: center; }
.cy-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.cy-table tbody tr:hover { background: #f5f7ff; }
.cy-table tbody td { padding: 12px 14px; color: #555; vertical-align: middle; }

.badge-total {
    display: inline-block; background: #e8eaf6; color: #3949ab;
    border-radius: 20px; padding: 2px 14px; font-size: 0.8rem; font-weight: 700;
}

.btn-manage {
    background: #5c6bc0; color: #fff; border: none; border-radius: 6px;
    padding: 7px 20px; font-size: 0.8rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-manage:hover { background: #3949ab; color: #fff; }

.btn-add-year {
    background: #43a047; color: #fff; border: none; border-radius: 6px;
    padding: 8px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-add-year:hover { background: #2e7d32; color: #fff; }

.cy-empty { text-align: center; padding: 40px; color: #aaa; }
</style>
@endpush

@section('content')
<div class="cy-page">
    <div class="cy-card">
        <div class="cy-icon cy-icon-year"><i class="bi bi-journal-bookmark-fill"></i></div>
        <div class="cy-card-header">
            <div class="cy-card-title">หลักสูตร / แผนการเรียน — เลือกปีการศึกษา</div>
            <a href="{{ route('curriculums.create') }}" class="btn-add-year">
                <i class="bi bi-plus-lg"></i> สร้างหลักสูตรใหม่
            </a>
        </div>

        <table class="cy-table">
            <thead>
                <tr>
                    <th style="width:60px">ลำดับ</th>
                    <th>ปีการศึกษา</th>
                    <th style="text-align:center">จำนวนหลักสูตร</th>
                    <th style="text-align:center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($years as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $row->year_applied }}</strong></td>
                    <td style="text-align:center">
                        <span class="badge-total">{{ $row->total }} หลักสูตร</span>
                    </td>
                    <td style="text-align:center">
                        <a href="{{ route('curriculums.byYear', $row->year_applied) }}" class="btn-manage">
                            <i class="bi bi-folder2-open"></i> จัดการหลักสูตร
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="cy-empty">
                        <i class="bi bi-journal-x" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                        ยังไม่มีหลักสูตร — กด "สร้างหลักสูตรใหม่" เพื่อเริ่มต้น
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
