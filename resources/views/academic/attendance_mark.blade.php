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
        width:56px; font-size:.78rem; padding:3px 2px; border-radius:4px; border:1px solid #d0d7e5;
        background:#fff; color:#444; font-family:inherit; cursor:pointer; transition:opacity .15s;
    }
    .att-cell.s-มา  { background:#dcfce7; border-color:#16a34a; color:#15803d; }
    .att-cell.s-ลา  { background:#eef4ff; border-color:#2563eb; color:#1d4ed8; }
    .att-cell.s-ขาด { background:#fee2e2; border-color:#dc2626; color:#b91c1c; }
    .att-cell.att-saving { opacity:.45; }
    .att-cell.att-save-error { outline:2px solid #dc2626; }
    .att-grid th.att-holiday-col { background:#e0e0e0; color:#707070; }
    .att-legend { display:flex; gap:14px; flex-wrap:wrap; font-size:.8rem; color:#555; margin-top:10px; }
    .att-legend span { display:inline-flex; align-items:center; gap:5px; }
    .att-legend i { width:12px; height:12px; border-radius:3px; display:inline-block; }
    #attSaveToast {
        position:fixed; bottom:20px; right:20px; background:#212529; color:#fff; padding:9px 18px;
        border-radius:6px; font-size:.82rem; opacity:0; transform:translateY(8px); transition:opacity .2s,transform .2s;
        pointer-events:none; z-index:2000;
    }
    #attSaveToast.show { opacity:1; transform:translateY(0); }
    #attSaveToast.error { background:#dc2626; }
</style>
@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb">
        <a href="{{ route('attendance.index') }}">เช็คชื่อ/ลา</a><i class="bi bi-chevron-right"></i>
        <span>{{ $assign->subject->name_th }} — {{ $assign->classSection->full_name ?? '' }}</span>
    </nav>

    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-calendar-month"></i> เลือกเดือน</div>
        <div class="ac-card-body">
            <form method="GET" action="{{ route('attendance.mark', $assign->assign_id) }}" style="display:flex; gap:12px; align-items:flex-end;">
                <div class="ac-field" style="margin:0">
                    <label>เดือน</label>
                    <input type="month" name="month" value="{{ $monthValue }}" class="ac-input" onchange="this.form.submit()">
                </div>
            </form>
            <p style="font-size:.8rem;color:#999;margin:10px 0 0">
                แสดงครบทุกวันของเดือนนี้ ({{ $dates->count() }} วัน) — คอลัมน์สีเทาคือวันเสาร์-อาทิตย์/วันหยุดตามปฏิทิน
                ({{ $dates->filter(fn($d) => $d->is_school_day)->count() }} วันเรียนจริง) แต่ยังเช็คชื่อได้ตามปกติเผื่อมีเรียนจริง —
                เลือกสถานะแล้วบันทึกให้ทันทีไม่ต้องกดปุ่มบันทึก
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
                <div class="att-grid-wrap">
                    <table class="att-grid">
                        <thead>
                            <tr>
                                <th class="att-sticky-col" style="width:44px">เลขที่</th>
                                <th class="att-sticky-col att-name-col">ชื่อ - สกุล</th>
                                @foreach($dates as $d)
                                    <th class="{{ $d->is_school_day ? '' : 'att-holiday-col' }}">{{ $d->date->format('d/m') }}<br><span style="font-weight:400;color:#789">{{ ['','จ','อ','พ','พฤ','ศ','ส','อา'][(int)$d->date->format('N')] }}</span></th>
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
                                            $dateStr = $d->date->format('Y-m-d');
                                            $cur = $existing->get($s->student_id . '|' . $dateStr)?->status ?? '';
                                        @endphp
                                        <td>
                                            <select class="att-cell {{ $cur ? 's-' . $cur : '' }}"
                                                data-date="{{ $dateStr }}" data-student="{{ $s->student_id }}"
                                                onchange="attSaveCell(this)">
                                                <option value="" {{ $cur === '' ? 'selected' : '' }}>-</option>
                                                @foreach(\App\Models\Academic\ClassAttendance::STATUSES as $st)
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
                    <span><i style="background:#eef4ff;border:1px solid #2563eb"></i> ลา</span>
                    <span><i style="background:#fee2e2;border:1px solid #dc2626"></i> ขาด</span>
                    <span><i style="background:#fff;border:1px solid #d0d7e5"></i> ยังไม่เช็ค</span>
                </div>
            @endif
        </div>
    </div>
</div>
<div id="attSaveToast"></div>
<script>
const attStoreCellUrl = "{{ url('/attendance') }}/{{ $assign->assign_id }}/cell";
const attCsrfToken = document.querySelector('meta[name="csrf-token"]').content;
let attToastTimer = null;

function attShowToast(text, isError) {
    const toast = document.getElementById('attSaveToast');
    toast.textContent = text;
    toast.classList.toggle('error', !!isError);
    toast.classList.add('show');
    clearTimeout(attToastTimer);
    attToastTimer = setTimeout(() => toast.classList.remove('show'), 1500);
}

function attColorCell(select) {
    select.classList.remove('s-มา', 's-ลา', 's-ขาด');
    if (select.value) select.classList.add('s-' + select.value);
}

function attSaveCell(select) {
    const prevValue = select.dataset.savedValue ?? '';
    attColorCell(select);
    select.classList.add('att-saving');
    select.classList.remove('att-save-error');
    select.disabled = true;

    fetch(attStoreCellUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': attCsrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ date: select.dataset.date, student_id: select.dataset.student, status: select.value }),
    })
    .then(async (res) => {
        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            throw new Error(data.message || 'บันทึกไม่สำเร็จ');
        }
        select.dataset.savedValue = select.value;
        attShowToast('บันทึกแล้ว');
    })
    .catch((err) => {
        select.value = prevValue;
        attColorCell(select);
        select.classList.add('att-save-error');
        attShowToast(err.message || 'บันทึกไม่สำเร็จ ลองใหม่อีกครั้ง', true);
    })
    .finally(() => {
        select.classList.remove('att-saving');
        select.disabled = false;
    });
}

document.querySelectorAll('select.att-cell').forEach(sel => { sel.dataset.savedValue = sel.value; });
</script>
@endsection
