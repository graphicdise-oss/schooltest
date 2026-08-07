@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">
<style>
.rp-page { padding: 24px 28px; }
.rp-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 20px 20px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.rp-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff; background: #8e24aa;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.rp-card-header { margin-left: 90px; margin-top: -8px; margin-bottom: 20px; }
.rp-card-title { font-size: 1.05rem; color: #555; }
.rp-card-title small { display:block; font-size:0.78rem; color:#999; font-weight:400; margin-top:2px; }

.btn-export {
    background: #8e24aa; color: #fff; border: none; border-radius: 6px;
    padding: 10px 26px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 8px;
}
.btn-export:hover { background: #6a1b7a; color: #fff; }
.btn-export.disabled { background: #bbb; pointer-events: none; }

.rp-empty { text-align: center; padding: 24px; color: #aaa; font-size: 0.85rem; }
</style>
@endpush

@section('content')
<div class="rp-page">
    <div class="rp-card">
        <div class="rp-icon"><i class="bi bi-bar-chart-line"></i></div>
        <div class="rp-card-header">
            <span class="rp-card-title">
                รายงานจัดอันดับคะแนนรายวิชา
                <small>เลือกปีการศึกษา ภาคเรียน และรายวิชา — จัดอันดับนักเรียนทุกห้องที่เรียนวิชานี้ตามคะแนนรวม</small>
            </span>
        </div>

        <form method="GET" action="{{ route('academic-reports.subject-rank') }}">
            <div class="ac-grid-3">
                <div class="ac-field">
                    <label>ปีการศึกษา</label>
                    <select name="year_id" class="ac-select" onchange="this.form.submit()">
                        @foreach($academicYears as $ay)
                        <option value="{{ $ay->year_id }}" {{ (string) $yearId === (string) $ay->year_id ? 'selected' : '' }}>{{ $ay->year_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ac-field">
                    <label>ภาคเรียน</label>
                    <select name="term" class="ac-select" onchange="this.form.submit()">
                        <option value="1" {{ $term == '1' ? 'selected' : '' }}>ภาคเรียนที่ 1</option>
                        <option value="2" {{ $term == '2' ? 'selected' : '' }}>ภาคเรียนที่ 2</option>
                    </select>
                </div>
                <div class="ac-field">
                    <label>รายวิชา</label>
                    <select name="subject_id" class="ac-select" onchange="this.form.submit()">
                        <option value="">-- เลือกรายวิชา --</option>
                        @foreach($subjects as $s)
                        <option value="{{ $s->subject_id }}" {{ (string) $subjectId === (string) $s->subject_id ? 'selected' : '' }}>{{ $s->name_th }} ({{ $s->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        @if($subjects->isEmpty())
        <div class="rp-empty">ภาคเรียนนี้ยังไม่มีวิชาที่บันทึกการสอนไว้</div>
        @else
        <div class="ac-save-wrap">
            <a class="btn-export {{ $subjectId ? '' : 'disabled' }}" target="_blank"
               href="{{ $subjectId ? route('academic-reports.subject-rank.print', ['year_id' => $yearId, 'term' => $term, 'subject_id' => $subjectId]) : '#' }}">
                <i class="bi bi-printer"></i> พิมพ์รายงาน (บันทึกเป็น PDF ได้จากหน้าต่างพิมพ์)
            </a>
        </div>

        @if($subjectId && $subject)
        <div style="margin-top:24px">
            <div class="rp-card-title" style="margin-bottom:10px">
                อันดับคะแนนวิชา {{ $subject->name_th }} ({{ count($rows) }} คน)
            </div>
            @if(empty($rows))
            <div class="rp-empty">ยังไม่มีข้อมูลคะแนนของวิชานี้ในภาคเรียนนี้</div>
            @else
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead>
                        <tr>
                            <th style="width:60px">ลำดับ</th>
                            <th style="width:120px">รหัสนักเรียน</th>
                            <th>ชื่อ-สกุล</th>
                            <th style="width:130px">ห้อง</th>
                            <th style="width:80px">คะแนน</th>
                            <th style="width:70px">ระดับ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $r)
                        @php $student = $r['grade']->student; @endphp
                        <tr>
                            <td>{{ $r['rank'] }}</td>
                            <td>{{ $student->student_code ?? '-' }}</td>
                            <td style="text-align:left">{{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }}</td>
                            <td>{{ $r['grade']->teachingAssign->classSection->full_name ?? '-' }}</td>
                            <td>{{ $r['grade']->total_score }}</td>
                            <td>{{ $r['grade']->grade }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
