@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
@php
    $levels = ['', 'ดีเยี่ยม', 'ดี', 'ผ่าน'];
@endphp
<div class="ac-page">
    <nav class="ac-breadcrumb">
        <a href="{{ route('por5.index') }}">ปพ.5</a><i class="bi bi-chevron-right"></i>
        <span>{{ $assign->subject->name_th }} — {{ $assign->classSection->level->name ?? '' }}/{{ $assign->classSection->section_number }}</span>
    </nav>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-clipboard-data"></i> ผลการประเมินคุณภาพผู้เรียนรายวิชา</div>
        <div class="ac-card-body">
            @if($students->isEmpty())
                <p class="ac-empty">ไม่มีนักเรียนในห้องนี้</p>
            @else
                <form method="POST" action="{{ route('por5.saveAssessment', $assign->assign_id) }}">
                    @csrf
                    <div class="ac-table-wrap">
                        <table class="ac-table">
                            <thead>
                                <tr>
                                    <th style="width:55px;">เลขที่</th>
                                    <th style="width:110px;">รหัส</th>
                                    <th style="text-align:left;">ชื่อ - สกุล</th>
                                    <th style="width:180px;">คุณลักษณะอันพึงประสงค์</th>
                                    <th style="width:180px;">การอ่านคิดวิเคราะห์ฯ</th>
                                    <th style="width:180px;">สมรรถนะสำคัญของผู้เรียน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $s)
                                    @php $a = $assessments->get($s->student_id); @endphp
                                    <tr>
                                        <td>{{ $s->student_number }}</td>
                                        <td>{{ $s->student->student_code }}</td>
                                        <td style="text-align:left">{{ $s->student->thai_prefix }}{{ $s->student->thai_firstname }} {{ $s->student->thai_lastname }}</td>
                                        <td>
                                            <select name="assess[{{ $s->student_id }}][char]" class="ac-select">
                                                @foreach($levels as $opt)
                                                    <option value="{{ $opt }}" {{ ($a->desired_char ?? '') === $opt ? 'selected' : '' }}>{{ $opt === '' ? '—' : $opt }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="assess[{{ $s->student_id }}][reading]" class="ac-select">
                                                @foreach($levels as $opt)
                                                    <option value="{{ $opt }}" {{ ($a->reading_thinking ?? '') === $opt ? 'selected' : '' }}>{{ $opt === '' ? '—' : $opt }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="assess[{{ $s->student_id }}][competency]" class="ac-select">
                                                @foreach($levels as $opt)
                                                    <option value="{{ $opt }}" {{ ($a->competency ?? '') === $opt ? 'selected' : '' }}>{{ $opt === '' ? '—' : $opt }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="ac-save-wrap" style="margin-top:16px; text-align:right;">
                        <a href="{{ route('por5.print', $assign->assign_id) }}" target="_blank" class="ac-btn ac-btn-secondary"><i class="bi bi-printer"></i> พิมพ์ ปพ.5</a>
                        <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึกผลการประเมิน</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
