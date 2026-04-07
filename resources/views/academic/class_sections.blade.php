@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><span>จัดการห้องเรียน</span></nav>

    <div class="ac-card">
        <div class="ac-card-header">
            <span><i class="bi bi-door-open"></i> ห้องเรียน</span>
            <button class="ac-btn ac-btn-success" onclick="document.getElementById('addOverlay').classList.add('active')"><i class="bi bi-plus-lg"></i> เพิ่มห้องเรียน</button>
        </div>
        <div class="ac-card-body">
            {{-- เลือกเทอม --}}
            <div class="ac-field" style="max-width:400px; margin-bottom:16px">
                <label>ภาคเรียน</label>
                <select class="ac-select" onchange="window.location='?semester_id='+this.value">
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->semester_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>
                            {{ $sem->academicYear->year_name }} เทอม {{ $sem->semester_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ระดับชั้น</th>
                            <th>ห้อง</th>
                            <th>ครูที่ปรึกษา</th>
                            <th>จำนวนนักเรียน</th>
                            <th>จำนวนสูงสุด</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sections as $i => $sec)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $sec->level->name ?? '-' }}</td>
                            <td style="font-weight:700; color:#4479DA">{{ $sec->level->name ?? '' }}/{{ $sec->section_number }}</td>
                            <td>{{ $sec->homeroomTeacher ? $sec->homeroomTeacher->thai_firstname . ' ' . $sec->homeroomTeacher->thai_lastname : '-' }}</td>
                            <td>
                                <span class="ac-badge ac-badge-info">
                                    {{ $sec->studentSections->where('status', 'กำลังศึกษา')->count() }} คน
                                </span>
                            </td>
                            <td>{{ $sec->max_students ?? 40 }}</td>
                            <td>
                                <a href="{{ route('class-sections.students', $sec->section_id) }}" class="ac-action-btn ac-action-view" title="จัดนักเรียน">
                                    <i class="bi bi-people"></i>
                                </a>
                                <button class="ac-action-btn ac-action-edit" title="แก้ไข"
                                    onclick="openEdit({{ $sec->section_id }}, {{ $sec->section_number }}, '{{ $sec->homeroom_teacher_id ?? '' }}', {{ $sec->max_students ?? 40 }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('class-sections.destroy', $sec->section_id) }}" method="POST" style="display:inline" onsubmit="return confirm('ลบห้องเรียนนี้?')">
                                    @csrf @method('DELETE')
                                    <button class="ac-action-btn ac-action-delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="ac-empty">ยังไม่มีห้องเรียนในเทอมนี้</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal เพิ่มห้องเรียน --}}
<div class="ac-overlay" id="addOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="ac-modal" onclick="event.stopPropagation()">
        <div class="ac-modal-header"><i class="bi bi-plus-circle me-2"></i> เพิ่มห้องเรียน</div>
        <form method="POST" action="{{ route('class-sections.store') }}">
            @csrf
            <input type="hidden" name="semester_id" value="{{ $semesterId }}">
            <div class="ac-modal-body">
                <label>ระดับชั้น *</label>
                <select name="level_id" required>
                    <option value="">-- เลือก --</option>
                    @foreach($levels as $l)
                        <option value="{{ $l->level_id }}">{{ $l->name }} ({{ $l->level_group }})</option>
                    @endforeach
                </select>

                <label>ห้องที่ *</label>
                <input type="number" name="section_number" required min="1" placeholder="เช่น 1, 2, 3">

                <label>ครูที่ปรึกษา</label>
                <select name="homeroom_teacher_id">
                    <option value="">-- ไม่ระบุ --</option>
                    @foreach(\App\Models\Personne\Personnel::where('status','ปฏิบัติงาน')->orderBy('thai_firstname')->get() as $t)
                        <option value="{{ $t->personnel_id }}">{{ $t->thai_firstname }} {{ $t->thai_lastname }}</option>
                    @endforeach
                </select>

                <label>จำนวนนักเรียนสูงสุด</label>
                <input type="number" name="max_students" value="40" min="1">
            </div>
            <div class="ac-modal-footer">
                <button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('addOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal แก้ไข --}}
<div class="ac-overlay" id="editOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="ac-modal" onclick="event.stopPropagation()">
        <div class="ac-modal-header"><i class="bi bi-pencil-square me-2"></i> แก้ไขห้องเรียน</div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="ac-modal-body">
                <label>ห้องที่ *</label>
                <input type="number" name="section_number" id="eSectionNum" required min="1">

                <label>ครูที่ปรึกษา</label>
                <select name="homeroom_teacher_id" id="eTeacher">
                    <option value="">-- ไม่ระบุ --</option>
                    @foreach(\App\Models\Personne\Personnel::where('status','ปฏิบัติงาน')->orderBy('thai_firstname')->get() as $t)
                        <option value="{{ $t->personnel_id }}">{{ $t->thai_firstname }} {{ $t->thai_lastname }}</option>
                    @endforeach
                </select>

                <label>จำนวนนักเรียนสูงสุด</label>
                <input type="number" name="max_students" id="eMax" min="1">
            </div>
            <div class="ac-modal-footer">
                <button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('editOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, num, teacherId, max) {
    document.getElementById('editForm').action = '{{ url("class-sections") }}/' + id;
    document.getElementById('eSectionNum').value = num;
    document.getElementById('eTeacher').value = teacherId || '';
    document.getElementById('eMax').value = max;
    document.getElementById('editOverlay').classList.add('active');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.ac-overlay.active').forEach(el => el.classList.remove('active'));
});
</script>


@endsection