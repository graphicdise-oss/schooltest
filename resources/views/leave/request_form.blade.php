@extends('layouts.sidebar')

@push('styles')
<style>
    .lf-page { padding: 24px 28px; min-height: 100%; }
    .lf-breadcrumb { display:flex; align-items:center; gap:6px; font-size:0.85rem; margin-bottom:20px; color:#555; }
    .lf-breadcrumb a { color:#5482e7; text-decoration:none; font-weight:500; }
    .lf-breadcrumb a:hover { text-decoration:underline; }
    .lf-breadcrumb span { color:#5482e7; font-weight:600; }
    .lf-breadcrumb i { font-size:0.7rem; color:#aaa; }

    /* กล่องเอกสาร */
    .lf-doc {
        background:#fff; border-radius:10px;
        box-shadow:0 2px 16px rgba(0,0,0,0.07);
        max-width:860px; margin:0 auto 32px;
        padding:36px 48px;
        font-size:0.95rem; color:#333;
    }

    /* หัวเรื่อง */
    .lf-doc-title {
        text-align:center; margin-bottom:6px;
        font-size:1.15rem; font-weight:700; color:#222;
    }
    .lf-doc-subtitle { text-align:center; font-size:0.9rem; color:#555; margin-bottom:4px; }
    .lf-doc-school  { text-align:center; font-size:0.88rem; color:#5482e7; font-weight:600; margin-bottom:24px; }

    /* ประเภทการลา (radio tabs) */
    .leave-type-tabs { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
    .leave-type-tab input[type=radio] { display:none; }
    .leave-type-tab label {
        display:inline-flex; align-items:center; gap:6px;
        border:1.5px solid #d0d7e5; border-radius:20px;
        padding:7px 18px; font-size:0.88rem; cursor:pointer;
        color:#555; transition:all 0.15s; user-select:none;
    }
    .leave-type-tab input[type=radio]:checked + label {
        border-color:#5482e7; background:#5482e7; color:#fff; font-weight:600;
    }
    .leave-type-tab label:hover { border-color:#5482e7; color:#5482e7; }

    /* ฟอร์มแถว */
    .lf-row { display:flex; align-items:baseline; gap:8px; margin-bottom:14px; flex-wrap:wrap; }
    .lf-label { font-size:0.88rem; font-weight:600; color:#333; white-space:nowrap; min-width:130px; }
    .lf-label-sm { font-size:0.88rem; font-weight:600; color:#333; white-space:nowrap; }
    .lf-input {
        border:none; border-bottom:1.5px solid #bbb; border-radius:0;
        padding:4px 6px; font-size:0.92rem; color:#333; font-family:inherit;
        outline:none; background:transparent; flex:1; min-width:80px;
        transition:border-color 0.2s;
    }
    .lf-input:focus { border-bottom-color:#5482e7; }
    .lf-select {
        border:none; border-bottom:1.5px solid #bbb; border-radius:0;
        padding:4px 6px; font-size:0.92rem; color:#333; font-family:inherit;
        outline:none; background:transparent; flex:1; min-width:120px;
        appearance:none; cursor:pointer;
    }
    .lf-select:focus { border-bottom-color:#5482e7; }
    .lf-textarea {
        border:1px solid #d0d7e5; border-radius:6px;
        padding:8px 10px; font-size:0.9rem; color:#333;
        font-family:inherit; outline:none; width:100%; resize:vertical;
        min-height:60px; transition:border-color 0.2s;
    }
    .lf-textarea:focus { border-color:#5482e7; }

    /* divider ส่วน */
    .lf-section { margin:24px 0 12px; padding-bottom:6px; border-bottom:2px solid #e8eef5; font-weight:700; color:#5482e7; font-size:0.92rem; }

    /* ช่วงเวลา */
    .period-radios { display:flex; gap:20px; }
    .period-radios label { display:flex; align-items:center; gap:6px; cursor:pointer; font-size:0.9rem; }

    /* สถิติ */
    .stats-table { width:100%; border-collapse:collapse; font-size:0.85rem; margin-top:8px; }
    .stats-table th { background:#5482e7; color:#fff; padding:8px 10px; text-align:center; }
    .stats-table td { border:1px solid #e0e4ea; padding:8px 10px; text-align:center; color:#555; }
    .stats-table th:first-child { border-top-left-radius:6px; }
    .stats-table th:last-child  { border-top-right-radius:6px; }

    /* ปุ่ม */
    .lf-actions { display:flex; justify-content:center; gap:14px; margin-top:30px; padding-top:20px; border-top:1px solid #f0f3f7; }
    .btn-submit {
        background:#5482e7; color:#fff; border:none; border-radius:8px;
        padding:11px 40px; font-size:0.95rem; font-weight:700; cursor:pointer;
        font-family:inherit; display:inline-flex; align-items:center; gap:8px; transition:background 0.2s;
    }
    .btn-submit:hover { background:#446bca; }
    .btn-cancel {
        background:#fff; color:#666; border:1.5px solid #d0d7de; border-radius:8px;
        padding:11px 32px; font-size:0.95rem; font-weight:600; cursor:pointer;
        font-family:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:8px;
    }
    .btn-cancel:hover { background:#f5f5f5; text-decoration:none; color:#333; }

    /* auto-filled */
    .lf-filled { color:#5482e7; font-weight:600; border-bottom-color:#5482e7; background:transparent; }

    @media (max-width:600px) {
        .lf-doc { padding:24px 16px; }
        .lf-label { min-width:100px; }
    }
</style>
@endpush

@section('content')
<div class="lf-page">

    <nav class="lf-breadcrumb">
        <a href="{{ route('leave.personnel.index') }}">ข้อมูลการลา</a>
        <i class="bi bi-chevron-right"></i>
        <span>ยื่นใบลาใหม่</span>
    </nav>

    <form method="POST" action="{{ route('leave.requests.store') }}" id="leaveForm">
    @csrf

    {{-- เพิ่มตรงนี้ --}}
@if ($errors->any())
    <div style="background:#fee2e2; border:1px solid #fca5a5; border-radius:8px; padding:14px 18px; margin-bottom:20px; color:#991b1b; font-size:0.88rem;">
        <strong><i class="fas fa-exclamation-triangle me-2"></i>กรุณาตรวจสอบข้อมูล</strong>
        <ul style="margin:8px 0 0 16px; padding:0;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <div class="lf-doc">

        {{-- หัวเอกสาร --}}
        <div class="lf-doc-title">แบบใบลาป่วย ลาคลอดบุตร ลากิจส่วนตัว</div>
        <div class="lf-doc-school">โรงเรียนสาธิตมหาวิทยาลัยราชภัฏวไลยอลงกรณ์ ในพระบรมราชูปถัมภ์</div>

        {{-- วันที่ + เรื่อง --}}
        <div class="lf-row">
            <span class="lf-label">วันที่</span>
            <input type="text" class="lf-input lf-filled" value="{{ \Carbon\Carbon::now()->addYears(543)->locale('th')->isoFormat('D MMMM YYYY') }}" readonly style="max-width:200px;">
        </div>
        <div class="lf-row">
            <span class="lf-label">เรื่อง</span>
            <span style="font-size:0.95rem;">ขออนุญาตลา</span>
        </div>

        {{-- ผู้ยื่นคำร้อง --}}
        <div class="lf-section">ข้อมูลผู้ยื่นคำร้อง</div>
        <div class="lf-row">
            <span class="lf-label">ผู้ยื่นคำร้อง</span>
            <select name="requester_id" id="requester_id" class="lf-select" required onchange="fillPosition(this)">
                <option value="">— เลือกบุคลากร —</option>
                @foreach ($personnels as $p)
                    <option value="{{ $p->personnel_id }}"
                        data-position="{{ $p->positionDetail->position_name ?? ($p->position ?? '') }}"
                        {{ (old('requester_id', $selectedPersonnelId) == $p->personnel_id) ? 'selected' : '' }}>
                        {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                    </option>
                @endforeach
               
            </select>
             @error('requester_id')
                    <div style="color:#dc2626; font-size:0.82rem; margin-top:4px;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
        </div>
        <div class="lf-row">
            <span class="lf-label">ตำแหน่ง</span>
            <input type="text" id="position_display" class="lf-input" placeholder="(เลือกบุคลากรเพื่อแสดงตำแหน่ง)" readonly style="color:#888;">
        </div>

        {{-- ประเภทการลา --}}
        <div class="lf-section">ประเภทการลา</div>
            <div class="leave-type-tabs">
             @foreach ($leaveTypes as $index => $lt)
                <div class="leave-type-tab">
                    <input type="radio" name="leave_type_key" id="lt_{{ $lt->leave_type_key }}"
                        value="{{ $lt->leave_type_key }}"
                        {{ old('leave_type_key') === $lt->leave_type_key ? 'checked' : '' }}>
                    <label for="lt_{{ $lt->leave_type_key }}">{{ $lt->leave_type_name }}</label>
                </div>
            @endforeach
            </div>
                @error('leave_type_key')
                    <div style="color:#dc2626; font-size:0.82rem; margin-top:4px;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
        

        {{-- เหตุผล --}}
        <div class="lf-row" style="align-items:flex-start;">
            <span class="lf-label" style="padding-top:6px;">เนื่องจาก</span>
            <textarea name="reason" class="lf-textarea" placeholder="ระบุเหตุผลการลา..." required>{{ old('reason') }}</textarea>
            @error('reason')
                <div style="color:#dc2626; font-size:0.82rem; margin-top:4px;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
            @enderror
        </div>

        {{-- วันที่ลา --}}
        <div class="lf-section">ระยะเวลาการลา</div>
        <div class="lf-row">
            <span class="lf-label">ตั้งแต่วันที่</span>
            <input type="date" name="start_date" id="start_date" class="lf-input" value="{{ old('start_date') }}" required onchange="calcDays()" style="max-width:180px;">
            <span class="lf-label-sm" style="margin-left:16px;">ถึงวันที่</span>
            <input type="date" name="end_date" id="end_date" class="lf-input" value="{{ old('end_date') }}" required onchange="calcDays()" style="max-width:180px;">
        </div>
        <div class="lf-row">
            <span class="lf-label">ช่วงเวลาการลา</span>
            <div class="period-radios">
                <label><input type="radio" name="leave_period" value="ทั้งวัน" checked onchange="calcDays()"> ทั้งวัน</label>
                <label><input type="radio" name="leave_period" value="ครึ่งวันเช้า" onchange="calcDays()"> ครึ่งวันเช้า</label>
                <label><input type="radio" name="leave_period" value="ครึ่งวันบ่าย" onchange="calcDays()"> ครึ่งวันบ่าย</label>
            </div>
        </div>
        <div class="lf-row">
            <span class="lf-label">รวมวันลา</span>
            <input type="number" name="num_days" id="num_days" class="lf-input lf-filled" step="0.5" min="0.5" value="{{ old('num_days', 1) }}" required style="max-width:80px;">
            <span style="font-size:0.9rem;">วัน</span>
        </div>

        {{-- สถานที่ติดต่อ --}}
        <div class="lf-section">สถานที่ติดต่อในช่วงการลา</div>
        <div class="lf-row">
            <span class="lf-label">บ้านเลขที่</span>
            <input type="text" name="contact_house" class="lf-input" value="{{ old('contact_house') }}" style="max-width:120px;">
            <span class="lf-label-sm" style="margin-left:16px;">ถนน</span>
            <input type="text" name="contact_road" class="lf-input" value="{{ old('contact_road') }}">
        </div>
        <div class="lf-row">
            <span class="lf-label">ตำบล</span>
            <input type="text" name="contact_subdistrict" class="lf-input" value="{{ old('contact_subdistrict') }}">
            <span class="lf-label-sm" style="margin-left:16px;">อำเภอ</span>
            <input type="text" name="contact_district" class="lf-input" value="{{ old('contact_district') }}">
        </div>
        <div class="lf-row">
            <span class="lf-label">จังหวัด</span>
            <input type="text" name="contact_province" class="lf-input" value="{{ old('contact_province') }}" style="max-width:160px;">
            <span class="lf-label-sm" style="margin-left:16px;">โทรศัพท์</span>
            <input type="text" name="contact_phone" class="lf-input" value="{{ old('contact_phone') }}" style="max-width:140px;">
        </div>

       
        

        {{-- ปุ่ม --}}
        <div class="lf-actions">
            <a href="{{ route('leave.personnel.index') }}" class="btn-cancel"><i class="fas fa-times"></i> ยกเลิก</a>
            <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> ส่งใบลา</button>
        </div>

    </div>
    </form>

</div>

@push('scripts')
<script>
    function fillPosition(sel) {
        const opt = sel.options[sel.selectedIndex];
        document.getElementById('position_display').value = opt.dataset.position || '';
    }

    function calcDays() {
        const start  = document.getElementById('start_date').value;
        const end    = document.getElementById('end_date').value;
        const period = document.querySelector('input[name=leave_period]:checked')?.value || 'ทั้งวัน';
        if (!start || !end) return;
        const s = new Date(start), e = new Date(end);
        if (e < s) { document.getElementById('num_days').value = 0; return; }
        let days = Math.round((e - s) / 86400000) + 1;
        if (period !== 'ทั้งวัน') days = days === 1 ? 0.5 : days - 0.5;
        document.getElementById('num_days').value = days;
    }

    window.addEventListener('DOMContentLoaded', () => {
        const sel = document.getElementById('requester_id');
        if (sel && sel.value) fillPosition(sel);
    });

    {{-- ⬇️ เพิ่มตรงนี้ --}}
    document.getElementById('leaveForm').addEventListener('submit', function(e) {
        const selected = document.querySelector('input[name=leave_type_key]:checked');
        if (!selected) {
            e.preventDefault();
            let err = document.getElementById('leave_type_error');
            if (!err) {
                err = document.createElement('div');
                err.id = 'leave_type_error';
                err.style = 'color:#dc2626; font-size:0.85rem; margin-top:6px;';
                err.innerHTML = '<i class="fas fa-exclamation-circle"></i> กรุณาเลือกประเภทการลา';
                document.querySelector('.leave-type-tabs').after(err);
            }
            document.querySelector('.leave-type-tabs').scrollIntoView({ behavior: 'smooth' });
        }
    });
</script>
@endpush
@endsection