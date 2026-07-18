@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">เอกสาร ปพ./รบ.</a><i class="bi bi-chevron-right"></i><span>แบบรายงานผลพัฒนาคุณภาพผู้เรียนรายบุคคล (ปพ.6)</span></nav>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-person-lines-fill"></i> เลือกภาคเรียน / ระดับชั้น / ห้องเรียน</div>
        <div class="ac-card-body">
            <form method="GET" action="{{ route('por6.index') }}" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;align-items:flex-end;max-width:800px;margin-bottom:20px">
                <div class="ac-field" style="margin:0">
                    <label>ปีการศึกษา / เทอม</label>
                    <select class="ac-select" name="semester_id" onchange="this.form.submit()">
                        @foreach($semesters as $sem)
                        <option value="{{ $sem->semester_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>
                            {{ $sem->academicYear->year_name }} เทอม {{ $sem->semester_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="ac-field" style="margin:0">
                    <label>ระดับชั้น</label>
                    <select class="ac-select" name="level_id" onchange="this.form.submit()">
                        <option value="">-- ทุกระดับ --</option>
                        @foreach($levels as $lv)
                        <option value="{{ $lv->level_id }}" {{ (string)$levelId===(string)$lv->level_id?'selected':'' }}>{{ $lv->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ac-field" style="margin:0">
                    <label>ห้องเรียน</label>
                    <select class="ac-select" name="section_id" onchange="this.form.submit()">
                        <option value="">-- เลือกห้องเรียน --</option>
                        @foreach($sections as $sec)
                        <option value="{{ $sec->section_id }}" {{ (string)$sectionId===(string)$sec->section_id?'selected':'' }}>{{ $sec->level->name ?? '' }}/{{ $sec->section_number }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if($sectionId)
                <div style="margin-bottom:16px;">
                    <a href="{{ route('por6.printSection', $sectionId) }}" target="_blank" class="ac-btn ac-btn-primary">
                        <i class="bi bi-printer"></i> พิมพ์ ปพ.6 ทั้งห้อง
                    </a>
                </div>
            @endif

            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>เลขที่</th><th>รหัส</th><th>ชื่อ - สกุล</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($students as $s)
                        <tr>
                            <td>{{ $s->student_number }}</td>
                            <td>{{ $s->student->student_code }}</td>
                            <td style="text-align:left">{{ $s->student->thai_prefix }}{{ $s->student->thai_firstname }} {{ $s->student->thai_lastname }}</td>
                            <td>
                                <a href="{{ route('por6.printStudent', ['section' => $sectionId, 'student' => $s->student_id]) }}" target="_blank" class="ac-btn ac-btn-secondary ac-btn-sm">
                                    <i class="bi bi-printer"></i> พิมพ์เดี่ยว
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="ac-empty">{{ $sectionId ? 'ไม่มีนักเรียนในห้องนี้' : 'กรุณาเลือกห้องเรียนก่อน' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
