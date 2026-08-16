@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page" x-data="{ tab: '{{ in_array(request('tab'), ['transfer','promote','graduate','opensem2']) ? request('tab') : 'transfer' }}' }">
    <nav class="ac-breadcrumb"><a href="#">ข้อมูลบุคคล</a><i class="bi bi-chevron-right"></i><span>ย้ายห้อง/เลื่อนชั้น/บันทึกจบ</span></nav>

    <div class="ac-card" style="overflow:visible">
        <div class="ac-card-header" style="display:flex; align-items:center; justify-content:space-between">
            <span><i class="bi bi-arrow-left-right"></i> ย้ายห้อง/เลื่อนชั้น/บันทึกจบ</span>
            <a href="{{ route('promotions.history') }}" class="ac-btn ac-btn-secondary ac-btn-sm"><i class="bi bi-clock-history"></i> ดูประวัติการเลื่อนชั้น/ย้ายห้อง</a>
        </div>
        <div class="ac-card-body">

            {{-- Tabs --}}
            <div class="ac-tabs">
                <button @click="tab='transfer'" :class="tab==='transfer'?'ac-tab-active':''" class="ac-tab"><i class="bi bi-arrow-left-right me-1"></i>ย้ายห้องเรียน</button>
                <button @click="tab='promote'" :class="tab==='promote'?'ac-tab-active':''" class="ac-tab"><i class="bi bi-arrow-up-circle me-1"></i>เลื่อนระดับชั้นเรียน</button>
                <button @click="tab='graduate'" :class="tab==='graduate'?'ac-tab-active':''" class="ac-tab"><i class="bi bi-mortarboard me-1"></i>บันทึกสำเร็จการศึกษา</button>
                <button @click="tab='opensem2'" :class="tab==='opensem2'?'ac-tab-active':''" class="ac-tab"><i class="bi bi-copy me-1"></i>เปิดเทอม 2</button>
            </div>

            {{-- ===== Tab 1: ย้ายห้อง ===== --}}
            <div x-show="tab==='transfer'" x-cloak>
                <form method="POST" action="{{ route('promotions.transfer') }}">
                    @csrf
                    <div style="display:grid; grid-template-columns:1fr auto 1fr; gap:16px; align-items:start">

                        {{-- ซ้าย: ห้องต้นทาง --}}
                        <div class="transfer-panel">
                            <h6>ข้อมูลนักเรียน (ห้องต้นทาง)</h6>
                            <div style="display:flex; gap:14px; margin-bottom:12px; flex-wrap:wrap">
                                <div class="ac-field" style="width:150px; flex:none">
                                    <label>ปีการศึกษา</label>
                                    <select id="filterYear" class="ac-select" onchange="filterSemesterByYear()">
                                        @foreach($academicYears as $y)<option value="{{ $y->year_id }}" {{ (string)$yearId===(string)$y->year_id?'selected':'' }}>{{ $y->year_name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="ac-field" style="width:150px; flex:none">
                                    <label>ภาคการศึกษา</label>
                                    <select id="filterSemester" class="ac-select" onchange="window.location='?tab=transfer&semester_id='+this.value">
                                        @foreach($semesters as $sem)<option value="{{ $sem->semester_id }}" data-year="{{ $sem->year_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>เทอม {{ $sem->semester_name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="ac-field" style="width:140px; flex:none">
                                    <label>ระดับ</label>
                                    <select id="fromLevel" class="ac-select" onchange="filterFromRoomsByLevel()">
                                        <option value="">ทุกระดับชั้น</option>
                                        @foreach($levels as $lv)<option value="{{ $lv->level_id }}">{{ $lv->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="ac-field" style="flex:1; min-width:180px">
                                    <label>ห้อง</label>
                                    <select name="from_section_id" id="fromSection" class="ac-select" onchange="filterStudents()">
                                        <option value="">เลือกห้อง</option>
                                        @foreach($fromSections as $sec)<option value="{{ $sec->section_id }}" data-level="{{ $sec->level_id }}" data-students='@json($sec->studentSections->map(fn($ss)=>["id"=>$ss->student_id,"num"=>$ss->student_number,"name"=>$ss->student->thai_prefix.$ss->student->thai_firstname." ".$ss->student->thai_lastname]))'>{{ $sec->full_name }} ({{ $sec->studentSections->count() }} คน)</option>@endforeach
                                    </select>
                                </div>
                            </div>
                            <ul class="transfer-list" id="fromList"></ul>
                        </div>

                        {{-- ปุ่มกลาง --}}
                        <div class="transfer-buttons">
                            <button type="button" class="ac-btn ac-btn-primary ac-btn-sm" onclick="moveAll()" style="margin-top:100px">ทั้งหมด &gt;&gt;</button>
                            <button type="button" class="ac-btn ac-btn-secondary ac-btn-sm" onclick="moveSelected()">&gt;</button>
                            <button type="button" class="ac-btn ac-btn-secondary ac-btn-sm" onclick="moveBack()">&lt;</button>
                        </div>

                        {{-- ขวา: ห้องปลายทาง --}}
                        <div class="transfer-panel">
                            <h6>ข้อมูลนักเรียนในชั้นเรียน (ห้องปลายทาง)</h6>
                            <div style="display:flex; gap:14px; margin-bottom:12px; flex-wrap:wrap">
                                <div class="ac-field" style="width:140px; flex:none">
                                    <label>ระดับ</label>
                                    <select id="toLevel" class="ac-select" onchange="filterToRoomsByLevel()">
                                        <option value="">ทุกระดับชั้น</option>
                                        @foreach($levels as $lv)<option value="{{ $lv->level_id }}">{{ $lv->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="ac-field" style="flex:1; min-width:180px"><label>ห้องปลายทาง</label>
                                    <select name="to_section_id" id="toSection" class="ac-select">
                                        <option value="">เลือกห้อง</option>
                                        @foreach($fromSections as $sec)<option value="{{ $sec->section_id }}" data-level="{{ $sec->level_id }}">{{ $sec->full_name }}</option>@endforeach
                                    </select>
                                </div>
                            </div>
                            <ul class="transfer-list" id="toList"></ul>
                            <p style="font-size:0.8rem; color:#888; margin-top:8px">จำนวนนักเรียน <span id="toCount">0</span> คน</p>
                        </div>
                    </div>
                    <div class="ac-save-wrap"><button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button></div>
                </form>
            </div>

            {{-- ===== Tab 2: เลื่อนชั้น ===== --}}
            <div x-show="tab==='promote'" x-cloak>
                <form method="POST" action="{{ route('promotions.promote') }}">
                    @csrf
                    <div style="display:grid; grid-template-columns:1fr auto 1fr; gap:16px; align-items:start">
                        <div class="transfer-panel">
                            <h6>ห้องเดิม</h6>
                            <div class="ac-field" style="margin-bottom:12px"><label>ภาคการศึกษา</label>
                                <select class="ac-select" onchange="window.location='{{ route('promotions.index') }}?tab=promote&semester_id='+this.value">
                                    @foreach($semesters as $sem)<option value="{{ $sem->semester_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>{{ $sem->academicYear?->year_name ?? '' }} เทอม {{ $sem->semester_name }}</option>@endforeach
                                </select>
                                <p style="font-size:.78rem;color:#999;margin:6px 0 0">เลือกเทอมอื่นได้ เผื่อมีนักเรียนบางคนยังค้างอยู่ห้องของเทอมก่อนหน้า</p>
                            </div>
                            <div class="ac-field" style="margin-bottom:12px"><label>ระดับ</label>
                                <select id="promoteFromLevel" class="ac-select" onchange="filterPromoteRoomsByLevel()">
                                    <option value="">ทุกระดับชั้น</option>
                                    @foreach($levels as $lv)<option value="{{ $lv->level_id }}">{{ $lv->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="ac-field" style="margin-bottom:12px"><label>เลือกห้อง</label>
                                <select name="from_section_id" id="promoteFrom" class="ac-select" onchange="filterPromoteStudents()">
                                    <option value="">เลือกห้อง</option>
                                    @foreach($fromSections as $sec)<option value="{{ $sec->section_id }}" data-level="{{ $sec->level_id }}" data-students='@json($sec->studentSections->map(fn($ss)=>["id"=>$ss->student_id,"num"=>$ss->student_number,"name"=>$ss->student->thai_prefix.$ss->student->thai_firstname." ".$ss->student->thai_lastname]))'>{{ $sec->full_name }}</option>@endforeach
                                </select>
                            </div>
                            <ul class="transfer-list" id="promoteFromList"></ul>
                        </div>

                        <div class="transfer-buttons">
                            <button type="button" class="ac-btn ac-btn-primary ac-btn-sm" onclick="promoteAll()" style="margin-top:80px">ทั้งหมด &gt;&gt;</button>
                            <button type="button" class="ac-btn ac-btn-secondary ac-btn-sm" onclick="promoteSelected()">&gt;</button>
                        </div>

                        <div class="transfer-panel">
                            <h6>ห้องใหม่ (ปีการศึกษาถัดไป)</h6>
                            @if($nextSemester)
                            <p style="font-size:.78rem;color:#999;margin:0 0 10px">เทอมปลายทาง: {{ $nextSemester->academicYear?->year_name ?? '' }} เทอม {{ $nextSemester->semester_name ?? '' }} (เลื่อนชั้นข้ามปีการศึกษาเสมอ จะข้ามไปเทอม 2 ของปีเดียวกันไม่ได้)</p>
                            <div class="ac-field" style="margin-bottom:12px"><label>เลือกห้อง</label>
                                <select name="to_section_id" id="promoteToSection" class="ac-select">
                                    <option value="">เลือกห้องเดิมก่อน</option>
                                    @foreach($toSections as $sec)<option value="{{ $sec->section_id }}" data-level="{{ $sec->level_id }}" hidden>{{ $sec->full_name }}</option>@endforeach
                                </select>
                                <p style="font-size:.78rem;color:#999;margin:6px 0 0">แสดงเฉพาะห้องของระดับชั้นถัดไปเท่านั้น (เลือกห้องเดิมก่อนถึงจะเห็นตัวเลือก)</p>
                                <p id="promoteToEmptyHint" style="font-size:.78rem;color:#dc3545;margin:6px 0 0;display:none">⚠ ยังไม่มีห้องของระดับนี้ในเทอม "{{ $nextSemester->academicYear?->year_name ?? '' }} เทอม {{ $nextSemester->semester_name ?? '' }}" กรุณาไปสร้างห้องเรียนที่หน้า "จัดการห้องเรียน" ก่อน</p>
                                <p id="promoteToNoNextLevelHint" style="font-size:.78rem;color:#dc3545;margin:6px 0 0;display:none">⚠ ระดับชั้นนี้เป็นระดับสุดท้ายในระบบแล้ว ไม่มีระดับถัดไปให้เลื่อนขึ้น — ถ้าจบการศึกษาแล้ว ให้ใช้แท็บ "บันทึกสำเร็จการศึกษา" แทน</p>
                            </div>
                            @else
                            <div class="ac-empty">ยังไม่มีปีการศึกษาถัดไป กรุณาสร้างปีการศึกษาใหม่ก่อน</div>
                            @endif
                            <ul class="transfer-list" id="promoteToList"></ul>
                        </div>
                    </div>
                    <div class="ac-save-wrap"><button type="submit" class="ac-btn ac-btn-success"><i class="bi bi-arrow-up-circle"></i> เลื่อนชั้น</button></div>
                </form>
            </div>

            {{-- ===== Tab 3: บันทึกจบ ===== --}}
            <div x-show="tab==='graduate'" x-cloak>
                <form method="POST" action="{{ route('promotions.graduate') }}">
                    @csrf
                    <div class="ac-grid-2" style="margin-bottom:16px">
                        <div class="ac-field"><label>ระดับ</label>
                            <select id="gradLevel" class="ac-select" onchange="filterGradRoomsByLevel()">
                                <option value="">เลือกระดับชั้นเรียน</option>
                                @foreach($graduateLevels as $lv)<option value="{{ $lv->level_id }}">{{ $lv->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="ac-field"><label>เลือกห้อง</label>
                            <select name="from_section_id" id="gradFrom" class="ac-select" onchange="filterGradStudents()">
                                <option value="">เลือกห้อง</option>
                                @foreach($graduateSections as $sec)<option value="{{ $sec->section_id }}" data-level="{{ $sec->level_id }}" data-students='@json($sec->studentSections->map(fn($ss)=>["id"=>$ss->student_id,"num"=>$ss->student_number,"name"=>$ss->student->thai_prefix.$ss->student->thai_firstname." ".$ss->student->thai_lastname]))'>{{ $sec->full_name }}</option>@endforeach
                            </select>
                            <p style="font-size:.78rem;color:#999;margin:6px 0 0">แสดงเฉพาะห้องของชั้นปีสุดท้ายในแต่ละช่วงชั้น (ป.6 / ม.3 / ม.6) เท่านั้น</p>
                        </div>
                        <div class="ac-field"><label>หมายเหตุ</label><input type="text" name="remark" class="ac-input" placeholder="เช่น จบหลักสูตรปี 2567"></div>
                    </div>

                    <div class="ac-table-wrap">
                        <table class="ac-table">
                            <thead><tr><th><input type="checkbox" id="gradCheckAll" onchange="toggleGradAll(this)"></th><th>เลขที่</th><th>ชื่อ-นามสกุล</th></tr></thead>
                            <tbody id="gradBody">
                                <tr><td colspan="3" class="ac-empty">เลือกห้องเรียนก่อน</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="ac-save-wrap"><button type="submit" class="ac-btn ac-btn-danger"><i class="bi bi-mortarboard"></i> บันทึกสำเร็จการศึกษา</button></div>
                </form>
            </div>

            {{-- ===== Tab 4: เปิดเทอม 2 ===== --}}
            <div x-show="tab==='opensem2'" x-cloak>
                <form method="POST" action="{{ route('promotions.openSemester2') }}">
                    @csrf
                    <p style="font-size:.85rem; color:#666; margin-bottom:14px">
                        คัดลอกห้อง + แผนการเรียน + ครูประจำชั้น จาก "เทอม 1" ไปสร้างเป็นห้องใหม่ใน "เทอม 2" ของปีการศึกษาเดียวกัน —
                        <strong>ไม่คัดลอกตารางสอนและไม่คัดลอกรายชื่อนักเรียน</strong> (ห้องใหม่จะว่างเปล่า รอจัดตารางสอน/ลงทะเบียนนักเรียนทีหลัง)
                        ถ้าห้องนั้นมีอยู่ในเทอม 2 อยู่แล้ว ระบบจะข้ามให้อัตโนมัติ ไม่สร้างซ้ำ
                    </p>
                    <div class="ac-grid-4" style="margin-bottom:16px">
                        <div class="ac-field"><label>ปีการศึกษา *</label>
                            <select name="year_id" id="os2Year" class="ac-select" onchange="filterOs2Sections()" required>
                                <option value="">-- เลือกปีการศึกษา --</option>
                                @foreach($academicYears as $y)<option value="{{ $y->year_id }}">{{ $y->year_name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="ac-field"><label>ระดับ</label>
                            <select id="os2Level" class="ac-select" onchange="filterOs2Sections()">
                                <option value="">ทุกระดับชั้น</option>
                                @foreach($levels as $lv)<option value="{{ $lv->level_id }}">{{ $lv->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>

                    <div class="ac-table-wrap">
                        <table class="ac-table">
                            <thead><tr><th style="width:40px"><input type="checkbox" id="os2CheckAll" onchange="toggleOs2All(this)"></th><th>ห้อง (เทอม 1)</th><th>แผนการเรียน</th></tr></thead>
                            <tbody id="os2Body">
                                <tr><td colspan="3" class="ac-empty">เลือกปีการศึกษาก่อน</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="ac-save-wrap"><button type="submit" class="ac-btn ac-btn-success"><i class="bi bi-copy"></i> เปิดเทอม 2 (คัดลอกห้องที่เลือก)</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ===== เปิดเทอม 2 =====
const term1Sections = @json($term1Sections->map(fn($s) => [
    'section_id' => $s->section_id,
    'year_id'    => $s->semester?->year_id,
    'level_id'   => $s->level_id,
    'label'      => $s->full_name,
    'study_plan' => $s->study_plan,
])->values());

function filterOs2Sections() {
    const yearId = document.getElementById('os2Year').value;
    const levelId = document.getElementById('os2Level').value;
    const body = document.getElementById('os2Body');
    if (!yearId) {
        body.innerHTML = '<tr><td colspan="3" class="ac-empty">เลือกปีการศึกษาก่อน</td></tr>';
        return;
    }
    const filtered = term1Sections.filter(s => String(s.year_id) === yearId && (!levelId || String(s.level_id) === levelId));
    body.innerHTML = filtered.length
        ? filtered.map(s => `<tr><td><input type="checkbox" name="section_ids[]" value="${s.section_id}" class="os2-cb"></td><td style="text-align:left">${s.label}</td><td>${s.study_plan || '-'}</td></tr>`).join('')
        : '<tr><td colspan="3" class="ac-empty">ไม่มีห้องของเทอม 1 ในปีการศึกษานี้</td></tr>';
}
function toggleOs2All(el) { document.querySelectorAll('.os2-cb').forEach(cb => cb.checked = el.checked); }
</script>

<script>
// ===== เลือกปีการศึกษา -> กรองภาคการศึกษา =====
function filterSemesterByYear() {
    const yearId = document.getElementById('filterYear').value;
    const semSelect = document.getElementById('filterSemester');
    let currentStillVisible = false;
    Array.from(semSelect.options).forEach(opt => {
        const matches = opt.dataset.year === yearId;
        opt.hidden = !matches;
        if (matches && opt.selected) currentStillVisible = true;
    });
    if (!currentStillVisible) {
        const firstVisible = Array.from(semSelect.options).find(opt => !opt.hidden);
        if (firstVisible) window.location = '?tab=transfer&semester_id=' + firstVisible.value;
    }
}

// ===== ย้ายห้อง =====
let fromStudents = [], toStudents = [];

function filterLevelRoomOptions(levelSelectId, roomSelectId, onCleared) {
    const levelId = document.getElementById(levelSelectId).value;
    const roomSelect = document.getElementById(roomSelectId);
    let currentStillVisible = false;
    Array.from(roomSelect.options).forEach(opt => {
        if (!opt.value) { opt.hidden = false; return; }
        const matches = !levelId || opt.dataset.level === levelId;
        opt.hidden = !matches;
        if (matches && opt.selected) currentStillVisible = true;
    });
    if (!currentStillVisible) {
        roomSelect.value = '';
        if (onCleared) onCleared();
    }
}
function filterFromRoomsByLevel() { filterLevelRoomOptions('fromLevel', 'fromSection', filterStudents); }
function filterToRoomsByLevel() { filterLevelRoomOptions('toLevel', 'toSection', null); }

function filterStudents() {
    const sel = document.getElementById('fromSection');
    const opt = sel.options[sel.selectedIndex];
    fromStudents = opt.dataset.students ? JSON.parse(opt.dataset.students) : [];
    toStudents = [];
    renderTransferLists();
}
const byStudentNum = (a, b) => (a.num ?? 0) - (b.num ?? 0);

function renderTransferLists() {
    fromStudents.sort(byStudentNum);
    toStudents.sort(byStudentNum);
    document.getElementById('fromList').innerHTML = fromStudents.map(s => `<li><input type="checkbox" class="from-cb" value="${s.id}"> ${s.num}. ${s.name}</li>`).join('');
    document.getElementById('toList').innerHTML = toStudents.map(s => `<li><input type="hidden" name="student_ids[]" value="${s.id}">${s.num}. ${s.name}</li>`).join('');
    document.getElementById('toCount').textContent = toStudents.length;
}
function moveAll() { toStudents = [...toStudents, ...fromStudents]; fromStudents = []; renderTransferLists(); }
function moveSelected() {
    document.querySelectorAll('.from-cb:checked').forEach(cb => {
        const s = fromStudents.find(x => x.id == cb.value);
        if (s) { toStudents.push(s); fromStudents = fromStudents.filter(x => x.id != cb.value); }
    });
    renderTransferLists();
}
function moveBack() { fromStudents = [...fromStudents, ...toStudents]; toStudents = []; renderTransferLists(); }

// ===== เลื่อนชั้น =====
const nextLevelMap = @json($nextLevelMap ?? []);
let promoteFrom = [], promoteTo = [];

function filterPromoteRoomsByLevel() {
    const levelId = document.getElementById('promoteFromLevel').value;
    const roomSelect = document.getElementById('promoteFrom');
    let currentStillVisible = false;
    Array.from(roomSelect.options).forEach(opt => {
        if (!opt.value) { opt.hidden = false; return; }
        const matches = !levelId || opt.dataset.level === levelId;
        opt.hidden = !matches;
        if (matches && opt.selected) currentStillVisible = true;
    });
    if (!currentStillVisible) {
        roomSelect.value = '';
        filterPromoteStudents();
    }
}

function filterPromoteStudents() {
    const sel = document.getElementById('promoteFrom');
    const opt = sel.options[sel.selectedIndex];
    promoteFrom = opt.dataset.students ? JSON.parse(opt.dataset.students) : [];
    promoteTo = [];
    renderPromoteLists();
    filterPromoteToRooms(opt.value ? opt.dataset.level : null);
}

// จำกัดห้องปลายทางให้เห็นเฉพาะระดับชั้น "ถัดไป" ของห้องต้นทางที่เลือกไว้เท่านั้น กันเลื่อนข้ามชั้น
function filterPromoteToRooms(fromLevelId) {
    const toSelect = document.getElementById('promoteToSection');
    if (!toSelect) return;
    // ระดับสุดท้ายในระบบ (เช่น ม.6) จะไม่มี key นี้เลยใน nextLevelMap หรือค่าเป็น null ตรงๆ
    // ต่างจากกรณีมีระดับถัดไปแต่แค่ยังไม่มีห้องสร้างไว้ในเทอมนั้น (nextLevelId จะมีค่าแต่หาห้องไม่เจอ)
    const rawNext = fromLevelId ? nextLevelMap[fromLevelId] : undefined;
    const isTerminalLevel = !!fromLevelId && (rawNext === null || rawNext === undefined);
    const nextLevelId = isTerminalLevel ? '' : String(rawNext);
    let currentStillVisible = false;
    let anyMatch = false;
    Array.from(toSelect.options).forEach(opt => {
        if (!opt.value) { opt.hidden = false; return; }
        const matches = !!nextLevelId && opt.dataset.level === nextLevelId;
        opt.hidden = !matches;
        if (matches) anyMatch = true;
        if (matches && opt.selected) currentStillVisible = true;
    });
    if (!currentStillVisible) toSelect.value = '';

    const hint = document.getElementById('promoteToEmptyHint');
    const noNextHint = document.getElementById('promoteToNoNextLevelHint');
    if (noNextHint) noNextHint.style.display = isTerminalLevel ? 'block' : 'none';
    if (hint) hint.style.display = (fromLevelId && !isTerminalLevel && !anyMatch) ? 'block' : 'none';
}

function renderPromoteLists() {
    promoteFrom.sort(byStudentNum);
    promoteTo.sort(byStudentNum);
    document.getElementById('promoteFromList').innerHTML = promoteFrom.map(s => `<li><input type="checkbox" class="promo-cb" value="${s.id}"> ${s.num}. ${s.name}</li>`).join('');
    document.getElementById('promoteToList').innerHTML = promoteTo.map(s => `<li><input type="hidden" name="student_ids[]" value="${s.id}">${s.num}. ${s.name}</li>`).join('');
}
function promoteAll() { promoteTo = [...promoteTo, ...promoteFrom]; promoteFrom = []; renderPromoteLists(); }
function promoteSelected() {
    document.querySelectorAll('.promo-cb:checked').forEach(cb => {
        const s = promoteFrom.find(x => x.id == cb.value);
        if (s) { promoteTo.push(s); promoteFrom = promoteFrom.filter(x => x.id != cb.value); }
    });
    renderPromoteLists();
}

// ===== จบการศึกษา =====
function filterGradRoomsByLevel() {
    const levelId = document.getElementById('gradLevel').value;
    const roomSelect = document.getElementById('gradFrom');
    let currentStillVisible = false;
    Array.from(roomSelect.options).forEach(opt => {
        if (!opt.value) { opt.hidden = false; return; }
        const matches = !levelId || opt.dataset.level === levelId;
        opt.hidden = !matches;
        if (matches && opt.selected) currentStillVisible = true;
    });
    if (!currentStillVisible) {
        roomSelect.value = '';
        filterGradStudents();
    }
}
function filterGradStudents() {
    const sel = document.getElementById('gradFrom');
    const opt = sel.options[sel.selectedIndex];
    const students = opt.dataset.students ? JSON.parse(opt.dataset.students) : [];
    document.getElementById('gradBody').innerHTML = students.length
        ? students.map(s => `<tr><td><input type="checkbox" name="student_ids[]" value="${s.id}" class="grad-cb"></td><td>${s.num}</td><td style="text-align:left">${s.name}</td></tr>`).join('')
        : '<tr><td colspan="3" class="ac-empty">ไม่มีนักเรียน</td></tr>';
}
function toggleGradAll(el) { document.querySelectorAll('.grad-cb').forEach(cb => cb.checked = el.checked); }
</script>

@if(session('success'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>Swal.fire({icon:'success',title:'สำเร็จ!',text:'{{ session("success") }}',timer:2000,showConfirmButton:false});</script>
@endif
@if(session('error'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>Swal.fire({icon:'error',title:'ทำรายการไม่สำเร็จ',text:'{{ session("error") }}'});</script>
@endif
@endsection