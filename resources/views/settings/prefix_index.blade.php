@extends('layouts.sidebar')

@push('styles')
<style>
    .pf-page { padding: 24px 28px; min-height: 100%; }

    /* Card */
    .pf-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 30px 20px 20px; position: relative;
        margin-top: 50px; border: none; margin-bottom: 28px;
    }
    .pf-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        background: #ff9800;
    }
    .pf-header {
        margin-left: 90px; font-size: 1.15rem;
        color: #555; margin-top: -10px;
        display: flex; justify-content: space-between; align-items: center;
    }

    /* ปุ่มเพิ่ม */
    .btn-add-prefix {
        background: #e74c3c; color: #fff; border: none;
        border-radius: 6px; padding: 10px 24px;
        font-size: 0.9rem; font-weight: 600;
        cursor: pointer; font-family: inherit;
        transition: all 0.2s;
    }
    .btn-add-prefix:hover { background: #c0392b; }

    /* Filter tabs */
    .pf-filter {
        display: flex; gap: 8px; margin: 20px 0 16px;
    }
    .pf-filter a {
        padding: 6px 18px; border-radius: 20px;
        font-size: 0.82rem; font-weight: 600;
        text-decoration: none; transition: all 0.2s;
        border: 1.5px solid #e0e0e0; color: #666;
    }
    .pf-filter a:hover { border-color: #ff9800; color: #ff9800; }
    .pf-filter a.active {
        background: #ff9800; color: #fff; border-color: #ff9800;
    }

    /* Table */
    .pf-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
    .pf-table thead th {
        padding: 14px 12px; font-weight: 600; color: #333;
        border-bottom: 2px solid #eee; text-align: left;
    }
    .pf-table tbody tr { border-bottom: 1px solid #f5f5f5; transition: background 0.1s; }
    .pf-table tbody tr:hover { background: #fef9f0; }
    .pf-table tbody td { padding: 12px 12px; color: #555; vertical-align: middle; }

    /* Badge สถานะ */
    .pf-badge {
        display: inline-block; padding: 4px 16px;
        border-radius: 4px; font-size: 0.8rem; font-weight: 600;
    }
    .pf-badge-active { background: #a5ffc8; color: #065f46; }
    .pf-badge-inactive { background: #ffb6c8; color: #991b1b; }

    /* Badge role */
    .pf-role-badge {
        display: inline-block; padding: 2px 10px;
        border-radius: 10px; font-size: 0.72rem; font-weight: 600;
    }
    .pf-role-student { background: #dbeafe; color: #1d4ed8; }
    .pf-role-personnel { background: #fef3c7; color: #92400e; }
    .pf-role-all { background: #e0e7ff; color: #4338ca; }

    /* Toggle switch */
    .toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #ccc; border-radius: 24px;
        transition: 0.3s;
    }
    .toggle-slider::before {
        content: ''; position: absolute;
        height: 18px; width: 18px;
        left: 3px; bottom: 3px;
        background: #fff; border-radius: 50%;
        transition: 0.3s;
    }
    .toggle-switch input:checked + .toggle-slider { background: #4caf50; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }

    /* Action buttons */
    .pf-action-btn {
        width: 30px; height: 30px; border-radius: 6px; border: none;
        cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.85rem; transition: all 0.15s; margin: 0 2px;
    }
    .pf-action-edit { background: #e8f0fe; color: #1a73e8; }
    .pf-action-edit:hover { background: #d2e3fc; }
    .pf-action-delete { background: #fce8e6; color: #d93025; }
    .pf-action-delete:hover { background: #f8d7da; }

    /* ===== Modal ===== */
    .pf-overlay {
        display: none; position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.45); z-index: 9999;
        justify-content: center; align-items: center;
    }
    .pf-overlay.active { display: flex; }
    .pf-modal {
        background: #fff; border-radius: 14px;
        width: 460px; max-width: 92vw;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        overflow: hidden;
        animation: slideUp 0.25s ease;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .pf-modal-header {
        background: linear-gradient(135deg, #ff9800, #ffa726);
        color: #fff; padding: 18px 24px;
        font-size: 1rem; font-weight: 700;
    }
    .pf-modal-body { padding: 24px; }
    .pf-modal-body label { font-size: 0.82rem; font-weight: 600; color: #555; margin-bottom: 4px; display: block; }
    .pf-modal-body input, .pf-modal-body select {
        width: 100%; height: 40px; border: 1.5px solid #e0e0e0;
        border-radius: 10px; padding: 0 14px; font-size: 0.85rem;
        font-family: inherit; outline: none; box-sizing: border-box;
        margin-bottom: 14px;
    }
    .pf-modal-body input:focus, .pf-modal-body select:focus {
        border-color: #ff9800; box-shadow: 0 0 0 3px rgba(255,152,0,0.1);
    }
    .pf-modal-footer {
        display: flex; justify-content: flex-end; gap: 10px;
        padding: 16px 24px; border-top: 1px solid #f0f0f0;
    }
    .btn-modal-cancel {
        background: #f5f5f5; color: #666; border: none;
        border-radius: 8px; padding: 9px 22px; font-size: 0.85rem;
        font-weight: 600; cursor: pointer; font-family: inherit;
    }
    .btn-modal-save {
        background: #ff9800; color: #fff; border: none;
        border-radius: 8px; padding: 9px 22px; font-size: 0.85rem;
        font-weight: 600; cursor: pointer; font-family: inherit;
        transition: all 0.2s;
    }
    .btn-modal-save:hover { background: #e68900; }
</style>
@endpush

@section('content')
<div class="pf-page">

    <div class="pf-card">
        <div class="pf-icon"><i class="bi bi-list-ul"></i></div>
        <div class="pf-header">
            <span>รายการค้นหา</span>
            <button type="button" class="btn-add-prefix" onclick="openAddModal()">
                <i class="bi bi-plus-lg me-1"></i> เพิ่มคำนำหน้า
            </button>
        </div>

        {{-- Filter tabs --}}
        <div class="pf-filter">
            <a href="{{ route('prefixes.index') }}" class="{{ !request('role_filter') ? 'active' : '' }}">ทั้งหมด</a>
            <a href="{{ route('prefixes.index', ['role_filter' => 'student']) }}" class="{{ request('role_filter') == 'student' ? 'active' : '' }}">นักเรียน</a>
            <a href="{{ route('prefixes.index', ['role_filter' => 'personnel']) }}" class="{{ request('role_filter') == 'personnel' ? 'active' : '' }}">บุคลากร</a>
            <a href="{{ route('prefixes.index', ['role_filter' => 'all']) }}" class="{{ request('role_filter') == 'all' ? 'active' : '' }}">ใช้ทั้งคู่</a>
        </div>

        {{-- Table --}}
        <table class="pf-table">
            <thead>
                <tr>
                    <th style="width:60px">ลำดับ</th>
                    <th>คำนำหน้า(ภาษาไทย)</th>
                    <th>คำนำหน้า(ภาษาอังกฤษ)</th>
                    <th>ใช้สำหรับ</th>
                    <th>สถานะการใช้งาน</th>
                    <th style="width:160px">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prefixes as $i => $pf)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $pf->name_th }}</td>
                    <td>{{ $pf->name_en ?? '-' }}</td>
                    <td>
                        @if($pf->role == 'student')
                            <span class="pf-role-badge pf-role-student">นักเรียน</span>
                        @elseif($pf->role == 'personnel')
                            <span class="pf-role-badge pf-role-personnel">บุคลากร</span>
                        @else
                            <span class="pf-role-badge pf-role-all">ใช้ทั้งคู่</span>
                        @endif
                    </td>
                    <td>
                        <span class="pf-badge {{ $pf->is_active ? 'pf-badge-active' : 'pf-badge-inactive' }}">
                            {{ $pf->is_active ? 'เปิดใช้งาน' : 'ไม่ใช้งาน' }}
                        </span>
                    </td>
                    <td>
                        {{-- Toggle --}}
                        <form action="{{ route('prefixes.toggle', $pf->prefix_id) }}" method="POST" style="display:inline">
                            @csrf @method('PUT')
                            <label class="toggle-switch">
                                <input type="checkbox" {{ $pf->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                <span class="toggle-slider"></span>
                            </label>
                        </form>

                        {{-- แก้ไข --}}
                        <button type="button" class="pf-action-btn pf-action-edit"
                            onclick="openEditModal({{ $pf->prefix_id }}, '{{ $pf->name_th }}', '{{ $pf->name_en }}', '{{ $pf->role }}')" title="แก้ไข">
                            <i class="bi bi-pencil"></i>
                        </button>

                        {{-- ลบ --}}
                        <form action="{{ route('prefixes.destroy', $pf->prefix_id) }}" method="POST" style="display:inline"
                            onsubmit="return confirm('ลบคำนำหน้านี้?')">
                            @csrf @method('DELETE')
                            <button class="pf-action-btn pf-action-delete" title="ลบ"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4" style="color:#aaa">ไม่มีข้อมูล</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== Modal เพิ่ม ===== --}}
<div class="pf-overlay" id="addOverlay" onclick="closeModal('addOverlay', event)">
    <div class="pf-modal" onclick="event.stopPropagation()">
        <div class="pf-modal-header"><i class="bi bi-plus-circle me-2"></i> เพิ่มคำนำหน้า</div>
        <form method="POST" action="{{ route('prefixes.store') }}">
            @csrf
            <div class="pf-modal-body">
                <label>คำนำหน้า (ภาษาไทย) <span style="color:red">*</span></label>
                <input type="text" name="name_th" required placeholder="เช่น นาย, นาง, เด็กชาย">

                <label>คำนำหน้า (ภาษาอังกฤษ)</label>
                <input type="text" name="name_en" placeholder="เช่น Mr., Mrs., Master">

                <label>ใช้สำหรับ <span style="color:red">*</span></label>
                <select name="role" required>
                    <option value="">-- เลือก --</option>
                    <option value="student">นักเรียน</option>
                    <option value="personnel">บุคลากร</option>
                    <option value="all">ใช้ทั้งคู่</option>
                </select>
            </div>
            <div class="pf-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('addOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== Modal แก้ไข ===== --}}
<div class="pf-overlay" id="editOverlay" onclick="closeModal('editOverlay', event)">
    <div class="pf-modal" onclick="event.stopPropagation()">
        <div class="pf-modal-header"><i class="bi bi-pencil-square me-2"></i> แก้ไขคำนำหน้า</div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="pf-modal-body">
                <label>คำนำหน้า (ภาษาไทย) <span style="color:red">*</span></label>
                <input type="text" name="name_th" id="editNameTh" required>

                <label>คำนำหน้า (ภาษาอังกฤษ)</label>
                <input type="text" name="name_en" id="editNameEn">

                <label>ใช้สำหรับ <span style="color:red">*</span></label>
                <select name="role" id="editRole" required>
                    <option value="student">นักเรียน</option>
                    <option value="personnel">บุคลากร</option>
                    <option value="all">ใช้ทั้งคู่</option>
                </select>
            </div>
            <div class="pf-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('editOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addOverlay').classList.add('active');
    }

    function openEditModal(id, nameTh, nameEn, role) {
        document.getElementById('editForm').action = '{{ url("prefixes") }}/' + id;
        document.getElementById('editNameTh').value = nameTh;
        document.getElementById('editNameEn').value = nameEn;
        document.getElementById('editRole').value = role;
        document.getElementById('editOverlay').classList.add('active');
    }

    function closeModal(overlayId, e) {
        if (e && e.target !== e.currentTarget) return;
        document.getElementById(overlayId).classList.remove('active');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.pf-overlay.active').forEach(el => el.classList.remove('active'));
        }
    });
</script>



@endsection