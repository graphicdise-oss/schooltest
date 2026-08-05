@extends('layouts.sidebar')
@push('styles')
<style>
.gib-page { padding: 24px 28px; }

.gib-card {
    background: #fff; border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 24px 24px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.gib-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.gib-icon-search { background: #00bcd4; }
.gib-icon-list   { background: #5c6bc0; }
.gib-card-title  { margin-left: 90px; font-size: 1.05rem; color: #555; margin-top: -8px; }

.gib-search-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 14px 24px; margin-top: 20px; align-items: end;
}
.gib-field label { font-size: 0.82rem; font-weight: 600; color: #444; margin-bottom: 3px; display: block; }
.gib-field select {
    width: 100%; height: 36px; border: none; border-bottom: 1.5px solid #bbb;
    padding: 0 8px; font-size: 0.88rem; font-family: inherit; outline: none;
    background: transparent; box-sizing: border-box;
}
.gib-field select:focus { border-bottom-color: #00bcd4; }

.gib-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 8px; }
.gib-table thead th {
    padding: 11px 12px; border-bottom: 2px solid #eee;
    color: #333; font-weight: 600; text-align: left; font-size: 0.83rem;
}
.gib-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.gib-table tbody tr:hover { background: #f9fbff; }
.gib-table tbody td { padding: 10px 12px; color: #555; vertical-align: middle; }

.btn-dl {
    background: #0d6efd; color: #fff; border: none; border-radius: 6px;
    padding: 8px 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-dl:hover { background: #0b5ed7; color: #fff; }
.btn-ul {
    background: #198754; color: #fff; border: none; border-radius: 6px;
    padding: 8px 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    font-family: inherit;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-ul:hover { background: #157347; }
.gib-empty { text-align: center; padding: 40px; color: #aaa; }

/* Modal (ชื่อคลาสตามแพทเทิร์นเดียวกับ grades_edit.blade.php) */
.gib-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center; }
.gib-overlay.active { display:flex; }
.gib-modal { background:#fff; border-radius:10px; width:480px; max-width:95vw; box-shadow:0 8px 30px rgba(0,0,0,0.2); }
.gib-modal-header { padding:18px 22px 14px; font-size:1rem; font-weight:700; border-bottom:1px solid #eee; color:#333; display:flex; align-items:center; gap:8px; }
.gib-modal-body { padding:18px 22px; display:flex; flex-direction:column; gap:14px; }
.gib-modal-footer { padding:14px 22px 18px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #eee; }
.btn-modal-cancel { background:#eee; color:#555; border:none; border-radius:6px; padding:8px 20px; font-size:0.85rem; font-weight:600; cursor:pointer; font-family:inherit; }
.btn-modal-save { background:#198754; color:#fff; border:none; border-radius:6px; padding:8px 22px; font-size:0.85rem; font-weight:600; cursor:pointer; font-family:inherit; display:inline-flex; align-items:center; gap:6px; }
</style>
@endpush

@section('content')
<div class="gib-page">

    {{-- เลือกห้อง --}}
    <div class="gib-card">
        <div class="gib-icon gib-icon-search"><i class="bi bi-search"></i></div>
        <div class="gib-card-title">นำเข้าเกรดรวม (Transcript) หลายคน/ทั้งห้อง — เลือกห้องเรียน</div>
        <form method="GET" action="{{ route('grades.importBulkIndex') }}" id="searchForm">
            <div class="gib-search-grid">
                <div class="gib-field">
                    <label>ปีการศึกษา</label>
                    <select name="year_id" onchange="this.form.submit()">
                        @foreach($academicYears as $ay)
                        <option value="{{ $ay->year_id }}" {{ $yearId == $ay->year_id ? 'selected' : '' }}>
                            {{ $ay->year_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="gib-field">
                    <label>เทอม</label>
                    <select name="term" onchange="this.form.submit()">
                        <option value="1" {{ $term == '1' ? 'selected' : '' }}>เทอม 1</option>
                        <option value="2" {{ $term == '2' ? 'selected' : '' }}>เทอม 2</option>
                    </select>
                </div>
                <div class="gib-field">
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
                <div class="gib-field">
                    <label>ห้องเรียน</label>
                    <select name="section_id" onchange="this.form.submit()">
                        <option value="">-- เลือกห้องเรียน --</option>
                        @foreach($sections as $sec)
                        <option value="{{ $sec->section_id }}" {{ (string) $sectionId === (string) $sec->section_id ? 'selected' : '' }}>
                            {{ $sec->full_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    {{-- รายชื่อนักเรียนในห้อง + ปุ่มดาวน์โหลด/อัปโหลด --}}
    <div class="gib-card">
        <div class="gib-icon gib-icon-list"><i class="bi bi-people-fill"></i></div>
        <div class="gib-card-title" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-right:0">
            <span>
                @if($currentSection)
                    ห้อง {{ $currentSection->full_name }} ({{ $students->count() }} คน)
                @else
                    เลือกห้องเรียนด้านบนก่อน
                @endif
            </span>
            @if($currentSection && $students->count())
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="{{ route('grades.importBulkTemplate', $sectionId) }}" class="btn-dl">
                    <i class="bi bi-download"></i> ดาวน์โหลดแบบฟอร์มทั้งห้อง
                </a>
                <button type="button" class="btn-ul" onclick="document.getElementById('bulkImportOverlay').classList.add('active')">
                    <i class="bi bi-upload"></i> นำเข้าไฟล์ที่กรอกแล้ว
                </button>
            </div>
            @endif
        </div>

        @if(!$currentSection)
        <div class="gib-empty">
            <i class="bi bi-arrow-up-circle" style="font-size:2rem;display:block;margin-bottom:8px"></i>
            เลือกปี/เทอม/ห้องเรียนด้านบนก่อน ระบบจะโชว์รายชื่อนักเรียนในห้องนั้นให้ดาวน์โหลดฟอร์ม
        </div>
        @elseif(!$students->count())
        <div class="gib-empty">
            <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
            ห้องนี้ยังไม่มีนักเรียน
        </div>
        @else
        <table class="gib-table">
            <thead>
                <tr>
                    <th style="width:60px">เลขที่</th>
                    <th>รหัสนักเรียน</th>
                    <th>ชื่อ - นามสกุล</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $ss)
                <tr>
                    <td>{{ $ss->student_number }}</td>
                    <td>{{ $ss->student->student_code ?? '-' }}</td>
                    <td>{{ $ss->student->thai_prefix ?? '' }}{{ $ss->student->thai_firstname ?? '' }} {{ $ss->student->thai_lastname ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</div>

{{-- นำเข้าเกรดรวมทั้งห้อง Modal --}}
<div class="gib-overlay" id="bulkImportOverlay" onclick="if(event.target===this)closeBulkImportModal()">
    <div class="gib-modal">
        <div class="gib-modal-header"><i class="bi bi-upload"></i> นำเข้าเกรดรวมทั้งห้อง</div>
        <form method="POST" action="{{ route('grades.importBulkUpload') }}"
              enctype="multipart/form-data" onsubmit="submitBulkImportForm()">
            @csrf
            <div class="gib-modal-body">
                <p style="font-size:0.8rem; color:#777; margin:0 0 4px">
                    ใช้ไฟล์ที่ดาวน์โหลดจากปุ่ม "ดาวน์โหลดแบบฟอร์มทั้งห้อง" แล้วกรอกข้อมูล — ไฟล์นี้ระบุเจ้าของแต่ละแถวด้วย
                    "เลขประจำตัวนักเรียน" เอง จึงใส่ข้อมูลของใครก็ได้ในไฟล์เดียวกัน ไม่จำเป็นต้องเป็นห้องที่เลือกไว้ด้านบน
                </p>
                <div>
                    <input type="file" name="file" accept=".xlsx" required>
                </div>
                <div>
                    <label style="display:flex; align-items:center; gap:6px; font-weight:400; font-size:0.85rem; color:#444">
                        <input type="checkbox" name="dry_run" value="1" style="width:auto">
                        ทดสอบก่อน (dry-run) — ยังไม่บันทึกข้อมูลจริง
                    </label>
                </div>
            </div>
            <div class="gib-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeBulkImportModal()">ยกเลิก</button>
                <button type="submit" id="bulkImportSubmitBtn" class="btn-modal-save">
                    <i class="bi bi-upload"></i> เริ่มนำเข้า
                </button>
            </div>
        </form>
    </div>
</div>

@if (session('transcript_import_output'))
<div class="gib-overlay active" id="bulkImportResultOverlay">
    <div class="gib-modal" style="width:640px; max-width:95vw">
        <div class="gib-modal-header"><i class="bi bi-clipboard-check"></i> ผลการนำเข้าเกรดรวมทั้งห้อง</div>
        <div class="gib-modal-body">
            <pre style="background:#f5f5f5; padding:12px; border-radius:8px; overflow:auto; max-height:60vh; font-size:0.8rem; white-space:pre-wrap">{{ session('transcript_import_output') }}</pre>
        </div>
        <div class="gib-modal-footer">
            <button type="button" class="btn-modal-save" onclick="document.getElementById('bulkImportResultOverlay').remove()">ปิด</button>
        </div>
    </div>
</div>
@endif

<script>
function closeBulkImportModal() {
    document.getElementById('bulkImportOverlay').classList.remove('active');
}
function submitBulkImportForm() {
    document.getElementById('bulkImportSubmitBtn').disabled = true;
    document.getElementById('bulkImportSubmitBtn').innerText = 'กำลังนำเข้า... กรุณารอสักครู่';
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.gib-overlay.active').forEach(el => el.classList.remove('active'));
});
</script>
@endsection
