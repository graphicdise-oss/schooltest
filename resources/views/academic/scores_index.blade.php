@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><span>บันทึกคะแนน</span></nav>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-pencil-square"></i> เลือกวิชาเพื่อบันทึกคะแนน</div>
        <div class="ac-card-body">
            <form method="GET" action="{{ route('scores.index') }}" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:flex-end;max-width:900px;margin-bottom:20px">
                <div class="ac-field" style="margin:0">
                    <label>ปีการศึกษา / เทอม</label>
                    <select class="ac-select" name="semester_id">
                        @foreach($semesters as $sem)
                        <option value="{{ $sem->semester_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>
                            {{ $sem->academicYear->year_name }} เทอม {{ $sem->semester_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="ac-field" style="margin:0">
                    <label>วิชา</label>
                    <select class="ac-select" name="subject_id">
                        <option value="">-- ทุกวิชา --</option>
                        @foreach($subjects as $sub)
                        <option value="{{ $sub->subject_id }}" {{ $subjectId==$sub->subject_id?'selected':'' }}>
                            {{ $sub->code }} — {{ $sub->name_th }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="ac-field" style="margin:0">
                    <label>ครูผู้สอน</label>
                    <select class="ac-select" name="personnel_id">
                        <option value="">-- ทุกคน --</option>
                        @foreach($teachers as $t)
                        <option value="{{ $t->personnel_id }}" {{ $personnelId==$t->personnel_id?'selected':'' }}>
                            {{ $t->thai_firstname }} {{ $t->thai_lastname }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="ac-btn ac-btn-primary" style="height:38px;white-space:nowrap">
                    <i class="bi bi-search"></i> ค้นหา
                </button>
            </form>

            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>#</th><th>รหัสวิชา</th><th>ชื่อวิชา</th><th>ห้อง</th><th>ครูผู้สอน</th><th>หมวดคะแนน</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($assigns as $i => $a)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $a->subject->code }}</td>
                            <td style="text-align:left">{{ $a->subject->name_th }}</td>
                            <td>{{ $a->classSection->level->name }}/{{ $a->classSection->section_number }}</td>
                            <td>{{ $a->personnel->thai_firstname }} {{ $a->personnel->thai_lastname }}</td>
                            <td><span class="ac-badge ac-badge-info">{{ $a->scoreCategories->count() }} หมวด</span></td>
                            <td><a href="{{ route('scores.manage', $a->assign_id) }}" class="ac-btn ac-btn-primary ac-btn-sm"><i class="bi bi-pencil"></i> บันทึกคะแนน</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="ac-empty">ไม่มีข้อมูลการสอนในเทอมนี้</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
