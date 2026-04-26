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

/* Semester tabs */
.sem-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
.sem-tab {
    padding: 8px 20px; border-radius: 8px; font-size: 0.85rem; font-weight: 600;
    border: 2px solid #c5cae9; color: #3949ab; background: #fff; cursor: pointer;
    font-family: inherit; transition: all 0.15s; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 2px;
}
.sem-tab:hover { border-color: #5c6bc0; background: #e8eaf6; }
.sem-tab.active { background: #5c6bc0; color: #fff; border-color: #5c6bc0; }
.sem-tab .sem-year { font-size: 0.78rem; font-weight: 700; }
.sem-tab .sem-term { font-size: 0.72rem; opacity: 0.8; }

/* Section grid */
.sec-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
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

.btn-gpa {
    background: #43a047; color: #fff; border: none; border-radius: 6px;
    padding: 8px 18px; font-size: 0.82rem; font-weight: 600;
    cursor: pointer; font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-gpa:hover { background: #2e7d32; color: #fff; }

.gi-empty { text-align: center; padding: 40px; color: #aaa; }
</style>
@endpush

@section('content')
<div class="gi-page">

    {{-- Semester selector --}}
    <div class="gi-card">
        <div class="gi-icon gi-icon-filter"><i class="bi bi-calendar3"></i></div>
        <div class="gi-card-header">
            <span class="gi-card-title">เลือกปีการศึกษา / ภาคเรียน</span>
            <a href="{{ route('grades.gpa') }}" class="btn-gpa">
                <i class="bi bi-trophy"></i> รายงาน GPA
            </a>
        </div>

        <div class="sem-tabs">
            @foreach($semesters as $sem)
            <button class="sem-tab {{ $semesterId == $sem->semester_id ? 'active' : '' }}"
                    onclick="window.location='?semester_id={{ $sem->semester_id }}'">
                <span class="sem-year">{{ $sem->academicYear->year_name ?? '' }}</span>
                <span class="sem-term">ภาคเรียน {{ $sem->semester_name }}</span>
            </button>
            @endforeach
        </div>
    </div>

    {{-- Section grid --}}
    <div class="gi-card">
        <div class="gi-icon gi-icon-list"><i class="bi bi-people-fill"></i></div>
        <div class="gi-card-header">
            <span class="gi-card-title">เลือกห้องเรียน</span>
        </div>

        @if($sections->count())
        <div class="sec-grid">
            @foreach($sections as $sec)
            <a href="{{ route('grades.section', $sec->section_id) }}" class="sec-card">
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
