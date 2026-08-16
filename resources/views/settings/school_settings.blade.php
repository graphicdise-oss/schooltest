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
                    <input type="file" name="logo" accept=".png,.jpg,.jpeg" onchange="previewLogo(this)" class="ac-input" style="height:auto;padding:8px">
                    <div class="ac-save-wrap" style="text-align:left;padding-top:14px">
                        <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-upload"></i> อัปโหลดตราโรงเรียน</button>
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

function previewLogo(input) {
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
            const img = document.getElementById('logoPreview');
            img.src = e.target.result;
            img.style.display = '';
            const empty = document.getElementById('logoPreviewEmpty');
            if (empty) empty.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
