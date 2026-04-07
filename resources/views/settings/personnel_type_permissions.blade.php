@extends('layouts.sidebar')

@push('styles')
<style>
    .pm-page { padding: 24px 28px; min-height: 100%; }
    .pm-back { color: #4479DA; text-decoration: none; font-size: 0.88rem; font-weight: 500; }
    .pm-back:hover { text-decoration: underline; }

    .pm-card {
        background: #fff; border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        margin-bottom: 24px; overflow: hidden;
    }
    .pm-card-header {
        background: linear-gradient(135deg, #4479DA, #5a95f5);
        color: #fff; padding: 16px 24px;
        font-size: 1rem; font-weight: 700;
        display: flex; justify-content: space-between; align-items: center;
    }
    .pm-card-body { padding: 24px; }

    .pm-type-name {
        font-size: 1.3rem; font-weight: 700; color: #333;
        margin-bottom: 4px;
    }
    .pm-type-sub { font-size: 0.85rem; color: #888; margin-bottom: 24px; }

    /* Group */
    .pm-group { margin-bottom: 24px; }
    .pm-group-title {
        font-size: 0.9rem; font-weight: 700; color: #4479DA;
        padding-bottom: 8px; border-bottom: 2px solid #e8f0fe;
        margin-bottom: 12px;
        display: flex; align-items: center; gap: 8px;
    }
    .pm-group-title i { font-size: 1rem; }

    /* Checkbox grid */
    .pm-perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 10px;
    }
    .pm-perm-item {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 14px;
        border: 1.5px solid #e8ecf2;
        border-radius: 10px;
        transition: all 0.15s;
        cursor: pointer;
    }
    .pm-perm-item:hover { border-color: #4479DA; background: #f0f5ff; }
    .pm-perm-item.checked { border-color: #4479DA; background: #e8f0fe; }

    .pm-perm-item input[type="checkbox"] {
        width: 18px; height: 18px;
        accent-color: #4479DA;
        cursor: pointer;
    }
    .pm-perm-item label {
        font-size: 0.84rem; color: #333;
        cursor: pointer; font-weight: 500;
        margin: 0;
    }

    /* Save */
    .pm-save-wrap { text-align: center; padding: 20px 0; }
    .pm-btn-save {
        background: linear-gradient(135deg, #4479DA, #5a95f5);
        color: #fff; border: none; border-radius: 10px;
        padding: 12px 48px; font-size: 0.95rem; font-weight: 600;
        cursor: pointer; font-family: inherit;
        display: inline-flex; align-items: center; gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(68,121,218,0.3);
    }
    .pm-btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(68,121,218,0.35); }

    /* Select all */
    .pm-select-all {
        font-size: 0.82rem; color: #4479DA; cursor: pointer;
        font-weight: 600; text-decoration: underline;
    }
</style>
@endpush

@section('content')
<div class="pm-page">

    <a href="{{ route('personnel-types.index') }}" class="pm-back">
        <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
    </a>

    <div class="pm-card" style="margin-top: 20px;">
        <div class="pm-card-header">
            <span><i class="bi bi-shield-lock me-2"></i> กำหนดสิทธิ์การเข้าถึง</span>
        </div>
        <div class="pm-card-body">
            <div class="pm-type-name">{{ $type->name }}</div>
            <div class="pm-type-sub">เลือกเมนูที่ประเภทบุคลากรนี้สามารถเข้าถึงได้</div>

            <form method="POST" action="{{ route('personnel-types.savePermissions', $type->type_id) }}">
                @csrf

                @foreach($menuList as $group => $menus)
                <div class="pm-group">
                    <div class="pm-group-title">
                        <i class="bi bi-folder2-open"></i> {{ $group }}
                        <span class="pm-select-all" onclick="toggleGroup(this, '{{ Str::slug($group) }}')">เลือกทั้งหมด</span>
                    </div>
                    <div class="pm-perm-grid">
                        @foreach($menus as $menu)
                        @php $isChecked = isset($existingPerms[$menu['key']]) && $existingPerms[$menu['key']]->is_allowed; @endphp
                        <div class="pm-perm-item {{ $isChecked ? 'checked' : '' }}"
                            onclick="toggleItem(this)">
                            <input type="checkbox" name="permissions[]" value="{{ $menu['key'] }}"
                                id="perm-{{ $menu['key'] }}"
                                data-group="{{ Str::slug($group) }}"
                                {{ $isChecked ? 'checked' : '' }}>
                            <label for="perm-{{ $menu['key'] }}">{{ $menu['label'] }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <div class="pm-save-wrap">
                    <button type="submit" class="pm-btn-save">
                        <i class="bi bi-check-lg"></i> บันทึกสิทธิ์
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleItem(el) {
    const cb = el.querySelector('input[type="checkbox"]');
    // ไม่ toggle ถ้าคลิกที่ checkbox โดยตรง (มันจะ toggle เอง)
    if (event.target === cb) {
        el.classList.toggle('checked', cb.checked);
        return;
    }
    cb.checked = !cb.checked;
    el.classList.toggle('checked', cb.checked);
}

function toggleGroup(btn, groupSlug) {
    const checkboxes = document.querySelectorAll(`input[data-group="${groupSlug}"]`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);

    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
        cb.closest('.pm-perm-item').classList.toggle('checked', cb.checked);
    });

    btn.textContent = allChecked ? 'เลือกทั้งหมด' : 'ยกเลิกทั้งหมด';
}
</script>
@endsection