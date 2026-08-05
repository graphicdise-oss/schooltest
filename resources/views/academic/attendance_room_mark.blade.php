@extends('layouts.sidebar')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">
<style>
    .att-grid-wrap { overflow-x:auto; }
    .att-grid { border-collapse:collapse; font-size:.82rem; white-space:nowrap; }
    .att-grid th, .att-grid td { border:1px solid #e5e9f0; padding:4px 6px; text-align:center; }
    .att-grid thead th { background:#eaf2f8; font-weight:600; position:sticky; top:0; z-index:2; }
    .att-grid th.att-sticky-col, .att-grid td.att-sticky-col { position:sticky; left:0; background:#fff; z-index:1; text-align:left; }
    .att-grid thead th.att-sticky-col { background:#eaf2f8; z-index:3; }
    .att-grid td.att-name-col { min-width:160px; }
    .att-grid select.att-cell {
        width:64px; font-size:.78rem; padding:3px 2px; border-radius:4px; border:1px solid #d0d7e5;
        background:#fff; color:#444; font-family:inherit; cursor:pointer;
    }
    .att-cell.s-มา   { background:#dcfce7; border-color:#16a34a; color:#15803d; }
    .att-cell.s-ไม่มา { background:#fee2e2; border-color:#dc2626; color:#b91c1c; }
    .att-legend { display:flex; gap:14px; flex-wrap:wrap; font-size:.8rem; color:#555; margin-top:10px; }
    .att-legend span { display:inline-flex; align-items:center; gap:5px; }
    .att-legend i { width:12px; height:12px; border-radius:3px; display:inline-block; }
</style>
@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb">
        <a href="{{ route('attendance.index') }}">เช็คชื่อ/ลา</a><i class="bi bi-chevron-right"></i>
        <span>เช็คชื่อรวมทั้งห้อง — {{ $section->full_name ?? '' }}</span>
    </nav>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-calendar-month"></i> เลือกเดือน</div>
        <div class="ac-card-body">
            <form method="GET" action="{{ route('attendance.roomMark', $section->section_id) }}" style="display:flex; gap:12px; align-items:flex-end;">
                <div class="ac-field" style="margin:0">
                    <label>เดือน</label>
                    <input type="month" name="month" value="{{ $monthValue }}" class="ac-input" onchange="this.form.submit()">
                </div>
            </form>
            <p style="font-size:.8rem;color:#999;margin:10px 0 0">
                เช็คชื่อรวมทั้งห้อง ไม่ต้องเปิดเช็คทีละวิชา — ตัดวันเสาร์-อาทิตย์และวันหยุดตามปฏิทินออกให้อัตโนมัติ เหลือเฉพาะวันเรียนจริงของเดือนนี้ ({{ $dates->count() }} วัน)
            </p>
        </div>
    </div>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-people"></i> รายชื่อนักเรียน</div>
        <div class="ac-card-body">
            @if($students->isEmpty())
                <p class="ac-empty">ไม่มีนักเรียนในห้องนี้</p>
            @elseif($dates->isEmpty())
                <p class="ac-empty">ไม่มีวันเรียนในเดือนนี้ (อาจอยู่นอกภาคเรียน หรือเป็นวันหยุดทั้งเดือน)</p>
            @else
                <form method="POST" action="{{ route('attendance.roomStore', $section->section_id) }}" id="attRoomMarkForm">
                    @csrf
                    <input type="hidden" name="month" value="{{ $monthValue }}">
                    <div class="att-grid-wrap">
                        <table class="att-grid">
                            <thead>
                                <tr>
                                    <th class="att-sticky-col" style="width:44px">เลขที่</th>
                                    <th class="att-sticky-col att-name-col">ชื่อ - สกุล</th>
                                    @foreach($dates as $d)
                                        <th>{{ $d->format('d/m') }}<br><span style="font-weight:400;color:#789">{{ ['','จ','อ','พ','พฤ','ศ','ส','อา'][(int)$d->format('N')] }}</span></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $s)
                                    <tr>
                                        <td class="att-sticky-col">{{ $s->student_number }}</td>
                                        <td class="att-sticky-col att-name-col">{{ $s->student->thai_prefix }}{{ $s->student->thai_firstname }} {{ $s->student->thai_lastname }}</td>
                                        @foreach($dates as $d)
                                            @php
                                                $dateStr = $d->format('Y-m-d');
                                                $cur = $existing->get($s->student_id . '|' . $dateStr)?->status ?? '';
                                            @endphp
                                            <td>
                                                <select name="status[{{ $dateStr }}][{{ $s->student_id }}]" class="att-cell {{ $cur ? 's-' . $cur : '' }}" onchange="attRoomColorCell(this)">
                                                    <option value="" {{ $cur === '' ? 'selected' : '' }}>-</option>
                                                    @foreach(\App\Models\Academic\RoomAttendance::STATUSES as $st)
                                                        <option value="{{ $st }}" {{ $cur === $st ? 'selected' : '' }}>{{ $st }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="att-legend">
                        <span><i style="background:#dcfce7;border:1px solid #16a34a"></i> มา</span>
                        <span><i style="background:#fee2e2;border:1px solid #dc2626"></i> ไม่มา</span>
                        <span><i style="background:#fff;border:1px solid #d0d7e5"></i> ยังไม่เช็ค (ไม่บันทึก)</span>
                    </div>
                    <div class="ac-save-wrap" style="margin-top:16px; text-align:right;">
                        <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึกการเช็คชื่อรวม</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
<script>
function attRoomColorCell(select) {
    select.classList.remove('s-มา', 's-ไม่มา');
    if (select.value) select.classList.add('s-' + select.value);
}
</script>
@endsection
