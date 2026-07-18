@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">

    {{-- Breadcrumb --}}
    <nav class="ac-breadcrumb">
        <a href="#">วิชาการ</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('grades.index') }}">ผลการเรียน</a>
        <i class="bi bi-chevron-right"></i>
        <span>{{ $section->level->name }}/{{ $section->section_number }}</span>
    </nav>

    {{-- ===== Card: ผลการเรียนรายห้อง ===== --}}
    <div class="ac-card">
        <div class="ac-card-header">
            <span>
                <span style="background:#4479DA; color:#fff; border-radius:8px; width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; margin-right:10px; font-size:1.1rem; vertical-align:middle">
                    <i class="bi bi-people-fill"></i>
                </span>
                ผลการเรียน — {{ $section->level->name }}/{{ $section->section_number }}
                <span style="font-size:0.82rem; color:#888; font-weight:400; margin-left:8px">
                    {{ $section->semester->academicYear->year_name }} เทอม {{ $section->semester->semester_name }}
                </span>
            </span>
            <div style="display:flex; gap:8px; align-items:center">
                <span style="font-size:0.82rem; color:#888">นักเรียน {{ $students->count() }} คน</span>
                <button onclick="window.print()" class="ac-btn ac-btn-sm" style="background:linear-gradient(135deg,#7c3aed,#a855f7); color:#fff; border:none">
                    <i class="bi bi-printer"></i> พิมพ์เกรดทั้งห้อง
                </button>
            </div>
        </div>
        <div class="ac-card-body">
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead>
                        <tr>
                            <th style="min-width:48px">เลขที่</th>
                            <th style="min-width:90px">รหัส</th>
                            <th style="min-width:160px; text-align:left">ชื่อ-นามสกุล</th>
                            <th style="min-width:80px">จำนวนวิชา</th>
                            <th style="min-width:70px">GPA</th>
                            <th style="min-width:110px">จัดการ</th>
                        </tr>
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
                            <td style="text-align:left; white-space:nowrap">{{ $ss->student->thai_prefix }}{{ $ss->student->thai_firstname }} {{ $ss->student->thai_lastname }}</td>
                            <td>{{ $sGrades->count() }}</td>
                            <td>
                                @if($gpa !== '-')
                                    <span style="font-weight:700; font-size:1rem; color:{{ $gpa >= 3.0 ? '#16a34a' : ($gpa >= 2.0 ? '#d97706' : '#dc2626') }}">
                                        {{ $gpa }}
                                    </span>
                                @else
                                    <span style="color:#aaa">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('grades.transcript', $ss->student_id) }}"
                                   class="ac-action-btn ac-action-view" title="ดูใบแสดงผลการเรียน">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('grades.student.edit', $ss->student_id) }}"
                                   class="ac-action-btn ac-action-edit" title="แก้ไขเกรด">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('grades.transcript.print', $ss->student_id) }}"
                                   target="_blank"
                                   class="ac-action-btn" title="พิมพ์ใบแสดงผลการเรียน"
                                   style="background:#f3f0ff; color:#7c3aed; border:1px solid #c4b5fd">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
