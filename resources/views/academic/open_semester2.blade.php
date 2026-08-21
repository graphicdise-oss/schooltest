@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">ข้อมูลบุคคล</a><i class="bi bi-chevron-right"></i><span>เปิดภาคเรียน</span></nav>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-calendar-range"></i> ตั้งค่าภาคเรียน</div>
        <div class="ac-card-body">
            @if($academicYears->isEmpty())
                <div class="ac-empty">ยังไม่มีปีการศึกษาในระบบ — ไปเพิ่มที่หน้า งานวิชาการ &gt; จัดการหลักสูตร/แผน ก่อน</div>
            @else
                <div class="ac-grid-4" style="margin-bottom:16px">
                    <div class="ac-field">
                        <label>ปีการศึกษา</label>
                        <select id="os2YearSelect" class="ac-select"
                                onchange="window.location.href = '{{ route('open-semester2.form') }}?year_id=' + this.value">
                            @foreach($academicYears as $y)
                                <option value="{{ $y->year_id }}" {{ (string) $y->year_id === (string) $selectedYearId ? 'selected' : '' }}>
                                    {{ $y->year_name }}{{ $y->is_current ? ' ⭐ (ปีปัจจุบัน)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ac-field">
                        <label>ภาคเรียน</label>
                        @if($selectedYear && $selectedYear->semesters->isNotEmpty())
                            <select id="os2SemSelect" class="ac-select" onchange="os2HandleSemChange(this)">
                                @foreach($selectedYear->semesters->sortBy('semester_name') as $sem)
                                    <option value="{{ $sem->semester_id }}"
                                        data-start="{{ optional($sem->start_date)->format('Y-m-d') }}"
                                        data-end="{{ optional($sem->end_date)->format('Y-m-d') }}"
                                        data-current="{{ $sem->is_current ? '1' : '0' }}"
                                        {{ $sem->is_current ? 'selected' : '' }}>
                                        เทอม {{ $sem->semester_name }} {{ $sem->is_current ? '⭐ (ปัจจุบัน)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div style="padding:8px 12px; background:#f8fafc; color:#64748b; font-size:0.85rem; border-radius:6px; border:1px solid #e2e8f0;">
                                ปีนี้ยังไม่มีภาคเรียน
                            </div>
                        @endif
                    </div>
                </div>

                @if($selectedYear && $selectedYear->semesters->isNotEmpty())
                    <form method="POST" id="os2DatesForm">
                        @csrf @method('PUT')
                        <div class="ac-grid-4">
                            <div class="ac-field">
                                <label>วันเริ่มภาคเรียน</label>
                                <input type="date" name="start_date" id="os2Start" class="ac-input">
                            </div>
                            <div class="ac-field">
                                <label>วันสิ้นสุดภาคเรียน</label>
                                <input type="date" name="end_date" id="os2End" class="ac-input">
                            </div>
                        </div>
                        <div class="ac-save-wrap">
                            <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึกวันเริ่ม-สิ้นสุดภาคเรียน</button>
                        </div>
                    </form>
                    <form method="POST" id="os2SetCurrentForm" style="margin-top:8px;">
                        @csrf @method('PUT')
                        <button type="submit" id="os2SetCurrentBtn" class="ac-btn ac-btn-sm" style="background:#10b981; color:#fff;">
                            <i class="bi bi-check-circle"></i> ตั้งเป็นเทอมปัจจุบัน
                        </button>
                    </form>
                    <p style="font-size:0.78rem; color:#94a3b8; margin:10px 0 0;">
                        <i class="bi bi-info-circle"></i> ตั้งวันเริ่มภาคเรียนไว้ล่วงหน้าได้ — พอถึงวันนั้นระบบจะสลับเป็นเทอมปัจจุบันให้อัตโนมัติ
                        (ถ้าเป็นเทอม 2 และยังไม่มีห้องเรียน ระบบจะคัดลอกห้องจากเทอม 1 ให้อัตโนมัติด้วย ไม่ต้องมากดคัดลอกเองด้านล่างก็ได้)
                    </p>
                @endif
            @endif
        </div>
    </div>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-copy"></i> เปิดภาคเรียน 2 (คัดลอกห้อง+แผนการเรียนจากเทอม 1)</div>
        <div class="ac-card-body">
            @if(! $selectedYear)
                <div class="ac-empty">เลือกปีการศึกษาด้านบนก่อน</div>
            @else
                <form method="POST" action="{{ route('open-semester2.store') }}">
                    @csrf
                    <input type="hidden" name="year_id" value="{{ $selectedYear->year_id }}">
                    <p style="font-size:.85rem; color:#666; margin-bottom:14px">
                        คัดลอกห้อง + แผนการเรียน + ครูประจำชั้น จาก "เทอม 1" ไปสร้างเป็นห้องใหม่ใน "เทอม 2" ของปีการศึกษา {{ $selectedYear->year_name }} —
                        <strong>ไม่คัดลอกตารางสอนและไม่คัดลอกรายชื่อนักเรียน</strong> (ห้องใหม่จะว่างเปล่า รอจัดตารางสอน/ลงทะเบียนนักเรียนทีหลัง)
                        ถ้าห้องนั้นมีอยู่ในเทอม 2 อยู่แล้ว ระบบจะข้ามให้อัตโนมัติ ไม่สร้างซ้ำ
                    </p>

                    @if($term1SectionsJson->isEmpty())
                        <div class="ac-empty">ปีนี้ยังไม่มีห้องเรียนในเทอม 1 ให้คัดลอก</div>
                    @else
                        <div class="ac-field" style="max-width:260px; margin-bottom:16px">
                            <label>กรองตามระดับชั้น</label>
                            <select id="os2Level" class="ac-select" onchange="filterOs2Sections()">
                                <option value="">ทุกระดับชั้น</option>
                                @foreach($levels as $lv)<option value="{{ $lv->level_id }}">{{ $lv->name }}</option>@endforeach
                            </select>
                        </div>

                        <div class="ac-table-wrap">
                            <table class="ac-table">
                                <thead><tr><th style="width:40px"><input type="checkbox" id="os2CheckAll" onchange="toggleOs2All(this)"></th><th>ห้อง (เทอม 1)</th><th>แผนการเรียน</th></tr></thead>
                                <tbody id="os2Body"></tbody>
                            </table>
                        </div>
                        <div class="ac-save-wrap"><button type="submit" class="ac-btn ac-btn-success"><i class="bi bi-copy"></i> เปิดเทอม 2 (คัดลอกห้องที่เลือก)</button></div>
                    @endif
                </form>
            @endif
        </div>
    </div>
</div>

<script>
function os2HandleSemChange(selectObj) {
    const opt = selectObj.options[selectObj.selectedIndex];
    document.getElementById('os2Start').value = opt.getAttribute('data-start') || '';
    document.getElementById('os2End').value = opt.getAttribute('data-end') || '';
    document.getElementById('os2DatesForm').action = '{{ url('academic-years/semester') }}/' + selectObj.value + '/dates';
    document.getElementById('os2SetCurrentForm').action = '{{ url('academic-years/semester') }}/' + selectObj.value + '/current';
    document.getElementById('os2SetCurrentBtn').style.display = opt.getAttribute('data-current') === '1' ? 'none' : 'inline-block';
}
document.addEventListener('DOMContentLoaded', () => {
    const sSelect = document.getElementById('os2SemSelect');
    if (sSelect) os2HandleSemChange(sSelect);
});

const term1Sections = @json($term1SectionsJson);

function filterOs2Sections() {
    const levelId = document.getElementById('os2Level')?.value || '';
    const body = document.getElementById('os2Body');
    if (!body) return;
    const filtered = term1Sections.filter(s => !levelId || String(s.level_id) === levelId);
    body.innerHTML = filtered.length
        ? filtered.map(s => `<tr><td><input type="checkbox" name="section_ids[]" value="${s.section_id}" class="os2-cb" checked></td><td style="text-align:left">${s.label}</td><td>${s.study_plan || '-'}</td></tr>`).join('')
        : '<tr><td colspan="3" class="ac-empty">ไม่มีห้องของเทอม 1 ในระดับชั้นนี้</td></tr>';
    const checkAll = document.getElementById('os2CheckAll');
    if (checkAll) checkAll.checked = filtered.length > 0;
}
function toggleOs2All(el) { document.querySelectorAll('.os2-cb').forEach(cb => cb.checked = el.checked); }
document.addEventListener('DOMContentLoaded', filterOs2Sections);
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
