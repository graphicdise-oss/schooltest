@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><a href="{{ route('curriculums.index') }}">หลักสูตร</a><i class="bi bi-chevron-right"></i><span>{{ isset($curriculum) ? 'แก้ไข' : 'สร้าง' }}หลักสูตร</span></nav>

    {{-- ข้อมูลหลักสูตร --}}
    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-journal-text"></i> ข้อมูลหลักสูตร</div>
        <div class="ac-card-body">
            <form method="POST" action="{{ isset($curriculum) ? route('curriculums.update', $curriculum->curriculum_id) : route('curriculums.store') }}">
                @csrf
                @if(isset($curriculum)) @method('PUT') @endif
                <div class="ac-grid-2">
                    <div class="ac-field"><label>ชื่อหลักสูตร *</label><input type="text" name="name" class="ac-input" value="{{ $curriculum->name ?? '' }}" required></div>
                    <div class="ac-field"><label>ระดับชั้น</label>
                        <select name="level_id" class="ac-select">
                            <option value="">-- ทุกระดับ --</option>
                            @foreach($levels as $l)<option value="{{ $l->level_id }}" {{ ($curriculum->level_id ?? '')==$l->level_id?'selected':'' }}>{{ $l->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="ac-field"><label>ปีที่ใช้</label><input type="text" name="year_applied" class="ac-input" value="{{ $curriculum->year_applied ?? '' }}" placeholder="เช่น 2551"></div>
                    <div class="ac-field"><label>คำอธิบาย</label><input type="text" name="description" class="ac-input" value="{{ $curriculum->description ?? '' }}"></div>
                </div>
                <div class="ac-save-wrap"><button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-save"></i> บันทึกหลักสูตร</button></div>
            </form>
        </div>
    </div>

    {{-- วิชาในหลักสูตร (แสดงเฉพาะตอนแก้ไข) --}}
    @if(isset($curriculum))
    <div class="ac-card">
        <div class="ac-card-header">
            <span><i class="bi bi-list-check"></i> วิชาในหลักสูตร</span>
            <button class="ac-btn ac-btn-success ac-btn-sm" onclick="document.getElementById('addSubjOverlay').classList.add('active')"><i class="bi bi-plus"></i> เพิ่มวิชา</button>
        </div>
        <div class="ac-card-body">
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>#</th><th>รหัสวิชา</th><th>ชื่อวิชา</th><th>หน่วยกิต</th><th>เทอม</th><th>ประเภท</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($curriculum->curriculumSubjects as $i => $cs)
                        <tr>
                            <td>{{ $i+1 }}</td><td>{{ $cs->subject->code }}</td><td style="text-align:left">{{ $cs->subject->name_th }}</td>
                            <td>{{ $cs->subject->credits }}</td>
                            <td>{{ $cs->semester_type == 'both' ? 'ทั้ง 2 เทอม' : 'เทอม ' . $cs->semester_type }}</td>
                            <td><span class="ac-badge {{ $cs->is_required ? 'ac-badge-info' : 'ac-badge-warn' }}">{{ $cs->is_required ? 'บังคับ' : 'เลือก' }}</span></td>
                            <td>
                                <form action="{{ route('curriculums.removeSubject', [$curriculum->curriculum_id, $cs->id]) }}" method="POST" style="display:inline" onsubmit="return confirm('ลบ?')">@csrf @method('DELETE')<button class="ac-action-btn ac-action-delete"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="ac-empty">ยังไม่มีวิชาในหลักสูตร</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal เพิ่มวิชา --}}
    <div class="ac-overlay" id="addSubjOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="ac-modal"><div class="ac-modal-header"><i class="bi bi-plus-circle me-2"></i>เพิ่มวิชาในหลักสูตร</div>
    <form method="POST" action="{{ route('curriculums.addSubject', $curriculum->curriculum_id) }}">@csrf
    <div class="ac-modal-body">
        <label>เลือกวิชา *</label>
        <select name="subject_id" required>
            <option value="">-- เลือก --</option>
            @foreach($subjects as $sub)<option value="{{ $sub->subject_id }}">{{ $sub->code }} — {{ $sub->name_th }} ({{ $sub->credits }} หน่วย)</option>@endforeach
        </select>
        <label>เทอม</label>
        <select name="semester_type"><option value="both">ทั้ง 2 เทอม</option><option value="1">เทอม 1</option><option value="2">เทอม 2</option></select>
        <label>ประเภท</label>
        <select name="is_required"><option value="1">บังคับ</option><option value="0">เลือก</option></select>
    </div>
    <div class="ac-modal-footer"><button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('addSubjOverlay').classList.remove('active')">ยกเลิก</button><button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> เพิ่ม</button></div>
    </form></div></div>
    @endif
</div>
<script>document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.ac-overlay.active').forEach(el=>el.classList.remove('active'))});</script>
@endsection