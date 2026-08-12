@extends('layouts.sidebar')

@push('styles')
<style>
    .bs-page { padding: 24px 28px; min-height: 100%; }

    .bs-breadcrumb { font-size: 0.85rem; color: #999; margin-bottom: 20px; }
    .bs-breadcrumb a { color: #4b7ce3; text-decoration: none; }
    .bs-breadcrumb a:hover { text-decoration: underline; }

    .bs-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 24px 20px 20px; position: relative;
        margin-bottom: 28px;
    }
    .bs-card-title { font-size: 1.05rem; font-weight: 700; color: #444; margin-bottom: 16px; }

    .bs-form-label { font-size: 0.82rem; color: #666; font-weight: 600; margin-bottom: 4px; }
    .bs-form-select, .bs-form-input {
        height: 38px; border: 1px solid #ccc; border-radius: 4px;
        padding: 0 12px; font-size: 0.88rem; width: 100%;
        background: #fff; outline: none; font-family: inherit; box-sizing: border-box;
    }
    .bs-form-select:focus, .bs-form-input:focus { border-color: #00bcd4; }

    .btn-search-teal {
        background: #00bcd4; color: #fff; border: none; border-radius: 4px;
        padding: 9px 28px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-search-teal:hover { background: #00a5bb; }

    .bs-table { width: 100%; border-collapse: collapse; font-size: 0.86rem; }
    .bs-table thead th {
        padding: 10px 10px; font-weight: 600; color: #333;
        border-bottom: 2px solid #eee; background: #fafafa; white-space: nowrap;
    }
    .bs-table tbody tr { border-bottom: 1px solid #f5f5f5; }
    .bs-table tbody tr:hover { background: #fef9f0; }
    .bs-table tbody td { padding: 10px 10px; color: #555; vertical-align: middle; }
    .bs-table .no-data { text-align: center; padding: 30px; color: #aaa; }

    .bs-score { font-weight: 700; color: #00838f; }

    .bs-btn { border: none; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; font-size: 0.95rem; margin: 0 2px; }
    .bs-btn-add { background: #4caf50; color: #fff; }
    .bs-btn-add:hover { background: #43a047; }
    .bs-btn-deduct { background: #ff9800; color: #fff; }
    .bs-btn-deduct:hover { background: #fb8c00; }
    .bs-btn-view {
        background: #00bcd4; color: #fff; border: none; border-radius: 6px;
        padding: 0 12px; height: 32px; font-size: 0.8rem; font-weight: 600;
        cursor: pointer; text-decoration: none; display: inline-flex; align-items: center;
    }
    .bs-btn-view:hover { background: #00acc1; color: #fff; text-decoration: none; }

    .bs-empty-box { text-align: center; color: #aaa; padding: 50px 0; }
    .bs-empty-box i { font-size: 2.5rem; display: block; margin-bottom: 10px; }

    /* Modal */
    .bs-overlay {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.45); z-index: 9999;
        justify-content: center; align-items: center;
    }
    .bs-overlay.active { display: flex; }
    .bs-modal {
        background: #fff; border-radius: 14px; width: 460px; max-width: 94vw;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden;
    }
    .bs-modal-header { background: linear-gradient(135deg, #00838f, #26c6da); color: #fff; padding: 18px 24px; font-size: 1rem; font-weight: 700; }
    .bs-modal-body { padding: 24px; }
    .bs-modal-body label { font-size: 0.82rem; font-weight: 600; color: #555; margin-bottom: 4px; display: block; }
    .bs-modal-body input, .bs-modal-body select {
        width: 100%; height: 40px; border: 1.5px solid #e0e0e0; border-radius: 10px;
        padding: 0 14px; font-size: 0.85rem; font-family: inherit; outline: none;
        box-sizing: border-box; margin-bottom: 14px;
    }
    .bs-modal-body input:focus, .bs-modal-body select:focus { border-color: #00bcd4; box-shadow: 0 0 0 3px rgba(0,188,212,0.1); }
    .bs-modal-body input[readonly] { background: #f5f5f5; color: #888; }
    .bs-modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid #f0f0f0; }
    .btn-modal-cancel { background: #f5f5f5; color: #666; border: none; border-radius: 8px; padding: 9px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit; }
    .btn-modal-save { background: #00838f; color: #fff; border: none; border-radius: 8px; padding: 9px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit; }
    .btn-modal-save:hover { background: #006e78; }
</style>
@endpush

@section('content')
<div class="bs-page">

    <div class="bs-breadcrumb">
        กิจการนักเรียน &rsaquo; <span style="color:#00838f">ระบบความประพฤติ</span> &rsaquo;
        ตัดคะแนนความประพฤติ
    </div>

    @if(session('success'))
    <div style="background:#d4edda;color:#155724;padding:12px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem;">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="background:#f8d7da;color:#842029;padding:12px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem;">
        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
    </div>
    @endif

    <div class="bs-card">
        <div class="bs-card-title"><i class="bi bi-search me-1"></i> ค้นหา</div>
        <form method="GET" action="{{ route('behavior-scores.index') }}" id="searchForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="bs-form-label">ระดับชั้นเรียน</div>
                    <select name="level_id" class="bs-form-select" onchange="document.getElementById('section_id_field').value=''; document.getElementById('searchForm').submit()">
                        <option value="">เลือกระดับชั้น</option>
                        @foreach ($levels as $lv)
                            <option value="{{ $lv->level_id }}" {{ (string)$levelId === (string)$lv->level_id ? 'selected' : '' }}>{{ $lv->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="bs-form-label">ชั้นเรียน</div>
                    <select name="section_id" id="section_id_field" class="bs-form-select" onchange="document.getElementById('searchForm').submit()">
                        <option value="">เลือกชั้นเรียน</option>
                        @foreach ($sections as $sec)
                            <option value="{{ $sec->section_id }}" {{ (string)$sectionId === (string)$sec->section_id ? 'selected' : '' }}>{{ $sec->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="bs-form-label">ชื่อ - นามสกุล</div>
                    <input type="text" name="search" class="bs-form-input" placeholder="ค้นหาชื่อ/รหัสนักเรียน" value="{{ $search }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn-search-teal w-100"><i class="bi bi-search"></i> ค้นหา</button>
                </div>
            </div>
        </form>
    </div>

    <div class="bs-card">
        <div class="bs-card-title">
            ตัดคะแนนความประพฤติของนักเรียน
            @if($selectedSection)
                <span style="color:#888;font-weight:400;font-size:0.85rem;">
                    ({{ $selectedSection->full_name }} &middot; ปีการศึกษา {{ $year->year_name ?? '-' }}/{{ $semester->semester_name ?? '-' }})
                </span>
            @endif
        </div>

        @if($students->isEmpty())
            <div class="bs-empty-box">
                <i class="bi bi-people"></i>
                กรุณาเลือกระดับชั้นเรียนและชั้นเรียนด้านบนก่อน
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="bs-table">
                    <thead>
                        <tr>
                            <th style="width:50px">ลำดับ</th>
                            <th>ชื่อ</th>
                            <th>นามสกุล</th>
                            <th>รหัสนักเรียน</th>
                            <th>ชั้นเรียน</th>
                            <th>รวมคะแนน</th>
                            <th style="width:150px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $ss)
                            @php
                                $stu = $ss->student;
                                $balance = $fullScore + (float) ($balances[$stu->student_id] ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $stu->thai_firstname }}</td>
                                <td>{{ $stu->thai_lastname }}</td>
                                <td>{{ $stu->student_code }}</td>
                                <td>{{ $selectedSection->full_name ?? '-' }}</td>
                                <td class="bs-score">{{ number_format($balance, 2) }}</td>
                                <td>
                                    <button type="button" class="bs-btn bs-btn-add" title="เพิ่มคะแนน"
                                        onclick="openScoreModal('add', {{ $stu->student_id }}, '{{ $stu->student_code }}', '{{ addslashes($stu->thai_firstname.' '.$stu->thai_lastname) }}')">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                    <button type="button" class="bs-btn bs-btn-deduct" title="ลดคะแนน"
                                        onclick="openScoreModal('deduct', {{ $stu->student_id }}, '{{ $stu->student_code }}', '{{ addslashes($stu->thai_firstname.' '.$stu->thai_lastname) }}')">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                    <a href="{{ route('behavior-scores.history', $stu->student_id) }}" class="bs-btn-view">ดูข้อมูล</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Modal เพิ่ม/ลดคะแนน --}}
<div class="bs-overlay" id="scoreOverlay" onclick="if(event.target===this)closeScoreModal()">
    <div class="bs-modal" onclick="event.stopPropagation()">
        <div class="bs-modal-header" id="scoreModalHeader">เพิ่มคะแนนความประพฤติกรรม</div>
        <form method="POST" action="{{ route('behavior-scores.store') }}">
            @csrf
            <input type="hidden" name="student_id" id="scoreStudentId">
            <input type="hidden" name="level_id" value="{{ $levelId }}">
            <input type="hidden" name="section_id" value="{{ $sectionId }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <div class="bs-modal-body">
                <label>หมายเลขนักเรียน</label>
                <input type="text" id="scoreStudentCode" readonly>

                <label>ชื่อ - นามสกุล</label>
                <input type="text" id="scoreStudentName" readonly>

                <label>รายการพฤติกรรม <span style="color:red">*</span></label>
                <select name="behavior_item_id" id="scoreItemSelect" required></select>

                <label>หมายเหตุ</label>
                <input type="text" name="note" placeholder="หมายเหตุ (ถ้ามี)">
            </div>
            <div class="bs-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeScoreModal()">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="bi bi-check-lg me-1"></i> บันทึกข้อมูล</button>
            </div>
        </form>
    </div>
</div>

<script>
const behaviorAddItems = @json($addItems->map(fn($i) => ['id' => $i->behavior_item_id, 'label' => $i->name_th . ' (' . number_format($i->points, 2) . ' แต้ม)']));
const behaviorDeductItems = @json($deductItems->map(fn($i) => ['id' => $i->behavior_item_id, 'label' => $i->name_th . ' (' . number_format($i->points, 2) . ' แต้ม)']));

function openScoreModal(type, studentId, studentCode, studentName) {
    document.getElementById('scoreStudentId').value = studentId;
    document.getElementById('scoreStudentCode').value = studentCode;
    document.getElementById('scoreStudentName').value = studentName;
    document.getElementById('scoreModalHeader').textContent = type === 'add' ? 'เพิ่มคะแนนความประพฤติกรรม' : 'ลดคะแนนความประพฤติกรรม';

    const select = document.getElementById('scoreItemSelect');
    select.innerHTML = '';
    const items = type === 'add' ? behaviorAddItems : behaviorDeductItems;
    items.forEach(it => {
        const opt = document.createElement('option');
        opt.value = it.id;
        opt.textContent = it.label;
        select.appendChild(opt);
    });

    document.getElementById('scoreOverlay').classList.add('active');
}
function closeScoreModal() {
    document.getElementById('scoreOverlay').classList.remove('active');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeScoreModal();
});
</script>
@endsection
