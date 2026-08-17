@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">ข้อมูลบุคคล</a><i class="bi bi-chevron-right"></i><span>เปิดภาคเรียน 2</span></nav>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-copy"></i> เปิดภาคเรียน 2</div>
        <div class="ac-card-body">
            <form method="POST" action="{{ route('open-semester2.store') }}">
                @csrf
                <p style="font-size:.85rem; color:#666; margin-bottom:14px">
                    คัดลอกห้อง + แผนการเรียน + ครูประจำชั้น จาก "เทอม 1" ไปสร้างเป็นห้องใหม่ใน "เทอม 2" ของปีการศึกษาเดียวกัน —
                    <strong>ไม่คัดลอกตารางสอนและไม่คัดลอกรายชื่อนักเรียน</strong> (ห้องใหม่จะว่างเปล่า รอจัดตารางสอน/ลงทะเบียนนักเรียนทีหลัง)
                    ถ้าห้องนั้นมีอยู่ในเทอม 2 อยู่แล้ว ระบบจะข้ามให้อัตโนมัติ ไม่สร้างซ้ำ
                </p>
                <div class="ac-grid-4" style="margin-bottom:16px">
                    <div class="ac-field"><label>ปีการศึกษา *</label>
                        <select name="year_id" id="os2Year" class="ac-select" onchange="filterOs2Sections()" required>
                            <option value="">-- เลือกปีการศึกษา --</option>
                            @foreach($academicYears as $y)<option value="{{ $y->year_id }}">{{ $y->year_name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="ac-field"><label>ระดับ</label>
                        <select id="os2Level" class="ac-select" onchange="filterOs2Sections()">
                            <option value="">ทุกระดับชั้น</option>
                            @foreach($levels as $lv)<option value="{{ $lv->level_id }}">{{ $lv->name }}</option>@endforeach
                        </select>
                    </div>
                </div>

                <div class="ac-table-wrap">
                    <table class="ac-table">
                        <thead><tr><th style="width:40px"><input type="checkbox" id="os2CheckAll" onchange="toggleOs2All(this)"></th><th>ห้อง (เทอม 1)</th><th>แผนการเรียน</th></tr></thead>
                        <tbody id="os2Body">
                            <tr><td colspan="3" class="ac-empty">เลือกปีการศึกษาก่อน</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="ac-save-wrap"><button type="submit" class="ac-btn ac-btn-success"><i class="bi bi-copy"></i> เปิดเทอม 2 (คัดลอกห้องที่เลือก)</button></div>
            </form>
        </div>
    </div>
</div>

<script>
const term1Sections = @json($term1Sections->map(fn($s) => [
    'section_id' => $s->section_id,
    'year_id'    => $s->semester?->year_id,
    'level_id'   => $s->level_id,
    'label'      => $s->full_name,
    'study_plan' => $s->study_plan,
])->values());

function filterOs2Sections() {
    const yearId = document.getElementById('os2Year').value;
    const levelId = document.getElementById('os2Level').value;
    const body = document.getElementById('os2Body');
    if (!yearId) {
        body.innerHTML = '<tr><td colspan="3" class="ac-empty">เลือกปีการศึกษาก่อน</td></tr>';
        return;
    }
    const filtered = term1Sections.filter(s => String(s.year_id) === yearId && (!levelId || String(s.level_id) === levelId));
    body.innerHTML = filtered.length
        ? filtered.map(s => `<tr><td><input type="checkbox" name="section_ids[]" value="${s.section_id}" class="os2-cb"></td><td style="text-align:left">${s.label}</td><td>${s.study_plan || '-'}</td></tr>`).join('')
        : '<tr><td colspan="3" class="ac-empty">ไม่มีห้องของเทอม 1 ในปีการศึกษานี้</td></tr>';
}
function toggleOs2All(el) { document.querySelectorAll('.os2-cb').forEach(cb => cb.checked = el.checked); }
</script>

@if(session('success'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>Swal.fire({icon:'success',title:'สำเร็จ!',text:'{{ session("success") }}',timer:2500,showConfirmButton:false});</script>
@endif
@if(session('error'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>Swal.fire({icon:'error',title:'ทำรายการไม่สำเร็จ',text:'{{ session("error") }}'});</script>
@endif
@endsection
