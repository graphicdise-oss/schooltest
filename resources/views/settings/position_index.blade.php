@extends('layouts.sidebar')

@push('styles')
<style>
    .pos-page { padding: 24px 28px; min-height: 100%; }

    .pos-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 30px 20px 20px; position: relative;
        margin-top: 50px; margin-bottom: 28px;
    }
    .pos-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .pos-icon-search { background: #00bcd4; }
    .pos-icon-table  { background: #ff9800; }
    .pos-card-title {
        margin-left: 90px; font-size: 1.1rem; color: #555; margin-top: -10px;
        display: flex; justify-content: space-between; align-items: center;
    }

    /* Search */
    .pos-search-row { display: flex; align-items: center; gap: 16px; margin-top: 20px; flex-wrap: wrap; }
    .pos-search-row label { font-weight: 600; font-size: 0.88rem; color: #444; }
    .pos-search-input {
        height: 38px; border: 1px solid #ccc; border-radius: 4px;
        padding: 0 12px; font-size: 0.88rem; font-family: inherit; outline: none; width: 280px;
    }
    .pos-search-input:focus { border-color: #00bcd4; }
    .pos-search-center { display: flex; justify-content: center; margin-top: 18px; }
    .btn-search {
        background: #00bcd4; color: #fff; border: none; border-radius: 6px;
        padding: 9px 28px; font-size: 0.88rem; font-weight: 600;
        cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-search:hover { background: #00acc1; }

    /* Table */
    .btn-add {
        background: #4caf50; color: #fff; border: none; border-radius: 6px;
        padding: 10px 20px; font-size: 0.88rem; font-weight: 600;
        cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-add:hover { background: #43a047; }

    .pos-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 20px; }
    .pos-table thead th {
        padding: 13px 14px; font-weight: 600; color: #333;
        border-bottom: 1px solid #ddd; text-align: left;
    }
    .pos-table tbody tr { border-bottom: 1px solid #eee; }
    .pos-table tbody td { padding: 0; color: #555; vertical-align: middle; }
    .pos-table tbody td .cell-inner { padding: 13px 14px; }

    /* Status cell แบบ full background */
    .td-status { padding: 0 !important; }
    .status-cell {
        padding: 13px 14px; text-align: center; font-weight: 600; font-size: 0.85rem;
    }
    .status-active   { background: #69f0ae; color: #1b5e20; }
    .status-inactive { background: #f06292; color: #fff; }

    /* Toggle */
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

    .pos-btn {
        width: 30px; height: 30px; border-radius: 6px; border: none;
        cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.85rem; margin: 0 2px; text-decoration: none;
    }
    .pos-btn-edit   { background: #fff3e0; color: #e65100; }
    .pos-btn-edit:hover   { background: #ffe0b2; }
    .pos-btn-delete { background: transparent; color: #e53935; font-size: 1rem; }
    .pos-btn-delete:hover { color: #b71c1c; }

    /* Modal */
    .pos-overlay {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.45); z-index: 9999;
        justify-content: center; align-items: center;
    }
    .pos-overlay.active { display: flex; }
    .pos-modal {
        background: #fff; border-radius: 14px; width: 440px; max-width: 94vw;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden;
        animation: posSlide 0.25s ease;
    }
    @keyframes posSlide { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    .pos-modal-header { background: linear-gradient(135deg,#ff9800,#ffa726); color:#fff; padding:18px 24px; font-size:1rem; font-weight:700; }
    .pos-modal-body { padding: 24px; }
    .pos-modal-body label { font-size: 0.82rem; font-weight: 600; color: #555; margin-bottom: 4px; display: block; }
    .pos-modal-body input, .pos-modal-body select {
        width: 100%; height: 40px; border: 1.5px solid #e0e0e0; border-radius: 10px;
        padding: 0 14px; font-size: 0.85rem; font-family: inherit; outline: none;
        box-sizing: border-box; margin-bottom: 14px;
    }
    .pos-modal-body input:focus, .pos-modal-body select:focus { border-color: #ff9800; box-shadow: 0 0 0 3px rgba(255,152,0,0.1); }
    .pos-modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:16px 24px; border-top:1px solid #f0f0f0; }
    .btn-modal-cancel { background:#f5f5f5; color:#666; border:none; border-radius:8px; padding:9px 22px; font-size:0.85rem; font-weight:600; cursor:pointer; font-family:inherit; }
    .btn-modal-save { background:#ff9800; color:#fff; border:none; border-radius:8px; padding:9px 22px; font-size:0.85rem; font-weight:600; cursor:pointer; font-family:inherit; }
    .btn-modal-save:hover { background:#e68900; }
</style>
@endpush

@section('content')
<div class="pos-page">

    @if(session('success'))
    <div style="background:#d4edda;color:#155724;padding:12px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem;">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
    </div>
    @endif

    {{-- ค้นหา --}}
    <div class="pos-card">
        <div class="pos-icon pos-icon-search"><i class="bi bi-search"></i></div>
        <div class="pos-card-title"><span>ค้นหา</span></div>
        <form method="GET" action="{{ route('positions.index') }}">
            <div class="pos-search-row">
                <label>ค้นหาตำแหน่ง :</label>
                <input type="text" name="search" class="pos-search-input" value="{{ request('search') }}">
            </div>
            <div class="pos-search-center">
                <button type="submit" class="btn-search"><i class="bi bi-search"></i> ค้นหา</button>
            </div>
        </form>
    </div>

    {{-- ตาราง --}}
    <div class="pos-card">
        <div class="pos-icon pos-icon-table"><i class="bi bi-gear-fill"></i></div>
        <div class="pos-card-title">
            <span>ตั้งค่าตำแหน่ง</span>
            <button type="button" class="btn-add" onclick="document.getElementById('addOverlay').classList.add('active')">
                <i class="bi bi-plus-lg"></i> เพิ่มตำแหน่ง
            </button>
        </div>

        <table class="pos-table">
            <thead>
                <tr>
                    <th style="width:60px">ลำดับ</th>
                    <th>ตำแหน่ง</th>
                    <th>ประเภทพนักงาน</th>
                    <th style="width:200px;text-align:center">สถานะการใช้งาน</th>
                    <th style="width:110px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($positions as $i => $p)
                <tr>
                    <td><div class="cell-inner">{{ $positions->firstItem() + $i }}</div></td>
                    <td><div class="cell-inner">{{ $p->name }}</div></td>
                    <td><div class="cell-inner">{{ $p->employee_type }}</div></td>
                    <td class="td-status">
                        <div class="status-cell {{ $p->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $p->is_active ? 'ใช้งาน' : 'ไม่ใช้งาน' }}
                        </div>
                    </td>
                    <td>
                        <div class="cell-inner" style="display:flex;align-items:center;gap:6px">
                            {{-- Toggle --}}
                            <form action="{{ route('positions.toggle', $p->position_id) }}" method="POST" style="display:inline">
                                @csrf @method('PUT')
                                <label class="toggle-switch">
                                    <input type="checkbox" {{ $p->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="toggle-slider"></span>
                                </label>
                            </form>

                            {{-- แก้ไข --}}
                            <button type="button" class="pos-btn pos-btn-edit"
                                onclick="openEdit({{ $p->position_id }}, '{{ addslashes($p->name) }}', '{{ $p->employee_type }}')"
                                title="แก้ไข">
                                <i class="bi bi-pencil-fill"></i>
                            </button>

                            {{-- ลบ --}}
                            <form action="{{ route('positions.destroy', $p->position_id) }}" method="POST" style="display:inline"
                                onsubmit="return confirm('ลบตำแหน่งนี้?')">
                                @csrf @method('DELETE')
                                <button class="pos-btn pos-btn-delete" title="ลบ">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:30px;color:#aaa">ไม่พบข้อมูล</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:14px">{{ $positions->links() }}</div>
    </div>

</div>

{{-- Modal เพิ่ม --}}
<div class="pos-overlay" id="addOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="pos-modal" onclick="event.stopPropagation()">
        <div class="pos-modal-header"><i class="bi bi-plus-circle me-2"></i> เพิ่มตำแหน่ง</div>
        <form method="POST" action="{{ route('positions.store') }}">
            @csrf
            <div class="pos-modal-body">
                <label>ตำแหน่ง <span style="color:red">*</span></label>
                <input type="text" name="name" required placeholder="ชื่อตำแหน่ง">

                <label>ประเภทพนักงาน <span style="color:red">*</span></label>
                <select name="employee_type" required>
                    <option value="">-- เลือก --</option>
                    <option value="อาจารย์">อาจารย์</option>
                    <option value="พนักงาน">พนักงาน</option>
                    <option value="ลูกจ้าง">ลูกจ้าง</option>
                </select>
            </div>
            <div class="pos-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('addOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal แก้ไข --}}
<div class="pos-overlay" id="editOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="pos-modal" onclick="event.stopPropagation()">
        <div class="pos-modal-header"><i class="bi bi-pencil-square me-2"></i> แก้ไขตำแหน่ง</div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="pos-modal-body">
                <label>ตำแหน่ง <span style="color:red">*</span></label>
                <input type="text" name="name" id="eName" required>

                <label>ประเภทพนักงาน <span style="color:red">*</span></label>
                <select name="employee_type" id="eType" required>
                    <option value="อาจารย์">อาจารย์</option>
                    <option value="พนักงาน">พนักงาน</option>
                    <option value="ลูกจ้าง">ลูกจ้าง</option>
                </select>
            </div>
            <div class="pos-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('editOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, name, type) {
    document.getElementById('editForm').action = '{{ url("positions") }}/' + id;
    document.getElementById('eName').value  = name;
    document.getElementById('eType').value  = type;
    document.getElementById('editOverlay').classList.add('active');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.pos-overlay.active').forEach(el => el.classList.remove('active'));
});
</script>
@endsection
