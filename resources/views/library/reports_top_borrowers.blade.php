@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; max-width:820px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:22px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:16px; }
    .filter-row { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
    .filter-input { border:1px solid #d0d7e5; border-radius:6px; padding:8px 12px; font-size:.88rem; font-family:inherit; outline:none; }
    table { width:100%; border-collapse:collapse; font-size:.9rem; }
    thead th { background:#f2f6ff; color:#082b75; font-weight:600; padding:10px 12px; text-align:left; }
    tbody td { padding:9px 12px; border-bottom:1px solid #f0f2f7; color:#444; }
    .rank { display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:50%; background:#eef4ff; color:#2563eb; font-weight:700; font-size:.82rem; }
    .rank.top1 { background:#fef3c7; color:#b45309; }
    .rank.top2 { background:#e5e7eb; color:#4b5563; }
    .rank.top3 { background:#fde2c8; color:#c2410c; }
    .empty { text-align:center; color:#94a3b8; padding:26px 0; }
</style>
@endpush

@section('content')
<div class="page">
    <nav class="breadcrumb-custom mb-3">
        <a href="#">รายงานห้องสมุด</a><i class="bi bi-chevron-right"></i>
        <span style="color:#555;">รายงานผู้ยืมหนังสือมากที่สุด</span>
    </nav>

    <div class="card2">
        <div class="card-title"><i class="fas fa-trophy" style="color:#d97706;"></i> อันดับผู้ยืมหนังสือมากที่สุด (Top 20)</div>

        <form method="GET" action="{{ route('library.reports.topBorrowers') }}" class="filter-row">
            <label class="align-self-center">ตั้งแต่วันที่</label>
            <input type="date" name="from" class="filter-input" value="{{ $from }}">
            <label class="align-self-center">ถึงวันที่</label>
            <input type="date" name="to" class="filter-input" value="{{ $to }}">
            <button type="submit" class="filter-input" style="background:#00bcd4; color:#fff; border:none; cursor:pointer;">กรอง</button>
        </form>

        <table>
            <thead>
                <tr><th style="width:50px;">อันดับ</th><th>ชื่อ - สกุล</th><th>รหัส</th><th>ประเภท</th><th style="text-align:center;">จำนวนครั้งที่ยืม</th></tr>
            </thead>
            <tbody>
                @forelse($ranking as $i => $r)
                    <tr>
                        <td><span class="rank {{ $i === 0 ? 'top1' : ($i === 1 ? 'top2' : ($i === 2 ? 'top3' : '')) }}">{{ $i + 1 }}</span></td>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->code }}</td>
                        <td>{{ $r->type }}</td>
                        <td style="text-align:center; font-weight:700; color:#082b75;">{{ $r->count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty"><i class="fas fa-inbox"></i> ไม่มีข้อมูลการยืม</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
