@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">
<style>
.att-xl-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center; }
.att-xl-overlay.active { display:flex; }
.att-xl-modal { background:#fff; border-radius:10px; width:440px; max-width:95vw; box-shadow:0 8px 30px rgba(0,0,0,0.2); }
.att-xl-modal-header { padding:18px 22px 14px; font-size:1rem; font-weight:700; border-bottom:1px solid #eee; color:#333; display:flex; align-items:center; gap:8px; }
.att-xl-modal-body { padding:18px 22px; display:flex; flex-direction:column; gap:14px; }
.att-xl-modal-footer { padding:14px 22px 18px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #eee; }
.att-xl-field label { font-size:.82rem; font-weight:600; color:#444; margin-bottom:4px; display:block; }
.att-xl-field input[type=date] { width:100%; height:36px; border:1.5px solid #d0d7e5; border-radius:6px; padding:0 8px; font-size:.88rem; font-family:inherit; box-sizing:border-box; }
.att-btn-cancel { background:#eee; color:#555; border:none; border-radius:6px; padding:8px 20px; font-size:.85rem; font-weight:600; cursor:pointer; font-family:inherit; }
.att-btn-go { background:#0d6efd; color:#fff; border:none; border-radius:6px; padding:8px 20px; font-size:.85rem; font-weight:600; cursor:pointer; font-family:inherit; display:inline-flex; align-items:center; gap:6px; }
.att-btn-up { background:#198754; color:#fff; border:none; border-radius:6px; padding:8px 18px; font-size:.85rem; font-weight:600; cursor:pointer; font-family:inherit; display:inline-flex; align-items:center; gap:6px; }
</style>
@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><span>เช็คชื่อ/ปรับสถานะการมาเรียน</span></nav>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="ac-card">
        <div class="ac-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <span><i class="bi bi-clipboard-check"></i> เลือกวิชาเพื่อเช็คชื่อ</span>
            <button type="button" class="att-btn-up" onclick="document.getElementById('attImportOverlay').classList.add('active')">
                <i class="bi bi-upload"></i> นำเข้าไฟล์ Excel ที่กรอกแล้ว
            </button>
        </div>
        <p style="font-size:.82rem;color:#777;margin:10px 0 0;padding:0 20px">
            ไม่มีเน็ตตอนสอน? กด "Excel" ที่แถววิชาด้านล่างเพื่อดาวน์โหลดแบบฟอร์มไปกรอกออฟไลน์ แล้วค่อยอัปโหลดย้อนหลังด้วยปุ่ม "นำเข้าไฟล์ Excel" ด้านบนได้ทีหลัง
        </p>
        <div class="ac-card-body">
            <form method="GET" action="{{ route('attendance.index') }}" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:flex-end;max-width:900px;margin-bottom:20px">
                <div class="ac-field" style="margin:0">
                    <label>ปีการศึกษา / เทอม</label>
                    <select class="ac-select" name="semester_id">
                        @foreach($semesters as $sem)
                        <option value="{{ $sem->semester_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>
                            {{ $sem->academicYear->year_name }} เทอม {{ $sem->semester_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="ac-field" style="margin:0">
                    <label>วิชา</label>
                    <select class="ac-select" name="subject_id">
                        <option value="">-- ทุกวิชา --</option>
                        @foreach($subjects as $sub)
                        <option value="{{ $sub->subject_id }}" {{ $subjectId==$sub->subject_id?'selected':'' }}>{{ $sub->code }} — {{ $sub->name_th }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ac-field" style="margin:0">
                    <label>ครูผู้สอน</label>
                    <select class="ac-select" name="personnel_id">
                        <option value="">-- ทุกคน --</option>
                        @foreach($teachers as $t)
                        <option value="{{ $t->personnel_id }}" {{ $personnelId==$t->personnel_id?'selected':'' }}>{{ $t->thai_firstname }} {{ $t->thai_lastname }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="ac-btn ac-btn-primary" style="height:38px;white-space:nowrap"><i class="bi bi-search"></i> ค้นหา</button>
            </form>

            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>#</th><th>รหัสวิชา</th><th>ชื่อวิชา</th><th>ห้อง</th><th>ครูผู้สอน</th><th>เช็คชื่อแล้ว</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($assigns as $i => $a)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $a->subject->code }}</td>
                            <td style="text-align:left">{{ $a->subject->name_th }}</td>
                            <td>{{ $a->classSection->level->name ?? '' }}/{{ $a->classSection->section_number }}</td>
                            <td>{{ $a->personnel->thai_firstname }} {{ $a->personnel->thai_lastname }}</td>
                            <td><span class="ac-badge ac-badge-info">{{ $a->attendance_days }} วัน</span></td>
                            <td style="white-space:nowrap">
                                <a href="{{ route('attendance.mark', $a->assign_id) }}" class="ac-btn ac-btn-primary ac-btn-sm"><i class="bi bi-check2-square"></i> เช็คชื่อ</a>
                                <button type="button" class="ac-btn ac-btn-sm" style="background:#0d6efd;color:#fff"
                                    onclick="openExportModal({{ $a->assign_id }}, '{{ $a->subject->code }} — {{ $a->classSection->full_name ?? '' }}')">
                                    <i class="bi bi-file-earmark-excel"></i> Excel
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="ac-empty">ไม่พบรายวิชา</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ดาวน์โหลดแบบฟอร์ม Excel (เลือกเดือน หรือทุกเดือนในเทอม) --}}
<div class="att-xl-overlay" id="attExportOverlay" onclick="if(event.target===this)closeExportModal()">
    <div class="att-xl-modal">
        <div class="att-xl-modal-header"><i class="bi bi-file-earmark-excel"></i> ดาวน์โหลดแบบฟอร์มเช็คชื่อ</div>
        <div class="att-xl-modal-body">
            <p id="attExportLabel" style="font-size:.85rem;color:#555;margin:0;font-weight:600"></p>
            <div class="att-xl-field">
                <label>เดือน</label>
                <input type="month" id="attExportMonth">
            </div>
            <p style="font-size:.78rem;color:#999;margin:0">
                ระบบตัดวันเสาร์-อาทิตย์และวันหยุดตามปฏิทิน (ตั้งค่าไว้ที่เมนูวันหยุด) ออกให้อัตโนมัติ —
                แต่ละช่องในไฟล์มีลิสต์ให้เลือก ({{ implode(' / ', \App\Models\Academic\ClassAttendance::STATUSES) }}) เว้นว่างไว้ได้ถ้าวันนั้นไม่เช็คชื่อ
            </p>
        </div>
        <div class="att-xl-modal-footer" style="flex-wrap:wrap">
            <button type="button" class="att-btn-cancel" onclick="closeExportModal()">ยกเลิก</button>
            <button type="button" class="att-btn-go" style="background:#6c757d" onclick="downloadExport(true)"><i class="bi bi-calendar3-range"></i> ทุกเดือนในเทอม</button>
            <button type="button" class="att-btn-go" onclick="downloadExport(false)"><i class="bi bi-download"></i> ดาวน์โหลดเดือนนี้</button>
        </div>
    </div>
</div>

{{-- นำเข้าไฟล์ Excel ที่กรอกแล้ว --}}
<div class="att-xl-overlay" id="attImportOverlay" onclick="if(event.target===this)closeImportModal()">
    <div class="att-xl-modal">
        <div class="att-xl-modal-header"><i class="bi bi-upload"></i> นำเข้าไฟล์เช็คชื่อ (Excel)</div>
        <form method="POST" action="{{ route('attendance.importExcel') }}" enctype="multipart/form-data" onsubmit="submitImportForm()">
            @csrf
            <div class="att-xl-modal-body">
                <p style="font-size:.8rem;color:#777;margin:0">
                    ใช้ไฟล์ที่ดาวน์โหลดจากปุ่ม "Excel" ของแต่ละวิชาแล้วกรอกข้อมูล — ไฟล์นี้มีชีตแยกให้คนละวิชา-ห้อง อัปโหลดไฟล์เดียว
                    ก็นำเข้าได้หลายวิชาพร้อมกัน (ถ้าเอาหลายชีตมารวมไว้ในไฟล์เดียวกันเอง)
                </p>
                <div><input type="file" name="file" accept=".xlsx" required></div>
                <div>
                    <label style="display:flex;align-items:center;gap:6px;font-weight:400;font-size:.85rem;color:#444">
                        <input type="checkbox" name="dry_run" value="1" style="width:auto">
                        ทดสอบก่อน (dry-run) — ยังไม่บันทึกข้อมูลจริง
                    </label>
                </div>
            </div>
            <div class="att-xl-modal-footer">
                <button type="button" class="att-btn-cancel" onclick="closeImportModal()">ยกเลิก</button>
                <button type="submit" id="attImportSubmitBtn" class="att-btn-go" style="background:#198754">
                    <i class="bi bi-upload"></i> เริ่มนำเข้า
                </button>
            </div>
        </form>
    </div>
</div>

@if (session('attendance_import_output'))
<div class="att-xl-overlay active" id="attImportResultOverlay">
    <div class="att-xl-modal" style="width:640px;max-width:95vw">
        <div class="att-xl-modal-header"><i class="bi bi-clipboard-check"></i> ผลการนำเข้าเช็คชื่อ</div>
        <div class="att-xl-modal-body">
            <pre style="background:#f5f5f5;padding:12px;border-radius:8px;overflow:auto;max-height:60vh;font-size:.8rem;white-space:pre-wrap">{{ session('attendance_import_output') }}</pre>
        </div>
        <div class="att-xl-modal-footer">
            <button type="button" class="att-btn-go" onclick="document.getElementById('attImportResultOverlay').remove()">ปิด</button>
        </div>
    </div>
</div>
@endif

<script>
var attExportAssignId = null;
function openExportModal(assignId, label) {
    attExportAssignId = assignId;
    document.getElementById('attExportLabel').innerText = label;
    document.getElementById('attExportMonth').value = new Date().toISOString().slice(0, 7);
    document.getElementById('attExportOverlay').classList.add('active');
}
function downloadExport(all) {
    var url = "{{ url('/attendance/export-template') }}/" + attExportAssignId;
    if (all) {
        url += '?all=1';
    } else {
        var month = document.getElementById('attExportMonth').value;
        if (!month) { alert('กรุณาเลือกเดือน'); return; }
        url += '?month=' + month;
    }
    window.open(url, '_blank');
    closeExportModal();
}
function closeExportModal() { document.getElementById('attExportOverlay').classList.remove('active'); }
function closeImportModal() { document.getElementById('attImportOverlay').classList.remove('active'); }
function submitImportForm() {
    document.getElementById('attImportSubmitBtn').disabled = true;
    document.getElementById('attImportSubmitBtn').innerText = 'กำลังนำเข้า... กรุณารอสักครู่';
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.att-xl-overlay.active').forEach(el => el.classList.remove('active'));
});
</script>
@endsection
