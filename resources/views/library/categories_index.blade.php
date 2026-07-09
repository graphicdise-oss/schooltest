@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; max-width:820px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:22px; margin-bottom:20px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:8px; }
    table { width:100%; border-collapse:collapse; font-size:.9rem; }
    thead th { background:#f2f6ff; color:#082b75; font-weight:600; padding:11px 12px; text-align:left; white-space:nowrap; }
    tbody td { padding:10px 12px; border-bottom:1px solid #f0f2f7; color:#444; vertical-align:middle; }
    tbody tr:hover { background:#f7faff; }
    .badge2 { padding:3px 12px; border-radius:20px; font-size:.8rem; font-weight:600; }
    .b-on { background:#dcfce7; color:#16a34a; }
    .b-off { background:#fee2e2; color:#dc2626; }
    .btn-add { background:#4caf50; color:#fff; border:none; border-radius:6px; padding:8px 18px; font-size:.88rem; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
    .btn-add:hover { background:#43a047; }
    .btn-icon-edit { background:none; border:1.5px solid #f59e0b; color:#f59e0b; border-radius:4px; width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:.85rem; }
    .btn-icon-edit:hover { background:#fff8e1; }
    .btn-icon-del { background:none; border:none; color:#e53935; font-size:1.1rem; font-weight:700; cursor:pointer; padding:0 6px; }
    .btn-icon-del:hover { color:#b71c1c; }
    .empty { text-align:center; color:#94a3b8; padding:30px 0; }
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:9999; align-items:center; justify-content:center; }
    .modal-overlay.active { display:flex; }
    .modal-box { background:#fff; border-radius:12px; width:400px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden; }
    .modal-header { background:#4caf50; color:#fff; padding:16px 20px; font-size:1rem; font-weight:700; display:flex; justify-content:space-between; align-items:center; }
    .modal-header.edit-mode { background:#f59e0b; }
    .modal-body { padding:24px 20px; }
    .modal-input { border:1px solid #d0d7e5; border-radius:6px; padding:9px 12px; font-size:.9rem; width:100%; font-family:inherit; outline:none; box-sizing:border-box; }
    .modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:14px 20px 20px; }
    .btn-modal-save { background:#4caf50; color:#fff; border:none; border-radius:6px; padding:9px 24px; font-size:.9rem; font-weight:700; cursor:pointer; }
    .btn-modal-save.edit-mode { background:#f59e0b; }
    .btn-modal-cancel { background:#fff; color:#666; border:1.5px solid #d0d7de; border-radius:6px; padding:9px 20px; font-size:.9rem; font-weight:600; cursor:pointer; }
    .btn-close-x { background:none; border:none; color:#fff; font-size:1.2rem; cursor:pointer; }
</style>
@endpush

@section('content')
<div class="page">
    <nav class="breadcrumb-custom mb-3">
        <a href="#">ห้องสมุด</a><i class="bi bi-chevron-right"></i>
        <span style="color:#555;">ตั้งค่าหมวดหมู่หนังสือ</span>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card2">
        <div class="card-title">
            <span><i class="fas fa-tags"></i> หมวดหมู่หนังสือ</span>
            <button class="btn-add" onclick="openAddModal()"><i class="fas fa-plus"></i> เพิ่มหมวดหมู่</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:60px;">ลำดับ</th>
                    <th>ชื่อหมวดหมู่</th>
                    <th style="width:100px; text-align:center;">จำนวนหนังสือ</th>
                    <th style="width:110px; text-align:center;">สถานะ</th>
                    <th style="width:100px; text-align:right;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $i => $cat)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $cat->name }}</td>
                        <td style="text-align:center;">{{ $cat->books_count }}</td>
                        <td style="text-align:center;">
                            <form action="{{ route('library.categories.toggle', $cat->id) }}" method="POST" class="d-inline">
                                @csrf @method('PUT')
                                <button type="submit" class="badge2 {{ $cat->is_active ? 'b-on' : 'b-off' }}" style="border:none; cursor:pointer;">
                                    {{ $cat->is_active ? 'ใช้งาน' : 'ปิด' }}
                                </button>
                            </form>
                        </td>
                        <td style="text-align:right;">
                            <button class="btn-icon-edit" onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}')" title="แก้ไข">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form action="{{ route('library.categories.destroy', $cat->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('ยืนยันการลบหมวดหมู่ {{ addslashes($cat->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon-del" title="ลบ">✕</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty"><i class="fas fa-inbox"></i> ยังไม่มีหมวดหมู่หนังสือ</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal เพิ่ม --}}
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <span><i class="fas fa-plus me-2"></i>เพิ่มหมวดหมู่หนังสือ</span>
            <button class="btn-close-x" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST" action="{{ route('library.categories.store') }}">
            @csrf
            <div class="modal-body">
                <input type="text" name="name" class="modal-input" placeholder="เช่น นวนิยาย, สารคดี, วิชาการ" required autofocus>
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
            <span><i class="fas fa-pen me-2"></i>แก้ไขหมวดหมู่</span>
            <button class="btn-close-x" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-body">
                <input type="text" name="name" id="editName" class="modal-input" required>
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
    function openAddModal() { document.getElementById('addModal').classList.add('active'); }
    function openEditModal(id, name) {
        document.getElementById('editName').value = name;
        document.getElementById('editForm').action = '{{ url('library/categories') }}/' + id;
        document.getElementById('editModal').classList.add('active');
    }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }
    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', function (e) { if (e.target === this) this.classList.remove('active'); });
    });
</script>
@endpush
@endsection
