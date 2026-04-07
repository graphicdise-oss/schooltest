@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><span>ผลการเรียน</span></nav>

    <div class="ac-card">
        <div class="ac-card-header">
            <span><i class="bi bi-bar-chart-line"></i> ผลการเรียน</span>
            <a href="{{ route('grades.gpa') }}" class="ac-btn ac-btn-success ac-btn-sm"><i class="bi bi-trophy"></i> รายงาน GPA</a>
        </div>
        <div class="ac-card-body">
            <div class="ac-field" style="max-width:400px; margin-bottom:16px"><label>เทอม</label>
                <select class="ac-select" onchange="window.location='?semester_id='+this.value">
                    @foreach($semesters as $sem)<option value="{{ $sem->semester_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>{{ $sem->academicYear->year_name }} เทอม {{ $sem->semester_name }}</option>@endforeach
                </select>
            </div>

            <div class="ac-grid-3">
                @foreach($sections as $sec)
                <div style="background:#f8fafc; border-radius:12px; padding:16px; border:1.5px solid #e5e9ef; transition:all 0.2s; cursor:pointer"
                    onclick="window.location='{{ route('grades.section', $sec->section_id) }}'">
                    <div style="font-size:1.1rem; font-weight:700; color:#4479DA">{{ $sec->level->name }}/{{ $sec->section_number }}</div>
                    <div style="font-size:0.82rem; color:#888; margin-top:4px">{{ $sec->studentSections->where('status','กำลังศึกษา')->count() }} คน</div>
                </div>
                @endforeach
            </div>

            @if($sections->isEmpty())
            <div class="ac-empty"><i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>ไม่มีห้องเรียนในเทอมนี้</div>
            @endif
        </div>
    </div>
</div>
@endsection