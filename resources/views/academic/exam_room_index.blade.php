@extends('layouts.sidebar')

@push('styles')
<style>
    body { background: #f4f6f9; }
    .page { padding: 24px 28px; }

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
    .card-header-text {
        margin-left: 90px; font-size: 1.1rem; color: #555;
        margin-top: -10px; font-weight: 600;
    }

    .form-label-sm { font-size: 0.82rem; color: #666; font-weight: 600; margin-bottom: 4px; }
    .form-select-line {
        border: none; border-bottom: 1.5px solid #ccc; border-radius: 0;
        padding: 6px 4px; font-size: 0.88rem; width: 100%;
        background: transparent; outline: none; font-family: inherit;
    }
    .form-select-line:focus { border-bottom-color: #7c3aed; }
    .form-input-line {
        border: none; border-bottom: 1.5px solid #ccc; border-radius: 0;
        padding: 6px 4px; font-size: 0.88rem; width: 100%;
        background: transparent; outline: none; font-family: inherit;
    }
    .form-input-line:focus { border-bottom-color: #7c3aed; }

    .btn-search {
        background: #7c3aed; color: #fff; border: none; border-radius: 4px;
        padding: 9px 28px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-search:hover { background: #6d28d9; }
    .btn-add {
        background: #059669; color: #fff; border: none; border-radius: 4px;
        padding: 9px 20px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-add:hover { background: #047857; }

    .er-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
    .er-table th {
        padding: 10px 14px; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;
        font-weight: 600; color: #555; text-align: center; background: #fafafa;
    }
    .er-table th.left { text-align: left; }
    .er-table td {
        padding: 10px 14px; border-bottom: 1px solid #f0f2f5;
        color: #444; text-align: center; vertical-align: middle;
    }
    .er-table td.left { text-align: left; }

    .btn-edit   { background: #f59e0b; color: #fff; border: none; border-radius: 4px; padding: 5px 12px; font-size: 0.8rem; cursor: pointer; }
    .btn-delete { background: #ef4444; color: #fff; border: none; border-radius: 4px; padding: 5px 12px; font-size: 0.8rem; cursor: pointer; }
    .btn-edit:hover   { background: #d97706; }
    .btn-delete:hover { background: #dc2626; }

    .empty-box { text-align: center; color: #aaa; padding: 50px 0; }
    .empty-box i { font-size: 2.5rem; display: block; margin-bottom: 10px; }

    /* Modal */
    .modal-backdrop-custom {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.4); z-index: 1040;
        align-items: center; justify-content: center;
    }
    .modal-backdrop-custom.show { display: flex; }
    .modal-box {
        background: #fff; border-radius: 8px; width: 480px; max-width: 95vw;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15); padding: 28px;
    }
    .modal-title { font-size: 1rem; font-weight: 700; color: #333; margin-bottom: 20px; }
    .modal-field { margin-bottom: 16px; }
    .modal-label { font-size: 0.8rem; color: #777; font-weight: 600; margin-bottom: 4px; display: block; }
    .modal-input {
        width: 100%; border: 1px solid #ddd; border-radius: 4px;
        padding: 8px 10px; font-size: 0.88rem; outline: none; font-family: inherit;
    }
    .modal-input:focus { border-color: #7c3aed; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    .btn-save   { background: #7c3aed; color: #fff; border: none; border-radius: 4px; padding: 8px 24px; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
    .btn-cancel { background: #f3f4f6; color: #555; border: none; border-radius: 4px; padding: 8px 18px; font-size: 0.88rem; cursor: pointer; }
</style>
@endpush

@section('content')
<div class="page">

    @if (session('success'))
        <div style="background:#d1fae5; color:#065f46; padding:10px 16px; border-radius:6px; margin-bottom:16px; font-size:0.88rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ค้นหา --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#7c3aed;"><i class="fas fa-door-open"></i></div>
        <div class="card-header-text">ค้นหาห้องสอบ</div>

        <form method="GET" action="{{ route('exam-rooms.index') }}" style="margin-top:24px;">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="form-label-sm">แผนการเรียน</div>
                    <select name="curriculum_name" class="form-select-line" onchange="this.form.submit()">
                        <option value="">ทั้งหมด</option>
                        @foreach ($curriculumNames as $cn)
                            <option value="{{ $cn }}" {{ request('curriculum_name') == $cn ? 'selected' : '' }}>{{ $cn }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="form-label-sm">ชื่อห้องสอบ</div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="ค้นหาชื่อห้อง..." class="form-input-line">
                </div>
            </div>
            <div style="text-align:center; border-top:1px solid #f0f0f0; padding-top:14px; display:flex; justify-content:center; gap:12px;">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> ค้นหา</button>
                <a href="{{ route('exam-rooms.index') }}"
                    style="display:inline-flex; align-items:center; gap:6px; background:#fff; color:#666;
                           border:1.5px solid #d0d7de; border-radius:4px; padding:9px 20px;
                           font-size:0.9rem; font-weight:600; text-decoration:none;">
                    <i class="fas fa-redo"></i> ล้างค่า
                </a>
                <button type="button" class="btn-add" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> เพิ่มห้องสอบ
                </button>
            </div>
        </form>
    </div>

    {{-- ตาราง --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#7c3aed;"><i class="fas fa-list-alt"></i></div>
        <div class="card-header-text">รายการห้องสอบ</div>

        <div style="margin-top:20px;">
            @if ($rooms->isEmpty())
                <div class="empty-box">
                    <i class="fas fa-inbox" style="color:#ccc;"></i>
                    <div style="font-size:1rem; color:#555; margin-top:8px;">ไม่พบข้อมูลห้องสอบ</div>
                </div>
            @else
                <table class="er-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">ลำดับ</th>
                            <th class="left">แผนการเรียน</th>
                            <th class="left">ชื่อห้องสอบ</th>
                            <th style="width:160px;">จำนวนที่นั่งสอบ</th>
                            <th style="width:160px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rooms as $i => $room)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="left">{{ $room->curriculum_name ?: '-' }}</td>
                                <td class="left">{{ $room->room_name }}</td>
                                <td>{{ number_format($room->capacity) }}</td>
                                <td>
                                    <button class="btn-edit" onclick="openEditModal({{ $room->id }}, '{{ addslashes($room->curriculum_name) }}', '{{ addslashes($room->room_name) }}', {{ $room->capacity }})">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </button>
                                    <form method="POST" action="{{ route('exam-rooms.destroy', $room->id) }}" style="display:inline;"
                                        onsubmit="return confirm('ยืนยันลบห้องสอบ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-delete"><i class="fas fa-trash"></i> ลบ</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

{{-- Modal Add --}}
<div class="modal-backdrop-custom" id="modalAdd">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-plus-circle" style="color:#7c3aed;"></i> เพิ่มห้องสอบ</div>
        <form method="POST" action="{{ route('exam-rooms.store') }}">
            @csrf
            <div class="modal-field">
                <label class="modal-label">แผนการเรียน</label>
                <select name="curriculum_name" class="modal-input">
                    <option value="">- ไม่ระบุ -</option>
                    @foreach ($curriculumNames as $cn)
                        <option value="{{ $cn }}">{{ $cn }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-field">
                <label class="modal-label">ชื่อห้องสอบ <span style="color:red">*</span></label>
                <input type="text" name="room_name" class="modal-input" placeholder="เช่น ห้อง 101" required>
            </div>
            <div class="modal-field">
                <label class="modal-label">จำนวนที่นั่งสอบ <span style="color:red">*</span></label>
                <input type="number" name="capacity" class="modal-input" value="30" min="1" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalAdd')">ยกเลิก</button>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal-backdrop-custom" id="modalEdit">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b;"></i> แก้ไขห้องสอบ</div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-field">
                <label class="modal-label">แผนการเรียน</label>
                <select name="curriculum_name" id="edit_curriculum" class="modal-input">
                    <option value="">- ไม่ระบุ -</option>
                    @foreach ($curriculumNames as $cn)
                        <option value="{{ $cn }}">{{ $cn }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-field">
                <label class="modal-label">ชื่อห้องสอบ <span style="color:red">*</span></label>
                <input type="text" name="room_name" id="edit_room_name" class="modal-input" required>
            </div>
            <div class="modal-field">
                <label class="modal-label">จำนวนที่นั่งสอบ <span style="color:red">*</span></label>
                <input type="number" name="capacity" id="edit_capacity" class="modal-input" min="1" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalEdit')">ยกเลิก</button>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() { document.getElementById('modalAdd').classList.add('show'); }
function openEditModal(id, curriculum, roomName, capacity) {
    document.getElementById('editForm').action = '/exam-rooms/' + id;
    document.getElementById('edit_curriculum').value = curriculum;
    document.getElementById('edit_room_name').value = roomName;
    document.getElementById('edit_capacity').value = capacity;
    document.getElementById('modalEdit').classList.add('show');
}
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-backdrop-custom').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('show'); });
});
</script>
@endsection
