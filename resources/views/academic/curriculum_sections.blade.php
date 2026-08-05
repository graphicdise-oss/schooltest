@extends('layouts.sidebar')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">
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
</style>
@endpush

@section('content')
<div class="pp-page">

    <div class="pp-card">
        <div class="pp-icon"><i class="bi bi-door-open"></i></div>
        <div class="pp-card-header">
            <span class="pp-card-title">
                ห้องเรียนของแผน <strong>{{ $curriculum->name }}</strong>
                <small>{{ $curriculum->level->name ?? '' }}@if($curriculum->program) &middot; หลักสูตร {{ $curriculum->program->name }} @endif</small>
            </span>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button type="button" class="btn-add" onclick="document.getElementById('addSectionOverlay').classList.add('active')">
                    <i class="bi bi-plus-lg"></i> เพิ่มห้องเรียน
                </button>
                <a href="{{ $returnTo }}" class="pp-back">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </a>
            </div>
        </div>

        <table class="pp-table">
            <thead>
                <tr>
                    <th style="width:50px">ลำดับ</th>
                    <th>ห้อง</th>
                    <th>ภาคเรียน</th>
                    <th>ครูที่ปรึกษา</th>
                    <th>นักเรียน</th>
                    <th style="text-align:center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sections as $i => $sec)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="font-weight:700; color:#2e7d32">{{ $sec->full_name }}</td>
                    <td>{{ $sec->semester->academicYear->year_name ?? '-' }} เทอม {{ $sec->semester->semester_name ?? '-' }}</td>
                    <td>{{ $sec->homeroomTeacher ? $sec->homeroomTeacher->thai_firstname . ' ' . $sec->homeroomTeacher->thai_lastname : '-' }}</td>
                    <td><span class="ac-badge ac-badge-info">{{ $sec->studentSections->where('status', 'กำลังศึกษา')->count() }} คน</span></td>
                    <td style="text-align:center; white-space:nowrap">
                        <a href="{{ route('class-sections.students', $sec->section_id) }}" class="ac-action-btn ac-action-view" title="จัดนักเรียน">
                            <i class="bi bi-people"></i>
                        </a>
                        <button type="button" class="ac-action-btn ac-action-edit" title="แก้ไข"
                            onclick="openEditSection({{ $sec->section_id }}, {{ Js::from($sec->section_number . ($sec->study_plan ? ' '.$sec->study_plan : '')) }}, '{{ $sec->homeroom_teacher_id ?? '' }}', {{ $sec->max_students ?? 40 }})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('class-sections.destroy', $sec->section_id) }}" method="POST" style="display:inline" onsubmit="return confirm('ลบห้องเรียน {{ addslashes($sec->full_name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="ac-action-btn ac-action-delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="pp-empty">
                        <i class="bi bi-door-closed" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                        ยังไม่มีห้องเรียนของแผนนี้ — กด "เพิ่มห้องเรียน" เพื่อสร้าง เช่น {{ $curriculum->level->name ?? '' }}/1, {{ $curriculum->level->name ?? '' }}/2
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- Modal เพิ่มห้องเรียน --}}
<div class="ac-overlay" id="addSectionOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="ac-modal" onclick="event.stopPropagation()">
        <div class="ac-modal-header"><i class="bi bi-plus-circle me-2"></i> เพิ่มห้องเรียน — {{ $curriculum->name }}</div>
        <form method="POST" action="{{ route('class-sections.store') }}">
            @csrf
            <input type="hidden" name="level_id" value="{{ $curriculum->level_id }}">
            <input type="hidden" name="curriculum_id" value="{{ $curriculum->curriculum_id }}">
            <div class="ac-modal-body">
                <label>ภาคเรียน *</label>
                <select name="semester_id" required>
                    @foreach($semesters as $sem)
                    <option value="{{ $sem->semester_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>
                        {{ $sem->academicYear->year_name }} เทอม {{ $sem->semester_name }}
                    </option>
                    @endforeach
                </select>

                <label>ห้องที่ *</label>
                <input type="text" name="room_label" id="aRoomLabel" required
                    value="{{ $curriculum->level->name ?? '' }}/" placeholder="เช่น {{ $curriculum->level->name ?? '' }}/1">

                <label>ครูที่ปรึกษา</label>
                <select name="homeroom_teacher_id">
                    <option value="">-- ไม่ระบุ --</option>
                    @foreach($teachers as $t)
                    <option value="{{ $t->personnel_id }}">{{ $t->thai_firstname }} {{ $t->thai_lastname }}</option>
                    @endforeach
                </select>

                <label>จำนวนนักเรียนสูงสุด</label>
                <input type="number" name="max_students" value="40" min="1">
            </div>
            <div class="ac-modal-footer">
                <button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('addSectionOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal แก้ไขห้องเรียน --}}
<div class="ac-overlay" id="editSectionOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="ac-modal" onclick="event.stopPropagation()">
        <div class="ac-modal-header"><i class="bi bi-pencil-square me-2"></i> แก้ไขห้องเรียน</div>
        <form method="POST" id="editSectionForm">
            @csrf @method('PUT')
            <input type="hidden" name="curriculum_id" value="{{ $curriculum->curriculum_id }}">
            <div class="ac-modal-body">
                <label>ห้องที่ *</label>
                <input type="text" name="room_label" id="eRoomLabel" required placeholder="เช่น {{ $curriculum->level->name ?? '' }}/1">

                <label>ครูที่ปรึกษา</label>
                <select name="homeroom_teacher_id" id="eTeacher">
                    <option value="">-- ไม่ระบุ --</option>
                    @foreach($teachers as $t)
                    <option value="{{ $t->personnel_id }}">{{ $t->thai_firstname }} {{ $t->thai_lastname }}</option>
                    @endforeach
                </select>

                <label>จำนวนนักเรียนสูงสุด</label>
                <input type="number" name="max_students" id="eMax" min="1">
            </div>
            <div class="ac-modal-footer">
                <button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('editSectionOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditSection(id, roomLabel, teacherId, max) {
    document.getElementById('editSectionForm').action = '{{ url("class-sections") }}/' + id;
    document.getElementById('eRoomLabel').value = {{ Js::from(($curriculum->level->name ?? '') . '/') }} + roomLabel;
    document.getElementById('eTeacher').value = teacherId || '';
    document.getElementById('eMax').value = max;
    document.getElementById('editSectionOverlay').classList.add('active');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.ac-overlay.active').forEach(el => el.classList.remove('active'));
});
</script>
@endsection
