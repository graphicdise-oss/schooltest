@extends('layouts.sidebar')

@push('styles')
<style>
    .bi-page { padding: 24px 28px; min-height: 100%; }

    .bi-breadcrumb { font-size: 0.85rem; color: #999; margin-bottom: 20px; }
    .bi-breadcrumb a { color: #4b7ce3; text-decoration: none; }
    .bi-breadcrumb a:hover { text-decoration: underline; }

    .bi-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 24px 20px 20px; position: relative;
        margin-bottom: 28px;
    }

    .bi-tabs { display: flex; border-radius: 6px; overflow: hidden; margin-bottom: 20px; max-width: 420px; }
    .bi-tab {
        flex: 1; text-align: center; padding: 12px 10px; font-size: 0.92rem; font-weight: 700;
        text-decoration: none; color: #555; background: #eef1f5;
    }
    .bi-tab.active { background: #00838f; color: #fff; }
    .bi-tab:hover { text-decoration: none; }

    .bi-search-row { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .bi-search-input {
        height: 38px; border: 1px solid #ccc; border-radius: 4px;
        padding: 0 12px; font-size: 0.88rem; font-family: inherit; outline: none; width: 280px;
    }
    .bi-search-input:focus { border-color: #00bcd4; }
    .btn-search {
        background: #00bcd4; color: #fff; border: none; border-radius: 6px;
        padding: 9px 22px; font-size: 0.88rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-search:hover { background: #00acc1; }

    .bi-toolbar { display: flex; justify-content: flex-end; margin-bottom: 12px; }
    .btn-add-item {
        background: #4caf50; color: #fff; border: none; border-radius: 6px;
        padding: 10px 20px; font-size: 0.88rem; font-weight: 600;
        cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-add-item:hover { background: #43a047; }

    .bi-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
    .bi-table thead th {
        padding: 12px 10px; font-weight: 600; color: #333;
        border-bottom: 2px solid #eee; background: #fafafa; white-space: nowrap;
    }
    .bi-table tbody tr { border-bottom: 1px solid #f5f5f5; }
    .bi-table tbody tr:hover { background: #fef9f0; }
    .bi-table tbody td { padding: 12px 10px; color: #555; vertical-align: middle; }
    .bi-table .no-data { text-align: center; padding: 30px; color: #aaa; }

    .bi-action-btn {
        width: 30px; height: 30px; border-radius: 6px; border: none;
        cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.85rem; transition: all 0.15s; margin: 0 2px;
    }
    .bi-action-edit   { background: #e8f0fe; color: #1a73e8; }
    .bi-action-edit:hover   { background: #d2e3fc; }
    .bi-action-delete { background: #fce8e6; color: #d93025; }
    .bi-action-delete:hover { background: #f8d7da; }

    .bi-pagination { display: flex; justify-content: flex-end; margin-top: 14px; font-size: 0.82rem; }

    /* Modal */
    .bi-overlay {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.45); z-index: 9999;
        justify-content: center; align-items: center;
    }
    .bi-overlay.active { display: flex; }
    .bi-modal {
        background: #fff; border-radius: 14px; width: 460px; max-width: 94vw;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden;
    }
    .bi-modal-header { background: linear-gradient(135deg, #00838f, #26c6da); color: #fff; padding: 18px 24px; font-size: 1rem; font-weight: 700; }
    .bi-modal-body { padding: 24px; }
    .bi-modal-body label { font-size: 0.82rem; font-weight: 600; color: #555; margin-bottom: 4px; display: block; }
    .bi-modal-body input {
        width: 100%; height: 40px; border: 1.5px solid #e0e0e0; border-radius: 10px;
        padding: 0 14px; font-size: 0.85rem; font-family: inherit; outline: none;
        box-sizing: border-box; margin-bottom: 14px;
    }
    .bi-modal-body input:focus { border-color: #00bcd4; box-shadow: 0 0 0 3px rgba(0,188,212,0.1); }
    .bi-modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid #f0f0f0; }
    .btn-modal-cancel { background: #f5f5f5; color: #666; border: none; border-radius: 8px; padding: 9px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit; }
    .btn-modal-save { background: #00838f; color: #fff; border: none; border-radius: 8px; padding: 9px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit; }
    .btn-modal-save:hover { background: #006e78; }
</style>
@endpush

@section('content')
<div class="bi-page">

    <div class="bi-breadcrumb">
        กิจการนักเรียน &rsaquo; <span style="color:#00838f">ระบบความประพฤติ</span> &rsaquo;
        เพิ่มข้อมูลคะแนนความประพฤติ
    </div>

    @if(session('success'))
    <div style="background:#d4edda;color:#155724;padding:12px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem;">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
    </div>
    @endif

    <div class="bi-card">

        <div class="bi-tabs">
            <a href="{{ route('behavior-items.index', ['type' => 'deduct']) }}" class="bi-tab {{ $type === 'deduct' ? 'active' : '' }}">ลด</a>
            <a href="{{ route('behavior-items.index', ['type' => 'add']) }}" class="bi-tab {{ $type === 'add' ? 'active' : '' }}">เพิ่ม</a>
        </div>

        <form method="GET" action="{{ route('behavior-items.index') }}" class="bi-search-row">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="text" name="search" class="bi-search-input" placeholder="ค้นหาชื่อพฤติกรรม" value="{{ request('search') }}">
            <button type="submit" class="btn-search"><i class="bi bi-search"></i> ค้นหา</button>
        </form>

        <div class="bi-toolbar">
            <button type="button" class="btn-add-item" onclick="document.getElementById('addOverlay').classList.add('active')">
                <i class="bi bi-plus-lg"></i> เพิ่มข้อมูล
            </button>
        </div>

        <table class="bi-table">
            <thead>
                <tr>
                    <th style="width:60px">ลำดับ</th>
                    <th style="width:100px">จำนวนแต้ม</th>
                    <th>ชื่อพฤติกรรมภาษาไทย</th>
                    <th>ชื่อพฤติกรรมภาษาอังกฤษ</th>
                    <th style="width:100px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $i => $item)
                <tr>
                    <td>{{ $items->firstItem() + $i }}</td>
                    <td>{{ number_format($item->points, 2) }}</td>
                    <td>{{ $item->name_th }}</td>
                    <td>{{ $item->name_en ?? '-' }}</td>
                    <td>
                        <button type="button" class="bi-action-btn bi-action-edit"
                            onclick="openEditModal({{ $item->behavior_item_id }}, '{{ addslashes($item->name_th) }}', '{{ addslashes($item->name_en) }}', {{ $item->points }})"
                            title="แก้ไข">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('behavior-items.destroy', $item->behavior_item_id) }}" method="POST" style="display:inline"
                            onsubmit="return confirm('ลบรายการนี้?')">
                            @csrf @method('DELETE')
                            <button class="bi-action-btn bi-action-delete" title="ลบ">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="no-data">ไม่มีข้อมูล</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="bi-pagination">{{ $items->links('pagination::simple-default') }}</div>
    </div>
</div>

{{-- Modal เพิ่ม --}}
<div class="bi-overlay" id="addOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="bi-modal" onclick="event.stopPropagation()">
        <div class="bi-modal-header"><i class="bi bi-plus-circle me-2"></i> เพิ่มข้อมูลคะแนนความประพฤติ</div>
        <form method="POST" action="{{ route('behavior-items.store') }}">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="bi-modal-body">
                <label>ชื่อพฤติกรรมภาษาไทย <span style="color:red">*</span></label>
                <input type="text" name="name_th" required placeholder="เช่น 1.1 การไว้ทรงผมผิดระเบียบ...">

                <label>ชื่อพฤติกรรมภาษาอังกฤษ</label>
                <input type="text" name="name_en" placeholder="Behavior name (English)">

                <label>จำนวนแต้ม <span style="color:red">*</span></label>
                <input type="number" name="points" step="0.01" min="0" required placeholder="5.00">
            </div>
            <div class="bi-modal-footer">
                <button type="button" class="btn-modal-cancel"
                    onclick="document.getElementById('addOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal แก้ไข --}}
<div class="bi-overlay" id="editOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="bi-modal" onclick="event.stopPropagation()">
        <div class="bi-modal-header"><i class="bi bi-pencil-square me-2"></i> แก้ไขรายการพฤติกรรม</div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="bi-modal-body">
                <label>ชื่อพฤติกรรมภาษาไทย <span style="color:red">*</span></label>
                <input type="text" name="name_th" id="editNameTh" required>

                <label>ชื่อพฤติกรรมภาษาอังกฤษ</label>
                <input type="text" name="name_en" id="editNameEn">

                <label>จำนวนแต้ม <span style="color:red">*</span></label>
                <input type="number" name="points" id="editPoints" step="0.01" min="0" required>
            </div>
            <div class="bi-modal-footer">
                <button type="button" class="btn-modal-cancel"
                    onclick="document.getElementById('editOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, nameTh, nameEn, points) {
    document.getElementById('editForm').action = '{{ url("behavior-items") }}/' + id;
    document.getElementById('editNameTh').value = nameTh;
    document.getElementById('editNameEn').value = nameEn;
    document.getElementById('editPoints').value = points;
    document.getElementById('editOverlay').classList.add('active');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.bi-overlay.active').forEach(el => el.classList.remove('active'));
});
</script>
@endsection
