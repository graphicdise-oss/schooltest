@extends('layouts.sidebar')

@push('styles')
<style>
    .pt-page { padding: 24px 28px; min-height: 100%; }
    .pt-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 30px 20px 20px; position: relative;
        margin-top: 50px; border: none; margin-bottom: 28px;
    }
    .pt-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        background: #ff9800;
    }
    .pt-header {
        margin-left: 90px; font-size: 1.15rem;
        color: #555; margin-top: -10px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .btn-add-type {
        background: #4caf50; color: #fff; border: none;
        border-radius: 6px; padding: 10px 24px;
        font-size: 0.9rem; font-weight: 600;
        cursor: pointer; font-family: inherit; transition: all 0.2s;
    }
    .btn-add-type:hover { background: #43a047; }

    .pt-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 20px; }
    .pt-table thead th {
        padding: 14px 12px; font-weight: 600; color: #333;
        border-bottom: 2px solid #eee; text-align: left;
    }
    .pt-table tbody tr { border-bottom: 1px solid #f5f5f5; transition: background 0.1s; }
    .pt-table tbody tr:hover { background: #fef9f0; }
    .pt-table tbody td { padding: 14px 12px; color: #555; vertical-align: middle; }

    .pt-badge { display: inline-block; padding: 4px 16px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }
    .pt-badge-active { background: #a5ffc8; color: #065f46; }
    .pt-badge-inactive { background: #ffb6c8; color: #991b1b; }

    .toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background: #ccc; border-radius: 24px; transition: 0.3s;
    }
    .toggle-slider::before {
        content: ''; position: absolute; height: 18px; width: 18px;
        left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: 0.3s;
    }
    .toggle-switch input:checked + .toggle-slider { background: #4caf50; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }

    .pt-action-btn {
        width: 30px; height: 30px; border-radius: 6px; border: none;
        cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.85rem; transition: all 0.15s; margin: 0 2px; text-decoration: none;
    }
    .pt-action-edit { background: #e8f0fe; color: #1a73e8; }
    .pt-action-edit:hover { background: #d2e3fc; }
    .pt-action-perm { background: #fef3c7; color: #92400e; }
    .pt-action-perm:hover { background: #fde68a; }
    .pt-action-delete { background: #fce8e6; color: #d93025; }
    .pt-action-delete:hover { background: #f8d7da; }

    /* Modal */
    .pt-overlay {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.45); z-index: 9999;
        justify-content: center; align-items: center;
    }
    .pt-overlay.active { display: flex; }
    .pt-modal {
        background: #fff; border-radius: 14px; width: 420px; max-width: 92vw;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden;
        animation: slideUp 0.25s ease;
    }
    @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    .pt-modal-header { background: linear-gradient(135deg,#ff9800,#ffa726); color:#fff; padding:18px 24px; font-size:1rem; font-weight:700; }
    .pt-modal-body { padding: 24px; }
    .pt-modal-body label { font-size:0.82rem; font-weight:600; color:#555; margin-bottom:4px; display:block; }
    .pt-modal-body input {
        width:100%; height:40px; border:1.5px solid #e0e0e0; border-radius:10px;
        padding:0 14px; font-size:0.85rem; font-family:inherit; outline:none;
        box-sizing:border-box; margin-bottom:14px;
    }
    .pt-modal-body input:focus { border-color:#ff9800; box-shadow:0 0 0 3px rgba(255,152,0,0.1); }
    .pt-modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:16px 24px; border-top:1px solid #f0f0f0; }
    .btn-modal-cancel { background:#f5f5f5; color:#666; border:none; border-radius:8px; padding:9px 22px; font-size:0.85rem; font-weight:600; cursor:pointer; font-family:inherit; }
    .btn-modal-save { background:#ff9800; color:#fff; border:none; border-radius:8px; padding:9px 22px; font-size:0.85rem; font-weight:600; cursor:pointer; font-family:inherit; transition:all 0.2s; }
    .btn-modal-save:hover { background:#e68900; }
</style>
@endpush

@section('content')
<div class="pt-page">
    <div class="pt-card">
        <div class="pt-icon"><i class="bi bi-gear-fill"></i></div>
        <div class="pt-header">
            <span>ประเภทบุคลากร</span>
            <button type="button" class="btn-add-type" onclick="document.getElementById('addOverlay').classList.add('active')">
                <i class="bi bi-plus-lg me-1"></i> เพิ่มประเภทบุคลากร
            </button>
        </div>

        <table class="pt-table">
            <thead>
                <tr>
                    <th style="width:60px">ลำดับ</th>
                    <th>ประเภทบุคลากร</th>
                    <th>สถานะการใช้งาน</th>
                    <th style="width:200px">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($types as $i => $t)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $t->name }}</td>
                    <td>
                        <span class="pt-badge {{ $t->is_active ? 'pt-badge-active' : 'pt-badge-inactive' }}">
                            {{ $t->is_active ? 'ใช้งาน' : 'ไม่ใช้งาน' }}
                        </span>
                    </td>
                    <td>
                        {{-- Toggle --}}
                        <form action="{{ route('personnel-types.toggle', $t->type_id) }}" method="POST" style="display:inline">
                            @csrf @method('PUT')
                            <label class="toggle-switch">
                                <input type="checkbox" {{ $t->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                <span class="toggle-slider"></span>
                            </label>
                        </form>

                        {{-- แก้ไข --}}
                        <button type="button" class="pt-action-btn pt-action-edit"
                            onclick="openEditModal({{ $t->type_id }}, '{{ $t->name }}')" title="แก้ไข">
                            <i class="bi bi-pencil"></i>
                        </button>

                        {{-- กำหนดสิทธิ์ --}}
                        <a href="{{ route('personnel-types.permissions', $t->type_id) }}"
                            class="pt-action-btn pt-action-perm" title="กำหนดสิทธิ์">
                            <i class="bi bi-shield-lock"></i>
                        </a>

                        {{-- ลบ --}}
                        <form action="{{ route('personnel-types.destroy', $t->type_id) }}" method="POST" style="display:inline"
                            onsubmit="return confirm('ลบประเภทนี้?')">
                            @csrf @method('DELETE')
                            <button class="pt-action-btn pt-action-delete" title="ลบ"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-4" style="color:#aaa">ไม่มีข้อมูล</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal เพิ่ม --}}
<div class="pt-overlay" id="addOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="pt-modal" onclick="event.stopPropagation()">
        <div class="pt-modal-header"><i class="bi bi-plus-circle me-2"></i> เพิ่มประเภทบุคลากร</div>
        <form method="POST" action="{{ route('personnel-types.store') }}">
            @csrf
            <div class="pt-modal-body">
                <label>ชื่อประเภทบุคลากร <span style="color:red">*</span></label>
                <input type="text" name="name" required placeholder="เช่น ครูประจำการ, เจ้าหน้าที่ธุรการ">
            </div>
            <div class="pt-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('addOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal แก้ไข --}}
<div class="pt-overlay" id="editOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="pt-modal" onclick="event.stopPropagation()">
        <div class="pt-modal-header"><i class="bi bi-pencil-square me-2"></i> แก้ไขประเภทบุคลากร</div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="pt-modal-body">
                <label>ชื่อประเภทบุคลากร <span style="color:red">*</span></label>
                <input type="text" name="name" id="editName" required>
            </div>
            <div class="pt-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('editOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name) {
    document.getElementById('editForm').action = '{{ url("personnel-types") }}/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editOverlay').classList.add('active');
}
document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('.pt-overlay.active').forEach(el=>el.classList.remove('active')); });
</script>
@endsection