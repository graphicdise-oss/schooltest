@extends('layouts.sidebar')

@push('styles')
<style>
    .st-page { padding: 24px 28px; min-height: 100%; }

    /* Breadcrumb */
    .st-breadcrumb { font-size: 0.85rem; color: #999; margin-bottom: 20px; }
    .st-breadcrumb a { color: #4b7ce3; text-decoration: none; }
    .st-breadcrumb a:hover { text-decoration: underline; }

    /* Card */
    .st-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 30px 20px 20px; position: relative;
        margin-top: 50px; margin-bottom: 28px;
    }
    .st-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .st-icon-search { background: #00bcd4; }
    .st-icon-table  { background: #ff9800; }
    .st-icon-hist   { background: #4caf50; }

    .st-card-title {
        margin-left: 90px; font-size: 1.1rem; color: #555; margin-top: -10px;
        display: flex; justify-content: space-between; align-items: center;
    }

    /* Search card */
    .st-search-row { display: flex; align-items: center; gap: 16px; margin-top: 20px; flex-wrap: wrap; }
    .st-search-row label { font-weight: 600; font-size: 0.88rem; color: #444; min-width: 120px; text-align: right; }
    .st-search-input {
        height: 38px; border: 1px solid #ccc; border-radius: 4px;
        padding: 0 12px; font-size: 0.88rem; font-family: inherit; outline: none; width: 280px;
    }
    .st-search-input:focus { border-color: #00bcd4; }
    .btn-search {
        background: #00bcd4; color: #fff; border: none; border-radius: 6px;
        padding: 9px 28px; font-size: 0.88rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-search:hover { background: #00acc1; }
    .st-search-center { display: flex; justify-content: center; margin-top: 16px; }

    /* Table */
    .btn-add-type {
        background: #00bcd4; color: #fff; border: none; border-radius: 6px;
        padding: 10px 20px; font-size: 0.88rem; font-weight: 600;
        cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-add-type:hover { background: #00acc1; }

    .st-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 20px; }
    .st-table thead th {
        padding: 12px 10px; font-weight: 600; color: #333;
        border-bottom: 2px solid #eee; background: #fafafa;
        white-space: nowrap;
    }
    .st-table thead th .sort-icon { color: #bbb; font-size: 0.75rem; margin-left: 4px; }
    .st-table tbody tr { border-bottom: 1px solid #f5f5f5; transition: background 0.1s; }
    .st-table tbody tr:hover { background: #fef9f0; }
    .st-table tbody td { padding: 12px 10px; color: #555; vertical-align: middle; }
    .st-table .no-data { text-align: center; padding: 30px; color: #aaa; }

    .st-badge { display: inline-block; padding: 4px 14px; border-radius: 4px; font-size: 0.78rem; font-weight: 600; }
    .st-badge-active   { background: #a5ffc8; color: #065f46; }
    .st-badge-inactive { background: #ffb6c8; color: #991b1b; }

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

    .st-action-btn {
        width: 30px; height: 30px; border-radius: 6px; border: none;
        cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.85rem; transition: all 0.15s; margin: 0 2px;
    }
    .st-action-edit   { background: #e8f0fe; color: #1a73e8; }
    .st-action-edit:hover   { background: #d2e3fc; }
    .st-action-delete { background: #fce8e6; color: #d93025; }
    .st-action-delete:hover { background: #f8d7da; }

    /* Pagination */
    .st-pagination { display: flex; justify-content: flex-end; align-items: center; gap: 10px; margin-top: 14px; font-size: 0.82rem; color: #888; }
    .st-pagination a { color: #4b7ce3; text-decoration: none; }

    /* Modal */
    .st-overlay {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.45); z-index: 9999;
        justify-content: center; align-items: center;
    }
    .st-overlay.active { display: flex; }
    .st-modal {
        background: #fff; border-radius: 14px; width: 460px; max-width: 94vw;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden;
        animation: stSlideUp 0.25s ease;
    }
    @keyframes stSlideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    .st-modal-header { background: linear-gradient(135deg, #00bcd4, #26c6da); color: #fff; padding: 18px 24px; font-size: 1rem; font-weight: 700; }
    .st-modal-body { padding: 24px; }
    .st-modal-body label { font-size: 0.82rem; font-weight: 600; color: #555; margin-bottom: 4px; display: block; }
    .st-modal-body input {
        width: 100%; height: 40px; border: 1.5px solid #e0e0e0; border-radius: 10px;
        padding: 0 14px; font-size: 0.85rem; font-family: inherit; outline: none;
        box-sizing: border-box; margin-bottom: 14px;
    }
    .st-modal-body input:focus { border-color: #00bcd4; box-shadow: 0 0 0 3px rgba(0,188,212,0.1); }
    .st-modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid #f0f0f0; }
    .btn-modal-cancel { background: #f5f5f5; color: #666; border: none; border-radius: 8px; padding: 9px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit; }
    .btn-modal-save { background: #00bcd4; color: #fff; border: none; border-radius: 8px; padding: 9px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.2s; }
    .btn-modal-save:hover { background: #00acc1; }

    /* History card */
    .st-hist-refresh {
        background: #4caf50; color: #fff; border: none; border-radius: 6px;
        width: 38px; height: 38px; cursor: pointer; display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
    }
    .st-hist-refresh:hover { background: #43a047; }
</style>
@endpush

@section('content')
<div class="st-page">

    {{-- Breadcrumb --}}
    <div class="st-breadcrumb">
        ข้อมูลบุคคล &rsaquo; <a href="{{ route('students.index') }}">นักเรียน</a> &rsaquo;
        <span style="color:#00bcd4">ประเภทนักเรียน</span>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div style="background:#d4edda;color:#155724;padding:12px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem;">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
    </div>
    @endif

    {{-- ===== Card ค้นหา ===== --}}
    <div class="st-card">
        <div class="st-icon st-icon-search"><i class="bi bi-search"></i></div>
        <div class="st-card-title"><span>ค้นหา</span></div>

        <form method="GET" action="{{ route('student-types.index') }}">
            <div class="st-search-row">
                <label for="search">ประเภทนักเรียน</label>
                <input id="search" name="search" type="text" class="st-search-input"
                    placeholder="ประเภทนักเรียน" value="{{ request('search') }}">
            </div>
            <div class="st-search-center" style="margin-top:20px">
                <button type="submit" class="btn-search">
                    <i class="bi bi-search"></i> ค้นหา
                </button>
            </div>
        </form>
    </div>

    {{-- ===== Card ตาราง ===== --}}
    <div class="st-card">
        <div class="st-icon st-icon-table"><i class="bi bi-person-badge-fill"></i></div>
        <div class="st-card-title">
            <span>ตั้งค่าประเภทนักเรียน</span>
            <button type="button" class="btn-add-type" onclick="document.getElementById('addOverlay').classList.add('active')">
                <i class="bi bi-plus-lg"></i> เพิ่มประเภทนักเรียน
            </button>
        </div>

        <table class="st-table">
            <thead>
                <tr>
                    <th style="width:60px">ลำดับ <span class="sort-icon">&#8597;</span></th>
                    <th>ประเภทนักเรียน <span class="sort-icon">&#8597;</span></th>
                    <th>ประเภทนักเรียน(ภาษาอังกฤษ) <span class="sort-icon">&#8597;</span></th>
                    <th>ผู้ดูแล <span class="sort-icon">&#8597;</span></th>
                    <th>สถานะการใช้งาน <span class="sort-icon">&#8597;</span></th>
                    <th style="width:120px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($types as $i => $t)
                <tr>
                    <td>{{ $types->firstItem() + $i }}</td>
                    <td>{{ $t->name_th }}</td>
                    <td>{{ $t->name_en ?? '-' }}</td>
                    <td>{{ $t->caretaker ?? '-' }}</td>
                    <td>
                        <span class="st-badge {{ $t->is_active ? 'st-badge-active' : 'st-badge-inactive' }}">
                            {{ $t->is_active ? 'ใช้งาน' : 'ไม่ใช้งาน' }}
                        </span>
                    </td>
                    <td>
                        {{-- Toggle --}}
                        <form action="{{ route('student-types.toggle', $t->type_id) }}" method="POST" style="display:inline">
                            @csrf @method('PUT')
                            <label class="toggle-switch" title="{{ $t->is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}">
                                <input type="checkbox" {{ $t->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                <span class="toggle-slider"></span>
                            </label>
                        </form>

                        {{-- แก้ไข --}}
                        <button type="button" class="st-action-btn st-action-edit"
                            onclick="openEditModal({{ $t->type_id }}, '{{ addslashes($t->name_th) }}', '{{ addslashes($t->name_en) }}', '{{ addslashes($t->caretaker) }}')"
                            title="แก้ไข">
                            <i class="bi bi-pencil"></i>
                        </button>

                        {{-- ลบ --}}
                        <form action="{{ route('student-types.destroy', $t->type_id) }}" method="POST" style="display:inline"
                            onsubmit="return confirm('ลบประเภทนักเรียนนี้?')">
                            @csrf @method('DELETE')
                            <button class="st-action-btn st-action-delete" title="ลบ">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="no-data">No matching records found</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="st-pagination">
            <span>PREV</span>
            {{ $types->links('pagination::simple-default') }}
            <span>NEXT</span>
        </div>
    </div>

    {{-- ===== Card ประวัติการใช้งาน ===== --}}
    <div class="st-card" style="padding:20px;">
        <div class="st-icon st-icon-hist"><i class="bi bi-file-text-fill"></i></div>
        <div class="st-card-title">
            <span>ประวัติการใช้งาน</span>
            <button class="st-hist-refresh" title="รีเฟรช"><i class="bi bi-arrow-clockwise"></i></button>
        </div>
    </div>

</div>

{{-- ===== Modal เพิ่ม ===== --}}
<div class="st-overlay" id="addOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="st-modal" onclick="event.stopPropagation()">
        <div class="st-modal-header"><i class="bi bi-plus-circle me-2"></i> เพิ่มประเภทนักเรียน</div>
        <form method="POST" action="{{ route('student-types.store') }}">
            @csrf
            <div class="st-modal-body">
                <label>ประเภทนักเรียน <span style="color:red">*</span></label>
                <input type="text" name="name_th" required placeholder="ชื่อประเภทนักเรียน (ภาษาไทย)">

                <label>ประเภทนักเรียน (ภาษาอังกฤษ)</label>
                <input type="text" name="name_en" placeholder="Student Type (English)">

                <label>ผู้ดูแล</label>
                <input type="text" name="caretaker" placeholder="ชื่อผู้ดูแล">
            </div>
            <div class="st-modal-footer">
                <button type="button" class="btn-modal-cancel"
                    onclick="document.getElementById('addOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== Modal แก้ไข ===== --}}
<div class="st-overlay" id="editOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="st-modal" onclick="event.stopPropagation()">
        <div class="st-modal-header"><i class="bi bi-pencil-square me-2"></i> แก้ไขประเภทนักเรียน</div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="st-modal-body">
                <label>ประเภทนักเรียน <span style="color:red">*</span></label>
                <input type="text" name="name_th" id="editNameTh" required>

                <label>ประเภทนักเรียน (ภาษาอังกฤษ)</label>
                <input type="text" name="name_en" id="editNameEn">

                <label>ผู้ดูแล</label>
                <input type="text" name="caretaker" id="editCaretaker">
            </div>
            <div class="st-modal-footer">
                <button type="button" class="btn-modal-cancel"
                    onclick="document.getElementById('editOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, nameTh, nameEn, caretaker) {
    document.getElementById('editForm').action = '{{ url("student-types") }}/' + id;
    document.getElementById('editNameTh').value = nameTh;
    document.getElementById('editNameEn').value = nameEn;
    document.getElementById('editCaretaker').value = caretaker;
    document.getElementById('editOverlay').classList.add('active');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.st-overlay.active').forEach(el => el.classList.remove('active'));
});
</script>
@endsection
