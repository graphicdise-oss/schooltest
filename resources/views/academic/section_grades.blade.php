@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><a href="{{ route('grades.index') }}">ผลการเรียน</a><i class="bi bi-chevron-right"></i><span>{{ $section->level->name }}/{{ $section->section_number }}</span></nav>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-people"></i> ผลการเรียน — {{ $section->level->name }}/{{ $section->section_number }} ({{ $section->semester->academicYear->year_name }} เทอม {{ $section->semester->semester_name }})</div>
        <div class="ac-card-body">
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead>
                        <tr><th>เลขที่</th><th>รหัส</th><th>ชื่อ-นามสกุล</th><th>จำนวนวิชา</th><th>GPA</th><th>ดู</th></tr>
                    </thead>
                    <tbody>
                        @foreach($students as $ss)
                        @php
                            $sGrades = $grades[$ss->student_id] ?? collect();
                            $totalCredits = 0; $totalPoints = 0;
                            foreach($sGrades as $g) {
                                $c = $g->teachingAssign->subject->credits ?? 0;
                                $totalCredits += $c; $totalPoints += ($g->gpa_point ?? 0) * $c;
                            }
                            $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : '-';
                        @endphp
                        <tr>
                            <td>{{ $ss->student_number }}</td>
                            <td>{{ $ss->student->student_code }}</td>
                            <td style="text-align:left">{{ $ss->student->thai_prefix }}{{ $ss->student->thai_firstname }} {{ $ss->student->thai_lastname }}</td>
                            <td>{{ $sGrades->count() }}</td>
                            <td style="font-weight:700; color:#4479DA; font-size:1rem">{{ $gpa }}</td>
                            <td><a href="{{ route('grades.transcript', $ss->student_id) }}" class="ac-action-btn ac-action-view"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection