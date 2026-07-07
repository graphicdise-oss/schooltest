@extends('layouts.sidebar')
@push('styles')
<style>
.p7-page { padding: 24px 28px; }

.p7-card {
    background: #fff; border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 24px 24px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.p7-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.p7-icon-search { background: #00bcd4; }
.p7-icon-list   { background: #5c6bc0; }
.p7-card-title  { margin-left: 90px; font-size: 1.05rem; color: #555; margin-top: -8px; }

.p7-search-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 14px 24px; margin-top: 20px; align-items: end;
}
.p7-search-row2 {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 14px 24px; margin-top: 14px; align-items: end;
}
.p7-field label { font-size: 0.82rem; font-weight: 600; color: #444; margin-bottom: 3px; display: block; }
.p7-field select, .p7-field input[type=text] {
    width: 100%; height: 36px; border: none; border-bottom: 1.5px solid #bbb;
    padding: 0 8px; font-size: 0.88rem; font-family: inherit; outline: none;
    background: transparent; box-sizing: border-box;
}
.p7-field select:focus, .p7-field input:focus { border-bottom-color: #00bcd4; }
.btn-search {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px;
    padding: 9px 28px; font-size: 0.88rem; font-weight: 600;
    cursor: pointer; font-family: inherit; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 6px;
    height: 36px;
}

/* Table */
.p7-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 8px; }
.p7-table thead th {
    padding: 11px 12px; border-bottom: 2px solid #eee;
    color: #333; font-weight: 600; text-align: left; font-size: 0.83rem;
}
.p7-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.p7-table tbody tr:hover { background: #f9fbff; }
.p7-table tbody td { padding: 10px 12px; color: #555; vertical-align: middle; }

.btn-print-por7 {
    background: #00bcd4; color: #fff; border: none; border-radius: 5px;
    padding: 6px 14px; font-size: 0.8rem; font-weight: 600; cursor: pointer;
    font-family: inherit; display: inline-flex; align-items: center; gap: 5px;
    text-decoration: none;
}
.btn-print-por7:hover { background: #0097a7; color: #fff; }

/* Modal */
.p7-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.45); z-index: 9999;
    align-items: center; justify-content: center;
}
.p7-modal-overlay.open { display: flex; }
.p7-modal {
    background: #fff; border-radius: 10px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    width: 420px; max-width: 94vw; padding: 28px 28px 22px;
}
.p7-modal h3 { font-size: 1rem; font-weight: 700; color: #333; margin: 0 0 20px; text-align: center; }
.p7-modal-field { margin-bottom: 16px; }
.p7-modal-field label { font-size: 0.82rem; font-weight: 600; color: #555; display: block; margin-bottom: 5px; }
.p7-modal-field input, .p7-modal-field select {
    width: 100%; border: 1.5px solid #ddd; border-radius: 6px;
    padding: 8px 12px; font-size: 0.9rem; font-family: inherit; outline: none; box-sizing: border-box;
}
.p7-modal-field input:focus, .p7-modal-field select:focus { border-color: #00bcd4; }
.p7-modal-actions { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
.btn-save { background: #4caf50; color: #fff; border: none; border-radius: 6px; padding: 9px 28px; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
.btn-cancel { background: #f44336; color: #fff; border: none; border-radius: 6px; padding: 9px 28px; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
.p7-empty { text-align: center; padding: 40px; color: #aaa; }
</style>
@endpush

@section('content')
<div class="p7-page">

    @if(session('success'))
    <div style="background:#e8f5e9;color:#2e7d32;padding:10px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- ค้นหา --}}
    <div class="p7-card">
        <div class="p7-icon p7-icon-search"><i class="bi bi-search"></i></div>
        <div class="p7-card-title">ค้นหานักเรียน</div>
        <form method="GET" action="{{ route('por7.index') }}" id="searchForm">
            <div class="p7-search-grid">
                <div class="p7-field">
                    <label>ปีการศึกษา</label>
                    <select name="year_id" onchange="this.form.submit()">
                        @foreach($academicYears as $ay)
                        <option value="{{ $ay->year_id }}" {{ $yearId == $ay->year_id ? 'selected' : '' }}>
                            {{ $ay->year_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="p7-field">
                    <label>เทอม</label>
                    <select name="term" onchange="this.form.submit()">
                        <option value="1" {{ $term == '1' ? 'selected' : '' }}>เทอม 1</option>
                        <option value="2" {{ $term == '2' ? 'selected' : '' }}>เทอม 2</option>
                    </select>
                </div>
                <div class="p7-field">
                    <label>ระดับชั้น</label>
                    <select name="level_id" onchange="this.form.submit()">
                        <option value="">-- ทุกระดับ --</option>
                        @foreach($levels as $lv)
                        <option value="{{ $lv->level_id }}" {{ $levelId == $lv->level_id ? 'selected' : '' }}>
                            {{ $lv->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="p7-field">
                    <label>ชั้นเรียน</label>
                    <select name="section_id" onchange="this.form.submit()">
                        <option value="">-- ทุกชั้นเรียน --</option>
                        @foreach($sections as $sec)
                        <option value="{{ $sec->section_id }}" {{ $sectionId == $sec->section_id ? 'selected' : '' }}>
                            {{ $sec->level->name ?? '' }}/{{ $sec->section_number }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="p7-search-row2">
                <div class="p7-field">
                    <label>ค้นหาชื่อ / รหัส</label>
                    <input type="text" name="search" placeholder="พิมพ์ชื่อหรือรหัสนักเรียน..." value="{{ $search }}">
                </div>
                <div class="p7-field" style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="submit" class="btn-search"><i class="bi bi-search"></i> ค้นหา</button>
                    <button type="button" class="btn-search" style="background:#00897b;" onclick="openSchoolSettingModal()">
                        <i class="bi bi-gear"></i> ตั้งค่าข้อมูลโรงเรียน
                    </button>
                    <button type="button" class="btn-search" style="background:#5c6bc0;" onclick="openSignSettingsModal()">
                        <i class="bi bi-pen"></i> ตั้งค่าผู้ลงนาม
                    </button>
                    <button type="button" class="btn-search" style="background:#e65100;" onclick="openLogoModal()">
                        <i class="bi bi-image"></i> อัปโหลดตราโรงเรียน
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- รายการนักเรียน --}}
    <div class="p7-card">
        <div class="p7-icon p7-icon-list"><i class="bi bi-file-earmark-person"></i></div>
        <div class="p7-card-title">
            ใบรับรองการเป็นนักเรียน (ปพ.7)
            @if($currentSection)
            — {{ $currentSection->level->name ?? '' }}/{{ $currentSection->section_number }}
            @endif
            ({{ $students->count() }} คน)
        </div>

        @if($students->count())
        <table class="p7-table">
            <thead>
                <tr>
                    <th style="width:50px">ลำดับ</th>
                    <th>รหัสนักเรียน</th>
                    <th>ชื่อ - นามสกุล</th>
                    <th>เลขบัตรประชาชน</th>
                    <th>ชั้น/ห้อง</th>
                    <th style="text-align:center;min-width:130px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $row)
                @php $stu = $row['student']; $sec = $row['section']; @endphp
                <tr>
                    <td>{{ $i + 1 }}.</td>
                    <td>{{ $stu->student_code }}</td>
                    <td>{{ $stu->thai_prefix }}{{ $stu->thai_firstname }} {{ $stu->thai_lastname }}</td>
                    <td>{{ $stu->id_card_number }}</td>
                    <td>{{ $sec->level->name ?? '' }}/{{ $sec->section_number ?? '' }}</td>
                    <td style="text-align:center">
                        <button type="button" class="btn-print-por7"
                            onclick="openPrintModal({{ $stu->student_id }}, {{ $semesterId ?? 0 }})">
                            <i class="bi bi-printer"></i> พิมพ์ ปพ.7
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="p7-empty">
            <i class="bi bi-file-earmark-x" style="font-size:2rem;display:block;margin-bottom:8px"></i>
            กรุณาเลือกชั้นเรียนหรือค้นหาชื่อนักเรียน
        </div>
        @endif
    </div>

</div>

{{-- Modal: ตั้งค่าก่อนพิมพ์ ปพ.7 --}}
<div class="p7-modal-overlay" id="printModal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="p7-modal" onclick="event.stopPropagation()">
        <h3><i class="bi bi-printer"></i> พิมพ์ใบรับรองการเป็นนักเรียน (ปพ.7)</h3>
        <form id="por7PrintForm" method="GET" action="" target="_blank">
            <input type="hidden" name="semester_id" id="modal_semester_id">
            <div class="p7-modal-field">
                <label>วันที่ออกเอกสาร</label>
                <input type="date" name="issue_date" id="modal_issue_date" required>
            </div>
            <div class="p7-modal-field">
                <label>ผลการเรียน</label>
                <select name="grade_result" id="modal_grade_result">
                    <option value="ดีเยี่ยม">ดีเยี่ยม</option>
                    <option value="ดี">ดี</option>
                    <option value="พอใช้">พอใช้</option>
                    <option value="ผ่าน">ผ่าน</option>
                </select>
            </div>
            <div class="p7-modal-field">
                <label>ความประพฤติ</label>
                <select name="behavior" id="modal_behavior">
                    <option value="เรียบร้อย">เรียบร้อย</option>
                    <option value="ดี">ดี</option>
                    <option value="พอใช้">พอใช้</option>
                </select>
            </div>
            <div class="p7-modal-actions">
                <button type="submit" class="btn-save"><i class="bi bi-printer"></i> พิมพ์</button>
                <button type="button" class="btn-cancel" onclick="document.getElementById('printModal').classList.remove('open')">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: ตั้งค่าผู้ลงนาม --}}
<div class="p7-modal-overlay" id="signSettingsModal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="p7-modal" style="width:540px;max-width:95vw;" onclick="event.stopPropagation()">
        <h3><i class="bi bi-pen"></i> ตั้งค่าผู้ลงนามในใบ ปพ.7</h3>
        <form method="POST" action="{{ route('por7.saveSignSettings') }}">
            @csrf
            <div class="p7-modal-field">
                <label>นายทะเบียน</label>
                <select id="registrar_personnel_id" name="registrar_personnel_id"
                        onchange="onPersonnelChange('registrar')"
                        style="width:100%;height:36px;border:1px solid #ccc;border-radius:4px;padding:0 8px;font-size:0.88rem;font-family:inherit;">
                    <option value="">-- เลือกจากบุคลากร --</option>
                    @foreach($personnels as $p)
                    <option value="{{ $p->personnel_id }}"
                        {{ ($signSettings->registrar_personnel_id ?? null) == $p->personnel_id ? 'selected' : '' }}>
                        {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                        @if($p->position) ({{ $p->position }}) @endif
                    </option>
                    @endforeach
                    <option value="__manual__">-- พิมพ์ชื่อเอง --</option>
                </select>
            </div>
            <div class="p7-modal-field" id="registrar_manual_row"
                 style="display:{{ ($signSettings->registrar_personnel_id ?? null) ? 'none' : '' }}">
                <label>ชื่อนายทะเบียน (พิมพ์เอง)</label>
                <input type="text" name="registrar_name_manual"
                       value="{{ $signSettings->registrar_name ?? config('school.registrar_name') }}"
                       placeholder="ชื่อ-นามสกุล นายทะเบียน">
            </div>
            <div class="p7-modal-field" style="margin-top:16px;">
                <label>ผู้อำนวยการโรงเรียน</label>
                <select id="director_personnel_id" name="director_personnel_id"
                        onchange="onPersonnelChange('director')"
                        style="width:100%;height:36px;border:1px solid #ccc;border-radius:4px;padding:0 8px;font-size:0.88rem;font-family:inherit;">
                    <option value="">-- เลือกจากบุคลากร --</option>
                    @foreach($personnels as $p)
                    <option value="{{ $p->personnel_id }}"
                        {{ ($signSettings->director_personnel_id ?? null) == $p->personnel_id ? 'selected' : '' }}>
                        {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                        @if($p->position) ({{ $p->position }}) @endif
                    </option>
                    @endforeach
                    <option value="__manual__">-- พิมพ์ชื่อเอง --</option>
                </select>
            </div>
            <div class="p7-modal-field" id="director_manual_row"
                 style="display:{{ ($signSettings->director_personnel_id ?? null) ? 'none' : '' }}">
                <label>ชื่อผู้อำนวยการ (พิมพ์เอง)</label>
                <input type="text" name="director_name_manual"
                       value="{{ $signSettings->director_name ?? config('school.director_name') }}"
                       placeholder="ชื่อ-นามสกุล ผู้อำนวยการ">
            </div>
            <div class="p7-modal-actions">
                <button type="submit" class="btn-save"><i class="bi bi-check-lg"></i> บันทึก</button>
                <button type="button" class="btn-cancel" onclick="closeSignSettingsModal()">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: ตั้งค่าข้อมูลโรงเรียน --}}
<div class="p7-modal-overlay" id="schoolSettingModal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="p7-modal" style="width:560px;max-width:95vw;" onclick="event.stopPropagation()">
        <h3><i class="bi bi-gear"></i> ตั้งค่าข้อมูลโรงเรียน</h3>
        @if(session('success') && str_contains(session('success'), 'โรงเรียน'))
        <div style="background:#e8f5e9;color:#2e7d32;padding:8px 12px;border-radius:4px;margin-bottom:12px;font-size:0.85rem;">
            ✓ {{ session('success') }}
        </div>
        @endif
        <form method="POST" action="{{ route('por7.saveSchoolSetting') }}">
            @csrf
            <div class="p7-modal-field">
                <label>ชื่อโรงเรียน</label>
                <input type="text" name="school_name" class="form-control"
                    value="{{ old('school_name', $setting?->school_name ?? config('school.name')) }}"
                    placeholder="เช่น โรงเรียนสาธิตมหาวิทยาลัย...">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="p7-modal-field">
                    <label>จังหวัด</label>
                    <input type="text" name="province" class="form-control"
                        value="{{ old('province', $setting?->province ?? config('school.changwat')) }}"
                        placeholder="เช่น ปทุมธานี">
                </div>
                <div class="p7-modal-field">
                    <label>ชื่อผู้อำนวยการ</label>
                    <select name="director_personnel_id" class="form-select">
                        <option value="">-- เลือกบุคลากร --</option>
                        @foreach($directors as $p)
                            @php $fullName = ($p->thai_prefix ?? '').$p->thai_firstname.' '.$p->thai_lastname; @endphp
                            <option value="{{ $p->personnel_id }}"
                                {{ ($setting?->director_personnel_id) == $p->personnel_id ? 'selected' : '' }}>
                                {{ $fullName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="p7-modal-field">
                <label>สังกัด</label>
                <input type="text" name="affiliation" class="form-control"
                    value="{{ old('affiliation', $setting?->affiliation ?? config('school.affiliation')) }}"
                    placeholder="เช่น สำนักงานปลัดกระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม">
            </div>
            <div class="p7-modal-actions">
                <button type="submit" class="btn-save"><i class="bi bi-check-lg"></i> บันทึก</button>
                <button type="button" class="btn-cancel" onclick="closeSchoolSettingModal()">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<script>
const today = new Date().toISOString().split('T')[0];

function openPrintModal(studentId, semesterId) {
    document.getElementById('modal_issue_date').value = today;
    document.getElementById('modal_semester_id').value = semesterId;
    document.getElementById('por7PrintForm').action = "{{ url('por7/print') }}/" + studentId;
    document.getElementById('printModal').classList.add('open');
}

function openSignSettingsModal() { document.getElementById('signSettingsModal').classList.add('open'); }
function closeSignSettingsModal() { document.getElementById('signSettingsModal').classList.remove('open'); }

function openSchoolSettingModal() { document.getElementById('schoolSettingModal').classList.add('open'); }
function closeSchoolSettingModal() { document.getElementById('schoolSettingModal').classList.remove('open'); }

function onPersonnelChange(role) {
    const sel = document.getElementById(role + '_personnel_id');
    const manualRow = document.getElementById(role + '_manual_row');
    manualRow.style.display = sel.value === '__manual__' ? '' : 'none';
}

function openLogoModal() { document.getElementById('logoModal').classList.add('open'); }
function closeLogoModal() { document.getElementById('logoModal').classList.remove('open'); }

function previewLogo(input) {
    const maxMB = 2;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > maxMB * 1024 * 1024) {
            alert('ไฟล์ใหญ่เกินไป! ขนาดสูงสุดที่รองรับคือ ' + maxMB + ' MB\nไฟล์ที่เลือก: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB');
            input.value = '';
            document.getElementById('logoPreviewWrap').style.display = 'none';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('logoPreview').src = e.target.result;
            document.getElementById('logoPreviewWrap').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}
</script>

{{-- Modal: อัปโหลดตราโรงเรียน --}}
<div class="p7-modal-overlay" id="logoModal" onclick="if(event.target===this)closeLogoModal()">
    <div class="p7-modal" onclick="event.stopPropagation()">
        <h3><i class="bi bi-image"></i> อัปโหลดตราโรงเรียน</h3>
        <form method="POST" action="{{ route('por7.uploadLogo') }}" enctype="multipart/form-data">
            @csrf
            <div class="p7-modal-field">
                <label>เลือกไฟล์รูป (PNG หรือ JPG) — ขนาดสูงสุด 2 MB</label>
                <input type="file" name="logo" accept=".png,.jpg,.jpeg"
                    onchange="previewLogo(this)"
                    style="width:100%;border:1.5px solid #ddd;border-radius:6px;padding:8px;font-family:inherit;font-size:0.88rem;box-sizing:border-box;">
            </div>
            <div id="logoPreviewWrap" style="display:none;text-align:center;margin:12px 0;">
                <img id="logoPreview" style="max-height:100px;max-width:100px;object-fit:contain;border:1px solid #eee;padding:4px;border-radius:6px;">
            </div>
            <p style="font-size:0.8rem;color:#888;margin:0 0 12px;">
                <i class="bi bi-info-circle"></i> รูปนี้จะแสดงในหน้า ปพ.7 และ ปพ.1
            </p>
            <div class="p7-modal-actions">
                <button type="submit" class="btn-save"><i class="bi bi-upload"></i> อัปโหลด</button>
                <button type="button" class="btn-cancel" onclick="closeLogoModal()">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>
@endsection
