@extends('layouts.sidebar')

@push('styles')
<style>
    body { background: #f4f6f9; }
    .page { padding: 24px 28px; }
    .breadcrumb-custom a { color: #00bcd4; text-decoration: none; font-size: 0.95rem; }
    .breadcrumb-custom a:hover { text-decoration: underline; }
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
        font-size: 30px; color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .card-header-text {
        margin-left: 90px; font-size: 1.1rem; color: #555;
        margin-top: -10px; font-weight: 600;
    }

    .input-line {
        border: none; border-bottom: 1px solid #ccc; border-radius: 0;
        padding: 6px 0; font-size: 0.9rem; width: 100%;
        outline: none; background: transparent;
    }
    .input-line:focus { border-bottom-color: #00bcd4; border-bottom-width: 2px; }

    .btn-search-teal {
        background: #00bcd4; color: #fff; border: none; border-radius: 4px;
        padding: 9px 28px; font-size: 0.9rem; font-weight: 600;
        cursor: pointer; font-family: inherit;
    }
    .btn-search-teal:hover { background: #00a5bb; }

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
    .dept-name-link { color: #00bcd4; font-weight: 500; cursor: pointer; text-decoration: none; }
    .dept-name-link:hover { text-decoration: underline; color: #0097a7; }

    .btn-icon-edit {
        background: none; border: 1.5px solid #f59e0b; color: #f59e0b;
        border-radius: 4px; width: 32px; height: 32px;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 0.85rem;
    }
    .btn-icon-edit:hover { background: #fff8e1; }
    .btn-icon-del {
        background: none; border: none; color: #e53935;
        font-size: 1.1rem; font-weight: 700; cursor: pointer; padding: 0 6px;
    }
    .btn-icon-del:hover { color: #b71c1c; }

    /* Modal */
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.4); z-index: 9999;
        align-items: center; justify-content: center;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
        background: #fff; border-radius: 12px; width: 440px;
        max-width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden;
    }
    .modal-header {
        background: #4caf50; color: #fff; padding: 16px 20px;
        font-size: 1rem; font-weight: 700; display: flex; justify-content: space-between; align-items: center;
    }
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
    .btn-modal-save {
        background: #4caf50; color: #fff; border: none; border-radius: 6px;
        padding: 9px 24px; font-size: 0.9rem; font-weight: 700; cursor: pointer; font-family: inherit;
    }
    .btn-modal-save:hover { background: #43a047; }
    .btn-modal-save.edit-mode { background: #f59e0b; }
    .btn-modal-save.edit-mode:hover { background: #e69900; }
    .btn-modal-cancel {
        background: #fff; color: #666; border: 1.5px solid #d0d7de; border-radius: 6px;
        padding: 9px 20px; font-size: 0.9rem; font-weight: 600; cursor: pointer; font-family: inherit;
    }
    .btn-modal-cancel:hover { background: #f5f5f5; }
    .btn-close-x { background: none; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; }
</style>
@endpush

@section('content')
<div class="page">

    <nav class="breadcrumb-custom mb-3">
        <a href="#">ตั้งค่า</a>
        <i class="bi bi-chevron-right"></i>
        <span style="color:#555;">ตั้งค่าแผนก</span>
    </nav>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ค้นหา --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#00bcd4;"><i class="fas fa-search"></i></div>
        <div class="card-header-text">ค้นหา</div>
        <form method="GET" action="{{ route('departments.index') }}" style="margin-top:20px;">
            <div style="max-width:400px; margin-bottom:16px;">
                <label style="font-size:0.85rem;color:#555;font-weight:600;">ค้นหาแผนก</label>
                <input type="text" name="search" class="input-line" value="{{ $search }}" placeholder="ชื่อแผนก...">
            </div>
            <div style="text-align:center; border-top:1px solid #f0f0f0; padding-top:14px;">
                <button type="submit" class="btn-search-teal">ค้นหา</button>
            </div>
        </form>
    </div>

    {{-- ตาราง --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#f59e0b;"><i class="fas fa-cog"></i></div>
        <div class="card-header-text">ตั้งค่าแผนก</div>

        <div style="margin-top:20px; overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:60px;">ลำดับ</th>
                        <th>ชื่อแผนก</th>
                        <th>หัวหน้าแผนก</th>
                        <th style="width:130px; text-align:right;">
                            <button class="btn-add" onclick="openAddModal()">
                                <i class="fas fa-plus"></i> เพิ่มแผนก
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $i => $dept)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <a class="dept-name-link"
                                   onclick="openEditModal({{ $dept->id }}, '{{ addslashes($dept->name) }}', '{{ $dept->head_id ?? '' }}')">
                                    {{ $dept->name }}
                                </a>
                            </td>
                            <td>{{ $dept->head ? $dept->head->thai_prefix.$dept->head->thai_firstname.' '.$dept->head->thai_lastname : '-' }}</td>
                            <td style="text-align:right;">
                                <button class="btn-icon-edit"
                                        onclick="openEditModal({{ $dept->id }}, '{{ addslashes($dept->name) }}', '{{ $dept->head_id ?? '' }}')"
                                        title="แก้ไข">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form action="{{ route('departments.destroy', $dept->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('ยืนยันการลบแผนก {{ addslashes($dept->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-del" title="ลบ">✕</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fs-2 d-block mb-2"></i>
                                ยังไม่มีข้อมูลแผนก
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
            <span><i class="fas fa-plus me-2"></i>เพิ่มแผนกใหม่</span>
            <button class="btn-close-x" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST" action="{{ route('departments.store') }}">
            @csrf
            <div class="modal-body">
                <div class="modal-label">ชื่อแผนก <span style="color:red">*</span></div>
                <input type="text" name="name" class="modal-input" placeholder="เช่น ครูประถมศึกษา" required autofocus>

                <div class="modal-label">หัวหน้าแผนก</div>
                <select name="head_id" class="modal-select">
                    <option value="">— ไม่ระบุ —</option>
                    @foreach ($personnels as $p)
                        <option value="{{ $p->personnel_id }}">
                            {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                        </option>
                    @endforeach
                </select>
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
            <span><i class="fas fa-pen me-2"></i>แก้ไขแผนก</span>
            <button class="btn-close-x" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="modal-label">ชื่อแผนก <span style="color:red">*</span></div>
                <input type="text" name="name" id="editName" class="modal-input" required>

                <div class="modal-label">หัวหน้าแผนก</div>
                <select name="head_id" id="editHead" class="modal-select">
                    <option value="">— ไม่ระบุ —</option>
                    @foreach ($personnels as $p)
                        <option value="{{ $p->personnel_id }}">
                            {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                        </option>
                    @endforeach
                </select>
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
    function openAddModal() {
        document.getElementById('addModal').classList.add('active');
    }
    function openEditModal(id, name, headId) {
        document.getElementById('editName').value = name;
        document.getElementById('editHead').value = headId;
        document.getElementById('editForm').action = '/schooltest/public/departments/' + id;
        document.getElementById('editModal').classList.add('active');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }
    // ปิด modal เมื่อคลิกพื้นหลัง
    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });
    });
</script>
@endpush
@endsection