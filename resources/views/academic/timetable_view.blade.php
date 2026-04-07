@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><span>ตารางสอน</span></nav>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-calendar-week"></i> ตารางสอน</div>
        <div class="ac-card-body">
            <form method="GET" action="{{ route('timetable.view') }}" class="ac-grid-4" style="margin-bottom:20px">
                <div class="ac-field"><label>เทอม</label>
                    <select name="semester_id" class="ac-select">
                        @foreach($semesters as $sem)<option value="{{ $sem->semester_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>{{ $sem->academicYear->year_name }} เทอม {{ $sem->semester_name }}</option>@endforeach
                    </select>
                </div>
                <div class="ac-field"><label>ห้องเรียน</label>
                    <select name="section_id" class="ac-select">
                        <option value="">-- ทุกห้อง --</option>
                        @foreach($sections as $sec)<option value="{{ $sec->section_id }}" {{ $sectionId==$sec->section_id?'selected':'' }}>{{ $sec->level->name }}/{{ $sec->section_number }}</option>@endforeach
                    </select>
                </div>
                <div class="ac-field"><label>ครูผู้สอน</label>
                    <select name="teacher_id" class="ac-select">
                        <option value="">-- ทุกคน --</option>
                        @foreach($teachers as $t)<option value="{{ $t->personnel_id }}" {{ $teacherId==$t->personnel_id?'selected':'' }}>{{ $t->thai_firstname }} {{ $t->thai_lastname }}</option>@endforeach
                    </select>
                </div>
                <div class="ac-field" style="justify-content:flex-end"><button type="submit" class="ac-btn ac-btn-primary" style="margin-top:20px"><i class="bi bi-search"></i> แสดง</button></div>
            </form>

            {{-- ตาราง Grid --}}
            <div class="tt-grid">
                <div class="tt-header">เวลา</div>
                @foreach($days as $day)<div class="tt-header">{{ $day }}</div>@endforeach

                @php
                    $times = $slots->pluck('start_time')->unique()->sort()->values();
                    $slotsByDayTime = $slots->groupBy(fn($s) => $s->day_of_week . '|' . $s->start_time->format('H:i'));
                @endphp

                @foreach($times as $time)
                    <div class="tt-time">{{ \Carbon\Carbon::parse($time)->format('H:i') }}</div>
                    @foreach($days as $day)
                        <div class="tt-cell">
                            @php $key = $day . '|' . \Carbon\Carbon::parse($time)->format('H:i'); @endphp
                            @foreach($slotsByDayTime[$key] ?? [] as $slot)
                                <div class="tt-slot" title="{{ $slot->teachingAssign->personnel->thai_firstname }} {{ $slot->teachingAssign->personnel->thai_lastname }}">
                                    <strong>{{ $slot->teachingAssign->subject->code }}</strong><br>
                                    {{ $slot->teachingAssign->classSection->level->name }}/{{ $slot->teachingAssign->classSection->section_number }}<br>
                                    <small>{{ $slot->room ?? '' }}</small>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endforeach
            </div>

            @if($slots->isEmpty())
            <div class="ac-empty" style="margin-top:20px"><i class="bi bi-calendar-x" style="font-size:2rem;display:block;margin-bottom:8px"></i>ไม่พบข้อมูลตารางสอน</div>
            @endif
        </div>
    </div>
</div>
@endsection