@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:22px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; }
    .filter-row { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
    .filter-input, .filter-select { border:1px solid #d0d7e5; border-radius:6px; padding:8px 12px; font-size:.88rem; font-family:inherit; outline:none; }
    table { width:100%; border-collapse:collapse; font-size:.9rem; }
    thead th { background:#f2f6ff; color:#082b75; font-weight:600; padding:10px 12px; text-align:left; }
    tbody td { padding:9px 12px; border-bottom:1px solid #f0f2f7; color:#444; }
    .badge2 { padding:3px 12px; border-radius:20px; font-size:.8rem; font-weight:600; }
    .b-active { background:#eef4ff; color:#2563eb; }
    .b-returned { background:#dcfce7; color:#16a34a; }
    .b-bad { background:#fee2e2; color:#dc2626; }
    .empty { text-align:center; color:#94a3b8; padding:26px 0; }
</style>
@endpush

@section('content')
@php
    $badgeClass = fn($s) => $s === 'คืนแล้ว' ? 'b-returned' : (in_array($s, ['ชำรุด','สูญหาย']) ? 'b-bad' : 'b-active');
@endphp
<div class="page">
    <nav class="breadcrumb-custom mb-3">
        <a href="#">รายงานห้องสมุด</a><i class="bi bi-chevron-right"></i>
        <span style="color:#555;">รายงานยืม-คืนหนังสือ</span>
    </nav>

    <div class="card2">
        <div class="card-title">
            <span><i class="fas fa-book-open-reader" style="color:#4b7ce3;"></i> ประวัติการยืม-คืน ({{ $loans->count() }})</span>
        </div>

        <form method="GET" action="{{ route('library.reports.loans') }}" class="filter-row">
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">ทุกสถานะ</option>
                @foreach(['ยืมอยู่','คืนแล้ว','ชำรุด','สูญหาย'] as $s)
                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <input type="date" name="from" class="filter-input" value="{{ $from }}">
            <input type="date" name="to" class="filter-input" value="{{ $to }}">
            <button type="submit" class="filter-select" style="background:#00bcd4; color:#fff; border:none; cursor:pointer;">กรอง</button>
        </form>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>หนังสือ</th><th>ผู้ยืม</th><th>ประเภท</th><th>วันที่ยืม</th><th>กำหนดคืน</th><th>วันที่คืน</th><th style="text-align:center;">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                        <tr>
                            <td>{{ $loan->book->title ?? '-' }}</td>
                            <td>{{ $loan->borrower_name }} ({{ $loan->borrower_code }})</td>
                            <td>{{ $loan->borrower_type === 'student' ? 'นักเรียน' : 'บุคลากร' }}</td>
                            <td>{{ $loan->borrowed_at?->format('d/m/Y') }}</td>
                            <td>{{ $loan->due_at?->format('d/m/Y') }}</td>
                            <td>{{ $loan->returned_at?->format('d/m/Y') ?? '-' }}</td>
                            <td style="text-align:center;"><span class="badge2 {{ $badgeClass($loan->status) }}">{{ $loan->status }}</span></td>
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
