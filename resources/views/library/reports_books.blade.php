@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:22px; margin-bottom:20px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:16px; }
    .stat-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:14px; margin-bottom:20px; }
    .stat-tile { background:#eef4ff; border-radius:10px; padding:14px; text-align:center; }
    .stat-tile .v { color:#2563eb; font-weight:700; font-size:22px; }
    .stat-tile .l { color:#6b7a99; font-size:13px; }
    .filter-row { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
    .filter-input, .filter-select { border:1px solid #d0d7e5; border-radius:6px; padding:8px 12px; font-size:.88rem; font-family:inherit; outline:none; min-width:180px; }
    table { width:100%; border-collapse:collapse; font-size:.9rem; }
    thead th { background:#f2f6ff; color:#082b75; font-weight:600; padding:10px 12px; text-align:left; }
    tbody td { padding:9px 12px; border-bottom:1px solid #f0f2f7; color:#444; }
    .empty { text-align:center; color:#94a3b8; padding:26px 0; }
</style>
@endpush

@section('content')
<div class="page">
    <nav class="breadcrumb-custom mb-3">
        <a href="#">รายงานห้องสมุด</a><i class="bi bi-chevron-right"></i>
        <span style="color:#555;">รายงานข้อมูลหนังสือ</span>
    </nav>

    <div class="card2">
        <div class="stat-row">
            <div class="stat-tile"><div class="v">{{ number_format($summary['titles']) }}</div><div class="l">ชื่อเรื่อง</div></div>
            <div class="stat-tile"><div class="v">{{ number_format($summary['copies']) }}</div><div class="l">จำนวนเล่มทั้งหมด</div></div>
            <div class="stat-tile"><div class="v">{{ number_format($summary['available']) }}</div><div class="l">พร้อมให้ยืม</div></div>
            <div class="stat-tile"><div class="v">{{ number_format($summary['on_loan']) }}</div><div class="l">ถูกยืมอยู่</div></div>
        </div>

        <form method="GET" action="{{ route('library.reports.books') }}" class="filter-row">
            <input type="text" name="search" class="filter-input" value="{{ $search }}" placeholder="ค้นหาชื่อหนังสือ">
            <select name="category_id" class="filter-select" onchange="this.form.submit()">
                <option value="">ทุกหมวดหมู่</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="filter-select" style="background:#00bcd4; color:#fff; border:none; cursor:pointer;">ค้นหา</button>
        </form>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>รหัส</th><th>ชื่อหนังสือ</th><th>ผู้แต่ง</th><th>หมวดหมู่</th>
                        <th style="text-align:center;">คงเหลือ</th><th style="text-align:center;">ทั้งหมด</th><th style="text-align:center;">ยืมอยู่</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books as $book)
                        <tr>
                            <td>{{ $book->code ?: '-' }}</td>
                            <td>{{ $book->title }}</td>
                            <td>{{ $book->author ?: '-' }}</td>
                            <td>{{ $book->category->name ?? '-' }}</td>
                            <td style="text-align:center;">{{ $book->available_copies }}</td>
                            <td style="text-align:center;">{{ $book->total_copies }}</td>
                            <td style="text-align:center;">{{ $book->on_loan_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty"><i class="fas fa-inbox"></i> ไม่มีข้อมูล</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
