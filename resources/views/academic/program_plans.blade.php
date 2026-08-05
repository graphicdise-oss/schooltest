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
</style>
@endpush

@section('content')
<div class="pp-page">

    <div class="pp-card">
        <div class="pp-icon"><i class="bi bi-journal-check"></i></div>
        <div class="pp-card-header">
            <span class="pp-card-title">
                จัดการแผนปีการศึกษา หลักสูตร <strong>{{ $program->name }}</strong>
            </span>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('curriculums.create', ['program_id' => $program->program_id, 'return_to' => url()->full()]) }}" class="btn-add">
                    <i class="bi bi-plus-lg"></i> สร้างแผน
                </a>
                <a href="{{ route('programs.index') }}" class="pp-back">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </a>
            </div>
        </div>

        <table class="pp-table">
            <thead>
                <tr>
                    <th style="width:50px">ลำดับ</th>
                    <th>แผน</th>
                    <th style="text-align:center">ปีการศึกษา</th>
                    <th>ชั้นเรียน</th>
                    <th style="text-align:center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($curriculums as $i => $c)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <a href="{{ route('curriculums.sections', ['id' => $c->curriculum_id, 'return_to' => url()->full()]) }}" style="font-weight:700;color:#2e7d32;text-decoration:none">
                            {{ $c->name }}
                        </a>
                        <div style="font-size:0.78rem;color:#999;margin-top:2px">{{ $c->level->name ?? '-' }}</div>
                    </td>
                    <td style="text-align:center">{{ $c->year_applied ?: '-' }}</td>
                    <td>
                        @php $sections = $sectionsByCurriculum->get($c->curriculum_id); @endphp
                        <a href="{{ route('curriculums.sections', ['id' => $c->curriculum_id, 'return_to' => url()->full()]) }}" style="text-decoration:none">
                            @if($sections && $sections->count())
                                {{ $sections->map(fn($s) => $s->full_name)->implode(', ') }}
                            @else
                                <span style="color:#43a047"><i class="bi bi-plus-circle"></i> เพิ่มห้องเรียน</span>
                            @endif
                        </a>
                    </td>
                    <td style="text-align:center">
                        <div class="pp-action-wrap">
                            <button class="btn-action" onclick="toggleDd(this)">
                                จัดการ/แก้ไข <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="pp-dropdown">
                                <a href="{{ route('curriculums.sections', ['id' => $c->curriculum_id, 'return_to' => url()->full()]) }}">
                                    <i class="bi bi-door-open"></i> ห้องเรียน
                                </a>
                                <a href="{{ route('curriculums.edit', $c->curriculum_id) }}">
                                    <i class="bi bi-pencil"></i> แก้ไข
                                </a>
                                <form action="{{ route('curriculums.destroy', $c->curriculum_id) }}" method="POST"
                                      onsubmit="return confirm('ยืนยันลบแผน {{ addslashes($c->name) }}?')">
                                    @csrf @method('DELETE')
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
                    <td colspan="5" class="pp-empty">
                        <i class="bi bi-journal-x" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                        ยังไม่มีแผนในหลักสูตรนี้
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
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
    if (e.key === 'Escape') document.querySelectorAll('.pp-dropdown.open').forEach(d => d.classList.remove('open'));
});
</script>
@endsection
