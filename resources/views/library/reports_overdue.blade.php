@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:22px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:16px; }
    table { width:100%; border-collapse:collapse; font-size:.9rem; }
    thead th { background:#fff1e6; color:#b45309; font-weight:600; padding:10px 12px; text-align:left; }
    tbody td { padding:9px 12px; border-bottom:1px solid #f0f2f7; color:#444; }
    .badge2 { padding:3px 12px; border-radius:20px; font-size:.8rem; font-weight:600; background:#fee2e2; color:#dc2626; }
    .empty { text-align:center; color:#94a3b8; padding:26px 0; }
</style>
@endpush

@section('content')
<div class="page">
    <nav class="breadcrumb-custom mb-3">
        <a href="#">รายงานห้องสมุด</a><i class="bi bi-chevron-right"></i>
        <span style="color:#555;">รายงานค้างส่ง</span>
    </nav>

    <div class="card2">
        <div class="card-title"><i class="fas fa-triangle-exclamation" style="color:#d97706;"></i> รายการค้างส่ง ({{ $loans->count() }})</div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>หนังสือ</th><th>ผู้ยืม</th><th>ประเภท</th><th>วันที่ยืม</th><th>กำหนดคืน</th><th style="text-align:center;">เกินมา (วัน)</th>
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
                            <td style="text-align:center;"><span class="badge2">{{ $loan->due_at?->diffInDays(now()) }} วัน</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty"><i class="fas fa-check-circle"></i> ไม่มีรายการค้างส่ง</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
