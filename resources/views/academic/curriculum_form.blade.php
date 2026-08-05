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

/* Fields */
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
.cf-field-full { grid-column: 1 / -1; }

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

.btn-copy-plan {
    background: #039be5; color: #fff; border: none; border-radius: 6px;
    padding: 9px 20px; font-size: 0.85rem; font-weight: 600;
    cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-copy-plan:hover { background: #0277bd; }

/* Subject table */
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

/* Action dropdown */
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

/* Add subject button */
.btn-add-subj {
    background: #43a047; color: #fff; border: none; border-radius: 6px;
    padding: 8px 18px; font-size: 0.82rem; font-weight: 600;
    cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-add-subj:hover { background: #2e7d32; }

/* Modal */
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
.cf-modal-row { display: flex; gap: 14px; }
.cf-modal-row > div { flex: 1; }
.cf-modal-hint { font-size: 0.72rem; color: #999; font-weight: 400; margin-left: 4px; }
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

/* Searchable subject combobox */
.cf-combo { position: relative; }
.cf-combo-list {
    display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    max-height: 240px; overflow-y: auto; background: #fff;
    border: 1px solid #e0e0e0; border-radius: 6px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.14); z-index: 600;
}
.cf-combo-list.open { display: block; }
.cf-combo-item { padding: 9px 12px; font-size: 0.85rem; color: #444; cursor: pointer; }
.cf-combo-item:hover, .cf-combo-item.active { background: #f1f8f1; }
.cf-combo-item.hidden { display: none; }
.cf-combo-empty { padding: 14px 12px; font-size: 0.82rem; color: #aaa; text-align: center; }
</style>
@endpush

@section('content')
<div class="cf-page">

    {{-- ===== Card 1: ข้อมูลหลักสูตร ===== --}}
    <div class="cf-card">
        <div class="cf-icon cf-icon-info"><i class="bi bi-search"></i></div>
        <div class="cf-card-header">
            <span class="cf-card-title">จัดการหลักสูตร/แผน</span>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                @if(isset($curriculum))
                <button type="button" class="btn-copy-plan" onclick="document.getElementById('copyPlanOverlay').classList.add('active')">
                    <i class="bi bi-files"></i> คัดลอกแผนการเรียน
                </button>
                @endif
                @php
                    // ย้อนกลับไปหน้าที่มาจริงๆ ตามที่ส่งมาชัดๆ ผ่าน return_to (จากลิงก์ต้นทาง เช่น /programs
                    // หรือ /programs/{id}/plans) — เชื่อถือได้กว่า url()->previous() ที่พึ่งพา referer/session
                    // ซึ่งบางทีก็ใช้ไม่ได้ ถ้าไม่มี return_to เลยค่อย fallback ไปหน้าแผนของหลักสูตร/รายการหลักสูตร
                    $backUrl = $returnTo ?? (isset($program) && $program ? route('programs.plans', $program->program_id) : route('programs.index'));
                @endphp
                <a href="{{ $backUrl }}" class="btn-back">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </a>
            </div>
        </div>

        <form method="POST" action="{{ isset($curriculum) ? route('curriculums.update', $curriculum->curriculum_id) : route('curriculums.store') }}">
            @csrf
            @if(isset($curriculum)) @method('PUT') @endif
            <input type="hidden" name="return_to" value="{{ $returnTo ?? '' }}">

            <div class="cf-grid">
                <div class="cf-field">
                    <label>ชื่อแผน *</label>
                    <input type="text" name="name" id="curriculumNameInput" value="{{ $curriculum->name ?? '' }}" required placeholder="เช่น EP ป.1">
                </div>
                <div class="cf-field">
                    <label>หลักสูตร</label>
                    <select name="program_id" id="programSelect" onchange="onCurriculumProgramChange(this)">
                        <option value="">-- ไม่ระบุ --</option>
                        @foreach($programs as $p)
                            <option value="{{ $p->program_id }}"
                                {{ (string)($curriculum->program_id ?? optional($program ?? null)->program_id ?? '') === (string)$p->program_id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="cf-field">
                    <label>ระดับชั้น</label>
                    <select name="level_id" id="levelSelect" onchange="onCurriculumLevelChange(this)">
                        <option value="">-- ทุกระดับ --</option>
                        @foreach($levels as $l)
                            @if(!isset($curriculum) && isset($usedLevelIds) && $usedLevelIds->contains($l->level_id))
                                @continue
                            @endif
                            <option value="{{ $l->level_id }}" data-name="{{ $l->name }}" {{ ($curriculum->level_id ?? '') == $l->level_id ? 'selected' : '' }}>
                                {{ $l->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="cf-field">
                    <label>
                        ปีการศึกษา
                        @if(!isset($curriculum) && !empty($yearApplied))
                            <button type="button" onclick="document.getElementById('yearAppliedInput').readOnly=false; this.remove();"
                                style="border:none;background:none;color:#00bcd4;font-size:0.75rem;font-weight:600;cursor:pointer;padding:0;margin-left:6px;">
                                (มาจากปีที่เลือกไว้แล้ว — แก้ไขได้)
                            </button>
                        @endif
                    </label>
                    <input type="text" name="year_applied" id="yearAppliedInput"
                        value="{{ $curriculum->year_applied ?? ($yearApplied ?? '') }}" placeholder="เช่น 2568"
                        {{ (!isset($curriculum) && !empty($yearApplied)) ? 'readonly style="background:#f5f5f5;color:#666;"' : '' }}>
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

    {{-- ===== แผนที่มีอยู่แล้วในหลักสูตรนี้ (โชว์รวมในหน้าเดียว ไม่ต้องมีหน้าลิสต์แยก) ===== --}}
    @if(!isset($curriculum) && isset($program) && $program && $existingPlans->count())
    <div class="cf-card">
        <div class="cf-icon cf-icon-subj"><i class="bi bi-journal-bookmark"></i></div>
        <div class="cf-card-header">
            <span class="cf-card-title">
                แผนที่มีอยู่แล้วในหลักสูตร {{ $program->name }}{{ !empty($yearApplied) ? ' (ปีการศึกษา ' . $yearApplied . ')' : '' }}
            </span>
            @if(!empty($yearApplied))
                <a href="{{ route('programs.plans', $program->program_id) }}" style="font-size:0.78rem;color:#00bcd4;text-decoration:none;font-weight:600;">
                    ดูแผนทุกปีของหลักสูตรนี้ &raquo;
                </a>
            @endif
        </div>

        <table class="cf-table">
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
                @foreach($existingPlans as $i => $ep)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $ep->name }}</strong>
                        <div style="font-size:0.78rem;color:#999;margin-top:2px">{{ $ep->level->name ?? '-' }}</div>
                    </td>
                    <td style="text-align:center">{{ $ep->year_applied ?: '-' }}</td>
                    <td>
                        @php $epSections = $sectionsByCurriculum->get($ep->curriculum_id); @endphp
                        @if($epSections && $epSections->count())
                            {{ $epSections->map(fn($s) => $s->full_name)->implode(', ') }}
                        @else
                            <span style="color:#ccc">-</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        <div style="display:inline-flex; gap:6px;">
                            <a href="{{ route('curriculums.edit', $ep->curriculum_id) }}" class="btn-action" style="text-decoration:none">
                                <i class="bi bi-pencil"></i> แก้ไข
                            </a>
                            <form action="{{ route('curriculums.destroy', $ep->curriculum_id) }}" method="POST"
                                  onsubmit="return confirm('ยืนยันลบแผน {{ addslashes($ep->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:#e53935; color:#fff; border:none; border-radius:6px; padding:6px 14px; font-size:0.8rem; font-weight:600; cursor:pointer; font-family:inherit; display:inline-flex; align-items:center; gap:5px;">
                                    <i class="bi bi-trash"></i> ลบ
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ===== Card 2: วิชาเรียน (เฉพาะตอนแก้ไข) ===== --}}
    @if(isset($curriculum))
    <div class="cf-card">
        <div class="cf-icon cf-icon-subj"><i class="bi bi-journal-bookmark"></i></div>
        <div class="cf-card-header">
            <span class="cf-card-title">จัดการวิชาเรียน</span>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button class="btn-add-subj" style="background:#039be5" onclick="document.getElementById('importSubjOverlay').classList.add('active')">
                    <i class="bi bi-file-earmark-excel"></i> นำเข้าจาก Excel
                </button>
                <button class="btn-add-subj" onclick="openAddSubjectModal()">
                    <i class="bi bi-plus-lg"></i> เพิ่มวิชา
                </button>
            </div>
        </div>

        <table class="cf-table">
            <thead>
                <tr>
                    <th style="width:50px">ลำดับ</th>
                    <th>รหัสวิชา</th>
                    <th style="text-align:center">หน่วยกิต</th>
                    <th style="text-align:center">ชั่วโมง/ปี</th>
                    <th style="text-align:center">ชั่วโมง/สัปดาห์</th>
                    <th>ชื่อวิชา</th>
                    <th style="text-align:center">เทอม</th>
                    <th style="text-align:center">ประเภท</th>
                    <th>ครูผู้สอน</th>
                    <th style="text-align:center">สถานะ</th>
                    <th style="text-align:center">จัดการข้อมูล</th>
                </tr>
            </thead>
            <tbody>
                @forelse($curriculum->curriculumSubjects as $i => $cs)
                <tr @if(!$cs->is_active) style="opacity:0.55" @endif>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $cs->subject->code ?? '-' }}</strong></td>
                    <td style="text-align:center">{{ $cs->credits ?? $cs->subject->credits ?? '-' }}</td>
                    <td style="text-align:center">{{ $cs->hours_per_year ?? $cs->subject->hours_per_year ?? '-' }}</td>
                    <td style="text-align:center">{{ $cs->hours_per_week ?? $cs->subject->hours_per_week ?? '-' }}</td>
                    <td>{{ $cs->subject->name_th ?? '-' }}</td>
                    <td style="text-align:center">
                        <span class="badge-sem">
                            @if($cs->semester_type === 'both') 1, 2
                            @else เทอม {{ $cs->semester_type }}
                            @endif
                        </span>
                    </td>
                    <td style="text-align:center">
                        @php
                            $subjType = $cs->subject->subject_type ?? '-';
                            $typeBadgeClass = match($subjType) {
                                'พื้นฐาน' => 'badge-req',
                                'เพิ่มเติม' => 'badge-opt',
                                'กิจกรรม' => 'badge-sem',
                                default => 'badge-opt',
                            };
                        @endphp
                        <span class="{{ $typeBadgeClass }}">{{ $subjType }}</span>
                    </td>
                    <td style="font-size:0.82rem;color:#555">
                        @if($cs->teachers->isNotEmpty())
                            {{ $cs->teachers->map(fn($t) => ($t->thai_prefix ?? '') . $t->thai_firstname . ' ' . $t->thai_lastname)->implode(', ') }}
                        @elseif($cs->personnel)
                            {{ $cs->personnel->thai_prefix ?? '' }}{{ $cs->personnel->thai_firstname }} {{ $cs->personnel->thai_lastname }}
                        @else
                            <span style="color:#ccc">—</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        <form action="{{ route('curriculums.toggleSubject', [$curriculum->curriculum_id, $cs->id]) }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="{{ $cs->is_active ? 'badge-req' : 'badge-opt' }}"
                                style="border:none;cursor:pointer;font-family:inherit;font-size:1.1rem;line-height:1;padding:2px 8px"
                                title="{{ $cs->is_active ? 'คลิกเพื่อปิดใช้งาน' : 'คลิกเพื่อเปิดใช้งาน' }}">
                                <i class="bi {{ $cs->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                            </button>
                        </form>
                    </td>
                    <td style="text-align:center">
                        <div class="cf-action-wrap">
                            <button class="btn-action" onclick="toggleDd(this)">
                                จัดการข้อมูล <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="cf-dropdown">
                                @php
                                    $csPersonnelName = $cs->personnel
                                        ? addslashes(($cs->personnel->thai_prefix ?? '') . $cs->personnel->thai_firstname . ' ' . $cs->personnel->thai_lastname)
                                        : '';
                                @endphp
                                <button type="button" onclick="openEditModal({{ $cs->id }}, '{{ $cs->semester_type }}', {{ $cs->personnel_id ?? 'null' }}, '{{ $csPersonnelName }}', {{ $cs->credits ?? $cs->subject->credits ?? 'null' }}, {{ $cs->hours_per_year ?? $cs->subject->hours_per_year ?? 'null' }}, {{ $cs->hours_per_week ?? $cs->subject->hours_per_week ?? 'null' }}, '{{ $cs->subject->subject_type ?? '' }}')">
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
                    <td colspan="11" class="cf-empty">
                        <i class="bi bi-journal-x" style="font-size:1.8rem;display:block;margin-bottom:6px"></i>
                        ยังไม่มีวิชาในหลักสูตรนี้
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal นำเข้าวิชาจาก Excel --}}
    <div class="cf-overlay" id="importSubjOverlay" onclick="if(event.target===this)this.classList.remove('active')">
        <div class="cf-modal">
            <div class="cf-modal-header"><i class="bi bi-file-earmark-excel"></i> นำเข้ารายวิชาจาก Excel</div>
            <form method="POST" action="{{ route('curriculums.importSubjects', $curriculum->curriculum_id) }}"
                  enctype="multipart/form-data" onsubmit="submitImportSubjForm()">
                @csrf
                <div class="cf-modal-body">
                    <p style="font-size:0.8rem; color:#777; margin:0">
                        รองรับไฟล์รูปแบบ PlanCourses (.xlsx) — วิชาที่ยังไม่มีในระบบจะถูกสร้างใหม่ (จับคู่ด้วยรหัสวิชา)
                        วิชาที่มีอยู่แล้วจะอัปเดตข้อมูลให้ตรงกับไฟล์ ครูผู้สอนจับคู่ด้วยเลขบัตรประชาชน — คนไหนหาไม่เจอในระบบจะข้ามเฉพาะคนนั้น ไม่กระทบข้อมูลส่วนอื่นของแถวนั้น
                    </p>
                    <div>
                        <input type="file" name="file" accept=".xlsx" required>
                    </div>
                    <div>
                        <label style="display:flex; align-items:center; gap:6px; font-weight:400; font-size:0.85rem; color:#444">
                            <input type="checkbox" name="dry_run" value="1" style="width:auto">
                            ทดสอบก่อน (dry-run) — ยังไม่บันทึกข้อมูลจริง
                        </label>
                    </div>
                </div>
                <div class="cf-modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="document.getElementById('importSubjOverlay').classList.remove('active')">ยกเลิก</button>
                    <button type="submit" id="importSubjSubmitBtn" class="btn-modal-ok">
                        <i class="bi bi-upload"></i> เริ่มนำเข้า
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if (session('curriculum_import_output'))
    <div class="cf-overlay active" id="importSubjResultOverlay">
        <div class="cf-modal" style="width:640px; max-width:95vw">
            <div class="cf-modal-header"><i class="bi bi-clipboard-check"></i> ผลการนำเข้ารายวิชา</div>
            <div class="cf-modal-body">
                <pre style="background:#f5f5f5; padding:12px; border-radius:8px; overflow:auto; max-height:60vh; font-size:0.8rem; white-space:pre-wrap">{{ session('curriculum_import_output') }}</pre>
            </div>
            <div class="cf-modal-footer">
                <button type="button" class="btn-modal-ok" onclick="document.getElementById('importSubjResultOverlay').remove()">ปิด</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal เพิ่มวิชา --}}
    <div class="cf-overlay" id="addSubjOverlay" onclick="if(event.target===this)this.classList.remove('active')">
        <div class="cf-modal">
            <div class="cf-modal-header"><i class="bi bi-plus-circle"></i> เพิ่มวิชาในหลักสูตร</div>
            <form method="POST" action="{{ route('curriculums.addSubject', $curriculum->curriculum_id) }}" onsubmit="return validateAddSubjectForm()">
                @csrf
                <div class="cf-modal-body">
                    <div class="cf-combo" id="add_subject_combo">
                        <label>เลือกวิชา *</label>
                        <input type="text" id="add_subject_search" class="cf-combo-input" autocomplete="off"
                            placeholder="พิมพ์รหัสวิชาหรือชื่อวิชาเพื่อค้นหา..."
                            onfocus="openSubjectCombo()" oninput="filterSubjectCombo()">
                        <input type="hidden" name="subject_id" id="add_subject_id">
                        <div class="cf-combo-list" id="add_subject_list">
                            @foreach($subjects as $sub)
                                <div class="cf-combo-item"
                                    data-id="{{ $sub->subject_id }}"
                                    data-search="{{ \Illuminate\Support\Str::lower($sub->code . ' ' . $sub->name_th) }}"
                                    data-label="{{ $sub->code }} — {{ $sub->name_th }} ({{ $sub->credits }} หน่วย)"
                                    data-credits="{{ $sub->credits ?? '' }}"
                                    data-hours-year="{{ $sub->hours_per_year ?? '' }}"
                                    data-hours-week="{{ $sub->hours_per_week ?? '' }}"
                                    data-subject-type="{{ $sub->subject_type ?? '' }}"
                                    onclick="selectSubjectCombo(this)">
                                    {{ $sub->code }} — {{ $sub->name_th }} ({{ $sub->credits }} หน่วย)
                                </div>
                            @endforeach
                            <div class="cf-combo-empty" id="add_subject_empty" style="display:none">ไม่พบวิชาที่ค้นหา</div>
                        </div>
                    </div>
                    <div class="cf-modal-hint" style="margin:-6px 0 0">(ค่าเริ่มต้นดึงมาจากวิชาที่เลือก แก้ไขได้เฉพาะแผนนี้ ไม่กระทบข้อมูลวิชากลาง)</div>
                    <div class="cf-modal-row">
                        <div>
                            <label>หน่วยกิต</label>
                            <input type="number" step="0.5" min="0" name="credits" id="add_credits" placeholder="ค่าเริ่มต้นจากวิชา">
                        </div>
                        <div>
                            <label>ชั่วโมง/ปี</label>
                            <input type="number" step="1" min="0" name="hours_per_year" id="add_hours_per_year" placeholder="ค่าเริ่มต้นจากวิชา">
                        </div>
                        <div>
                            <label>ชั่วโมง/สัปดาห์</label>
                            <input type="number" step="0.5" min="0" name="hours_per_week" id="add_hours_per_week" placeholder="ค่าเริ่มต้นจากวิชา">
                        </div>
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
                        <label>ประเภทวิชา (ดึงจากวิชาที่เลือก)</label>
                        <div style="padding:7px 4px;font-size:0.9rem;color:#333" id="add_subject_type_display">-</div>
                        <input type="hidden" name="is_required" id="add_is_required" value="1">
                    </div>
                    <div>
                        <label>ครูผู้สอน</label>
                        <div class="cf-combo" id="add_personnel_combo">
                            <input type="text" id="add_personnel_search" class="cf-combo-input" autocomplete="off"
                                placeholder="พิมพ์ชื่อครูเพื่อค้นหา..."
                                onfocus="openPersonnelCombo('add_')" oninput="filterPersonnelCombo('add_')">
                            <input type="hidden" name="personnel_id" id="add_personnel_id">
                            <div class="cf-combo-list" id="add_personnel_list">
                                <div class="cf-combo-item" data-id="" data-search="" data-label="-- ยังไม่กำหนด --" onclick="selectPersonnelCombo('add_', this)">-- ยังไม่กำหนด --</div>
                                @foreach($personnels ?? [] as $p)
                                <div class="cf-combo-item"
                                    data-id="{{ $p->personnel_id }}"
                                    data-search="{{ \Illuminate\Support\Str::lower(($p->thai_prefix ?? '') . $p->thai_firstname . ' ' . $p->thai_lastname) }}"
                                    data-label="{{ $p->thai_prefix ?? '' }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}"
                                    onclick="selectPersonnelCombo('add_', this)">
                                    {{ $p->thai_prefix ?? '' }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                                </div>
                                @endforeach
                                <div class="cf-combo-empty" id="add_personnel_empty" style="display:none">ไม่พบครูที่ค้นหา</div>
                            </div>
                        </div>
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
                    <div class="cf-modal-hint" style="margin:-6px 0 0">(ค่าเริ่มต้นดึงมาจากวิชา แก้ไขได้เฉพาะแผนนี้ ไม่กระทบข้อมูลวิชากลาง)</div>
                    <div class="cf-modal-row">
                        <div>
                            <label>หน่วยกิต</label>
                            <input type="number" step="0.5" min="0" name="credits" id="edit_credits" placeholder="ค่าเริ่มต้นจากวิชา">
                        </div>
                        <div>
                            <label>ชั่วโมง/ปี</label>
                            <input type="number" step="1" min="0" name="hours_per_year" id="edit_hours_per_year" placeholder="ค่าเริ่มต้นจากวิชา">
                        </div>
                        <div>
                            <label>ชั่วโมง/สัปดาห์</label>
                            <input type="number" step="0.5" min="0" name="hours_per_week" id="edit_hours_per_week" placeholder="ค่าเริ่มต้นจากวิชา">
                        </div>
                    </div>
                    <div>
                        <label>เทอม</label>
                        <select name="semester_type" id="edit_semester_type">
                            <option value="both">ทั้ง 2 เทอม</option>
                            <option value="1">เทอม 1</option>
                            <option value="2">เทอม 2</option>
                        </select>
                    </div>
                    <div>
                        <label>ประเภทวิชา (ดึงจากวิชานี้)</label>
                        <div style="padding:7px 4px;font-size:0.9rem;color:#333" id="edit_subject_type_display">-</div>
                        <input type="hidden" name="is_required" id="edit_is_required" value="1">
                    </div>
                    <div>
                        <label>ครูผู้สอน</label>
                        <div class="cf-combo" id="edit_personnel_combo">
                            <input type="text" id="edit_personnel_search" class="cf-combo-input" autocomplete="off"
                                placeholder="พิมพ์ชื่อครูเพื่อค้นหา..."
                                onfocus="openPersonnelCombo('edit_')" oninput="filterPersonnelCombo('edit_')">
                            <input type="hidden" name="personnel_id" id="edit_personnel_id">
                            <div class="cf-combo-list" id="edit_personnel_list">
                                <div class="cf-combo-item" data-id="" data-search="" data-label="-- ยังไม่กำหนด --" onclick="selectPersonnelCombo('edit_', this)">-- ยังไม่กำหนด --</div>
                                @foreach($personnels ?? [] as $p)
                                <div class="cf-combo-item"
                                    data-id="{{ $p->personnel_id }}"
                                    data-search="{{ \Illuminate\Support\Str::lower(($p->thai_prefix ?? '') . $p->thai_firstname . ' ' . $p->thai_lastname) }}"
                                    data-label="{{ $p->thai_prefix ?? '' }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}"
                                    onclick="selectPersonnelCombo('edit_', this)">
                                    {{ $p->thai_prefix ?? '' }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                                </div>
                                @endforeach
                                <div class="cf-combo-empty" id="edit_personnel_empty" style="display:none">ไม่พบครูที่ค้นหา</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cf-modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="document.getElementById('editSubjOverlay').classList.remove('active')">ยกเลิก</button>
                    <button type="submit" class="btn-modal-ok"><i class="bi bi-save"></i> บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    @else
    {{-- ===== Card 2 (ตอนสร้างแผนใหม่): แสดงให้เห็นว่าจะมาเพิ่มวิชาตรงนี้ แต่ต้องบันทึกข้อมูลด้านบนก่อน ===== --}}
    <div class="cf-card">
        <div class="cf-icon cf-icon-subj"><i class="bi bi-journal-bookmark"></i></div>
        <div class="cf-card-header">
            <span class="cf-card-title">จัดการวิชาเรียน</span>
        </div>
        <div class="cf-empty" style="padding:28px 20px;">
            <i class="bi bi-arrow-up-circle" style="font-size:1.8rem;display:block;margin-bottom:8px;color:#43a047"></i>
            กด "บันทึกหลักสูตร" ด้านบนก่อน แล้วจะกลับมาที่หน้านี้พร้อมเพิ่มวิชาได้ทันที
        </div>
    </div>
    @endif

    @if(isset($curriculum))
    {{-- ===== Modal คัดลอกแผนการเรียน (ในปีเดิม หรือไปปีอื่น เช่นปีหน้า) ===== --}}
    @php
        $suggestedNextYear = is_numeric($curriculum->year_applied) ? ((int) $curriculum->year_applied + 1) : '';
    @endphp
    <div class="cf-overlay" id="copyPlanOverlay" onclick="if(event.target===this)this.classList.remove('active')">
        <div class="cf-modal">
            <div class="cf-modal-header"><i class="bi bi-files"></i> คัดลอกแผนการเรียน</div>
            <form action="{{ route('curriculums.copy', $curriculum->curriculum_id) }}" method="POST">
                @csrf
                <input type="hidden" name="return_to" value="{{ $returnTo ?? '' }}">
                <div class="cf-modal-body">
                    <div>
                        คัดลอกแผน <strong>{{ $curriculum->name }}</strong> พร้อมวิชาทั้งหมดในแผนเป็นแผนใหม่
                    </div>
                    <div>
                        <label>ปีการศึกษาของแผนใหม่ <span class="cf-modal-hint">(เว้นว่างไว้ = คัดลอกในปีเดิม ระบบจะตั้งชื่อเติม "(คัดลอก)" ให้อัตโนมัติ)</span></label>
                        <input type="text" name="year_applied" value="{{ $suggestedNextYear }}" placeholder="เช่น {{ $suggestedNextYear ?: '2569' }}">
                    </div>
                </div>
                <div class="cf-modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="document.getElementById('copyPlanOverlay').classList.remove('active')">ยกเลิก</button>
                    <button type="submit" class="btn-modal-ok"><i class="bi bi-files"></i> คัดลอกแผน</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
<script>
const curriculumIsEdit = @json(isset($curriculum));
const curriculumProgramNames = @json($programs->pluck('name', 'program_id'));
function onCurriculumLevelChange(select) {
    if (curriculumIsEdit) return; // แก้ไขแผนที่มีอยู่แล้ว ไม่ต้องเปลี่ยนชื่อให้อัตโนมัติ
    const progId = document.getElementById('programSelect').value;
    const progName = curriculumProgramNames[progId];
    if (!progName) return;
    const opt = select.options[select.selectedIndex];
    const nameInput = document.getElementById('curriculumNameInput');
    if (opt.dataset.name) {
        nameInput.value = progName + ' ' + opt.dataset.name;
    }
}
function onCurriculumProgramChange(select) {
    onCurriculumLevelChange(document.getElementById('levelSelect'));
}
function submitImportSubjForm() {
    document.getElementById('importSubjSubmitBtn').disabled = true;
    document.getElementById('importSubjSubmitBtn').innerText = 'กำลังนำเข้า... กรุณารอสักครู่';
}
function openAddSubjectModal() {
    document.getElementById('add_subject_id').value = '';
    document.getElementById('add_subject_search').value = '';
    document.getElementById('add_credits').value = '';
    document.getElementById('add_hours_per_year').value = '';
    document.getElementById('add_hours_per_week').value = '';
    document.getElementById('add_is_required').value = '1';
    document.getElementById('add_subject_type_display').textContent = '-';
    document.getElementById('add_personnel_id').value = '';
    document.getElementById('add_personnel_search').value = '';
    document.getElementById('add_subject_list').classList.remove('open');
    document.getElementById('add_personnel_list').classList.remove('open');
    document.getElementById('addSubjOverlay').classList.add('active');
}
// พื้นฐาน/กิจกรรม = บังคับ โดยปริยาย, เพิ่มเติม = เลือก โดยปริยาย (ยังแก้เองได้เสมอ แค่ตั้งค่าเริ่มต้นให้)
function defaultIsRequiredFor(subjectType) {
    return subjectType === 'เพิ่มเติม' ? '0' : '1';
}
function openSubjectCombo() {
    document.getElementById('add_subject_list').classList.add('open');
    filterSubjectCombo();
}
function filterSubjectCombo() {
    // ผู้ใช้เพิ่งพิมพ์เอง (ไม่ใช่จากการคลิกเลือกในลิสต์) ล้างค่าที่เคยเลือกไว้ กันส่ง subject_id เก่า
    // ที่ไม่ตรงกับข้อความที่เห็นตอนนี้แล้ว
    document.getElementById('add_subject_id').value = '';
    const q = document.getElementById('add_subject_search').value.trim().toLowerCase();
    let anyVisible = false;
    document.querySelectorAll('#add_subject_list .cf-combo-item').forEach(item => {
        const match = !q || item.dataset.search.includes(q);
        item.classList.toggle('hidden', !match);
        if (match) anyVisible = true;
    });
    document.getElementById('add_subject_empty').style.display = anyVisible ? 'none' : 'block';
}
function selectSubjectCombo(item) {
    document.getElementById('add_subject_id').value = item.dataset.id;
    document.getElementById('add_subject_search').value = item.dataset.label;
    document.getElementById('add_credits').value = item.dataset.credits || '';
    document.getElementById('add_hours_per_year').value = item.dataset.hoursYear || '';
    document.getElementById('add_hours_per_week').value = item.dataset.hoursWeek || '';
    document.getElementById('add_is_required').value = defaultIsRequiredFor(item.dataset.subjectType || '');
    document.getElementById('add_subject_type_display').textContent = item.dataset.subjectType || '-';
    document.getElementById('add_subject_list').classList.remove('open');
}
function validateAddSubjectForm() {
    if (!document.getElementById('add_subject_id').value) {
        alert('กรุณาเลือกวิชาจากลิสต์ก่อน');
        document.getElementById('add_subject_search').focus();
        return false;
    }
    return true;
}
function openPersonnelCombo(prefix) {
    document.getElementById(prefix + 'personnel_list').classList.add('open');
    filterPersonnelCombo(prefix);
}
function filterPersonnelCombo(prefix) {
    // เหมือนวิชา — พิมพ์เองแล้วล้างค่าที่เคยเลือกไว้ กันส่ง personnel_id เก่าที่ไม่ตรงกับข้อความที่เห็น
    document.getElementById(prefix + 'personnel_id').value = '';
    const q = document.getElementById(prefix + 'personnel_search').value.trim().toLowerCase();
    let anyVisible = false;
    document.querySelectorAll('#' + prefix + 'personnel_list .cf-combo-item').forEach(item => {
        const match = !q || item.dataset.search.includes(q);
        item.classList.toggle('hidden', !match);
        if (match) anyVisible = true;
    });
    document.getElementById(prefix + 'personnel_empty').style.display = anyVisible ? 'none' : 'block';
}
function selectPersonnelCombo(prefix, item) {
    document.getElementById(prefix + 'personnel_id').value = item.dataset.id;
    document.getElementById(prefix + 'personnel_search').value = item.dataset.label;
    document.getElementById(prefix + 'personnel_list').classList.remove('open');
}
function bindComboKeydown(inputId, listId, onSelect) {
    document.getElementById(inputId)?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const firstVisible = document.querySelector('#' + listId + ' .cf-combo-item:not(.hidden)');
            if (firstVisible) onSelect(firstVisible);
        } else if (e.key === 'Escape') {
            document.getElementById(listId).classList.remove('open');
        }
    });
}
bindComboKeydown('add_subject_search', 'add_subject_list', selectSubjectCombo);
bindComboKeydown('add_personnel_search', 'add_personnel_list', item => selectPersonnelCombo('add_', item));
bindComboKeydown('edit_personnel_search', 'edit_personnel_list', item => selectPersonnelCombo('edit_', item));
document.addEventListener('click', e => {
    const insideCombo = e.target.closest('.cf-combo');
    document.querySelectorAll('.cf-combo-list.open').forEach(list => {
        if (!insideCombo || list.closest('.cf-combo') !== insideCombo) {
            list.classList.remove('open');
        }
    });
});
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

function openEditModal(csId, semType, personnelId, personnelName, credits, hoursPerYear, hoursPerWeek, subjectType) {
    document.getElementById('editSubjForm').action =
        '/curriculums/{{ $curriculum->curriculum_id ?? "" }}/subjects/' + csId;
    document.getElementById('edit_semester_type').value = semType;
    document.getElementById('edit_is_required').value = defaultIsRequiredFor(subjectType || '');
    document.getElementById('edit_subject_type_display').textContent = subjectType || '-';
    document.getElementById('edit_personnel_id').value = personnelId || '';
    document.getElementById('edit_personnel_search').value = personnelName || '';
    document.getElementById('edit_personnel_list').classList.remove('open');
    document.getElementById('edit_credits').value = (credits === null || credits === undefined) ? '' : credits;
    document.getElementById('edit_hours_per_year').value = (hoursPerYear === null || hoursPerYear === undefined) ? '' : hoursPerYear;
    document.getElementById('edit_hours_per_week').value = (hoursPerWeek === null || hoursPerWeek === undefined) ? '' : hoursPerWeek;
    document.querySelectorAll('.cf-dropdown.open').forEach(d => d.classList.remove('open'));
    document.getElementById('editSubjOverlay').classList.add('active');
}
</script>
@endsection
