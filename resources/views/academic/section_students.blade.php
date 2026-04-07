@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb">
        <a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i>
        <a href="{{ route('class-sections.index') }}">ห้องเรียน</a><i class="bi bi-chevron-right"></i>
        <span>จัดนักเรียน — {{ $section->level->name }}/{{ $section->section_number }}</span>
    </nav>

    <div class="ac-grid-2" style="gap:20px">
        {{-- ซ้าย: นักเรียนในห้อง --}}
        <div class="ac-card">
            <div class="ac-card-header">
                <span><i class="bi bi-people-fill"></i> นักเรียนในห้อง {{ $section->level->name }}/{{ $section->section_number }} ({{ $section->studentSections->count() }} คน)</span>
            </div>
            <div class="ac-card-body">
                <div class="ac-table-wrap">
                    <table class="ac-table">
                        <thead><tr><th>เลขที่</th><th>รหัส</th><th>ชื่อ-นามสกุล</th><th>สถานะ</th><th>ลบ</th></tr></thead>
                        <tbody>
                            @forelse($section->studentSections->sortBy('student_number') as $ss)
                            <tr>
                                <td>{{ $ss->student_number }}</td>
                                <td>{{ $ss->student->student_code ?? '-' }}</td>
                                <td style="text-align:left">{{ $ss->student->thai_prefix }}{{ $ss->student->thai_firstname }} {{ $ss->student->thai_lastname }}</td>
                                <td><span class="ac-badge ac-badge-active">{{ $ss->status }}</span></td>
                                <td>
                                    <form action="{{ route('class-sections.removeStudent', [$section->section_id, $ss->id]) }}" method="POST" onsubmit="return confirm('นำออกจากห้อง?')">
                                        @csrf @method('DELETE')
                                        <button class="ac-action-btn ac-action-delete"><i class="bi bi-x"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="ac-empty">ยังไม่มีนักเรียนในห้อง</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ขวา: เพิ่มนักเรียนเข้าห้อง --}}
        <div class="ac-card">
            <div class="ac-card-header"><i class="bi bi-person-plus"></i> เพิ่มนักเรียนเข้าห้อง</div>
            <div class="ac-card-body">
                @if($availableStudents->count() > 0)
                <form method="POST" action="{{ route('class-sections.assignStudents', $section->section_id) }}">
                    @csrf
                    <div style="margin-bottom:12px">
                        <label style="font-size:0.82rem; font-weight:600; display:flex; align-items:center; gap:6px">
                            <input type="checkbox" id="checkAll" onchange="document.querySelectorAll('.student-cb').forEach(cb=>cb.checked=this.checked)">
                            เลือกทั้งหมด ({{ $availableStudents->count() }} คน)
                        </label>
                    </div>

                    <div style="max-height:500px; overflow-y:auto; border:1px solid #e5e9ef; border-radius:10px">
                        @foreach($availableStudents as $s)
                        <label style="display:flex; align-items:center; gap:10px; padding:8px 12px; border-bottom:1px solid #f5f5f5; font-size:0.84rem; cursor:pointer">
                            <input type="checkbox" name="student_ids[]" value="{{ $s->student_id }}" class="student-cb">
                            <span style="color:#888; min-width:60px">{{ $s->student_code }}</span>
                            {{ $s->thai_prefix }}{{ $s->thai_firstname }} {{ $s->thai_lastname }}
                        </label>
                        @endforeach
                    </div>

                    <div style="text-align:center; margin-top:16px">
                        <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-plus-lg"></i> เพิ่มนักเรียนที่เลือก</button>
                    </div>
                </form>
                @else
                <div class="ac-empty">นักเรียนทุกคนถูกจัดเข้าห้องแล้ว</div>
                @endif
            </div>
        </div>
    </div>
</div>


@endsection