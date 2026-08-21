@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">
<style>
.ss-page { padding: 24px 28px; }
.ss-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 20px 20px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.ss-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.ss-icon-school { background: #00897b; }
.ss-icon-logo { background: #e65100; }
.ss-icon-sign { background: #5c6bc0; }
.ss-icon-term { background: #2563eb; }
.ss-card-header { margin-left: 90px; margin-top: -8px; margin-bottom: 20px; }
.ss-card-title { font-size: 1.05rem; color: #555; }
.ss-card-title small { display:block; font-size:0.78rem; color:#999; font-weight:400; margin-top:2px; }

.ss-logo-row { display: flex; align-items: center; gap: 24px; flex-wrap: wrap; }
.ss-logo-preview {
    width: 110px; height: 110px; border: 1.5px dashed #d0d7de; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;
    background: #fafbfc;
}
.ss-logo-preview img { max-width: 100%; max-height: 100%; object-fit: contain; }
.ss-logo-preview .ss-no-logo { color: #aab; font-size: 0.78rem; text-align: center; padding: 8px; }

.ss-alert-success {
    background: #e8f5e9; color: #2e7d32; padding: 10px 16px; border-radius: 6px;
    margin-bottom: 16px; font-size: 0.85rem;
}
</style>
@endpush

@section('content')
<div class="ss-page">

    @if(session('success'))
    <div class="ss-alert-success"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
    @endif

    {{-- ข้อมูลโรงเรียน --}}
    <div class="ss-card">
        <div class="ss-icon ss-icon-school"><i class="bi bi-building"></i></div>
        <div class="ss-card-header">
            <span class="ss-card-title">
                ข้อมูลโรงเรียน
                <small>ใช้พิมพ์บนเอกสารทุกหน้า (ปพ.1, ปพ.3, ปพ.5, ปพ.6, ปพ.7, ใบระเบียนผลการเรียน, รายงานต่างๆ)</small>
            </span>
        </div>
        <form method="POST" action="{{ route('settings.school.updateSchool') }}">
            @csrf
            <div class="ac-field" style="margin-bottom:14px">
                <label>ชื่อโรงเรียน</label>
                <input type="text" name="school_name" class="ac-input"
                       value="{{ old('school_name', $setting->school_name ?? config('school.name')) }}"
                       placeholder="เช่น โรงเรียนสาธิต...">
            </div>
            <div class="ac-field" style="margin-bottom:14px">
                <label>สังกัด</label>
                <input type="text" name="affiliation" class="ac-input"
                       value="{{ old('affiliation', $setting->affiliation ?? config('school.affiliation')) }}"
                       placeholder="เช่น สำนักงานคณะกรรมการการศึกษาขั้นพื้นฐาน">
            </div>
            <div class="ac-grid-4">
                <div class="ac-field">
                    <label>ตำบล/แขวง</label>
                    <input type="text" name="tambon" class="ac-input"
                           value="{{ old('tambon', $setting->tambon ?? config('school.tambon')) }}">
                </div>
                <div class="ac-field">
                    <label>อำเภอ/เขต</label>
                    <input type="text" name="amphoe" class="ac-input"
                           value="{{ old('amphoe', $setting->amphoe ?? config('school.amphoe')) }}">
                </div>
                <div class="ac-field">
                    <label>จังหวัด</label>
                    <input type="text" name="province" class="ac-input"
                           value="{{ old('province', $setting->province ?? config('school.changwat')) }}">
                </div>
                <div class="ac-field">
                    <label>สำนักงานเขตพื้นที่การศึกษา</label>
                    <input type="text" name="education_area" class="ac-input"
                           value="{{ old('education_area', $setting->education_area ?? config('school.education_area')) }}">
                </div>
            </div>
            <div class="ac-save-wrap">
                <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึกข้อมูลโรงเรียน</button>
            </div>
        </form>
    </div>

    {{-- วันเริ่ม-สิ้นสุดภาคเรียน — แก้ได้ทุกปี/ทุกเทอม ไม่ใช่แค่เทอมปัจจุบัน ใช้คำนวณตารางเช็คชื่อ ปพ.5 --}}
    <div class="ss-card" id="ssTermCard">
        <div class="ss-icon ss-icon-term"><i class="bi bi-calendar-range"></i></div>
        <div class="ss-card-header">
            <span class="ss-card-title">
                วันเริ่ม-สิ้นสุดภาคเรียน
                <small>ใช้คำนวณตารางเช็คชื่อ ปพ.5 — เลือกปี/เทอมได้ทุกปี ไม่จำกัดแค่เทอมปัจจุบัน (จัดการปี/เทอมแบบเต็มได้ที่หน้า งานวิชาการ &gt; จัดการหลักสูตร/แผน)</small>
            </span>
        </div>

        @if($academicYears->isEmpty())
            <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:12px 16px; color:#9a3412; font-size:0.85rem;">
                <i class="bi bi-info-circle-fill"></i> ยังไม่มีปีการศึกษาในระบบ — ไปที่หน้า งานวิชาการ &gt; จัดการหลักสูตร/แผน เพื่อเพิ่มปีการศึกษาก่อน
            </div>
        @else
            <div style="display:flex; flex-wrap:wrap; gap:20px; margin-bottom:16px;">
                <div style="flex:1; min-width:220px;">
                    <label style="font-size:0.85rem; font-weight:700; color:#475569; display:block; margin-bottom:6px;">ปีการศึกษา</label>
                    <select id="ssTermYearSelect" class="ac-select" style="background:#fff; width:100%;"
                            onchange="window.location.href = '{{ route('settings.school.index') }}?year_id=' + this.value + '#ssTermCard'">
                        @foreach($academicYears as $y)
                            <option value="{{ $y->year_id }}" {{ (string) $y->year_id === (string) $selectedTermYearId ? 'selected' : '' }}>
                                {{ $y->year_name }}{{ $y->is_current ? ' ⭐ (ปีปัจจุบัน)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1; min-width:220px;">
                    <label style="font-size:0.85rem; font-weight:700; color:#475569; display:block; margin-bottom:6px;">ภาคเรียน</label>
                    @if($selectedTermYear && $selectedTermYear->semesters->isNotEmpty())
                        <select id="ssTermSemSelect" class="ac-select" style="background:#fff; width:100%;" onchange="ssHandleSemChange(this)">
                            @foreach($selectedTermYear->semesters->sortBy('semester_name') as $sem)
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

            @if($selectedTermYear && $selectedTermYear->semesters->isNotEmpty())
                <form method="POST" id="ssTermDatesForm">
                    @csrf @method('PUT')
                    <div class="ac-grid-4">
                        <div class="ac-field">
                            <label>วันเริ่มภาคเรียน</label>
                            <input type="date" name="start_date" id="ssTermStart" class="ac-input">
                        </div>
                        <div class="ac-field">
                            <label>วันสิ้นสุดภาคเรียน</label>
                            <input type="date" name="end_date" id="ssTermEnd" class="ac-input">
                        </div>
                    </div>
                    <div class="ac-save-wrap" style="display:flex; gap:10px; align-items:center;">
                        <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึกวันเริ่ม-สิ้นสุดภาคเรียน</button>
                    </div>
                </form>
                <form method="POST" id="ssTermSetCurrentForm" style="margin-top:8px;">
                    @csrf @method('PUT')
                    <button type="submit" id="ssTermSetCurrentBtn" class="ac-btn ac-btn-sm" style="background:#10b981; color:#fff;">
                        <i class="bi bi-check-circle"></i> ตั้งเป็นเทอมปัจจุบัน
                    </button>
                </form>
                <script>
                    function ssHandleSemChange(selectObj) {
                        const opt = selectObj.options[selectObj.selectedIndex];
                        document.getElementById('ssTermStart').value = opt.getAttribute('data-start') || '';
                        document.getElementById('ssTermEnd').value = opt.getAttribute('data-end') || '';
                        document.getElementById('ssTermDatesForm').action =
                            '{{ url('academic-years/semester') }}/' + selectObj.value + '/dates';
                        document.getElementById('ssTermSetCurrentForm').action =
                            '{{ url('academic-years/semester') }}/' + selectObj.value + '/current';
                        document.getElementById('ssTermSetCurrentBtn').style.display =
                            opt.getAttribute('data-current') === '1' ? 'none' : 'inline-block';
                    }
                    document.addEventListener('DOMContentLoaded', () => {
                        ssHandleSemChange(document.getElementById('ssTermSemSelect'));
                    });
                </script>
            @endif

            {{-- เปิดภาคเรียน 2 — คัดลอกห้อง+แผนการเรียนจากเทอม 1 ของปีที่เลือกอยู่ --}}
            @if($selectedTermYear)
                <hr style="margin:22px 0 18px; border-top:1px dashed #e2e8f0;">
                <div style="font-size:0.85rem; font-weight:700; color:#475569; margin-bottom:10px;">
                    <i class="bi bi-copy"></i> เปิดภาคเรียน 2 (คัดลอกห้อง+แผนการเรียนจากเทอม 1 ของปี {{ $selectedTermYear->year_name }})
                </div>
                @if($term1SectionsForSelectedYear->isEmpty())
                    <div style="padding:8px 12px; background:#f8fafc; color:#64748b; font-size:0.85rem; border-radius:6px; border:1px solid #e2e8f0;">
                        ปีนี้ยังไม่มีห้องเรียนในเทอม 1 ให้คัดลอก
                    </div>
                @else
                    <form method="POST" action="{{ route('open-semester2.store') }}">
                        @csrf
                        <input type="hidden" name="year_id" value="{{ $selectedTermYear->year_id }}">
                        <div style="max-height:220px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:8px; padding:10px; margin-bottom:12px;">
                            <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.85rem; margin-bottom:8px; cursor:pointer; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                                <input type="checkbox" onchange="document.querySelectorAll('.ssOs2Cb').forEach(cb=>cb.checked=this.checked)" style="width:16px;height:16px;">
                                เลือกทั้งหมด
                            </label>
                            @foreach($term1SectionsForSelectedYear as $sec)
                                <label style="display:flex; align-items:center; gap:8px; font-size:0.85rem; padding:4px 0; cursor:pointer;">
                                    <input type="checkbox" name="section_ids[]" value="{{ $sec->section_id }}" class="ssOs2Cb" checked style="width:16px;height:16px;">
                                    {{ $sec->full_name }}
                                </label>
                            @endforeach
                        </div>
                        <p style="font-size:0.78rem; color:#94a3b8; margin:0 0 10px;">
                            <i class="bi bi-info-circle"></i> ไม่คัดลอกตารางสอนและรายชื่อนักเรียน (ห้องใหม่จะว่างเปล่า รอจัดทีหลัง)
                            ห้องที่มีอยู่ในเทอม 2 แล้วจะถูกข้ามอัตโนมัติ ไม่สร้างซ้ำ
                        </p>
                        <button type="submit" class="ac-btn" style="background:#0891b2; color:#fff;"><i class="bi bi-copy"></i> เปิดภาคเรียน 2 (คัดลอกห้องที่เลือก)</button>
                    </form>
                @endif
            @endif
        @endif
    </div>

    {{-- ตราโรงเรียน --}}
    <div class="ss-card">
        <div class="ss-icon ss-icon-logo"><i class="bi bi-image"></i></div>
        <div class="ss-card-header">
            <span class="ss-card-title">
                ตราโรงเรียน
                <small>รูปนี้จะแสดงบนหัวเอกสารทุกหน้าที่มีตราโรงเรียน — PNG หรือ JPG ขนาดไม่เกิน 2 MB</small>
            </span>
        </div>
        <form method="POST" action="{{ route('settings.school.uploadLogo') }}" enctype="multipart/form-data">
            @csrf
            <div class="ss-logo-row">
                <div class="ss-logo-preview" id="logoPreviewWrap">
                    @if($logoUrl)
                    <img src="{{ $logoUrl }}" id="logoPreview" alt="ตราโรงเรียน">
                    @else
                    <span class="ss-no-logo" id="logoPreviewEmpty">ยังไม่มีตราโรงเรียน</span>
                    <img src="" id="logoPreview" alt="ตราโรงเรียน" style="display:none">
                    @endif
                </div>
                <div style="flex:1;min-width:240px">
                    <input type="file" name="logo" accept=".png,.jpg,.jpeg" onchange="previewImage(this, 'logoPreview', 'logoPreviewEmpty')" class="ac-input" style="height:auto;padding:8px">
                    <div class="ac-save-wrap" style="text-align:left;padding-top:14px">
                        <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-upload"></i> อัปโหลดตราโรงเรียน</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ตราครุฑ --}}
    <div class="ss-card">
        <div class="ss-icon ss-icon-logo"><i class="bi bi-shield-fill-check"></i></div>
        <div class="ss-card-header">
            <span class="ss-card-title">
                ตราครุฑ
                <small>ใช้แสดงในเอกสารราชการ (ปพ.1/ปพ.2) แยกจากตราโรงเรียน — PNG หรือ JPG ขนาดไม่เกิน 2 MB</small>
            </span>
        </div>
        <form method="POST" action="{{ route('settings.school.uploadGaruda') }}" enctype="multipart/form-data">
            @csrf
            <div class="ss-logo-row">
                <div class="ss-logo-preview" id="garudaPreviewWrap">
                    @if($garudaUrl)
                    <img src="{{ $garudaUrl }}" id="garudaPreview" alt="ตราครุฑ">
                    @else
                    <span class="ss-no-logo" id="garudaPreviewEmpty">ยังไม่มีตราครุฑ</span>
                    <img src="" id="garudaPreview" alt="ตราครุฑ" style="display:none">
                    @endif
                </div>
                <div style="flex:1;min-width:240px">
                    <input type="file" name="garuda" accept=".png,.jpg,.jpeg" onchange="previewImage(this, 'garudaPreview', 'garudaPreviewEmpty')" class="ac-input" style="height:auto;padding:8px">
                    <div class="ac-save-wrap" style="text-align:left;padding-top:14px">
                        <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-upload"></i> อัปโหลดตราครุฑ</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ลายเซ็นผู้ลงนาม --}}
    <div class="ss-card">
        <div class="ss-icon ss-icon-sign"><i class="bi bi-pen"></i></div>
        <div class="ss-card-header">
            <span class="ss-card-title">
                ลายเซ็นผู้ลงนามในเอกสาร ปพ.
                <small>เลือกจากรายชื่อบุคลากร หรือพิมพ์ชื่อเองก็ได้</small>
            </span>
        </div>
        <form method="POST" action="{{ route('settings.school.updateSignatures') }}">
            @csrf
            <div class="ac-grid-2">
                <div>
                    <div class="ac-field" style="margin-bottom:8px">
                        <label>นายทะเบียน</label>
                        <select name="registrar_personnel_id" class="ac-select" onchange="onPersonnelChange('registrar')" id="registrar_personnel_id">
                            <option value="">-- เลือกจากบุคลากร --</option>
                            @foreach($personnels as $p)
                            <option value="{{ $p->personnel_id }}" {{ ($setting->registrar_personnel_id ?? null) == $p->personnel_id ? 'selected' : '' }}>
                                {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                                @if($p->position) ({{ $p->position }}) @endif
                            </option>
                            @endforeach
                            <option value="__manual__">-- พิมพ์ชื่อเอง --</option>
                        </select>
                    </div>
                    <div class="ac-field" id="registrar_manual_row" style="display:{{ ($setting->registrar_personnel_id ?? null) ? 'none' : '' }}">
                        <label>ชื่อนายทะเบียน (พิมพ์เอง)</label>
                        <input type="text" name="registrar_name_manual" class="ac-input"
                               value="{{ $setting->registrar_name ?? config('school.registrar_name') }}"
                               placeholder="ชื่อ-นามสกุล นายทะเบียน">
                    </div>
                </div>
                <div>
                    <div class="ac-field" style="margin-bottom:8px">
                        <label>ผู้อำนวยการโรงเรียน</label>
                        <select name="director_personnel_id" class="ac-select" onchange="onPersonnelChange('director')" id="director_personnel_id">
                            <option value="">-- เลือกจากบุคลากร --</option>
                            @foreach($directors as $p)
                            <option value="{{ $p->personnel_id }}" {{ ($setting->director_personnel_id ?? null) == $p->personnel_id ? 'selected' : '' }}>
                                {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                                @if($p->position) ({{ $p->position }}) @endif
                            </option>
                            @endforeach
                            <option value="__manual__">-- พิมพ์ชื่อเอง --</option>
                        </select>
                        @if($directors->isEmpty())
                            <small style="color:#94a3b8">ไม่พบบุคลากรที่ตำแหน่งมีคำว่า "ผู้อำนวยการ" ในระบบ — ใช้ "พิมพ์ชื่อเอง" แทนได้</small>
                        @endif
                    </div>
                    <div class="ac-field" id="director_manual_row" style="display:{{ ($setting->director_personnel_id ?? null) ? 'none' : '' }}">
                        <label>ชื่อผู้อำนวยการ (พิมพ์เอง)</label>
                        <input type="text" name="director_name_manual" class="ac-input"
                               value="{{ $setting->director_name ?? config('school.director_name') }}"
                               placeholder="ชื่อ-นามสกุล ผู้อำนวยการ">
                    </div>
                </div>
            </div>
            <div class="ac-grid-2" style="margin-top:14px">
                <div>
                    <div class="ac-field" style="margin-bottom:8px">
                        <label>รองผู้อำนวยการฝ่ายวิชาการ</label>
                        <select name="deputy_academic_personnel_id" class="ac-select" onchange="onPersonnelChange('deputy_academic')" id="deputy_academic_personnel_id">
                            <option value="">-- เลือกจากบุคลากร --</option>
                            @foreach($personnels as $p)
                            <option value="{{ $p->personnel_id }}" {{ ($setting->deputy_academic_personnel_id ?? null) == $p->personnel_id ? 'selected' : '' }}>
                                {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                                @if($p->position) ({{ $p->position }}) @endif
                            </option>
                            @endforeach
                            <option value="__manual__">-- พิมพ์ชื่อเอง --</option>
                        </select>
                    </div>
                    <div class="ac-field" id="deputy_academic_manual_row" style="display:{{ ($setting->deputy_academic_personnel_id ?? null) ? 'none' : '' }}">
                        <label>ชื่อรองผู้อำนวยการฝ่ายวิชาการ (พิมพ์เอง)</label>
                        <input type="text" name="deputy_academic_name_manual" class="ac-input"
                               value="{{ $setting->deputy_academic_name ?? '' }}"
                               placeholder="ชื่อ-นามสกุล รองผู้อำนวยการฝ่ายวิชาการ">
                    </div>
                </div>
                <div>
                    <div class="ac-field" style="margin-bottom:8px">
                        <label>หัวหน้าฝ่ายวัดผลและประเมินผล</label>
                        <select name="measurement_head_personnel_id" class="ac-select" onchange="onPersonnelChange('measurement_head')" id="measurement_head_personnel_id">
                            <option value="">-- เลือกจากบุคลากร --</option>
                            @foreach($personnels as $p)
                            <option value="{{ $p->personnel_id }}" {{ ($setting->measurement_head_personnel_id ?? null) == $p->personnel_id ? 'selected' : '' }}>
                                {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                                @if($p->position) ({{ $p->position }}) @endif
                            </option>
                            @endforeach
                            <option value="__manual__">-- พิมพ์ชื่อเอง --</option>
                        </select>
                    </div>
                    <div class="ac-field" id="measurement_head_manual_row" style="display:{{ ($setting->measurement_head_personnel_id ?? null) ? 'none' : '' }}">
                        <label>ชื่อหัวหน้าฝ่ายวัดผลและประเมินผล (พิมพ์เอง)</label>
                        <input type="text" name="measurement_head_name_manual" class="ac-input"
                               value="{{ $setting->measurement_head_name ?? '' }}"
                               placeholder="ชื่อ-นามสกุล หัวหน้าฝ่ายวัดผลและประเมินผล">
                    </div>
                </div>
            </div>
            <div class="ac-save-wrap">
                <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึกผู้ลงนาม</button>
            </div>
        </form>
    </div>

    <div class="ss-card">
        <div class="ss-icon ss-icon-sign"><i class="bi bi-people"></i></div>
        <div class="ss-card-header">
            <span class="ss-card-title">
                หัวหน้ากลุ่มสาระการเรียนรู้
                <small>คนเดียวต่อกลุ่มสาระ ใช้ร่วมกันทุกวิชา/ทุกห้อง/ทุกเทอมในกลุ่มนั้น (เช่น ปพ.5)</small>
            </span>
        </div>
        <form method="POST" action="{{ route('settings.school.updateSubjectGroupHeads') }}">
            @csrf
            <div class="ac-grid-2">
                @foreach($subjectGroups as $group)
                <div class="ac-field" style="margin-bottom:8px">
                    <label>{{ $group }}</label>
                    <select name="heads[{{ $group }}]" class="ac-select">
                        <option value="">-- ยังไม่กำหนด --</option>
                        @foreach($personnels as $p)
                        <option value="{{ $p->personnel_id }}" {{ ($subjectGroupHeads[$group]->personnel_id ?? null) == $p->personnel_id ? 'selected' : '' }}>
                            {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                            @if($p->position) ({{ $p->position }}) @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                @endforeach
            </div>
            <div class="ac-save-wrap">
                <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึกหัวหน้ากลุ่มสาระ</button>
            </div>
        </form>
    </div>

</div>

<script>
function onPersonnelChange(role) {
    const sel = document.getElementById(role + '_personnel_id');
    const manualRow = document.getElementById(role + '_manual_row');
    manualRow.style.display = sel.value === '__manual__' || sel.value === '' ? '' : 'none';
}

function previewImage(input, previewId, emptyId) {
    const maxMB = 2;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > maxMB * 1024 * 1024) {
            alert('ไฟล์ใหญ่เกินไป! ขนาดสูงสุดที่รองรับคือ ' + maxMB + ' MB\nไฟล์ที่เลือก: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById(previewId);
            img.src = e.target.result;
            img.style.display = '';
            const empty = document.getElementById(emptyId);
            if (empty) empty.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
