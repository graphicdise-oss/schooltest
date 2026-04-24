@extends('layouts.sidebar')
@push('styles')
<style>
.ti-page { padding: 24px 28px; }

.ti-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 20px 20px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.ti-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.ti-icon-search { background: #00bcd4; }
.ti-icon-list   { background: #607d8b; }
.ti-card-title  { margin-left: 90px; font-size: 1.05rem; color: #555; margin-top: -8px; }

.ti-search-grid {
    display: grid; grid-template-columns: 1fr 1fr 1fr auto;
    gap: 14px 24px; margin-top: 20px; align-items: end;
}
.ti-field label { font-size: 0.82rem; font-weight: 600; color: #444; margin-bottom: 3px; display: block; }
.ti-field select {
    width: 100%; height: 36px; border: none; border-bottom: 1.5px solid #bbb;
    padding: 0 8px; font-size: 0.88rem; font-family: inherit; outline: none;
    background: transparent; box-sizing: border-box;
}
.ti-field select:focus { border-bottom-color: #00bcd4; }
.btn-search {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px;
    padding: 9px 28px; font-size: 0.88rem; font-weight: 600;
    cursor: pointer; font-family: inherit; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 6px;
}

/* Table */
.ti-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 20px; }
.ti-table thead th {
    padding: 12px 14px; background: #5c6bc0; color: #fff;
    font-weight: 600; text-align: left; font-size: 0.85rem;
}
.ti-table thead th:first-child { border-radius: 6px 0 0 0; }
.ti-table thead th:last-child  { border-radius: 0 6px 0 0; text-align: center; }
.ti-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.ti-table tbody tr:hover { background: #f5f7ff; }
.ti-table tbody td { padding: 12px 14px; color: #555; vertical-align: middle; }

.badge-count {
    display: inline-block; background: #e8f5e9; color: #388e3c;
    border-radius: 20px; padding: 2px 12px; font-size: 0.78rem; font-weight: 700;
}
.badge-slot {
    display: inline-block; background: #e3f2fd; color: #1565c0;
    border-radius: 20px; padding: 2px 12px; font-size: 0.78rem; font-weight: 700;
}

.btn-go {
    background: #5c6bc0; color: #fff; border: none; border-radius: 6px;
    padding: 7px 18px; font-size: 0.8rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-go:hover { background: #3949ab; color: #fff; }

.ti-empty { text-align: center; padding: 40px; color: #aaa; }
</style>
@endpush

@section('content')
<div class="ti-page">

    {{-- ค้นหา --}}
    <div class="ti-card">
        <div class="ti-icon ti-icon-search"><i class="bi bi-search"></i></div>
        <div class="ti-card-title">ค้นหา</div>

        <form method="GET" action="{{ route('timetable.index') }}">
            <div class="ti-search-grid">
                <div class="ti-field">
                    <label>ปีการศึกษา / ภาคเรียน</label>
                    <select name="semester_id" onchange="this.form.submit()">
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->semester_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>
                                {{ $sem->academicYear->year_name ?? '' }} ภาคเรียนที่ {{ $sem->semester_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ti-field">
                    <label>ระดับชั้น</label>
                    <select name="level_id" onchange="this.form.submit()">
                        <option value="">-- ทุกระดับ --</option>
                        @foreach($levels as $lv)
                            <option value="{{ $lv->level_id }}" {{ request('level_id')==$lv->level_id?'selected':'' }}>
                                {{ $lv->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ti-field" style="visibility:hidden"><label>x</label><select></select></div>
                <div class="ti-field">
                    <button type="submit" class="btn-search"><i class="bi bi-search"></i> ค้นหา</button>
                </div>
            </div>
        </form>
    </div>

    {{-- รายการ --}}
    <div class="ti-card">
        <div class="ti-icon ti-icon-list"><i class="bi bi-calendar-week"></i></div>
        <div class="ti-card-title">ตารางสอนแต่ละชั้นเรียน</div>

        <table class="ti-table">
            <thead>
                <tr>
                    <th style="width:60px">ลำดับ</th>
                    <th>ระดับชั้น</th>
                    <th>ห้อง</th>
                    <th>อาจารย์ประจำชั้น</th>
                    <th style="text-align:center">วิชาที่มอบหมาย</th>
                    <th style="text-align:center">คาบที่กำหนด</th>
                    <th style="text-align:center">จัดการตาราง</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sections as $i => $sec)
                @php
                    $subjectCount = $sec->teachingAssigns->count();
                    $slotCount    = $sec->teachingAssigns->sum(fn($a) => $a->timetableSlots->count());
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $sec->level->name ?? '-' }}</td>
                    <td><strong>{{ $sec->level->name ?? '' }}/{{ $sec->section_number }}</strong></td>
                    <td>
                        @if($sec->homeroomTeacher)
                            {{ $sec->homeroomTeacher->thai_prefix }}{{ $sec->homeroomTeacher->thai_firstname }}
                            {{ $sec->homeroomTeacher->thai_lastname }}
                        @else
                            <span style="color:#bbb">ยังไม่กำหนด</span>
                        @endif
                    </td>
                    <td style="text-align:center"><span class="badge-count">{{ $subjectCount }} วิชา</span></td>
                    <td style="text-align:center"><span class="badge-slot">{{ $slotCount }} คาบ</span></td>
                    <td style="text-align:center">
                        <a href="{{ route('timetable.section', $sec->section_id) }}" class="btn-go">
                            <i class="bi bi-pencil-square"></i> จัดการตาราง
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="ti-empty">
                        <i class="bi bi-calendar-x" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                        ไม่พบห้องเรียนในภาคเรียนนี้
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
