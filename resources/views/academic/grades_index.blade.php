@extends('layouts.sidebar')
@push('styles')
<style>
.gi-page { padding: 24px 28px; }

.gi-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 22px 22px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.gi-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.gi-icon-filter { background: #5c6bc0; }
.gi-icon-list   { background: #00897b; }

.gi-card-header {
    margin-left: 90px; display: flex; align-items: center;
    justify-content: space-between; margin-top: -8px; margin-bottom: 20px;
    flex-wrap: wrap; gap: 8px;
}
.gi-card-title { font-size: 1.05rem; color: #555; font-weight: 600; }

/* Semester dropdown */
.sem-select-wrap { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.sem-label { font-size: 0.85rem; font-weight: 600; color: #444; white-space: nowrap; }
.sem-select {
    border: none; border-bottom: 2px solid #5c6bc0;
    padding: 8px 12px; font-size: 0.95rem; font-family: inherit;
    color: #1a237e; font-weight: 600; background: transparent;
    outline: none; cursor: pointer; min-width: 260px;
}
.sem-select:focus { border-bottom-color: #3949ab; }

.btn-gpa {
    background: #43a047; color: #fff; border: none; border-radius: 6px;
    padding: 9px 18px; font-size: 0.83rem; font-weight: 600;
    cursor: pointer; font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-gpa:hover { background: #2e7d32; color: #fff; }

/* Current semester badge */
.sem-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: #e8eaf6; color: #3949ab; border-radius: 20px;
    padding: 5px 14px; font-size: 0.82rem; font-weight: 700;
    margin-top: 10px;
}

/* Section grid */
.sec-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 14px;
}
.sec-card {
    background: #f8faff; border: 1.5px solid #e3e8f5; border-radius: 10px;
    padding: 16px 14px; cursor: pointer; transition: all 0.15s; text-decoration: none;
    display: block;
}
.sec-card:hover { border-color: #5c6bc0; background: #eef0fb; box-shadow: 0 2px 8px rgba(92,107,192,0.15); }
.sec-card-level { font-size: 1.15rem; font-weight: 700; color: #3949ab; }
.sec-card-count { font-size: 0.78rem; color: #888; margin-top: 4px; }
.sec-card-icon  { font-size: 1.4rem; color: #c5cae9; margin-bottom: 6px; }

.gi-empty { text-align: center; padding: 40px; color: #aaa; }
</style>
@endpush

@section('content')
<div class="gi-page">

    {{-- Filter card --}}
    <div class="gi-card">
        <div class="gi-icon gi-icon-filter"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="gi-card-header">
            <span class="gi-card-title">ผลการเรียน</span>
            <a href="{{ route('grades.gpa') }}" class="btn-gpa">
                <i class="bi bi-trophy"></i> รายงาน GPA
            </a>
        </div>

        <form method="GET" action="{{ route('grades.index') }}">
            <div class="sem-select-wrap">
                <span class="sem-label"><i class="bi bi-calendar3"></i> ปีการศึกษา / ภาคเรียน</span>
                <select name="semester_id" class="sem-select" onchange="this.form.submit()">
                    @foreach($semesters as $sem)
                    <option value="{{ $sem->semester_id }}" {{ $semesterId == $sem->semester_id ? 'selected' : '' }}>
                        ปีการศึกษา {{ $sem->academicYear->year_name ?? '' }} — ภาคเรียนที่ {{ $sem->semester_name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </form>

        @php $currentSem = $semesters->firstWhere('semester_id', $semesterId); @endphp
        @if($currentSem)
        <div class="sem-badge">
            <i class="bi bi-check-circle-fill"></i>
            กำลังดู: ปีการศึกษา {{ $currentSem->academicYear->year_name ?? '' }} ภาคเรียน {{ $currentSem->semester_name }}
        </div>
        @endif
    </div>

    {{-- Section grid --}}
    <div class="gi-card">
        <div class="gi-icon gi-icon-list"><i class="bi bi-people-fill"></i></div>
        <div class="gi-card-header">
            <span class="gi-card-title">เลือกห้องเรียน</span>
            <span style="font-size:0.8rem;color:#aaa">{{ $sections->count() }} ห้อง</span>
        </div>

        @if($sections->count())
        <div class="sec-grid">
            @foreach($sections as $sec)
            <a href="{{ route('grades.section', $sec->section_id) }}" class="sec-card">
                <div class="sec-card-icon"><i class="bi bi-door-open"></i></div>
                <div class="sec-card-level">{{ $sec->level->name ?? '' }}/{{ $sec->section_number }}</div>
                <div class="sec-card-count">
                    <i class="bi bi-person" style="color:#5c6bc0"></i>
                    {{ $sec->studentSections->where('status','กำลังศึกษา')->count() }} คน
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="gi-empty">
            <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
            ไม่มีห้องเรียนในภาคเรียนนี้
        </div>
        @endif
    </div>

</div>
@endsection
