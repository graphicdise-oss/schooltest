@extends('layouts.sidebar')
@push('styles')
<style>
.pp-page { padding: 24px 28px; }

.pp-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 20px 20px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.pp-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff; background: #43a047;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.pp-card-header {
    margin-left: 90px; display: flex; align-items: center;
    justify-content: space-between; margin-top: -8px; margin-bottom: 20px;
    flex-wrap: wrap; gap: 10px;
}
.pp-card-title { font-size: 1.05rem; color: #555; }
.pp-card-title small { display:block; font-size:0.78rem; color:#999; font-weight:400; margin-top:2px; }

.pp-back {
    background: #5c6bc0; color: #fff; border: none; border-radius: 6px;
    padding: 7px 18px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.pp-back:hover { background: #3949ab; color: #fff; }

.btn-add {
    background: #43a047; color: #fff; border: none; border-radius: 6px;
    padding: 8px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-add:hover { background: #2e7d32; color: #fff; }

.pp-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 10px; }
.pp-table thead th {
    padding: 12px 14px; background: #43a047; color: #fff;
    font-weight: 600; text-align: left; font-size: 0.85rem;
}
.pp-table thead th:first-child { border-radius: 6px 0 0 0; }
.pp-table thead th:last-child  { border-radius: 0 6px 0 0; text-align: center; }
.pp-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.pp-table tbody tr:hover { background: #f1f8f1; }
.pp-table tbody td { padding: 12px 14px; color: #555; vertical-align: middle; }

.pp-empty { text-align: center; padding: 40px; color: #aaa; }

/* Action dropdown (same pattern as curriculum_form.blade.php) */
.pp-action-wrap { position: relative; display: inline-block; }
.btn-action {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px;
    padding: 6px 14px; font-size: 0.8rem; font-weight: 600; cursor: pointer;
    font-family: inherit; display: inline-flex; align-items: center; gap: 5px;
}
.btn-action:hover { background: #0097a7; }
.pp-dropdown {
    display: none; position: absolute; right: 0; top: calc(100% + 4px);
    background: #fff; border: 1px solid #e0e0e0; border-radius: 6px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12); min-width: 150px; z-index: 100;
}
.pp-dropdown.open { display: block; }
.pp-dropdown a, .pp-dropdown button {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 16px; font-size: 0.82rem; color: #444;
    text-decoration: none; width: 100%; background: none; border: none;
    font-family: inherit; cursor: pointer; text-align: left;
}
.pp-dropdown a:hover, .pp-dropdown button:hover { background: #f5f5f5; }
.pp-dropdown .dd-delete { color: #e53935; }
.pp-dropdown .dd-delete:hover { background: #ffebee; }

/* ป๊อปอัพเพิ่มแผน */
.pp-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.45); z-index: 500;
    align-items: center; justify-content: center;
}
.pp-overlay.active { display: flex; }
.pp-modal {
    background: #fff; border-radius: 8px; width: 440px; max-width: 95vw;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
}
.pp-modal-header {
    padding: 18px 22px 14px; font-size: 1rem; font-weight: 700;
    border-bottom: 1px solid #eee; color: #333;
    display: flex; align-items: center; gap: 8px;
}
.pp-modal-body { padding: 18px 22px; display: flex; flex-direction: column; gap: 14px; }
.pp-modal-body label { font-size: 0.82rem; font-weight: 600; color: #555; margin-bottom: 2px; display: block; }
.pp-modal-body select, .pp-modal-body input {
    width: 100%; border: 1.5px solid #d0d7e5; border-radius: 6px;
    padding: 8px 10px; font-size: 0.88rem; font-family: inherit;
    outline: none; box-sizing: border-box;
}
.pp-modal-footer {
    padding: 14px 22px 18px; display: flex; justify-content: flex-end; gap: 10px;
    border-top: 1px solid #eee;
}
.btn-modal-cancel {
    background: #eee; color: #555; border: none; border-radius: 6px;
    padding: 8px 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit;
}
.btn-modal-ok {
    background: #43a047; color: #fff; border: none; border-radius: 6px;
    padding: 8px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-modal-ok:hover { background: #2e7d32; }
</style>
@endpush

@section('content')
<div class="pp-page">

    <div class="pp-card">
        <div class="pp-icon"><i class="bi bi-journal-check"></i></div>
        <div class="pp-card-header">
            <span class="pp-card-title">
                แผนของ <strong>{{ $level->name }}</strong>
                <small>หลักสูตร {{ $program->name }} — {{ $selectedYear ? 'ปีการศึกษา ' . $selectedYear->year_name : 'ทุกปีการศึกษา' }}</small>
            </span>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button type="button" class="btn-add" onclick="document.getElementById('addPlanOverlay').classList.add('active')">
                    <i class="bi bi-plus-lg"></i> สร้างแผน
                </button>
                <a href="{{ route('programs.plans', $program->program_id) }}?year_id={{ $currentYearId }}" class="pp-back">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </a>
            </div>
        </div>

        @if($hasOtherYearPlans)
        <div style="margin:0 0 16px; padding:10px 16px; background:#fff8e1; border:1px solid #ffe082; border-radius:6px; color:#8a6d00; font-size:0.85rem;">
            <i class="bi bi-info-circle"></i>
            {{ $level->name }} ในหลักสูตรนี้มีแผนของปีการศึกษาอื่นอยู่ด้วย แต่ไม่แสดงในนี้เพราะกำลังกรองเฉพาะปี {{ $selectedYear->year_name }} —
            เปลี่ยนปีการศึกษาที่หน้ารายการหลักสูตรเพื่อดูแผนปีอื่น
        </div>
        @endif

        <table class="pp-table">
            <thead>
                <tr>
                    <th style="width:50px">ลำดับ</th>
                    <th>แผน</th>
                    <th style="text-align:center">ปีการศึกษา</th>
                    <th style="text-align:center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($curriculums as $i => $c)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $c->name }}</strong></td>
                    <td style="text-align:center">{{ $c->year_applied ?: '-' }}</td>
                    <td style="text-align:center">
                        <div class="pp-action-wrap">
                            <button class="btn-action" onclick="toggleDd(this)">
                                จัดการ/แก้ไข <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="pp-dropdown">
                                <a href="{{ route('curriculums.edit', ['id' => $c->curriculum_id, 'return_to' => url()->full()]) }}">
                                    <i class="bi bi-pencil"></i> แก้ไข
                                </a>
                                <form action="{{ route('curriculums.destroy', $c->curriculum_id) }}" method="POST"
                                      onsubmit="return confirm('ยืนยันลบแผน {{ addslashes($c->name) }}?')">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="return_to" value="{{ url()->full() }}">
                                    <button type="submit" class="dd-delete">
                                        <i class="bi bi-trash"></i> ลบ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="pp-empty">
                        <i class="bi bi-journal-x" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                        @if($selectedYear)
                            ยังไม่มีแผนของ {{ $level->name }} ในหลักสูตรนี้สำหรับปีการศึกษา {{ $selectedYear->year_name }}
                        @else
                            ยังไม่มีแผนของ {{ $level->name }} ในหลักสูตรนี้
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- ป๊อปอัพ เพิ่มแผน --}}
<div class="pp-overlay" id="addPlanOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="pp-modal">
        <div class="pp-modal-header"><i class="bi bi-plus-circle"></i> สร้างแผน — {{ $level->name }} หลักสูตร {{ $program->name }}</div>
        <form method="POST" action="{{ route('curriculums.store') }}">
            @csrf
            <input type="hidden" name="program_id" value="{{ $program->program_id }}">
            <input type="hidden" name="level_id" value="{{ $level->level_id }}">
            <input type="hidden" name="return_to" value="{{ url()->full() }}">
            <div class="pp-modal-body">
                <div>
                    <label>ชื่อแผน *</label>
                    <input type="text" name="name" required placeholder="เช่น วิทย์-คณิต {{ $level->name }}/1">
                </div>
                <div>
                    <label>ปีการศึกษา</label>
                    <input type="text" name="year_applied" value="{{ $currentYearName }}" placeholder="เช่น 2569">
                </div>
                <div>
                    <label>คำอธิบาย</label>
                    <input type="text" name="description" placeholder="(ไม่บังคับ)">
                </div>
            </div>
            <div class="pp-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('addPlanOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-ok"><i class="bi bi-check-lg"></i> สร้างแผน</button>
            </div>
        </form>
    </div>
</div>
<script>
function toggleDd(btn) {
    document.querySelectorAll('.pp-dropdown.open').forEach(d => {
        if (d !== btn.nextElementSibling) d.classList.remove('open');
    });
    btn.nextElementSibling.classList.toggle('open');
}
document.addEventListener('click', e => {
    if (!e.target.closest('.pp-action-wrap')) {
        document.querySelectorAll('.pp-dropdown.open').forEach(d => d.classList.remove('open'));
    }
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.pp-dropdown.open').forEach(d => d.classList.remove('open'));
        document.querySelectorAll('.pp-overlay.active').forEach(el => el.classList.remove('active'));
    }
});
</script>
@endsection
