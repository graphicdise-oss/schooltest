@extends('layouts.sidebar')
@push('styles')
<style>
.ts-page { padding: 20px 28px; }

.ts-back { display:inline-flex; align-items:center; gap:6px; color:#666; text-decoration:none; font-size:0.88rem; margin-bottom:16px; }
.ts-back:hover { color:#00bcd4; }

.ts-header {
    display:flex; align-items:flex-start; justify-content:space-between;
    flex-wrap:wrap; gap:16px; margin-bottom:20px;
}
.ts-info h2 { font-size:1.1rem; font-weight:700; color:#333; margin:0 0 6px; }
.ts-info-row { font-size:0.85rem; color:#666; margin:3px 0; }
.ts-info-row strong { color:#333; }

.ts-actions { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.btn-clear {
    background:#e53935; color:#fff; border:none; border-radius:7px;
    padding:9px 18px; font-size:0.82rem; font-weight:600; cursor:pointer;
    font-family:inherit; display:inline-flex; align-items:center; gap:6px;
}
.btn-clear:hover { background:#c62828; }
.btn-add-subject {
    background:#43a047; color:#fff; border:none; border-radius:7px;
    padding:9px 18px; font-size:0.82rem; font-weight:600; cursor:pointer;
    font-family:inherit; display:inline-flex; align-items:center; gap:6px;
}
.btn-add-subject:hover { background:#2e7d32; }
.btn-assign {
    background:#1e88e5; color:#fff; border:none; border-radius:7px;
    padding:9px 18px; font-size:0.82rem; font-weight:600; cursor:pointer;
    font-family:inherit; display:inline-flex; align-items:center; gap:6px;
}
.btn-assign:hover { background:#1565c0; }

/* Grid */
.ts-grid-wrap { overflow-x:auto; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.07); background:#fff; }
.ts-grid { border-collapse:collapse; width:100%; min-width:1000px; }
.ts-grid th {
    border:1px solid #e0e0e0; padding:8px 6px;
    font-size:0.75rem; color:#555; font-weight:600; text-align:center; white-space:nowrap;
    background:#f8f9fa;
}
.ts-grid th.day-col { background:#3949ab; color:#fff; width:90px; font-size:0.85rem; }
.ts-grid td {
    border:1px solid #ececec; padding:0; height:58px;
    min-width:68px; vertical-align:top; cursor:pointer;
}
.ts-grid td:hover { background:#e8f4fd; }
.ts-grid td.occupied { cursor:default; padding:0; }

.ts-slot {
    height:100%; min-height:58px; padding:5px 6px;
    font-size:0.72rem; line-height:1.35; display:flex; flex-direction:column;
    justify-content:center; color:#fff; font-weight:600; position:relative;
}
.ts-slot-code { font-size:0.8rem; font-weight:700; }
.ts-slot-name { font-size:0.68rem; opacity:.9; }
.ts-slot-teacher { font-size:0.65rem; opacity:.85; }
.ts-slot-del {
    position:absolute; top:3px; right:4px;
    background:rgba(0,0,0,0.25); border:none; color:#fff;
    border-radius:3px; font-size:0.65rem; padding:1px 5px;
    cursor:pointer; display:none;
}
.ts-slot:hover .ts-slot-del { display:block; }

/* Legend */
.ts-legend { margin-top:24px; }
.ts-legend-title { font-size:0.88rem; font-weight:700; color:#444; margin-bottom:12px; border-bottom:1px solid #eee; padding-bottom:8px; }
.ts-legend-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px 32px; }
.ts-legend-item { display:flex; align-items:center; gap:10px; font-size:0.82rem; color:#555; }
.ts-legend-dot { width:14px; height:14px; border-radius:3px; flex-shrink:0; }

/* Modal */
.ts-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,0.45); z-index:9000;
    justify-content:center; align-items:center;
}
.ts-overlay.open { display:flex; animation:fadeIn .18s ease; }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
.ts-modal {
    background:#fff; border-radius:14px; width:460px; max-width:94vw;
    box-shadow:0 20px 60px rgba(0,0,0,0.2); animation:slideUp .25s ease;
}
@keyframes slideUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.ts-modal-head { padding:18px 24px 12px; border-bottom:1px solid #f0f0f0; font-size:1rem; font-weight:700; color:#333; }
.ts-modal-body { padding:18px 24px; display:flex; flex-direction:column; gap:14px; }
.ts-modal-foot { display:flex; justify-content:flex-end; gap:10px; padding:12px 24px 20px; }

.mfield label { font-size:0.8rem; font-weight:600; color:#555; display:block; margin-bottom:4px; }
.mfield select, .mfield input {
    width:100%; height:38px; border:1.5px solid #ddd; border-radius:8px;
    padding:0 12px; font-size:0.85rem; font-family:inherit; outline:none; box-sizing:border-box;
}
.mfield select:focus, .mfield input:focus { border-color:#00bcd4; }
.mrow { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

.btn-mcancel { background:#eee; color:#555; border:none; border-radius:8px; padding:9px 20px; font-size:0.85rem; cursor:pointer; font-family:inherit; }
.btn-msave { background:#43a047; color:#fff; border:none; border-radius:8px; padding:9px 24px; font-size:0.85rem; font-weight:600; cursor:pointer; font-family:inherit; }
.btn-mblue { background:#1e88e5; color:#fff; border:none; border-radius:8px; padding:9px 24px; font-size:0.85rem; font-weight:600; cursor:pointer; font-family:inherit; }
</style>
@endpush

@section('content')
@php
$palette = ['#1e88e5','#43a047','#e53935','#fb8c00','#8e24aa','#00897b','#3949ab','#f4511e','#039be5','#7cb342','#6d4c41','#00acc1'];
$colorMap = [];
foreach($assigns as $i => $a) { $colorMap[$a->assign_id] = $palette[$i % count($palette)]; }
@endphp

<div class="ts-page">

    <a href="{{ route('timetable.index') }}" class="ts-back">
        <i class="bi bi-arrow-left"></i> กลับรายการห้องเรียน
    </a>

    <div class="ts-header">
        <div class="ts-info">
            <h2><i class="bi bi-calendar3 me-2" style="color:#00bcd4"></i>จัดการตารางสอน</h2>
            <div class="ts-info-row">
                ปีการศึกษา <strong>{{ $section->semester?->academicYear?->year_name }} ภาคเรียนที่ {{ $section->semester?->semester_name }}</strong>
            </div>
            <div class="ts-info-row">
                ห้อง <strong>{{ $section->level?->name }}/{{ $section->section_number }}</strong>
            </div>
            <div class="ts-info-row">
                อาจารย์ประจำชั้น
                <strong>
                    @if($section->homeroomTeacher)
                        {{ $section->homeroomTeacher->thai_prefix }}{{ $section->homeroomTeacher->thai_firstname }}
                        {{ $section->homeroomTeacher->thai_lastname }}
                    @else
                        ยังไม่ได้กำหนด
                    @endif
                </strong>
            </div>
            <div class="ts-info-row" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:4px">
                แผนการเรียน:
                @if(isset($section->curriculum) && $section->curriculum)
                    <span style="background:#f3e5f5;color:#7b1fa2;border-radius:12px;padding:2px 10px;font-size:0.8rem;font-weight:700">
                        {{ $section->curriculum->name }} (ปี {{ $section->curriculum->year_applied }})
                    </span>
                @else
                    <span style="color:#aaa;font-size:0.82rem">ยังไม่ได้เลือก</span>
                @endif
            </div>
        </div>

        <div class="ts-actions">
            <form method="POST" action="{{ route('timetable.clearSection', $section->section_id) }}"
                  onsubmit="return confirm('ยืนยันล้างคาบเรียนทั้งหมดของห้องนี้?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-clear"><i class="bi bi-trash3"></i> ล้างข้อมูล</button>
            </form>
            @if($curriculums->count())
            <button class="btn-assign" style="background:#7b1fa2" onclick="openImportModal()">
                <i class="bi bi-journal-bookmark"></i> นำเข้าจากแผนการเรียน
            </button>
            @endif
            <button class="btn-add-subject" onclick="openSlotModal()">
                <i class="bi bi-plus-lg"></i> เพิ่มวิชาเรียน
            </button>
            <button class="btn-assign" onclick="openAssignModal()">
                <i class="bi bi-person-workspace"></i> มอบหมายวิชา
            </button>
        </div>
    </div>

    {{-- ตาราง --}}
    <div class="ts-grid-wrap">
        <table class="ts-grid">
            <thead>
                <tr>
                    <th class="day-col">วัน / เวลา</th>
                    @foreach($slotTimes as $st)
                    <th style="min-width:42px;font-size:0.7rem">{{ $st }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                $slotTimesFlipped = array_flip($slotTimes);
                $skipCells = [];
                foreach ($slotGrid as $d => $daySlots) {
                    foreach ($daySlots as $startKey => $cell) {
                        $span = $cell['span'] ?? 1;
                        $pos  = $slotTimesFlipped[$startKey] ?? null;
                        if ($pos === null) continue;
                        for ($s = 1; $s < $span; $s++) {
                            if (isset($slotTimes[$pos + $s])) {
                                $skipCells[$d][$slotTimes[$pos + $s]] = true;
                            }
                        }
                    }
                }
                @endphp
                @foreach($days as $day)
                <tr>
                    <th class="day-col">{{ $day }}</th>
                    @foreach($slotTimes as $stIdx => $st)
                    @if(isset($skipCells[$day][$st]))
                        @continue
                    @endif
                    @php
                        $cell   = $slotGrid[$day][$st] ?? null;
                        $nextSt = $slotTimes[$stIdx + 1] ?? $st;
                    @endphp
                    @if($cell)
                        <td class="occupied" colspan="{{ $cell['span'] ?? 1 }}">
                            <div class="ts-slot" style="background:{{ $colorMap[$cell['assign']->assign_id] }}">
                                <div class="ts-slot-code">{{ $cell['assign']->subject->code }}</div>
                                <div class="ts-slot-name">{{ Str::limit($cell['assign']->subject->name_th, 12) }}</div>
                                <div class="ts-slot-teacher">{{ $cell['assign']->personnel->thai_firstname }}</div>
                                <form method="POST" action="{{ route('timetable.destroySlot', $cell['slot']->slot_id) }}"
                                      onsubmit="return confirm('ลบคาบนี้?')" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ts-slot-del">✕</button>
                                </form>
                            </div>
                        </td>
                    @else
                        <td onclick="openSlotModal('{{ $day }}','{{ $st }}','{{ $nextSt }}')"></td>
                    @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Legend --}}
    @if($assigns->count())
    <div class="ts-legend">
        <div class="ts-legend-title"><i class="bi bi-grid-3x3-gap me-1"></i> กลุ่มวิชา / รายวิชา</div>
        <div class="ts-legend-grid">
            @foreach($assigns as $a)
            <div class="ts-legend-item">
                <div class="ts-legend-dot" style="background:{{ $colorMap[$a->assign_id] }}"></div>
                <span><strong>{{ $a->subject->code }}</strong> — {{ $a->subject->name_th }}
                    &nbsp;({{ $a->personnel->thai_firstname }} {{ $a->personnel->thai_lastname }})
                    &nbsp;
                    <form method="POST" action="{{ route('timetable.destroyAssign', $a->assign_id) }}"
                          style="display:inline" onsubmit="return confirm('ลบวิชานี้ออกจากห้อง?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none;border:none;color:#e53935;cursor:pointer;font-size:0.8rem;padding:0">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- Modal เพิ่มคาบ --}}
<div class="ts-overlay" id="slotModal" onclick="if(event.target===this)closeSlotModal()">
    <div class="ts-modal">
        <div class="ts-modal-head"><i class="bi bi-clock me-2" style="color:#43a047"></i>เพิ่มคาบเรียน</div>
        <form method="POST" action="{{ route('timetable.storeSlot') }}">
            @csrf
            <div class="ts-modal-body">
                <div class="mfield">
                    <label>วิชา *</label>
                    <select name="assign_id" required>
                        <option value="">-- เลือกวิชา --</option>
                        @foreach($assigns as $a)
                        <option value="{{ $a->assign_id }}">{{ $a->subject->code }} — {{ $a->subject->name_th }}</option>
                        @endforeach
                    </select>
                    @if($assigns->isEmpty())
                    <small style="color:#e53935">ยังไม่มีวิชา กรุณา "มอบหมายวิชา" ก่อน</small>
                    @endif
                </div>
                <div class="mfield">
                    <label>วัน *</label>
                    <select name="day_of_week" id="slotDay" required>
                        @foreach($days as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mrow">
                    <div class="mfield">
                        <label>เวลาเริ่ม *</label>
                        <input type="time" name="start_time" id="slotStart" required>
                    </div>
                    <div class="mfield">
                        <label>เวลาสิ้นสุด *</label>
                        <input type="time" name="end_time" id="slotEnd" required>
                    </div>
                </div>
                <div class="mfield">
                    <label>ห้องเรียน</label>
                    <input type="text" name="room" placeholder="เช่น 301, ห้องวิทย์">
                </div>
            </div>
            <div class="ts-modal-foot">
                <button type="button" class="btn-mcancel" onclick="closeSlotModal()">ยกเลิก</button>
                <button type="submit" class="btn-msave" {{ $assigns->isEmpty()?'disabled':'' }}>
                    <i class="bi bi-check-lg me-1"></i>บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal มอบหมายวิชา --}}
<div class="ts-overlay" id="assignModal" onclick="if(event.target===this)closeAssignModal()">
    <div class="ts-modal">
        <div class="ts-modal-head"><i class="bi bi-person-workspace me-2" style="color:#1e88e5"></i>มอบหมายวิชาให้ครู</div>
        <form method="POST" action="{{ route('timetable.storeAssign') }}">
            @csrf
            <input type="hidden" name="semester_id" value="{{ $section->semester_id }}">
            <input type="hidden" name="section_id" value="{{ $section->section_id }}">
            <div class="ts-modal-body">
                <div class="mfield">
                    <label>ครูผู้สอน *</label>
                    <select name="personnel_id" required>
                        <option value="">-- เลือก --</option>
                        @foreach($teachers as $t)
                        <option value="{{ $t->personnel_id }}">{{ $t->thai_firstname }} {{ $t->thai_lastname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mfield">
                    <label>วิชา *</label>
                    <select name="subject_id" required>
                        <option value="">-- เลือก --</option>
                        @foreach($subjects as $sub)
                        <option value="{{ $sub->subject_id }}">{{ $sub->code }} — {{ $sub->name_th }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="ts-modal-foot">
                <button type="button" class="btn-mcancel" onclick="closeAssignModal()">ยกเลิก</button>
                <button type="submit" class="btn-mblue">มอบหมาย</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal นำเข้าแผนการเรียน --}}
<div class="ts-overlay" id="importModal" onclick="if(event.target===this)closeImportModal()">
    <div class="ts-modal" style="width:580px">
        <div class="ts-modal-head" style="background:#7b1fa2;color:#fff;border-radius:14px 14px 0 0">
            <i class="bi bi-journal-bookmark me-2"></i>นำเข้าวิชาจากแผนการเรียน
        </div>
        <form method="POST" action="{{ route('timetable.importCurriculum', $section->section_id) }}" id="importForm">
            @csrf
            <div class="ts-modal-body" style="max-height:70vh;overflow-y:auto">
                <div class="mfield">
                    <label>เลือกแผนการเรียน *</label>
                    <select id="curriculumSelect" name="curriculum_id" onchange="loadCurriculumSubjects(this.value)" style="width:100%;height:38px;border:1.5px solid #ddd;border-radius:8px;padding:0 12px;font-size:0.85rem;font-family:inherit">
                        <option value="">-- เลือกแผนการเรียน --</option>
                        @foreach($curriculums as $cur)
                        <option value="{{ $cur->curriculum_id }}">
                            {{ $cur->name }} (ปี {{ $cur->year_applied }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div id="subjectRows" style="display:none;margin-top:8px">
                    <div style="font-size:0.8rem;font-weight:700;color:#555;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid #eee">
                        รายวิชาในแผน — กำหนดครูผู้สอนแต่ละวิชา (เว้นว่างไว้ = ข้ามวิชานั้น)
                    </div>
                    <div id="subjectList"></div>
                </div>
                <div id="noCurriculum" style="display:none;text-align:center;padding:24px;color:#aaa">
                    <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                    ไม่มีวิชาในแผนการเรียนนี้
                </div>
            </div>
            <div class="ts-modal-foot">
                <button type="button" class="btn-mcancel" onclick="closeImportModal()">ยกเลิก</button>
                <button type="submit" id="importBtn" class="btn-msave" style="background:#7b1fa2" disabled>
                    <i class="bi bi-check-lg me-1"></i>นำเข้าวิชา
                </button>
            </div>
        </form>
    </div>
</div>

@php
$curriculumJson = $curriculums->mapWithKeys(function($c) {
    return [$c->curriculum_id => [
        'id' => $c->curriculum_id,
        'subjects' => $c->curriculumSubjects->map(function($cs) {
            return [
                'subject_id'   => $cs->subject_id,
                'code'         => $cs->subject->code ?? '',
                'name_th'      => $cs->subject->name_th ?? '',
                'sem_type'     => $cs->semester_type ?? '',
                'personnel_id' => $cs->personnel_id,
            ];
        })->values()
    ]];
});
$teacherJson = $teachers->map(function($t) {
    return ['id' => $t->personnel_id, 'name' => $t->thai_firstname . ' ' . $t->thai_lastname];
})->values();
@endphp
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const curriculumData = @json($curriculumJson);
const teachers = @json($teacherJson);

function loadCurriculumSubjects(curId) {
    const subjectRows = document.getElementById('subjectRows');
    const noCur       = document.getElementById('noCurriculum');
    const importBtn   = document.getElementById('importBtn');
    const list        = document.getElementById('subjectList');

    if (!curId) {
        subjectRows.style.display = 'none';
        noCur.style.display = 'none';
        importBtn.disabled = true;
        return;
    }

    const cur = curriculumData[curId];
    if (!cur || !cur.subjects.length) {
        subjectRows.style.display = 'none';
        noCur.style.display = 'block';
        importBtn.disabled = true;
        return;
    }

    const teacherOptions = teachers.map(t =>
        `<option value="${t.id}">${t.name}</option>`
    ).join('');

    list.innerHTML = cur.subjects.map(s => {
        const opts = teachers.map(t =>
            `<option value="${t.id}" ${s.personnel_id == t.id ? 'selected' : ''}>${t.name}</option>`
        ).join('');
        return `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;align-items:center">
            <div>
                <div style="font-size:0.82rem;font-weight:700;color:#333">${s.code}</div>
                <div style="font-size:0.78rem;color:#666">${s.name_th}</div>
            </div>
            <select name="personnel_ids[${s.subject_id}]"
                style="height:34px;border:1.5px solid #ddd;border-radius:7px;padding:0 8px;font-size:0.82rem;font-family:inherit;width:100%">
                <option value="">-- ข้ามวิชานี้ --</option>
                ${opts}
            </select>
        </div>
    `}).join('');

    subjectRows.style.display = 'block';
    noCur.style.display = 'none';
    importBtn.disabled = false;
}

function openImportModal()  {
    document.getElementById('curriculumSelect').value = '';
    document.getElementById('subjectRows').style.display = 'none';
    document.getElementById('noCurriculum').style.display = 'none';
    document.getElementById('importBtn').disabled = true;
    document.getElementById('importModal').classList.add('open');
}
function closeImportModal() { document.getElementById('importModal').classList.remove('open'); }
</script>
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded',()=>{
    Swal.fire({icon:'success',title:'สำเร็จ!',text:"{{ session('success') }}",timer:2000,showConfirmButton:false});
});
</script>
@endif
<script>
function openSlotModal(day, start, end) {
    if(day)  document.getElementById('slotDay').value  = day;
    if(start) document.getElementById('slotStart').value = start;
    if(end)   document.getElementById('slotEnd').value   = end;
    document.getElementById('slotModal').classList.add('open');
}
function closeSlotModal()  { document.getElementById('slotModal').classList.remove('open'); }
function openAssignModal() { document.getElementById('assignModal').classList.add('open'); }
function closeAssignModal(){ document.getElementById('assignModal').classList.remove('open'); }
document.addEventListener('keydown', e => {
    if(e.key==='Escape'){ closeSlotModal(); closeAssignModal(); }
});
</script>
@endpush
@endsection
