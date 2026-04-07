@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><a href="{{ route('grades.index') }}">ผลการเรียน</a><i class="bi bi-chevron-right"></i><span>รายงาน GPA</span></nav>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-trophy"></i> รายงาน GPA จัดอันดับ</div>
        <div class="ac-card-body">
            <div class="ac-field" style="max-width:400px; margin-bottom:16px"><label>เทอม</label>
                <select class="ac-select" onchange="window.location='?semester_id='+this.value">
                    @foreach($semesters as $sem)<option value="{{ $sem->semester_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>{{ $sem->academicYear->year_name }} เทอม {{ $sem->semester_name }}</option>@endforeach
                </select>
            </div>

            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>อันดับ</th><th>รหัส</th><th>ชื่อ-นามสกุล</th><th>เลขที่</th><th>หน่วยกิตรวม</th><th>GPA</th><th>ดู</th></tr></thead>
                    <tbody>
                        @forelse($gpaData as $i => $g)
                        <tr>
                            <td style="font-weight:700; color:{{ $i < 3 ? '#f59e0b' : '#555' }}">{{ $i + 1 }}</td>
                            <td>{{ $g->student_code }}</td>
                            <td style="text-align:left">{{ $g->thai_firstname }} {{ $g->thai_lastname }}</td>
                            <td>{{ $g->student_number }}</td>
                            <td>{{ $g->total_credits }}</td>
                            <td style="font-weight:700; font-size:1.05rem; color:#4479DA">{{ $g->gpa }}</td>
                            <td><a href="{{ route('grades.transcript', $g->student_id) }}" class="ac-action-btn ac-action-view"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="ac-empty">ไม่มีข้อมูลเกรดในเทอมนี้</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection