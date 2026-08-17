@extends('layouts.sidebar')

@push('styles')
<style>
    .lt-page { padding: 24px 28px; min-height: 100%; }
    .lt-card {
        background: #fff; border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        padding: 30px 24px 24px; position: relative;
        margin-top: 50px; margin-bottom: 28px; border: none;
    }
    .lt-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: #fff; background: #f97316;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .lt-card-header {
        margin-left: 90px; font-size: 1.1rem; color: #555; margin-top: -10px;
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;
    }
    .lt-note { font-size: 0.85rem; color: #444; margin-top: 20px; margin-bottom: 6px; }

    .lt-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 14px; }
    .lt-table thead th {
        padding: 12px 14px; font-weight: 700; color: #333;
        border-bottom: 2px solid #e8eaf0; background: #f8f9fc; text-align: left;
    }
    .lt-table tbody tr { border-bottom: 1px solid #f2f4f8; }
    .lt-table tbody tr:hover { background: #f0f4ff; }
    .lt-table tbody td { padding: 12px 14px; color: #555; vertical-align: middle; }

    .btn-primary {
        background: #082b75; color: #fff; border: none; border-radius: 6px;
        padding: 10px 22px; font-size: 0.88rem; font-weight: 600;
        cursor: pointer; font-family: inherit;
    }
    .btn-primary:hover { background: #0a3491; }
    .btn-add {
        background: #00bcd4; color: #fff; border: none; border-radius: 6px;
        padding: 9px 20px; font-size: 0.86rem; font-weight: 600;
        cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-add:hover { background: #00a5bb; }
    .btn-edit {
        background: #f59e0b; color: #fff; border: none; border-radius: 6px;
        padding: 6px 12px; font-size: 0.78rem; font-weight: 600; cursor: pointer; font-family: inherit;
    }
    .btn-edit:hover { background: #d97706; }
    .btn-danger {
        background: #ef4444; color: #fff; border: none; border-radius: 6px;
        padding: 6px 12px; font-size: 0.78rem; font-weight: 600; cursor: pointer; font-family: inherit;
    }
    .btn-danger:hover { background: #dc2626; }
    .btn-cancel {
        background: #f3f4f6; color: #555; border: none; border-radius: 6px;
        padding: 9px 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit;
    }

    .lt-input {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 8px 12px;
        font-size: 0.88rem; color: #333; width: 100%; font-family: inherit; outline: none;
    }
    .lt-input:focus { border-color: #4b7ce3; box-shadow: 0 0 0 3px rgba(75,124,227,0.1); }
    .lt-label { font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: 4px; }

    .lt-toggle { position: relative; display: inline-block; width: 44px; height: 24px; }
    .lt-toggle input { opacity: 0; width: 0; height: 0; }
    .lt-toggle-slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background: #ccc; border-radius: 24px; transition: 0.3s;
    }
    .lt-toggle-slider::before {
        content: ''; position: absolute; width: 18px; height: 18px;
        left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: 0.3s;
    }
    input:checked + .lt-toggle-slider { background: #4caf50; }
    input:checked + .lt-toggle-slider::before { transform: translateX(20px); }

    .lt-alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 0.87rem; }
    .lt-alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .lt-alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    .lt-modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.4);
        display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 16px;
    }
    .lt-modal { background: #fff; border-radius: 10px; padding: 28px; width: 100%; max-width: 480px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .lt-modal-title { font-size: 1rem; font-weight: 700; color: #082b75; margin-bottom: 20px; }
    .lt-modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
</style>
@endpush

@section('content')
<div class="lt-page">

    @if(session('success'))
        <div class="lt-alert lt-alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="lt-alert lt-alert-error">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="lt-card">
        <div class="lt-icon"><i class="fas fa-trash-alt"></i></div>
        <div class="lt-card-header">
            <strong>ตั้งค่าประเภทการลา / Set personnel leave type</strong>
            <button class="btn-add" onclick="document.getElementById('modal-add-type').style.display='flex'">
                <i class="fas fa-plus"></i> เพิ่ม
            </button>
        </div>

        <div class="lt-note">
            รูปแบบการคำนวณวันลา : ไม่ตรวจสอบตารางเวลา (คำนวณวันลาโดยรวมวันหยุด) ใช้กับทุกประเภทการลา แก้ไขได้ที่หน้า
            <a href="{{ route('leave-settings.index') }}">ตั้งค่าการลา</a>
        </div>

        <table class="lt-table">
            <thead>
                <tr>
                    <th style="width:60px;">ลำดับ</th>
                    <th>ประเภทการลา</th>
                    <th>ตัวย่อ</th>
                    <th>ประเภทการลา (ภาษาอังกฤษ)</th>
                    <th>ตัวย่อ (ภาษาอังกฤษ)</th>
                    <th style="width:80px;">เปิดใช้งาน</th>
                    <th style="width:130px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveTypes as $type)
                <tr>
                    <td>{{ $loop->iteration + ($leaveTypes->currentPage() - 1) * $leaveTypes->perPage() }}</td>
                    <td>{{ $type->leave_type_name }}</td>
                    <td>{{ $type->abbr_th ?: '-' }}</td>
                    <td>{{ $type->name_en ?: '-' }}</td>
                    <td>{{ $type->abbr_en ?: '-' }}</td>
                    <td>
                        <form action="{{ route('leave-types.toggle', $type->id) }}" method="POST" style="display:inline;">
                            @csrf @method('PUT')
                            <label class="lt-toggle" title="{{ $type->is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}">
                                <input type="checkbox" onchange="this.form.submit()" {{ $type->is_active ? 'checked' : '' }}>
                                <span class="lt-toggle-slider"></span>
                            </label>
                        </form>
                    </td>
                    <td>
                        <button class="btn-edit" onclick="openEditType(
                            {{ $type->id }},
                            '{{ addslashes($type->leave_type_name) }}',
                            '{{ addslashes($type->abbr_th) }}',
                            '{{ addslashes($type->name_en) }}',
                            '{{ addslashes($type->abbr_en) }}'
                        )"><i class="fas fa-pen"></i></button>
                        <form action="{{ route('leave-types.destroy', $type->id) }}" method="POST" style="display:inline;"
                            onsubmit="return confirm('ยืนยันการลบประเภทการลา {{ $type->leave_type_name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:#aaa;padding:24px;">ยังไม่มีประเภทการลา</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:16px;">{{ $leaveTypes->links() }}</div>
    </div>
</div>

{{-- Modal: เพิ่มประเภทการลา --}}
<div id="modal-add-type" class="lt-modal-overlay" style="display:none;">
    <div class="lt-modal">
        <div class="lt-modal-title"><i class="fas fa-plus-circle"></i> เพิ่มประเภทการลา</div>
        <form action="{{ route('leave-types.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px;">
                <div class="lt-label">ประเภทการลา <span style="color:red;">*</span></div>
                <input type="text" name="leave_type_name" class="lt-input" placeholder="เช่น ลาป่วย" required>
            </div>
            <div style="margin-bottom:14px;">
                <div class="lt-label">ตัวย่อ</div>
                <input type="text" name="abbr_th" class="lt-input" placeholder="เช่น ป่วย">
            </div>
            <div style="margin-bottom:14px;">
                <div class="lt-label">ประเภทการลา (ภาษาอังกฤษ)</div>
                <input type="text" name="name_en" class="lt-input" placeholder="เช่น Sick leave">
            </div>
            <div style="margin-bottom:14px;">
                <div class="lt-label">ตัวย่อ (ภาษาอังกฤษ)</div>
                <input type="text" name="abbr_en" class="lt-input" placeholder="เช่น SL">
            </div>
            <div class="lt-modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('modal-add-type').style.display='none'">ยกเลิก</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: แก้ไขประเภทการลา --}}
<div id="modal-edit-type" class="lt-modal-overlay" style="display:none;">
    <div class="lt-modal">
        <div class="lt-modal-title"><i class="fas fa-pen"></i> แก้ไขประเภทการลา</div>
        <form id="form-edit-type" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom:14px;">
                <div class="lt-label">ประเภทการลา <span style="color:red;">*</span></div>
                <input type="text" name="leave_type_name" id="edit-type-name-th" class="lt-input" required>
            </div>
            <div style="margin-bottom:14px;">
                <div class="lt-label">ตัวย่อ</div>
                <input type="text" name="abbr_th" id="edit-type-abbr-th" class="lt-input">
            </div>
            <div style="margin-bottom:14px;">
                <div class="lt-label">ประเภทการลา (ภาษาอังกฤษ)</div>
                <input type="text" name="name_en" id="edit-type-name-en" class="lt-input">
            </div>
            <div style="margin-bottom:14px;">
                <div class="lt-label">ตัวย่อ (ภาษาอังกฤษ)</div>
                <input type="text" name="abbr_en" id="edit-type-abbr-en" class="lt-input">
            </div>
            <div class="lt-modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('modal-edit-type').style.display='none'">ยกเลิก</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditType(id, nameTh, abbrTh, nameEn, abbrEn) {
    document.getElementById('form-edit-type').action = '/leave-types/' + id;
    document.getElementById('edit-type-name-th').value = nameTh;
    document.getElementById('edit-type-abbr-th').value = abbrTh;
    document.getElementById('edit-type-name-en').value = nameEn;
    document.getElementById('edit-type-abbr-en').value = abbrEn;
    document.getElementById('modal-edit-type').style.display = 'flex';
}

document.querySelectorAll('.lt-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>
@endpush

@endsection
