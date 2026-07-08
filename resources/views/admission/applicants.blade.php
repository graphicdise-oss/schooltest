@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:22px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:16px; }
    .tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
    .tab { padding:7px 16px; border-radius:20px; font-size:.88rem; text-decoration:none; color:#4b6aa5; background:#eef2f9; }
    .tab.active { background:#2563eb; color:#fff; font-weight:600; }
    table { width:100%; border-collapse:collapse; font-size:.9rem; }
    thead th { background:#f2f6ff; color:#082b75; font-weight:600; padding:11px 12px; text-align:left; white-space:nowrap; }
    tbody td { padding:10px 12px; border-bottom:1px solid #f0f2f7; color:#444; vertical-align:middle; }
    .badge2 { padding:3px 12px; border-radius:20px; font-size:.8rem; font-weight:600; }
    .b-wait { background:#fff7ed; color:#c2410c; }
    .b-pass { background:#dcfce7; color:#16a34a; }
    .b-fail { background:#fee2e2; color:#dc2626; }
    .btn-ap { background:#16a34a; color:#fff; border:none; border-radius:6px; padding:6px 12px; font-size:.82rem; cursor:pointer; }
    .btn-rj { background:#fff; color:#dc2626; border:1.5px solid #dc2626; border-radius:6px; padding:6px 12px; font-size:.82rem; cursor:pointer; }
    .empty { text-align:center; color:#94a3b8; padding:30px 0; }
</style>
@endpush

@section('content')
@php
    $tabs = ['รอการตรวจสอบ', 'ผ่าน', 'ไม่ผ่าน', 'ทั้งหมด'];
    $badge = ['รอการตรวจสอบ' => 'b-wait', 'ผ่าน' => 'b-pass', 'ไม่ผ่าน' => 'b-fail'];
@endphp
<div class="page">
    <nav class="breadcrumb-custom mb-3">
        <a href="#">รับสมัครนักเรียน</a><i class="bi bi-chevron-right"></i>
        <span style="color:#555;">ตรวจสอบผู้สมัคร</span>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card2">
        <div class="card-title"><i class="fas fa-user-check"></i> รายชื่อผู้สมัคร</div>

        <div class="tabs">
            @foreach($tabs as $t)
                <a href="{{ route('admissions.applicants', ['status' => $t]) }}"
                   class="tab {{ $status === $t ? 'active' : '' }}">{{ $t }}</a>
            @endforeach
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>วันที่สมัคร</th>
                        <th>ชื่อ - สกุล</th>
                        <th>เลขบัตร ปชช.</th>
                        <th>ระดับที่สมัคร</th>
                        <th>เบอร์โทร</th>
                        <th>สถานะ</th>
                        <th style="text-align:right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                        <tr>
                            <td>{{ $app->created_at?->format('d/m/Y') }}</td>
                            <td>
                                {{ $app->student->thai_prefix ?? '' }}{{ $app->student->thai_firstname ?? '-' }}
                                {{ $app->student->thai_lastname ?? '' }}
                            </td>
                            <td>{{ $app->student->id_card_number ?? '-' }}</td>
                            <td>{{ $app->level->name ?? '-' }}</td>
                            <td>{{ $app->applicant_phone ?: '-' }}</td>
                            <td><span class="badge2 {{ $badge[$app->status] ?? '' }}">{{ $app->status }}</span></td>
                            <td style="text-align:right; white-space:nowrap;">
                                @if($app->status === 'รอการตรวจสอบ')
                                    <form action="{{ route('admissions.approve', $app->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('อนุมัติผู้สมัครรายนี้? สถานะนักเรียนจะเป็น กำลังศึกษา')">
                                        @csrf
                                        <button class="btn-ap"><i class="fas fa-check"></i> อนุมัติ</button>
                                    </form>
                                    <form action="{{ route('admissions.reject', $app->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('ทำเครื่องหมายไม่ผ่านรายนี้?')">
                                        @csrf
                                        <button class="btn-rj"><i class="fas fa-xmark"></i> ไม่ผ่าน</button>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty"><i class="fas fa-inbox"></i> ไม่มีผู้สมัครในสถานะนี้</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
