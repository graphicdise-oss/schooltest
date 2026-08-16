@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><span>ตารางสอน</span></nav>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-calendar-week"></i> ดูตารางสอน — เลือกห้องเรียน</div>
        <div class="ac-card-body">
            <form method="GET" action="{{ route('timetable.view') }}" class="ac-grid-4" style="margin-bottom:20px">
                <div class="ac-field"><label>เทอม</label>
                    <select name="semester_id" class="ac-select" onchange="this.form.submit()">
                        @foreach($semesters as $sem)<option value="{{ $sem->semester_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>{{ $sem->academicYear->year_name }} เทอม {{ $sem->semester_name }}</option>@endforeach
                    </select>
                </div>
            </form>

            @if($sections->isEmpty())
            <div class="ac-empty" style="margin-top:20px"><i class="bi bi-calendar-x" style="font-size:2rem;display:block;margin-bottom:8px"></i>เทอมนี้ยังไม่มีห้องเรียน</div>
            @else
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:12px;">
                @foreach($sections as $sec)
                <a href="{{ route('timetable.print', $sec->section_id) }}" target="_blank"
                   style="display:flex; flex-direction:column; align-items:center; gap:6px; padding:16px 10px; border:1px solid #e2e8f0; border-radius:10px; text-decoration:none; color:#334155; background:#fff; transition:box-shadow .15s;"
                   onmouseover="this.style.boxShadow='0 2px 10px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
                    <i class="bi bi-calendar-week" style="font-size:1.4rem; color:#039be5"></i>
                    <span style="font-weight:700; font-size:0.95rem">{{ $sec->level->name ?? '' }}/{{ $sec->section_number }}</span>
                    @if($sec->study_plan)<span style="font-size:0.75rem; color:#94a3b8">{{ $sec->study_plan }}</span>@endif
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
