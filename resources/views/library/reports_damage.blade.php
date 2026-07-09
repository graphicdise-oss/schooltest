@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:22px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:16px; }
    .filter-row { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
    .filter-select { border:1px solid #d0d7e5; border-radius:6px; padding:8px 12px; font-size:.88rem; font-family:inherit; outline:none; }
    table { width:100%; border-collapse:collapse; font-size:.9rem; }
    thead th { background:#f2f6ff; color:#082b75; font-weight:600; padding:10px 12px; text-align:left; }
    tbody td { padding:9px 12px; border-bottom:1px solid #f0f2f7; color:#444; }
    .badge2 { padding:3px 12px; border-radius:20px; font-size:.8rem; font-weight:600; }
    .b-wait { background:#fff7ed; color:#c2410c; }
    .b-fixed { background:#dcfce7; color:#16a34a; }
    .b-off { background:#e5e7eb; color:#4b5563; }
    .btn-sm2 { border:none; border-radius:6px; padding:5px 12px; font-size:.78rem; font-weight:600; cursor:pointer; margin-right:4px; }
    .empty { text-align:center; color:#94a3b8; padding:26px 0; }
</style>
@endpush

@section('content')
@php
    $badgeClass = fn($s) => $s === 'ซ่อมแล้ว' ? 'b-fixed' : ($s === 'จำหน่ายออก' ? 'b-off' : 'b-wait');
@endphp
<div class="page">
    <nav class="breadcrumb-custom mb-3">
        <a href="#">รายงานห้องสมุด</a><i class="bi bi-chevron-right"></i>
        <span style="color:#555;">รายงานแจ้งชำรุดเสียหาย</span>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card2">
        <div class="card-title"><i class="fas fa-book-medical" style="color:#dc2626;"></i> รายการแจ้งชำรุด/สูญหาย ({{ $reports->count() }})</div>

        <form method="GET" action="{{ route('library.reports.damage') }}" class="filter-row">
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">ทุกสถานะ</option>
                @foreach(['รอดำเนินการ','ซ่อมแล้ว','จำหน่ายออก'] as $s)
                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </form>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>หนังสือ</th><th>ผู้ยืม</th><th>รายละเอียด</th><th>วันที่แจ้ง</th><th style="text-align:center;">สถานะ</th><th style="text-align:right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $r)
                        <tr>
                            <td>{{ $r->book->title ?? '-' }}</td>
                            <td>{{ $r->loan?->borrower_name ?? '-' }}</td>
                            <td>{{ $r->description ?: '-' }}</td>
                            <td>{{ $r->reported_at?->format('d/m/Y') }}</td>
                            <td style="text-align:center;"><span class="badge2 {{ $badgeClass($r->status) }}">{{ $r->status }}</span></td>
                            <td style="text-align:right; white-space:nowrap;">
                                @if($r->status === 'รอดำเนินการ')
                                    <form action="{{ route('library.reports.damage.resolve', $r->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="ซ่อมแล้ว">
                                        <button type="submit" class="btn-sm2" style="background:#16a34a; color:#fff;">ซ่อมแล้ว</button>
                                    </form>
                                    <form action="{{ route('library.reports.damage.resolve', $r->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="จำหน่ายออก">
                                        <button type="submit" class="btn-sm2" style="background:#6b7280; color:#fff;">จำหน่ายออก</button>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty"><i class="fas fa-check-circle"></i> ไม่มีรายการแจ้งชำรุด/สูญหาย</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
