@extends('layouts.sidebar')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">
<style>
    .att-status-group { display:flex; gap:6px; flex-wrap:wrap; }
    .att-status-opt { display:inline-flex; }
    .att-status-opt input { display:none; }
    .att-status-opt span {
        padding:6px 14px; border-radius:20px; font-size:.82rem; font-weight:600; cursor:pointer;
        border:1.5px solid #d0d7e5; color:#666; user-select:none;
    }
    .att-status-opt input:checked + span.s-มา { background:#dcfce7; border-color:#16a34a; color:#16a34a; }
    .att-status-opt input:checked + span.s-ป่วย { background:#fff7ed; border-color:#d97706; color:#d97706; }
    .att-status-opt input:checked + span.s-ลา { background:#eef4ff; border-color:#2563eb; color:#2563eb; }
    .att-status-opt input:checked + span.s-ขาด { background:#fee2e2; border-color:#dc2626; color:#dc2626; }
    .recent-date-pill { display:inline-block; background:#eef4ff; color:#2563eb; border-radius:20px; padding:4px 12px; font-size:.8rem; margin:2px; text-decoration:none; }
    .recent-date-pill:hover { background:#dbe8ff; }
</style>
@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb">
        <a href="{{ route('attendance.index') }}">เช็คชื่อ/ลา</a><i class="bi bi-chevron-right"></i>
        <span>{{ $assign->subject->name_th }} — {{ $assign->classSection->level->name ?? '' }}/{{ $assign->classSection->section_number }}</span>
    </nav>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-calendar-check"></i> เลือกวันที่เช็คชื่อ</div>
        <div class="ac-card-body">
            <form method="GET" action="{{ route('attendance.mark', $assign->assign_id) }}" style="display:flex; gap:12px; align-items:flex-end; margin-bottom:12px;">
                <div class="ac-field" style="margin:0">
                    <label>วันที่</label>
                    <input type="date" name="date" value="{{ $date }}" class="ac-input" onchange="this.form.submit()">
                </div>
            </form>
            @if($recentDates->isNotEmpty())
                <div>
                    <span style="font-size:.85rem; color:#6b7a99;">เช็คชื่อล่าสุด:</span>
                    @foreach($recentDates as $rd)
                        <a class="recent-date-pill" href="{{ route('attendance.mark', ['assign' => $assign->assign_id, 'date' => $rd->class_date]) }}">
                            {{ \Carbon\Carbon::parse($rd->class_date)->format('d/m/Y') }} ({{ $rd->total }})
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-people"></i> รายชื่อนักเรียน — วันที่ {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
        <div class="ac-card-body">
            @if($students->isEmpty())
                <p class="ac-empty">ไม่มีนักเรียนในห้องนี้</p>
            @else
                <form method="POST" action="{{ route('attendance.store', $assign->assign_id) }}">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <div class="ac-table-wrap">
                        <table class="ac-table">
                            <thead><tr><th style="width:60px;">เลขที่</th><th>ชื่อ - สกุล</th><th style="width:340px;">สถานะ</th></tr></thead>
                            <tbody>
                                @foreach($students as $s)
                                    @php $cur = $existing->get($s->student_id)?->status ?? 'มา'; @endphp
                                    <tr>
                                        <td>{{ $s->student_number }}</td>
                                        <td style="text-align:left">{{ $s->student->thai_prefix }}{{ $s->student->thai_firstname }} {{ $s->student->thai_lastname }}</td>
                                        <td>
                                            <div class="att-status-group">
                                                @foreach(\App\Models\Academic\ClassAttendance::STATUSES as $st)
                                                    <label class="att-status-opt">
                                                        <input type="radio" name="status[{{ $s->student_id }}]" value="{{ $st }}" {{ $cur === $st ? 'checked' : '' }}>
                                                        <span class="s-{{ $st }}">{{ $st }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="ac-save-wrap" style="margin-top:16px; text-align:right;">
                        <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึกการเช็คชื่อ</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
