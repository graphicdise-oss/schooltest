@extends('parent.layout')
@section('title', 'หน้าหลัก')

@section('content')
<div class="pp-card" style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
    <div style="width:96px; height:120px; border-radius:10px; overflow:hidden; background:#f1f5f9; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
        @if($student->student_image)
            <img src="{{ asset('storage/' . $student->student_image) }}" alt="photo" style="width:100%; height:100%; object-fit:cover;">
        @else
            <i class="bi bi-person" style="font-size:2.5rem; color:#94a3b8;"></i>
        @endif
    </div>
    <div>
        <div class="pp-title" style="border:none; padding:0; margin-bottom:6px;">{{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }}</div>
        <div style="color:#475569;">รหัสนักเรียน: <strong>{{ $student->student_code }}</strong></div>
        @if($studentSection?->classSection)
            <div style="color:#475569;">ห้องเรียน: <strong>{{ $studentSection->classSection->level->name ?? '' }}/{{ $studentSection->classSection->section_number }}</strong> · เลขที่ {{ $studentSection->student_number }}</div>
            <div style="color:#475569;">ครูประจำชั้น: <strong>{{ $studentSection->classSection->homeroomTeacher->thai_prefix ?? '' }}{{ $studentSection->classSection->homeroomTeacher->thai_firstname ?? '-' }} {{ $studentSection->classSection->homeroomTeacher->thai_lastname ?? '' }}</strong></div>
        @else
            <div style="color:#94a3b8;">ยังไม่มีข้อมูลห้องเรียนปัจจุบัน</div>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <a href="{{ route('parent.grades') }}" class="pp-card d-block text-decoration-none" style="text-align:center;">
            <i class="bi bi-mortarboard" style="font-size:1.8rem; color:#2563eb;"></i>
            <div style="color:#082b75; font-weight:600; margin-top:8px;">ดูผลการเรียน</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('parent.calendar') }}" class="pp-card d-block text-decoration-none" style="text-align:center;">
            <i class="bi bi-calendar3" style="font-size:1.8rem; color:#16a34a;"></i>
            <div style="color:#082b75; font-weight:600; margin-top:8px;">ปฏิทิน / วันหยุด</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('parent.contact') }}" class="pp-card d-block text-decoration-none" style="text-align:center;">
            <i class="bi bi-person-lines-fill" style="font-size:1.8rem; color:#d97706;"></i>
            <div style="color:#082b75; font-weight:600; margin-top:8px;">ติดต่อครูประจำชั้น</div>
        </a>
    </div>
</div>
@endsection
