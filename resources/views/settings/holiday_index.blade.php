@extends('layouts.sidebar')

@push('styles')
<style>
    body { background: #f4f6f9; }
    .page { padding: 24px 28px; }
    .breadcrumb-custom a { color: #00bcd4; text-decoration: none; font-size: 0.95rem; }
    .breadcrumb-custom i { color: #888; margin: 0 8px; font-size: 0.8rem; }

    .floating-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 30px 20px 20px; position: relative;
        margin-top: 50px; margin-bottom: 28px;
    }
    .floating-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .card-header-text { margin-left: 90px; font-size: 1.1rem; color: #555; margin-top: -10px; font-weight: 600; }

    .btn-add {
        background: #4caf50; color: #fff; border: none; border-radius: 4px;
        padding: 8px 18px; font-size: 0.88rem; font-weight: 600;
        cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-add:hover { background: #43a047; }

    .table > thead > tr > th {
        border-bottom: 2px solid #eee; color: #333; font-weight: 600;
        white-space: nowrap; padding-bottom: 14px;
    }
    .table > tbody > tr > td {
        vertical-align: middle; color: #555;
        border-bottom: 1px solid #f5f5f5; padding: 13px 10px;
    }
    .hol-title-link { color: #00bcd4; font-weight: 500; cursor: pointer; text-decoration: none; }
    .hol-title-link:hover { text-decoration: underline; }

    .year-select {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 8px 12px;
        font-size: 0.9rem; color: #333; font-family: inherit; outline: none; min-width: 220px;
    }
    .day-badge {
        background: #eef4ff; color: #2563eb; border-radius: 20px;
        padding: 3px 12px; font-size: 0.82rem; font-weight: 600;
    }

    .btn-icon-edit {
        background: none; border: 1.5px solid #f59e0b; color: #f59e0b;
        border-radius: 4px; width: 32px; height: 32px;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 0.85rem;
    }
    .btn-icon-edit:hover { background: #fff8e1; }
    .btn-icon-del { background: none; border: none; color: #e53935; font-size: 1.1rem; font-weight: 700; cursor: pointer; padding: 0 6px; }
    .btn-icon-del:hover { color: #b71c1c; }

    /* Modal */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 9999; align-items: center; justify-content: center; }
    .modal-overlay.active { display: flex; }
    .modal-box { background: #fff; border-radius: 12px; width: 460px; max-width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden; }
    .modal-header { background: #4caf50; color: #fff; padding: 16px 20px; font-size: 1rem; font-weight: 700; display: flex; justify-content: space-between; align-items: center; }
    .modal-header.edit-mode { background: #f59e0b; }
    .modal-body { padding: 24px 20px; }
    .modal-label { font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: 6px; }
    .modal-input, .modal-select {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 9px 12px;
        font-size: 0.9rem; color: #333; width: 100%; font-family: inherit;
        outline: none; box-sizing: border-box; margin-bottom: 16px;
    }
    .modal-input:focus, .modal-select:focus { border-color: #4caf50; }
    .modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 0 20px 20px; }
    .btn-modal-save { background: #4caf50; color: #fff; border: none; border-radius: 6px; padding: 9px 24px; font-size: 0.9rem; font-weight: 700; cursor: pointer; font-family: inherit; }
    .btn-modal-save.edit-mode { background: #f59e0b; }
    .btn-modal-cancel { background: #fff; color: #666; border: 1.5px solid #d0d7de; border-radius: 6px; padding: 9px 20px; font-size: 0.9rem; font-weight: 600; cursor: pointer; font-family: inherit; }
    .btn-close-x { background: none; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; }

    /* ปฏิทิน (สร้างเอง ไม่พึ่งไลบรารีภายนอก กันปัญหาโหลดไม่ขึ้น/พังบนบางเครื่อง) */
    .cal-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin: 20px 0 14px; }
    .cal-title { font-size: 1.3rem; font-weight: 700; color: #333; min-width: 160px; }
    .cal-nav { display: flex; align-items: center; gap: 8px; }
    .cal-nav-btn {
        background: #8b5cf6; color: #fff; border: none; border-radius: 50%;
        width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 0.9rem;
    }
    .cal-nav-btn:hover { background: #7c3aed; }
    .cal-today-btn {
        background: #e5e7eb; color: #6b7280; border: none; border-radius: 999px;
        padding: 7px 18px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit;
    }
    .cal-today-btn:hover { background: #d1d5db; }

    .cal-grid { border: 1px solid #eef1f6; border-radius: 8px; overflow: hidden; }
    .cal-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); background: #f8f9fc; }
    .cal-weekdays > div { padding: 10px 8px; text-align: center; font-size: 0.82rem; font-weight: 700; color: #555; }
    .cal-days { display: grid; grid-template-columns: repeat(7, 1fr); }
    .cal-day {
        min-height: 92px; border-top: 1px solid #eef1f6; border-left: 1px solid #eef1f6;
        padding: 6px; display: flex; flex-direction: column; gap: 3px; overflow: hidden;
    }
    .cal-day:nth-child(7n+1) { border-left: none; }
    .cal-day-num { font-size: 0.82rem; color: #333; font-weight: 600; }
    .cal-day.other-month .cal-day-num { color: #ccc; }
    .cal-day.is-today { background: #f5f3ff; }
    .cal-day.is-today .cal-day-num {
        display: inline-flex; align-items: center; justify-content: center;
        width: 22px; height: 22px; border-radius: 50%; background: #8b5cf6; color: #fff;
    }
    .cal-pill {
        font-size: 0.72rem; padding: 2px 7px; border-radius: 5px; color: #fff;
        white-space: normal; word-break: break-word; cursor: pointer;
        line-height: 1.4;
    }
    .cal-pill:hover { filter: brightness(0.92); }

    /* Modal รายละเอียดวันหยุด (คลิกจากปฏิทิน) */
    .info-modal-box { background: #fff; border-radius: 12px; width: 380px; max-width: 92vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2); padding: 32px 28px 28px; text-align: center; }
    .info-modal-icon { width: 56px; height: 56px; border-radius: 50%; border: 2.5px solid #38bdf8; color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 18px; }
    .info-modal-title-chip { display: inline-block; background: #1d4ed8; color: #fff; font-weight: 700; padding: 6px 14px; border-radius: 4px; margin-bottom: 18px; }
    .info-modal-row { text-align: left; font-size: 0.92rem; color: #333; margin-bottom: 10px; }
    .info-modal-row strong { color: #111; }
    .btn-info-close { background: #2563eb; color: #fff; border: none; border-radius: 6px; padding: 9px 26px; font-size: 0.9rem; font-weight: 600; cursor: pointer; font-family: inherit; margin-top: 10px; }
</style>
@endpush

@section('content')
@php
    $thMonths = [1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',
                 7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.'];
    $thDate = function ($d) use ($thMonths) {
        if (!$d) return '';
        return $d->day . ' ' . $thMonths[$d->month] . ' ' . ($d->year + 543);
    };
@endphp

<div class="page">

    <nav class="breadcrumb-custom mb-3">
        <a href="#">ตั้งค่า</a>
        <i class="bi bi-chevron-right"></i>
        <span style="color:#555;">ตั้งค่าวันหยุด</span>
    </nav>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- เลือกปีการศึกษา --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#00bcd4;"><i class="fas fa-calendar-alt"></i></div>
        <div class="card-header-text">วันหยุดทั้งปีการศึกษา</div>
        <form method="GET" action="{{ route('holidays.index') }}" style="margin-top:20px; display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
            <label style="font-size:0.9rem;color:#555;font-weight:600;">ปีการศึกษา</label>
            <select name="year_id" class="year-select" onchange="this.form.submit()">
                @forelse ($academicYears as $y)
                    <option value="{{ $y->year_id }}" {{ (string)$yearId === (string)$y->year_id ? 'selected' : '' }}>
                        ปีการศึกษา {{ $y->year_name }}
                    </option>
                @empty
                    <option value="">— ยังไม่มีปีการศึกษา —</option>
                @endforelse
            </select>
            <span class="day-badge">รวมวันหยุด {{ number_format($totalDays) }} วัน</span>
        </form>
    </div>

    {{-- ปฏิทินวันหยุด --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#8b5cf6;"><i class="fas fa-calendar-days"></i></div>
        <div class="card-header-text">ปฏิทิน</div>

        <div class="cal-header">
            <div class="cal-title" id="calTitle"></div>
            <div class="cal-nav">
                <button type="button" class="cal-today-btn" onclick="calGoToday()">วันนี้</button>
                <button type="button" class="cal-nav-btn" onclick="calChangeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
                <button type="button" class="cal-nav-btn" onclick="calChangeMonth(1)"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
        <div class="cal-grid">
            <div class="cal-weekdays">
                <div>อา</div><div>จ</div><div>อ</div><div>พ</div><div>พฤ</div><div>ศ</div><div>ส</div>
            </div>
            <div class="cal-days" id="calDays"></div>
        </div>
    </div>

    {{-- ตารางวันหยุด --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#f59e0b;"><i class="fas fa-umbrella-beach"></i></div>
        <div class="card-header-text">รายการวันหยุด</div>

        <div style="margin-top:20px; overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:60px;">ลำดับ</th>
                        <th>ชื่อวันหยุด</th>
                        <th style="width:230px;">ช่วงวันที่</th>
                        <th style="width:90px; text-align:center;">จำนวนวัน</th>
                        <th>หมายเหตุ</th>
                        <th style="width:280px; text-align:right;">
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <button class="btn-add" style="background:#0891b2;" onclick="openImportModal()" {{ $academicYears->isEmpty() ? 'disabled' : '' }}>
                                    <i class="fas fa-cloud-download-alt"></i> นำเข้าจาก API
                                </button>
                                <button class="btn-add" onclick="openAddModal()" {{ $academicYears->isEmpty() ? 'disabled' : '' }}>
                                    <i class="fas fa-plus"></i> เพิ่มวันหยุด
                                </button>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($holidays as $i => $h)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <a class="hol-title-link"
                                   onclick='openEditModal(@json($h->id), @json($h->title), @json(optional($h->start_date)->format("Y-m-d")), @json(optional($h->end_date)->format("Y-m-d")), @json($h->note), @json($h->year_id), @json($h->type))'>
                                    {{ $h->title }}
                                </a>
                            </td>
                            <td>
                                {{ $thDate($h->start_date) }}
                                @if ($h->end_date && $h->end_date->ne($h->start_date))
                                    <span style="color:#999;">–</span> {{ $thDate($h->end_date) }}
                                @endif
                            </td>
                            <td style="text-align:center;">{{ $h->day_count }}</td>
                            <td>{{ $h->note ?: '-' }}</td>
                            <td style="text-align:right;">
                                <button class="btn-icon-edit" title="แก้ไข"
                                        onclick='openEditModal(@json($h->id), @json($h->title), @json(optional($h->start_date)->format("Y-m-d")), @json(optional($h->end_date)->format("Y-m-d")), @json($h->note), @json($h->year_id), @json($h->type))'>
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form action="{{ route('holidays.destroy', $h->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('ยืนยันการลบวันหยุด {{ addslashes($h->title) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-del" title="ลบ">✕</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fs-2 d-block mb-2"></i>
                                ยังไม่มีวันหยุดในปีการศึกษานี้
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Modal เพิ่ม --}}
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <span><i class="fas fa-plus me-2"></i>เพิ่มวันหยุด</span>
            <button class="btn-close-x" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST" action="{{ route('holidays.store') }}">
            @csrf
            <div class="modal-body">
                <div class="modal-label">ปีการศึกษา <span style="color:red">*</span></div>
                <select name="year_id" class="modal-select" required>
                    @foreach ($academicYears as $y)
                        <option value="{{ $y->year_id }}" {{ (string)$yearId === (string)$y->year_id ? 'selected' : '' }}>
                            ปีการศึกษา {{ $y->year_name }}
                        </option>
                    @endforeach
                </select>

                <div class="modal-label">ชื่อวันหยุด <span style="color:red">*</span></div>
                <input type="text" name="title" class="modal-input" placeholder="เช่น วันสงกรานต์" required>

                <div class="modal-label">ประเภทวันหยุด</div>
                <select name="type" class="modal-select">
                    @foreach (\App\Http\Controllers\Setting\HolidayController::TYPES as $t)
                        <option value="{{ $t }}" {{ $t === 'วันหยุดราชการ' ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>

                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <div class="modal-label">วันเริ่ม <span style="color:red">*</span></div>
                        <input type="date" name="start_date" class="modal-input" required>
                    </div>
                    <div style="flex:1;">
                        <div class="modal-label">วันสิ้นสุด</div>
                        <input type="date" name="end_date" class="modal-input">
                    </div>
                </div>

                <div class="modal-label">หมายเหตุ</div>
                <input type="text" name="note" class="modal-input" placeholder="(ไม่บังคับ)">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('addModal')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="fas fa-check me-1"></i>บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal แก้ไข --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header edit-mode">
            <span><i class="fas fa-pen me-2"></i>แก้ไขวันหยุด</span>
            <button class="btn-close-x" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST" id="editForm" action="{{ route('holidays.update', 0) }}">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="modal-label">ปีการศึกษา <span style="color:red">*</span></div>
                <select name="year_id" id="editYear" class="modal-select" required>
                    @foreach ($academicYears as $y)
                        <option value="{{ $y->year_id }}">ปีการศึกษา {{ $y->year_name }}</option>
                    @endforeach
                </select>

                <div class="modal-label">ชื่อวันหยุด <span style="color:red">*</span></div>
                <input type="text" name="title" id="editTitle" class="modal-input" required>

                <div class="modal-label">ประเภทวันหยุด</div>
                <select name="type" id="editType" class="modal-select">
                    @foreach (\App\Http\Controllers\Setting\HolidayController::TYPES as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>

                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <div class="modal-label">วันเริ่ม <span style="color:red">*</span></div>
                        <input type="date" name="start_date" id="editStart" class="modal-input" required>
                    </div>
                    <div style="flex:1;">
                        <div class="modal-label">วันสิ้นสุด</div>
                        <input type="date" name="end_date" id="editEnd" class="modal-input">
                    </div>
                </div>

                <div class="modal-label">หมายเหตุ</div>
                <input type="text" name="note" id="editNote" class="modal-input" placeholder="(ไม่บังคับ)">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('editModal')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save edit-mode"><i class="fas fa-check me-1"></i>บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal นำเข้าวันหยุดจาก API (Nager.Date) --}}
<div class="modal-overlay" id="importModal">
    <div class="modal-box" style="width:560px;">
        <div class="modal-header" style="background:#0891b2;">
            <span><i class="fas fa-cloud-download-alt me-2"></i>นำเข้าวันหยุดจาก API</span>
            <button class="btn-close-x" onclick="closeModal('importModal')">✕</button>
        </div>
        <div class="modal-body">
            <div style="background:#ecfeff;border:1px solid #a5f3fc;border-radius:8px;padding:10px 14px;font-size:0.82rem;color:#155e75;margin-bottom:16px;">
                <i class="fas fa-info-circle"></i>
                ดึงข้อมูลวันหยุดราชการไทยจาก Nager.Date (ฟรี ไม่ต้องขอ API Key) — ชื่ออาจเป็นภาษาอังกฤษ
                แก้ไขก่อนนำเข้าได้ วันหยุดที่จะถูกเพิ่มไปที่ <strong>ปีการศึกษาที่เลือกอยู่ตอนนี้</strong>
            </div>

            <div style="display:flex; gap:10px; align-items:flex-end; margin-bottom:16px;">
                <div style="flex:1;">
                    <div class="modal-label">ปี ค.ศ. ที่จะดึงข้อมูล</div>
                    <input type="number" id="importCeYear" class="modal-input" value="{{ now()->year }}" min="2000" max="2100">
                </div>
                <button type="button" class="btn-modal-save" style="background:#0891b2;" onclick="fetchImportPreview()">
                    <i class="fas fa-search me-1"></i>ดึงข้อมูล
                </button>
            </div>

            <div id="importStatus" style="font-size:0.85rem;color:#888;margin-bottom:10px;"></div>
            <div id="importList" style="max-height:320px;overflow-y:auto;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal-cancel" onclick="closeModal('importModal')">ยกเลิก</button>
            <button type="button" id="importSubmitBtn" class="btn-modal-save" style="background:#0891b2;display:none;" onclick="submitImport()">
                <i class="fas fa-check me-1"></i>นำเข้าที่เลือก
            </button>
        </div>
    </div>
</div>

{{-- Modal รายละเอียดวันหยุด (คลิกจากปฏิทิน) --}}
<div class="modal-overlay" id="infoModal">
    <div class="info-modal-box">
        <div class="info-modal-icon"><i class="fas fa-info"></i></div>
        <div><span class="info-modal-title-chip" id="infoTitle"></span></div>
        <div class="info-modal-row"><strong>เริ่มวันที่:</strong> <span id="infoStart"></span></div>
        <div class="info-modal-row"><strong>สิ้นสุดวันที่:</strong> <span id="infoEnd"></span></div>
        <div class="info-modal-row"><strong>ประเภทวันหยุด:</strong> <span id="infoType"></span></div>
        <div class="info-modal-row" id="infoNoteRow" style="display:none;"><strong>หมายเหตุ:</strong> <span id="infoNote"></span></div>
        <button type="button" class="btn-info-close" onclick="closeModal('infoModal')"><i class="fas fa-times me-1"></i>Close</button>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // template URL สำหรับแก้ไข (id = 0) แล้วแทนที่ท้าย path ด้วย id จริง
    const EDIT_URL_TEMPLATE = "{{ route('holidays.update', 0) }}";
    const CALENDAR_EVENTS = @json($calendarEvents);

    function openAddModal() {
        document.getElementById('addModal').classList.add('active');
    }
    function openEditModal(id, title, start, end, note, yearId, type) {
        document.getElementById('editTitle').value = title ?? '';
        document.getElementById('editStart').value = start ?? '';
        document.getElementById('editEnd').value   = end ?? '';
        document.getElementById('editNote').value  = note ?? '';
        if (yearId != null) document.getElementById('editYear').value = yearId;
        if (type) document.getElementById('editType').value = type;
        document.getElementById('editForm').action = EDIT_URL_TEMPLATE.replace(/\/0$/, '/' + id);
        document.getElementById('editModal').classList.add('active');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }
    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', function (e) {
            if (e.target === this) this.classList.remove('active');
        });
    });

    // ===== นำเข้าวันหยุดจาก API (Thailand Formats) =====
    const IMPORT_PREVIEW_URL = "{{ route('holidays.importPreview') }}";
    const IMPORT_APPLY_URL   = "{{ route('holidays.import') }}";
    const IMPORT_YEAR_ID     = {{ $yearId ?? 'null' }};
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

    function openImportModal() {
        document.getElementById('importList').innerHTML = '';
        document.getElementById('importStatus').textContent = '';
        document.getElementById('importSubmitBtn').style.display = 'none';
        document.getElementById('importModal').classList.add('active');
    }

    function fetchImportPreview() {
        const ceYear = document.getElementById('importCeYear').value;
        const statusEl = document.getElementById('importStatus');
        const listEl = document.getElementById('importList');
        statusEl.textContent = 'กำลังดึงข้อมูล...';
        listEl.innerHTML = '';
        document.getElementById('importSubmitBtn').style.display = 'none';

        fetch(IMPORT_PREVIEW_URL + '?ce_year=' + encodeURIComponent(ceYear), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    statusEl.textContent = data.error;
                    return;
                }
                if (!data.items || !data.items.length) {
                    statusEl.textContent = 'ไม่พบข้อมูลวันหยุดปีนี้';
                    return;
                }
                statusEl.textContent = `พบ ${data.items.length} รายการ — ติ๊กเลือกและแก้ไขชื่อได้ก่อนนำเข้า`;
                listEl.innerHTML = data.items.map((item, i) => `
                    <div style="display:flex;align-items:center;gap:8px;padding:6px 4px;border-bottom:1px solid #f0f0f0;">
                        <input type="checkbox" class="import-chk" data-i="${i}" ${item.exists ? '' : 'checked'} style="width:16px;height:16px;flex-shrink:0;">
                        <span style="width:120px;flex-shrink:0;color:#666;font-size:0.8rem;">${item.date}${item.end_date && item.end_date !== item.date ? ' – ' + item.end_date : ''}</span>
                        <input type="text" class="modal-input import-title" data-i="${i}" value="${item.title.replace(/"/g, '&quot;')}" style="margin:0;padding:5px 8px;font-size:0.85rem;">
                        ${item.exists ? '<span style="font-size:0.75rem;color:#f59e0b;flex-shrink:0;">มีอยู่แล้ว</span>' : ''}
                    </div>
                `).join('');
                window.IMPORT_ITEMS = data.items;
                document.getElementById('importSubmitBtn').style.display = 'inline-block';
            })
            .catch(() => { statusEl.textContent = 'เชื่อมต่อไม่สำเร็จ กรุณาลองใหม่อีกครั้ง'; });
    }

    function submitImport() {
        const checked = Array.from(document.querySelectorAll('.import-chk')).filter(c => c.checked);
        if (!checked.length) { alert('กรุณาเลือกอย่างน้อย 1 รายการ'); return; }

        // ส่งเป็นฟอร์มธรรมดา (ไม่ใช่ fetch) เพื่อให้ redirect + flash 'success' หลังบันทึกทำงานปกติ
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = IMPORT_APPLY_URL;

        const addField = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        };

        addField('_token', CSRF_TOKEN);
        addField('year_id', IMPORT_YEAR_ID);

        checked.forEach(c => {
            const i = c.dataset.i;
            const titleInput = document.querySelector(`.import-title[data-i="${i}"]`);
            addField(`items[${i}][date]`, window.IMPORT_ITEMS[i].date);
            addField(`items[${i}][end_date]`, window.IMPORT_ITEMS[i].end_date || window.IMPORT_ITEMS[i].date);
            addField(`items[${i}][title]`, titleInput.value);
        });

        document.body.appendChild(form);
        form.submit();
    }

    // ===== ปฏิทิน (สร้างเอง — ไม่พึ่งไลบรารีภายนอก) =====
    // เหตุการณ์หลายวันแสดงเป็น "ป้ายเดียว" บนวันเริ่มเท่านั้น พร้อมช่วงวันที่ต่อท้าย
    // (เช่น "กิจกรรมวิทย์ (4-22 ส.ค.)") ไม่แสดงซ้ำทุกวันและไม่ทำเป็นแถบยาวคาดข้ามสัปดาห์
    const CAL_MONTHS = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    const CAL_MONTHS_SHORT = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];

    function calParseDate(s) {
        const [y, m, d] = s.split('-').map(Number);
        return new Date(y, m - 1, d);
    }
    function calYmd(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function calRangeLabel(startDate, endDateInclusive) {
        if (calYmd(startDate) === calYmd(endDateInclusive)) return '';
        if (startDate.getMonth() === endDateInclusive.getMonth()) {
            return ` (${startDate.getDate()}-${endDateInclusive.getDate()} ${CAL_MONTHS_SHORT[startDate.getMonth()]})`;
        }
        return ` (${startDate.getDate()} ${CAL_MONTHS_SHORT[startDate.getMonth()]}-${endDateInclusive.getDate()} ${CAL_MONTHS_SHORT[endDateInclusive.getMonth()]})`;
    }

    // เตรียมข้อมูล event ครั้งเดียว: หา key วันเริ่ม (Y-m-d) + label ที่จะโชว์
    const CAL_EVENTS_BY_START = {};
    CALENDAR_EVENTS.forEach(ev => {
        const start = calParseDate(ev.start);
        const endExclusive = calParseDate(ev.end); // FullCalendar-style: exclusive
        const endInclusive = new Date(endExclusive.getTime() - 86400000);
        const key = calYmd(start);
        (CAL_EVENTS_BY_START[key] = CAL_EVENTS_BY_START[key] || []).push({
            ev, label: ev.title + calRangeLabel(start, endInclusive),
        });
    });

    let calCursor = new Date(); // เดือน/ปีที่กำลังดูอยู่
    const calFirstEventDate = CALENDAR_EVENTS.length ? calParseDate(CALENDAR_EVENTS.slice().sort((a, b) => a.start.localeCompare(b.start))[0].start) : null;
    if (calFirstEventDate) calCursor = new Date(calFirstEventDate.getFullYear(), calFirstEventDate.getMonth(), 1);

    function calRender() {
        const year = calCursor.getFullYear();
        const month = calCursor.getMonth();
        document.getElementById('calTitle').textContent = CAL_MONTHS[month] + ' ' + (year + 543);

        const firstOfMonth = new Date(year, month, 1);
        const startOffset = firstOfMonth.getDay(); // 0=Sun
        const gridStart = new Date(year, month, 1 - startOffset);
        const today = new Date();
        const todayKey = calYmd(today);

        const daysEl = document.getElementById('calDays');
        daysEl.innerHTML = '';
        for (let i = 0; i < 42; i++) {
            const cellDate = new Date(gridStart.getFullYear(), gridStart.getMonth(), gridStart.getDate() + i);
            const key = calYmd(cellDate);
            const cell = document.createElement('div');
            cell.className = 'cal-day' + (cellDate.getMonth() !== month ? ' other-month' : '') + (key === todayKey ? ' is-today' : '');

            const num = document.createElement('div');
            num.className = 'cal-day-num';
            num.textContent = cellDate.getDate();
            cell.appendChild(num);

            (CAL_EVENTS_BY_START[key] || []).forEach(item => {
                const pill = document.createElement('div');
                pill.className = 'cal-pill';
                pill.style.background = item.ev.color;
                pill.textContent = item.label;
                pill.title = item.label;
                pill.onclick = () => calShowInfo(item.ev);
                cell.appendChild(pill);
            });

            daysEl.appendChild(cell);
            // เต็ม 6 แถวพอดี (42 วัน) แต่ถ้าแถวสุดท้ายไม่มีวันของเดือนนี้เลย ตัดออกเพื่อไม่ให้ปฏิทินยาวเกินจำเป็น
            if (i === 34) {
                const remainingHasCurrentMonth = Array.from({length: 7}, (_, j) => {
                    const d = new Date(gridStart.getFullYear(), gridStart.getMonth(), gridStart.getDate() + 35 + j);
                    return d.getMonth() === month;
                }).some(Boolean);
                if (!remainingHasCurrentMonth) break;
            }
        }
    }

    function calShowInfo(ev) {
        const p = ev.extendedProps;
        document.getElementById('infoTitle').textContent = ev.title;
        document.getElementById('infoStart').textContent = p.start_th;
        document.getElementById('infoEnd').textContent   = p.end_th;
        document.getElementById('infoType').textContent  = p.type;
        const noteRow = document.getElementById('infoNoteRow');
        if (p.note) {
            document.getElementById('infoNote').textContent = p.note;
            noteRow.style.display = '';
        } else {
            noteRow.style.display = 'none';
        }
        document.getElementById('infoModal').classList.add('active');
    }

    function calChangeMonth(delta) {
        calCursor = new Date(calCursor.getFullYear(), calCursor.getMonth() + delta, 1);
        calRender();
    }
    function calGoToday() {
        const now = new Date();
        calCursor = new Date(now.getFullYear(), now.getMonth(), 1);
        calRender();
    }

    document.addEventListener('DOMContentLoaded', calRender);
</script>
@endpush
