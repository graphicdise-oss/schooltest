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
                        <th style="width:150px; text-align:right;">
                            <button class="btn-add" onclick="openAddModal()" {{ $academicYears->isEmpty() ? 'disabled' : '' }}>
                                <i class="fas fa-plus"></i> เพิ่มวันหยุด
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($holidays as $i => $h)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <a class="hol-title-link"
                                   onclick='openEditModal(@json($h->id), @json($h->title), @json(optional($h->start_date)->format("Y-m-d")), @json(optional($h->end_date)->format("Y-m-d")), @json($h->note), @json($h->year_id))'>
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
                                        onclick='openEditModal(@json($h->id), @json($h->title), @json(optional($h->start_date)->format("Y-m-d")), @json(optional($h->end_date)->format("Y-m-d")), @json($h->note), @json($h->year_id))'>
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

@push('scripts')
<script>
    // template URL สำหรับแก้ไข (id = 0) แล้วแทนที่ท้าย path ด้วย id จริง
    const EDIT_URL_TEMPLATE = "{{ route('holidays.update', 0) }}";

    function openAddModal() {
        document.getElementById('addModal').classList.add('active');
    }
    function openEditModal(id, title, start, end, note, yearId) {
        document.getElementById('editTitle').value = title ?? '';
        document.getElementById('editStart').value = start ?? '';
        document.getElementById('editEnd').value   = end ?? '';
        document.getElementById('editNote').value  = note ?? '';
        if (yearId != null) document.getElementById('editYear').value = yearId;
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
</script>
@endpush
@endsection
