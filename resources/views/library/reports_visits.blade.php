@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:22px; margin-bottom:20px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:16px; }
    .filter-row { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px; align-items:center; }
    .filter-input { border:1px solid #d0d7e5; border-radius:6px; padding:8px 12px; font-size:.88rem; font-family:inherit; outline:none; }
    .chart-row { display:flex; align-items:flex-end; gap:10px; height:150px; padding:10px 0; overflow-x:auto; }
    .bar-col { display:flex; flex-direction:column; align-items:center; min-width:50px; }
    .bar { width:28px; background:linear-gradient(180deg,#4b7ce3,#2563eb); border-radius:6px 6px 0 0; }
    .bar-label { font-size:11px; color:#6b7a99; margin-top:6px; white-space:nowrap; }
    .bar-count { font-size:12px; color:#082b75; font-weight:700; margin-bottom:4px; }
    table { width:100%; border-collapse:collapse; font-size:.9rem; }
    thead th { background:#f2f6ff; color:#082b75; font-weight:600; padding:10px 12px; text-align:left; }
    tbody td { padding:9px 12px; border-bottom:1px solid #f0f2f7; color:#444; }
    .empty { text-align:center; color:#94a3b8; padding:26px 0; }
</style>
@endpush

@section('content')
@php $maxCount = $byDate->max() ?: 1; @endphp
<div class="page">
    <nav class="breadcrumb-custom mb-3">
        <a href="#">รายงานห้องสมุด</a><i class="bi bi-chevron-right"></i>
        <span style="color:#555;">รายงานเข้าใช้ห้องสมุด (สถิติ)</span>
    </nav>

    <div class="card2">
        <div class="card-title"><i class="fas fa-chart-column" style="color:#4b7ce3;"></i> สถิติการเข้าใช้ห้องสมุด</div>

        <form method="GET" action="{{ route('library.reports.visits') }}" class="filter-row">
            <label>ตั้งแต่วันที่</label>
            <input type="date" name="from" class="filter-input" value="{{ $from }}">
            <label>ถึงวันที่</label>
            <input type="date" name="to" class="filter-input" value="{{ $to }}">
            <button type="submit" class="filter-input" style="background:#00bcd4; color:#fff; border:none; cursor:pointer;">กรอง</button>
            <span style="margin-left:auto; color:#2563eb; font-weight:700;">รวม {{ number_format($visits->count()) }} ครั้ง</span>
        </form>

        @if($byDate->isNotEmpty())
            <div class="chart-row">
                @foreach($byDate as $date => $count)
                    <div class="bar-col">
                        <div class="bar-count">{{ $count }}</div>
                        <div class="bar" style="height:{{ max(6, ($count / $maxCount) * 110) }}px;"></div>
                        <div class="bar-label">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="empty"><i class="fas fa-inbox"></i> ไม่มีข้อมูลในช่วงที่เลือก</p>
        @endif
    </div>

    <div class="card2">
        <div class="card-title"><i class="fas fa-list" style="color:#4b7ce3;"></i> รายการเข้าใช้ล่าสุด</div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr><th style="width:150px;">วันที่/เวลา</th><th>ชื่อ - สกุล</th><th style="width:110px;">ประเภท</th></tr>
                </thead>
                <tbody>
                    @forelse($visits->take(100) as $v)
                        <tr>
                            <td>{{ $v->visited_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $v->visitor_name }}</td>
                            <td>{{ $v->visitor_type === 'student' ? 'นักเรียน' : 'บุคลากร' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty"><i class="fas fa-inbox"></i> ไม่มีข้อมูล</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
