@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><a href="{{ route('grades.index') }}">ผลการเรียน</a><i class="bi bi-chevron-right"></i><span>ใบแสดงผลการเรียน</span></nav>

    {{-- ข้อมูลนักเรียน --}}
    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-person-badge"></i> ใบแสดงผลการเรียน (Transcript)</div>
        <div class="ac-card-body" style="text-align:center">
            <h5 style="font-size:1.1rem; font-weight:700; color:#333">{{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }}</h5>
            <p style="color:#888; font-size:0.85rem">รหัสนักเรียน: {{ $student->student_code ?? '-' }}</p>
            <div style="display:inline-block; background:#e8f0fe; padding:10px 28px; border-radius:10px; margin-top:8px">
                <span style="font-size:0.8rem; color:#555">GPA รวม</span><br>
                <span style="font-size:1.8rem; font-weight:700; color:#4479DA">{{ $gpa }}</span>
            </div>
        </div>
    </div>

    {{-- เกรดแต่ละเทอม --}}
    @foreach($grades as $semLabel => $semGrades)
    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-calendar3"></i> {{ $semLabel }}</div>
        <div class="ac-card-body">
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>รหัสวิชา</th><th>ชื่อวิชา</th><th>หน่วยกิต</th><th>คะแนน</th><th>เกรด</th><th>GPA</th><th>หมายเหตุ</th></tr></thead>
                    <tbody>
                        @php $semCredits = 0; $semPoints = 0; @endphp
                        @foreach($semGrades as $g)
                        @php
                            $credits = $g->teachingAssign->subject->credits ?? 0;
                            $semCredits += $credits;
                            $semPoints += ($g->gpa_point ?? 0) * $credits;
                        @endphp
                        <tr>
                            <td>{{ $g->teachingAssign->subject->code }}</td>
                            <td style="text-align:left">{{ $g->teachingAssign->subject->name_th }}</td>
                            <td>{{ $credits }}</td>
                            <td>{{ $g->total_score }}</td>
                            <td><span style="font-weight:700; font-size:1rem; color:#4479DA">{{ $g->grade }}</span></td>
                            <td>{{ $g->gpa_point }}</td>
                            <td><span class="ac-badge {{ $g->remark == 'ผ่าน' ? 'ac-badge-active' : 'ac-badge-inactive' }}">{{ $g->remark }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f0f4ff">
                            <td colspan="2" style="text-align:right; font-weight:700">GPA เทอมนี้</td>
                            <td style="font-weight:700">{{ $semCredits }}</td>
                            <td colspan="2"></td>
                            <td style="font-weight:700; color:#4479DA; font-size:1.05rem">{{ $semCredits > 0 ? round($semPoints / $semCredits, 2) : 0 }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endforeach

    @if($grades->isEmpty())
    <div class="ac-card"><div class="ac-card-body ac-empty"><i class="bi bi-inbox" style="font-size:2rem; display:block; margin-bottom:8px"></i>ยังไม่มีผลการเรียน</div></div>
    @endif
</div>
@endsection