@extends('parent.layout')
@section('title', 'ติดต่อครูประจำชั้น')

@section('content')
<div class="pp-card">
    <div class="pp-title">ติดต่อครูประจำชั้น</div>

    @if(!$teacher)
        <div class="text-muted">ยังไม่มีข้อมูลครูประจำชั้นของนักเรียนในขณะนี้</div>
    @else
        <div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
            <div style="width:96px; height:120px; border-radius:10px; overflow:hidden; background:#f1f5f9; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                @if($teacher->personnel_image)
                    <img src="{{ asset('storage/' . $teacher->personnel_image) }}" alt="photo" style="width:100%; height:100%; object-fit:cover;">
                @else
                    <i class="bi bi-person" style="font-size:2.5rem; color:#94a3b8;"></i>
                @endif
            </div>
            <div>
                <div style="font-weight:700; font-size:1.1rem; color:#082b75;">{{ $teacher->thai_prefix }}{{ $teacher->thai_firstname }} {{ $teacher->thai_lastname }}</div>
                <div style="color:#475569;">ตำแหน่ง: {{ $teacher->position ?? '-' }}</div>
                <div style="color:#475569;">ครูประจำชั้น: {{ $studentSection->classSection->level->name ?? '' }}/{{ $studentSection->classSection->section_number ?? '' }}</div>
                <div style="margin-top:8px;">
                    @if($teacher->phone)
                        <div><i class="bi bi-telephone text-primary"></i> {{ $teacher->phone }}</div>
                    @endif
                    @if($teacher->email)
                        <div><i class="bi bi-envelope text-primary"></i> {{ $teacher->email }}</div>
                    @endif
                    @if(!$teacher->phone && !$teacher->email)
                        <div class="text-muted">ยังไม่มีข้อมูลช่องทางติดต่อ กรุณาติดต่อผ่านทางโรงเรียน</div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
