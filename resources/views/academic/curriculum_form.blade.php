@extends('layouts.sidebar')
@push('styles')
<style>
.cf-page { padding: 24px 28px; }

.cf-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 24px 24px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.cf-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.cf-icon-info   { background: #00bcd4; }
.cf-icon-subj   { background: #43a047; }

.cf-card-header {
    margin-left: 90px; font-size: 1.05rem; color: #555;
    margin-top: -8px; margin-bottom: 22px;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
}
.cf-card-title { font-weight: 600; }

.cf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 28px; }
@media(max-width:700px){ .cf-grid { grid-template-columns: 1fr; } }

.cf-field { display: flex; flex-direction: column; gap: 4px; }
.cf-field label { font-size: 0.82rem; font-weight: 600; color: #444; }
.cf-field input, .cf-field select, .cf-field textarea {
    width: 100%; border: none; border-bottom: 1.5px solid #ccc;
    padding: 7px 4px; font-size: 0.9rem; font-family: inherit;
    outline: none; background: transparent; box-sizing: border-box;
}
.cf-field input:focus, .cf-field select:focus { border-bottom-color: #00bcd4; }

.cf-save-row { margin-top: 24px; display: flex; align-items: center; gap: 10px; }
.btn-save {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px;
    padding: 9px 28px; font-size: 0.88rem; font-weight: 600;
    cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-save:hover { background: #0097a7; }

.btn-back {
    background: #5c6bc0; color: #fff; border: none; border-radius: 6px;
    padding: 9px 20px; font-size: 0.85rem; font-weight: 600;
    cursor: pointer; font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-back:hover { background: #3949ab; color: #fff; }

.cf-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.cf-table thead th {
    padding: 11px 14px; background: #43a047; color: #fff;
    font-weight: 600; text-align: left; font-size: 0.84rem;
}
.cf-table thead th:first-child { border-radius: 6px 0 0 0; }
.cf-table thead th:last-child  { border-radius: 0 6px 0 0; text-align: center; }
.cf-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.cf-table tbody tr:hover { background: #f1f8f1; }
.cf-table tbody td { padding: 11px 14px; color: #555; vertical-align: middle; }

.badge-req  { display: inline-block; background: #e8f5e9; color: #2e7d32; border-radius: 20px; padding: 2px 12px; font-size: 0.76rem; font-weight: 700; }
.badge-opt  { display: inline-block; background: #fff3e0; color: #e65100; border-radius: 20px; padding: 2px 12px; font-size: 0.76rem; font-weight: 700; }
.badge-sem  { display: inline-block; background: #e3f2fd; color: #1565c0; border-radius: 20px; padding: 2px 10px; font-size: 0.76rem; font-weight: 700; }

.cf-action-wrap { position: relative; display: inline-block; }
.btn-action {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px;
    padding: 6px 14px; font-size: 0.8rem; font-weight: 600; cursor: pointer;
    font-family: inherit; display: inline-flex; align-items: center; gap: 5px;
}
.btn-action:hover { background: #0097a7; }
.cf-dropdown {
    display: none; position: absolute; right: 0; top: calc(100% + 4px);
    background: #fff; border: 1px solid #e0e0e0; border-radius: 6px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12); min-width: 150px; z-index: 100;
}
.cf-dropdown.open { display: block; }
.cf-dropdown a, .cf-dropdown button {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 16px; font-size: 0.82rem; color: #444;
    text-decoration: none; width: 100%; background: none; border: none;
    font-family: inherit; cursor: pointer; text-align: left;
}
.cf-dropdown a:hover, .cf-dropdown button:hover { background: #f5f5f5; }
.cf-dropdown .dd-delete { color: #e53935; }
.cf-dropdown .dd-delete:hover { background: #ffebee; }

.cf-empty { text-align: center; padding: 36px; color: #bbb; }

.btn-add-subj {
    background: #43a047; color: #fff; border: none; border-radius: 6px;
    padding: 8px 18px; font-size: 0.82rem; font-weight: 600;
    cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-add-subj:hover { background: #2e7d32; }

.cf-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.45); z-index: 500;
    align-items: center; justify-content: center;
}
.cf-overlay.active { display: flex; }
.cf-modal {
    background: #fff; border-radius: 8px; width: 440px; max-width: 95vw;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
}
.cf-modal-header {
    padding: 18px 22px 14px; font-size: 1rem; font-weight: 700;
    border-bottom: 1px solid #eee; color: #333;
    display: flex; align-items: center; gap: 8px;
}
.cf-modal-body { padding: 18px 22px; display: flex; flex-direction: column; gap: 14px; }
.cf-modal-body label { font-size: 0.82rem; font-weight: 600; color: #555; margin-bottom: 2px; display: block; }
.cf-modal-body select, .cf-modal-body input {
    width: 100%; border: none; border-bottom: 1.5px solid #ccc;
    padding: 7px 4px; font-size: 0.88rem; font-family: inherit;
    outline: none; background: transparent;
}
.cf-modal-body select:focus { border-bottom-color: #43a047; }
.cf-modal-footer {
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
<div class="cf-page">

    <div class="cf-card">
        <div class="cf-icon cf-icon-info"><i class="bi bi-search"></i></div>
        <div class="cf-card-header">
            <span class="cf-card-title">จัดการหลักสูตร/แผน</span>
            <a href="{{ route('curriculums.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
        </div>

        <form method="POST" action="{{ isset($curriculum) ? route('curriculums.update', $curriculum->curriculum_id) : route('curriculums.store') }}">
            @csrf
            @if(isset($curriculum)) @method('PUT') @endif

            <div class="cf-grid">
                <div class="cf-field">
                    <label>ชื่อแผน *</label>
                    <input type="text" name="name" value="{{ $curriculum->name ?? '' }}" required placeholder="เช่น EP ป.1">
                </div>
                <div class="cf-field">
                    <label>ระดับชั้น</label>
                    <select name="level_id">
                        <option value="">-- ทุกระดับ --</option>
                        @foreach($levels as $l)
                            <option value="{{ $l->level_id }}" {{ ($curriculum->level_id ?? '') == $l->level_id ? 'selected' : '' }}>
                                {{ $l->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="cf-field">
                    <label>ปีการศึกษา</label>
                    <input type="text" name="year_applied" value="{{ $curriculum->year_applied ?? '' }}" placeholder="เช่น 2568">
                </div>
                <div class="cf-field">
                    <label>คำอธิบาย</label>
                    <input type="text" name="description" value="{{ $curriculum->description ?? '' }}" placeholder="(ไม่บังคับ)">
                </div>
            </div>

            <div class="cf-save-row">
                <button type="submit" class="btn-save"><i class="bi bi-save"></i> บันทึกหลักสูตร</button>
            </div>
        </form>
    </div>

    @if(isset($curriculum))
    <div class="cf-card">
        <div class="cf-icon cf-icon-subj"><i class="bi bi-journal-bookmark"></i></div>
        <div class="cf-card-header">
            <span class="cf-card-title">จัดการวิชาเรียน</span>
            <button class="btn-add-subj" onclick="document.getElementById('addSubjOverlay').classList.add('active')">
                <i class="bi bi-plus-lg"></i> เพิ่มวิชา
            </button>
        </div>

        <table class="cf-table">
            <thead>
                <tr>
                    <th style="width:50px">ลำดับ</th>
                    <th>รหัสวิชา</th>
                    <th style="text-align:center">หน่วยกิต</th>
                    <th>ชื่อวิชา</th>
                    <th style="text-align:center">เทอม</th>
                    <th style="text-align:center">ประเภท</th>
                    <th>ครูผู้สอน</th>
                    <th style="text-align:center">จัดการข้อมูล</th>
                </tr>
            </thead>
            <tbody>
                @forelse($curriculum->curriculumSubjects as $i => $cs)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $cs->subject->code ?? '-' }}</strong></td>
                    <td style="text-align:center">{{ $cs->subject->credits ?? '-' }}</td>
                    <td>{{ $cs->subject->name_th ?? '-' }}</td>
                    <td style="text-align:center">
                        <span class="badge-sem">
                            @if($cs->semester_type === 'both') 1, 2
                            @else เทอม {{ $cs->semester_type }}
                            @endif
                        </span>
                    </td>
                    <td style="text-align:center">
                        <span class="{{ $cs->is_required ? 'badge-req' : 'badge-opt' }}">
                            {{ $cs->is_required ? 'บังคับ' : 'เลือก' }}
                        </span>
                    </td>
                    <td style="font-size:0.82rem;color:#555">
                        @if($cs->personnel)
                            {{ $cs->personnel->thai_prefix ?? '' }}{{ $cs->personnel->thai_firstname }} {{ $cs->personnel->thai_lastname }}
                        @else
                            <span style="color:#ccc">—</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        <div class="cf-action-wrap">
                            <button class="btn-action" onclick="toggleDd(this)">
                                จัดการข้อมูล <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="cf-dropdown">
                                <button type="button" onclick="openEditModal({{ $cs->id }}, '{{ $cs->semester_type }}', {{ $cs->is_required ? 1 : 0 }}, {{ $cs->personnel_id ?? 'null' }})">
                                    <i class="bi bi-pencil"></i> แก้ไข
                                </button>
                                <form action="{{ route('curriculums.removeSubject', [$curriculum->curriculum_id, $cs->id]) }}" method="POST"
                                      onsubmit="return confirm('ลบวิชา {{ addslashes($cs->subject->name_th ?? '') }} ออกจากหลักสูตร?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dd-delete">
                                        <i class="bi bi-trash"></i> ลบออก
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="cf-empty">
                        <i class="bi bi-journal-x" style="font-size:1.8rem;display:block;margin-bottom:6px"></i>
                        ยังไม่มีวิชาในหลักสูตรนี้
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal เพิ่มวิชา --}}
    <div class="cf-overlay" id="addSubjOverlay" onclick="if(event.target===this)this.classList.remove('active')">
        <div class="cf-modal">
            <div class="cf-modal-header"><i class="bi bi-plus-circle"></i> เพิ่มวิชาในหลักสูตร</div>
            <form method="POST" action="{{ route('curriculums.addSubject', $curriculum->curriculum_id) }}">
                @csrf
                <div class="cf-modal-body">
                    <div>
                        <label>เลือกวิชา *</label>
                        <select name="subject_id" required>
                            <option value="">-- เลือกวิชา --</option>
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->subject_id }}">{{ $sub->code }} — {{ $sub->name_th }} ({{ $sub->credits }} หน่วย)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>เทอม</label>
                        <select name="semester_type">
                            <option value="both">ทั้ง 2 เทอม</option>
                            <option value="1">เทอม 1</option>
                            <option value="2">เทอม 2</option>
                        </select>
                    </div>
                    <div>
                        <label>ประเภทวิชา</label>
                        <select name="is_required">
                            <option value="1">บังคับ</option>
                            <option value="0">เลือก</option>
                        </select>
                    </div>
                    <div>
                        <label>ครูผู้สอน</label>
                        <select name="personnel_id">
                            <option value="">-- ยังไม่กำหนด --</option>
                            @foreach($personnels ?? [] as $p)
                            <option value="{{ $p->personnel_id }}">{{ $p->thai_prefix ?? '' }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="cf-modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="document.getElementById('addSubjOverlay').classList.remove('active')">ยกเลิก</button>
                    <button type="submit" class="btn-modal-ok"><i class="bi bi-check-lg"></i> เพิ่มวิชา</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal แก้ไขวิชา --}}
    <div class="cf-overlay" id="editSubjOverlay" onclick="if(event.target===this)this.classList.remove('active')">
        <div class="cf-modal">
            <div class="cf-modal-header"><i class="bi bi-pencil"></i> แก้ไขวิชาในหลักสูตร</div>
            <form method="POST" id="editSubjForm" action="">
                @csrf @method('PUT')
                <div class="cf-modal-body">
                    <div>
                        <label>เทอม</label>
                        <select name="semester_type" id="edit_semester_type">
                            <option value="both">ทั้ง 2 เทอม</option>
                            <option value="1">เทอม 1</option>
                            <option value="2">เทอม 2</option>
                        </select>
                    </div>
                    <div>
                        <label>ประเภทวิชา</label>
                        <select name="is_required" id="edit_is_required">
                            <option value="1">บังคับ</option>
                            <option value="0">เลือก</option>
                        </select>
                    </div>
                    <div>
                        <label>ครูผู้สอน</label>
                        <select name="personnel_id" id="edit_personnel_id">
                            <option value="">-- ยังไม่กำหนด --</option>
                            @foreach($personnels ?? [] as $p)
                            <option value="{{ $p->personnel_id }}">{{ $p->thai_prefix ?? '' }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="cf-modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="document.getElementById('editSubjOverlay').classList.remove('active')">ยกเลิก</button>
                    <button type="submit" class="btn-modal-ok"><i class="bi bi-save"></i> บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
<script>
function toggleDd(btn) {
    document.querySelectorAll('.cf-dropdown.open').forEach(d => {
        if (d !== btn.nextElementSibling) d.classList.remove('open');
    });
    btn.nextElementSibling.classList.toggle('open');
}
document.addEventListener('click', e => {
    if (!e.target.closest('.cf-action-wrap')) {
        document.querySelectorAll('.cf-dropdown.open').forEach(d => d.classList.remove('open'));
    }
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.cf-overlay.active').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.cf-dropdown.open').forEach(d => d.classList.remove('open'));
    }
});

<<<<<<< HEAD
function openEditModal(csId, semType, isReq) {
    // เปลี่ยนมาใช้ url() ของ Laravel
    document.getElementById('editSubjForm').action = 
        '{{ url("curriculums") }}/{{ $curriculum->curriculum_id ?? "" }}/subjects/' + csId;
        
=======
function openEditModal(csId, semType, isReq, personnelId) {
    document.getElementById('editSubjForm').action =
        '/curriculums/{{ $curriculum->curriculum_id ?? "" }}/subjects/' + csId;
>>>>>>> origin/claude/clarify-usage-wwKCQ
    document.getElementById('edit_semester_type').value = semType;
    document.getElementById('edit_is_required').value = isReq;
    document.getElementById('edit_personnel_id').value = personnelId || '';
    document.querySelectorAll('.cf-dropdown.open').forEach(d => d.classList.remove('open'));
    document.getElementById('editSubjOverlay').classList.add('active');
}
</script>
@endsection