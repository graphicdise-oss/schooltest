@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
@php
    $competencyLevels = ['', 'ดีเยี่ยม', 'ดี', 'ผ่าน'];
    $readLabels = \App\Models\Academic\SubjectAssessment::READ_LABELS;
    $charRefLabels = \App\Models\Academic\SubjectAssessment::CHAR_REFERENCE_LABELS;
@endphp
<div class="ac-page">
    <nav class="ac-breadcrumb">
        <a href="{{ route('por5.index') }}">ปพ.5</a><i class="bi bi-chevron-right"></i>
        <span>{{ $assign->subject->name_th }} — {{ $assign->classSection->full_name ?? '' }}</span>
    </nav>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-list-ol"></i> จำนวนหน่วยการเรียน (สำหรับประเมินคุณลักษณะอันพึงประสงค์)</div>
        <div class="ac-card-body">
            <form method="POST" action="{{ route('por5.saveUnitCount', $assign->assign_id) }}" style="display:flex; align-items:center; gap:10px;">
                @csrf
                <label style="margin:0;">จำนวนหน่วยที่ของวิชานี้ทั้งหมด</label>
                <input type="number" name="char_unit_count" class="ac-input" style="width:90px;" min="1" max="30" value="{{ $unitCount }}">
                <button type="submit" class="ac-btn ac-btn-secondary"><i class="bi bi-check-lg"></i> บันทึกจำนวนหน่วย</button>
                <span class="text-muted" style="font-size:.85rem;">เปลี่ยนจำนวนหน่วยจะไม่ลบคะแนนที่กรอกไว้ แต่หน่วยที่เกินจะไม่แสดงผล</span>
            </form>
        </div>
    </div>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-clipboard-data"></i> ผลการประเมินคุณภาพผู้เรียนรายวิชา</div>
        <div class="ac-card-body">
            @if($students->isEmpty())
                <p class="ac-empty">ไม่มีนักเรียนในห้องนี้</p>
            @else
                <p class="text-muted" style="margin-bottom:12px;">กด "แก้ไขรายข้อ" เพื่อกรอกคุณลักษณะอันพึงประสงค์ (รายหน่วยที่ {{ $unitCount }} หน่วย) และการอ่านคิดวิเคราะห์และเขียน (5 ข้อ) — ระบบคำนวณผลรวมให้อัตโนมัติตามเกณฑ์ สพฐ.</p>
                <form method="POST" action="{{ route('por5.saveAssessment', $assign->assign_id) }}" id="assessForm">
                    @csrf
                    <div class="ac-table-wrap">
                        <table class="ac-table">
                            <thead>
                                <tr>
                                    <th style="width:55px;">เลขที่</th>
                                    <th style="width:110px;">รหัส</th>
                                    <th style="text-align:left;">ชื่อ - สกุล</th>
                                    <th style="width:200px;">คุณลักษณะอันพึงประสงค์</th>
                                    <th style="width:200px;">การอ่านคิดวิเคราะห์ฯ</th>
                                    <th style="width:150px;">สมรรถนะสำคัญของผู้เรียน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $s)
                                    @php
                                        $a = $assessments->get($s->student_id);
                                        $charUnits = $a->char_units ?? [];
                                    @endphp
                                    <tr>
                                        <td>{{ $s->student_number }}</td>
                                        <td>{{ $s->student->student_code }}</td>
                                        <td style="text-align:left">{{ $s->student->thai_prefix }}{{ $s->student->thai_firstname }} {{ $s->student->thai_lastname }}</td>
                                        <td>
                                            <div class="assess-cell">
                                                <span class="assess-preview" id="char-preview-{{ $s->student_id }}">{{ $a->desired_char ?? '—' }}</span>
                                                <button type="button" class="ac-action-btn" onclick="openItemModal('char', {{ $s->student_id }}, '{{ addslashes($s->student->thai_prefix . $s->student->thai_firstname . ' ' . $s->student->thai_lastname) }}')"><i class="bi bi-pencil"></i> แก้ไขรายข้อ</button>
                                            </div>
                                            @foreach(range(1,$unitCount) as $i)
                                                <input type="hidden" name="assess[{{ $s->student_id }}][char][{{ $i }}]" id="char-{{ $i }}-{{ $s->student_id }}" value="{{ $charUnits[$i-1] ?? '' }}">
                                            @endforeach
                                        </td>
                                        <td>
                                            <div class="assess-cell">
                                                <span class="assess-preview" id="read-preview-{{ $s->student_id }}">{{ $a->reading_thinking ?? '—' }}</span>
                                                <button type="button" class="ac-action-btn" onclick="openItemModal('read', {{ $s->student_id }}, '{{ addslashes($s->student->thai_prefix . $s->student->thai_firstname . ' ' . $s->student->thai_lastname) }}')"><i class="bi bi-pencil"></i> แก้ไขรายข้อ</button>
                                            </div>
                                            @foreach(range(1,5) as $i)
                                                <input type="hidden" name="assess[{{ $s->student_id }}][read][{{ $i }}]" id="read-{{ $i }}-{{ $s->student_id }}" value="{{ $a?->{"read_$i"} }}">
                                            @endforeach
                                        </td>
                                        <td>
                                            <select name="assess[{{ $s->student_id }}][competency]" class="ac-select">
                                                @foreach($competencyLevels as $opt)
                                                    <option value="{{ $opt }}" {{ ($a->competency ?? '') === $opt ? 'selected' : '' }}>{{ $opt === '' ? '—' : $opt }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="ac-save-wrap" style="margin-top:16px; text-align:right;">
                        <a href="{{ route('por5.print', $assign->assign_id) }}" target="_blank" class="ac-btn ac-btn-secondary"><i class="bi bi-printer"></i> พิมพ์ ปพ.5</a>
                        <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึกผลการประเมิน</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-info-circle"></i> เกณฑ์อ้างอิงคุณลักษณะอันพึงประสงค์ (8 ด้าน ตาม สพฐ.)</div>
        <div class="ac-card-body">
            <p class="text-muted" style="margin-bottom:6px;">คะแนนแต่ละหน่วยที่ให้ประเมินภาพรวมของนักเรียนตาม 8 ด้านนี้ในหน่วยการเรียนนั้นๆ:</p>
            <div class="char-ref-list">
                @foreach($charRefLabels as $i => $label)<span>{{ $i }}. {{ $label }}</span>@endforeach
            </div>
        </div>
    </div>
</div>

<div class="ac-overlay" id="itemOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="ac-modal">
        <div class="ac-modal-header"><i class="bi bi-pencil-square me-2"></i><span id="itemModalTitle">แก้ไขรายข้อ</span></div>
        <div class="ac-modal-body" id="itemModalBody"></div>
        <div class="ac-modal-footer">
            <button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('itemOverlay').classList.remove('active')">ยกเลิก</button>
            <button type="button" class="ac-btn ac-btn-primary" onclick="applyItemModal()"><i class="bi bi-check-lg"></i> ตกลง</button>
        </div>
    </div>
</div>

<script>
const UNIT_COUNT = {{ (int) $unitCount }};
const READ_LABELS = @json($readLabels);
const LEVEL_OPTS = [['', '—'], [3, 'ดีเยี่ยม (3)'], [2, 'ดี (2)'], [1, 'ผ่าน (1)'], [0, 'ไม่ผ่าน (0)']];

let currentModal = { type: null, studentId: null, count: 0 };

function openItemModal(type, studentId, studentName) {
    const isChar = type === 'char';
    const count = isChar ? UNIT_COUNT : 5;
    currentModal = { type, studentId, count };

    document.getElementById('itemModalTitle').textContent = (isChar ? 'คุณลักษณะอันพึงประสงค์ (รายหน่วยที่)' : 'การอ่านคิดวิเคราะห์และเขียน') + ' — ' + studentName;

    let html = '';
    for (let i = 1; i <= count; i++) {
        const current = document.getElementById(`${type}-${i}-${studentId}`).value;
        let options = '';
        LEVEL_OPTS.forEach(([val, label]) => {
            const selected = String(val) === String(current) ? 'selected' : '';
            options += `<option value="${val}" ${selected}>${label}</option>`;
        });
        const label = isChar ? `หน่วยที่ ${i}` : `${i}. ${READ_LABELS[i]}`;
        html += `<div class="ac-field" style="margin-bottom:8px">
            <label>${label}</label>
            <select class="ac-select" id="modal-item-${i}">${options}</select>
        </div>`;
    }
    document.getElementById('itemModalBody').innerHTML = html;
    document.getElementById('itemOverlay').classList.add('active');
}

// สอดคล้องกับ SubjectAssessment::computeDesiredCharLevel() ฝั่งเซิร์ฟเวอร์ (สูตรสัดส่วนครึ่ง/เกินครึ่ง)
function computeDesiredCharLevel(items) {
    if (items.some(v => v === null || v === '')) return null;
    items = items.map(Number);
    const n = items.length;
    const min = Math.min(...items);
    if (min === 0) return 0;
    const excellent = items.filter(v => v === 3).length;
    const half = Math.floor(n / 2);
    const majority = half + 1;
    if (excellent >= majority && min >= 2) return 3;
    if (excellent >= 1 && min >= 2) return 2;
    if (excellent === half && min >= 1) return 2;
    if (excellent >= majority && min >= 1) return 2;
    if (min >= 1) return 1;
    return 0;
}

function computeReadingThinkingLevel(items) {
    if (items.some(v => v === null || v === '')) return null;
    return Math.min(...items.map(Number));
}

const LEVEL_LABEL = { 3: 'ดีเยี่ยม', 2: 'ดี', 1: 'ผ่าน', 0: 'ไม่ผ่าน' };

function applyItemModal() {
    const { type, studentId, count } = currentModal;
    const items = [];
    for (let i = 1; i <= count; i++) {
        const val = document.getElementById(`modal-item-${i}`).value;
        document.getElementById(`${type}-${i}-${studentId}`).value = val;
        items.push(val === '' ? null : val);
    }
    const level = type === 'char' ? computeDesiredCharLevel(items) : computeReadingThinkingLevel(items);
    document.getElementById(`${type}-preview-${studentId}`).textContent = level === null ? '—' : LEVEL_LABEL[level];
    document.getElementById('itemOverlay').classList.remove('active');
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.ac-overlay.active').forEach(el => el.classList.remove('active')); });
</script>

<style>
.assess-cell { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }
.assess-preview { font-weight: 600; }
.char-ref-list { display:flex; flex-wrap:wrap; gap:6px 20px; font-size:.88rem; color:#475569; }
</style>
@endsection
