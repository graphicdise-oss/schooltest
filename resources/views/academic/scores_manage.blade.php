@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">

    {{-- Breadcrumb --}}
    <nav class="ac-breadcrumb">
        <a href="{{ route('scores.index') }}">วิชาการ</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('scores.index') }}">บันทึกคะแนน</a>
        <i class="bi bi-chevron-right"></i>
        <span>{{ $assign->subject->code }}</span>
    </nav>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div style="background:#d1fae5; border:1px solid #6ee7b7; border-radius:8px; padding:10px 16px; margin-bottom:16px; color:#065f46; font-size:0.85rem; display:flex; align-items:center; gap:8px;">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    {{-- ===== Card 1: Subject Info ===== --}}
    <div class="ac-card" style="margin-bottom:20px">
        <div class="ac-card-header">
            <span><i class="bi bi-book"></i> ข้อมูลวิชา / ห้องเรียน</span>
            <a href="{{ route('scores.index') }}" class="ac-btn ac-btn-secondary ac-btn-sm" style="color:#333">
                <i class="bi bi-arrow-left"></i> กลับ
            </a>
        </div>
        <div class="ac-card-body" style="padding:16px 20px">
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px 24px">
                <div>
                    <div style="font-size:0.75rem; color:#888; font-weight:600; margin-bottom:2px">วิชา</div>
                    <div style="font-size:0.9rem; font-weight:700; color:#1a1a2e">{{ $assign->subject->name_th }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:#888; font-weight:600; margin-bottom:2px">รหัสวิชา</div>
                    <div style="font-size:0.9rem; font-weight:700; color:#4479DA">{{ $assign->subject->code }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:#888; font-weight:600; margin-bottom:2px">ระดับชั้น / ห้อง</div>
                    <div style="font-size:0.9rem; font-weight:700; color:#333">{{ $assign->classSection->level->name }} / {{ $assign->classSection->section_number }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:#888; font-weight:600; margin-bottom:2px">ครูผู้สอน</div>
                    <div style="font-size:0.9rem; font-weight:700; color:#333">{{ $assign->personnel->thai_prefix ?? '' }}{{ $assign->personnel->thai_firstname }} {{ $assign->personnel->thai_lastname }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem; color:#888; font-weight:600; margin-bottom:2px">ภาคเรียน</div>
                    <div style="font-size:0.9rem; font-weight:700; color:#333">
                        @if($assign->classSection->semester ?? null)
                            {{ $assign->classSection->semester->academicYear->year_name ?? '' }} เทอม {{ $assign->classSection->semester->semester_name ?? '' }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Card 2: Score Category Setup ===== --}}
    <div class="ac-card" style="margin-bottom:20px">
        <div class="ac-card-header">
            <span><i class="bi bi-sliders"></i> ตั้งค่าสัดส่วนคะแนน</span>
            <div style="display:flex; gap:8px">
                @if($categories->count() === 0)
                <form method="POST" action="{{ route('scores.setup', $assign->assign_id) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="ac-btn ac-btn-secondary ac-btn-sm" style="color:#333">
                        <i class="bi bi-gear-fill"></i> ตั้งค่ามาตรฐาน
                    </button>
                </form>
                @endif
                <button class="ac-btn ac-btn-success ac-btn-sm" onclick="openCatModal()">
                    <i class="bi bi-plus"></i> เพิ่มหมวด
                </button>
            </div>
        </div>
        <div class="ac-card-body">
            @php
                $totalWeight = $categories->sum('weight_pct');
                $weightColor = ($totalWeight == 100) ? '#16a34a' : '#dc2626';
                $weightBg    = ($totalWeight == 100) ? '#dcfce7' : '#fee2e2';
            @endphp

            @if($categories->count() > 0)
            <div class="ac-table-wrap" style="margin-bottom:12px">
                <table class="ac-table">
                    <thead>
                        <tr>
                            <th>ชื่อหมวด</th>
                            <th>คะแนนเต็ม</th>
                            <th>น้ำหนัก (%)</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                        <tr>
                            <td style="text-align:left">{{ $cat->name }}</td>
                            <td>{{ $cat->max_score }}</td>
                            <td>{{ $cat->weight_pct }}%</td>
                            <td>
                                <button class="ac-action-btn ac-action-edit" title="แก้ไข"
                                    onclick="openEditModal({{ $cat->category_id }}, '{{ addslashes($cat->name) }}', {{ $cat->max_score }}, {{ $cat->weight_pct }}, {{ $cat->sort_order ?? 0 }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('scores.destroyCategory', $cat->category_id) }}" method="POST" style="display:inline"
                                    onsubmit="return confirm('ลบหมวด «{{ $cat->name }}» ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ac-action-btn ac-action-delete" title="ลบ">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" style="text-align:right; font-weight:700; font-size:0.82rem; color:#555; background:#f8faff">รวมน้ำหนัก</td>
                            <td style="font-weight:700; background:{{ $weightBg }}; color:{{ $weightColor }}">
                                {{ $totalWeight }}%
                                @if($totalWeight == 100)
                                    <i class="bi bi-check-circle-fill" style="margin-left:4px"></i>
                                @else
                                    <i class="bi bi-exclamation-triangle-fill" style="margin-left:4px"></i>
                                @endif
                            </td>
                            <td style="background:#f8faff"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if($totalWeight != 100)
            <div style="background:#fef3c7; border:1px solid #fcd34d; border-radius:8px; padding:8px 14px; font-size:0.82rem; color:#92400e">
                <i class="bi bi-exclamation-triangle-fill"></i>
                น้ำหนักรวม <strong>{{ $totalWeight }}%</strong> — ควรรวมเป็น 100% เพื่อให้คะแนนถูกต้อง
            </div>
            @endif
            @else
            <div class="ac-empty">
                <i class="bi bi-inbox" style="font-size:2rem; color:#ccc; display:block; margin-bottom:8px"></i>
                ยังไม่มีหมวดคะแนน กรุณากด "ตั้งค่ามาตรฐาน" หรือ "เพิ่มหมวด"
            </div>
            @endif
        </div>
    </div>

    {{-- ===== Card 3: Grade Scale Reference ===== --}}
    <div class="ac-card" style="margin-bottom:20px">
        <div class="ac-card-header">
            <span><i class="bi bi-bar-chart-steps"></i> เกณฑ์ตัดเกรด</span>
        </div>
        <div class="ac-card-body" style="padding:16px 20px">
            <div class="ac-table-wrap">
                <table class="ac-table" style="max-width:480px">
                    <thead>
                        <tr><th>คะแนน %</th><th>เกรด</th><th>ระดับผลการเรียน</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>80 – 100</td><td style="font-weight:700; color:#16a34a">4</td><td>ดีเยี่ยม</td></tr>
                        <tr><td>75 – 79</td><td style="font-weight:700; color:#16a34a">3.5</td><td>ดีมาก</td></tr>
                        <tr><td>70 – 74</td><td style="font-weight:700; color:#4479DA">3</td><td>ดี</td></tr>
                        <tr><td>65 – 69</td><td style="font-weight:700; color:#4479DA">2.5</td><td>ค่อนข้างดี</td></tr>
                        <tr><td>60 – 64</td><td style="font-weight:700; color:#d97706">2</td><td>ปานกลาง</td></tr>
                        <tr><td>55 – 59</td><td style="font-weight:700; color:#d97706">1.5</td><td>พอใช้</td></tr>
                        <tr><td>50 – 54</td><td style="font-weight:700; color:#ea580c">1</td><td>ผ่านเกณฑ์ขั้นต่ำ</td></tr>
                        <tr><td>0 – 49</td><td style="font-weight:700; color:#dc2626">0</td><td>ไม่ผ่าน</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Card 4: Score Entry ===== --}}
    @if($categories->count() > 0)
    <div class="ac-card">
        <div class="ac-card-header">
            <span><i class="bi bi-table"></i> บันทึกคะแนน</span>
            <span style="font-size:0.78rem; opacity:0.85">นักเรียน {{ $students->count() }} คน</span>
        </div>
        <div class="ac-card-body" style="padding:16px 20px">

            <form method="POST" action="{{ route('scores.save', $assign->assign_id) }}" id="scoreForm">
                @csrf
                <div style="overflow-x:auto; border-radius:10px; border:1px solid #eaeef2">
                    <table class="ac-table" id="scoreTable" style="min-width:600px">
                        <thead style="position:sticky; top:0; z-index:10">
                            <tr>
                                <th style="min-width:48px">เลขที่</th>
                                <th style="min-width:90px">รหัส</th>
                                <th style="min-width:160px; text-align:left">ชื่อ-สกุล</th>
                                @foreach($categories as $cat)
                                <th style="min-width:80px">
                                    {{ $cat->name }}<br>
                                    <small style="font-weight:400; opacity:0.85">({{ $cat->max_score }} / {{ $cat->weight_pct }}%)</small>
                                </th>
                                @endforeach
                                <th style="min-width:70px">รวม (%)</th>
                                <th style="min-width:60px">เกรด</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $ss)
                            @php $s = $ss->student; @endphp
                            <tr data-student="{{ $s->student_id }}">
                                <td>{{ $ss->student_number }}</td>
                                <td>{{ $s->student_code }}</td>
                                <td style="text-align:left; white-space:nowrap">{{ $s->thai_prefix }}{{ $s->thai_firstname }} {{ $s->thai_lastname }}</td>
                                @foreach($categories as $cat)
                                <td>
                                    <input type="number"
                                        name="scores[{{ $s->student_id }}][{{ $cat->category_id }}]"
                                        class="score-input score-cell"
                                        step="0.5" min="0" max="{{ $cat->max_score }}"
                                        data-max="{{ $cat->max_score }}"
                                        data-weight="{{ $cat->weight_pct }}"
                                        data-student="{{ $s->student_id }}"
                                        value="{{ $scoreMatrix[$s->student_id][$cat->category_id] ?? '' }}"
                                        oninput="recalcRow({{ $s->student_id }})">
                                </td>
                                @endforeach
                                <td class="total-cell" id="total-{{ $s->student_id }}" style="font-weight:700; color:#4479DA">—</td>
                                <td class="grade-cell" id="grade-{{ $s->student_id }}" style="font-weight:700">—</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="display:flex; justify-content:center; gap:12px; margin-top:20px; flex-wrap:wrap">
                    <button type="submit" class="ac-btn ac-btn-primary">
                        <i class="bi bi-save"></i> บันทึกคะแนน
                    </button>
                    <button type="button" class="ac-btn ac-btn-success" onclick="document.getElementById('calcForm').submit()">
                        <i class="bi bi-calculator"></i> คำนวณและบันทึกเกรด
                    </button>
                    <a href="{{ route('grades.print', $assign->assign_id) }}" target="_blank" class="ac-btn" style="background:linear-gradient(135deg,#7c3aed,#a855f7); color:#fff; text-decoration:none">
                        <i class="bi bi-printer"></i> พิมพ์ใบเกรด
                    </a>
                </div>
            </form>

            <form id="calcForm" method="POST" action="{{ route('scores.calculate', $assign->assign_id) }}">@csrf</form>

        </div>
    </div>
    @endif

</div>{{-- end ac-page --}}

{{-- ===== Modal เพิ่ม/แก้ไขหมวดคะแนน ===== --}}
<div class="ac-overlay" id="catOverlay" onclick="if(event.target===this)closeCatModal()">
    <div class="ac-modal">
        <div class="ac-modal-header" id="catModalTitle">
            <i class="bi bi-plus-circle me-2"></i>เพิ่มหมวดคะแนน
        </div>
        <form method="POST" id="catForm" action="{{ route('scores.storeCategory') }}">
            @csrf
            <span id="catMethodField"></span>
            <input type="hidden" name="assign_id" value="{{ $assign->assign_id }}">
            <div class="ac-modal-body">
                <label>ชื่อหมวด *</label>
                <input type="text" name="name" id="catName" required placeholder="เช่น สอบกลางภาค, งานทำในชั้น">

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px">
                    <div>
                        <label>คะแนนเต็ม *</label>
                        <input type="number" name="max_score" id="catMax" required value="100" step="0.5" min="0">
                    </div>
                    <div>
                        <label>น้ำหนัก (%) *</label>
                        <input type="number" name="weight_pct" id="catWeight" required value="100" step="0.5" min="0" max="100">
                    </div>
                    <div>
                        <label>ลำดับ</label>
                        <input type="number" name="sort_order" id="catOrder" value="{{ $categories->count() + 1 }}" min="1">
                    </div>
                </div>
            </div>
            <div class="ac-modal-footer">
                <button type="button" class="ac-btn ac-btn-secondary" onclick="closeCatModal()">ยกเลิก</button>
                <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
// ===== Modal helpers =====
function openCatModal() {
    document.getElementById('catModalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>เพิ่มหมวดคะแนน';
    document.getElementById('catForm').action = '{{ route('scores.storeCategory') }}';
    document.getElementById('catMethodField').innerHTML = '';
    document.getElementById('catName').value = '';
    document.getElementById('catMax').value = 100;
    document.getElementById('catWeight').value = 100;
    document.getElementById('catOrder').value = {{ $categories->count() + 1 }};
    document.getElementById('catOverlay').classList.add('active');
}

function openEditModal(id, name, max, weight, order) {
    document.getElementById('catModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>แก้ไขหมวดคะแนน';
    document.getElementById('catForm').action = '/scores/category/' + id;
    document.getElementById('catMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('catName').value = name;
    document.getElementById('catMax').value = max;
    document.getElementById('catWeight').value = weight;
    document.getElementById('catOrder').value = order;
    document.getElementById('catOverlay').classList.add('active');
}

function closeCatModal() {
    document.getElementById('catOverlay').classList.remove('active');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeCatModal();
});

// ===== Real-time grade calculation =====
function getGrade(pct) {
    if (pct >= 80) return { grade: '4',   color: '#16a34a' };
    if (pct >= 75) return { grade: '3.5', color: '#16a34a' };
    if (pct >= 70) return { grade: '3',   color: '#4479DA' };
    if (pct >= 65) return { grade: '2.5', color: '#4479DA' };
    if (pct >= 60) return { grade: '2',   color: '#d97706' };
    if (pct >= 55) return { grade: '1.5', color: '#d97706' };
    if (pct >= 50) return { grade: '1',   color: '#ea580c' };
    return { grade: '0', color: '#dc2626' };
}

function recalcRow(studentId) {
    const inputs = document.querySelectorAll(`.score-cell[data-student="${studentId}"]`);
    let weighted = 0;
    let weightSum = 0;
    inputs.forEach(input => {
        const val = parseFloat(input.value);
        const max = parseFloat(input.dataset.max);
        const wt  = parseFloat(input.dataset.weight);
        if (!isNaN(val) && max > 0) {
            weighted += (val / max) * wt;
            weightSum += wt;
        }
    });

    const totalCell = document.getElementById('total-' + studentId);
    const gradeCell = document.getElementById('grade-' + studentId);

    if (weightSum === 0) {
        totalCell.textContent = '—';
        totalCell.style.color = '#aaa';
        gradeCell.textContent = '—';
        gradeCell.style.color = '#aaa';
        gradeCell.style.background = '';
        return;
    }

    // weighted already is Σ(score/max × weight_pct), if weights sum to 100 this IS the final %
    const finalPct = weighted;
    const info = getGrade(finalPct);

    totalCell.textContent = finalPct.toFixed(1) + '%';
    totalCell.style.color = '#4479DA';

    gradeCell.textContent = info.grade;
    gradeCell.style.color = '#fff';
    gradeCell.style.fontWeight = '700';
    gradeCell.style.borderRadius = '6px';
    gradeCell.style.padding = '2px 8px';
    gradeCell.style.background = finalPct >= 50 ? '#16a34a' : '#dc2626';
}

// Init all rows on page load
document.addEventListener('DOMContentLoaded', function() {
    const allInputs = document.querySelectorAll('.score-cell');
    const seen = new Set();
    allInputs.forEach(inp => {
        const sid = inp.dataset.student;
        if (!seen.has(sid)) {
            seen.add(sid);
            recalcRow(parseInt(sid));
        }
    });
});
</script>

@endsection
