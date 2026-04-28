@extends('layouts.sidebar')
@push('styles')
<style>
.ge-page { padding: 24px 28px; }
.ge-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 22px 22px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.ge-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.ge-icon-student { background: #1565c0; }
.ge-icon-grade   { background: #e65100; }
.ge-card-header {
    margin-left: 90px; display: flex; align-items: center;
    justify-content: space-between; margin-top: -8px; margin-bottom: 20px;
    flex-wrap: wrap; gap: 8px;
}
.ge-card-title { font-size: 1.05rem; color: #555; font-weight: 600; }

.btn-back {
    background: #5c6bc0; color: #fff; border: none; border-radius: 6px;
    padding: 8px 18px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-back:hover { background: #3949ab; color: #fff; }

.btn-print {
    background: #1565c0; color: #fff; border: none; border-radius: 6px;
    padding: 8px 18px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-print:hover { background: #0d47a1; color: #fff; }

/* Student info bar */
.stu-bar {
    display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
    background: #f0f4ff; border-radius: 8px; padding: 12px 16px; margin-bottom: 6px;
}
.stu-bar-name { font-size: 1rem; font-weight: 700; color: #1a237e; }
.stu-bar-code { font-size: 0.82rem; color: #666; }
.stu-bar-gpa  {
    margin-left: auto; font-size: 0.85rem; font-weight: 700;
    background: #1565c0; color: #fff; border-radius: 20px; padding: 4px 16px;
}

/* Grade table */
.ge-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.ge-table thead th {
    padding: 10px 12px; background: #e65100; color: #fff;
    font-weight: 600; text-align: left; font-size: 0.84rem;
}
.ge-table thead th:first-child { border-radius: 6px 0 0 0; }
.ge-table thead th:last-child  { border-radius: 0 6px 0 0; text-align: center; }
.ge-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.ge-table tbody tr:hover { background: #fff8f5; }
.ge-table tbody td { padding: 10px 12px; color: #555; vertical-align: middle; }

.badge-sem { display: inline-block; background: #e3f2fd; color: #1565c0; border-radius: 4px; padding: 2px 10px; font-size: 0.76rem; font-weight: 700; }
.badge-pass { display: inline-block; background: #e8f5e9; color: #2e7d32; border-radius: 20px; padding: 2px 10px; font-size: 0.76rem; font-weight: 700; }
.badge-fail { display: inline-block; background: #ffebee; color: #c62828; border-radius: 20px; padding: 2px 10px; font-size: 0.76rem; font-weight: 700; }

.grade-badge {
    display: inline-block; width: 36px; height: 36px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.95rem;
}
.grade-pass { background: #e8f5e9; color: #2e7d32; }
.grade-fail { background: #ffebee; color: #c62828; }

.btn-edit-grade {
    background: #fb8c00; color: #fff; border: none; border-radius: 6px;
    padding: 5px 12px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    font-family: inherit; display: inline-flex; align-items: center; gap: 4px;
}
.btn-edit-grade:hover { background: #e65100; }

.btn-del-grade {
    background: #e53935; color: #fff; border: none; border-radius: 6px;
    padding: 5px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    font-family: inherit; display: inline-flex; align-items: center; gap: 4px;
}
.btn-del-grade:hover { background: #b71c1c; }

/* Semester section header */
.sem-section-header {
    background: #37474f; color: #fff; padding: 8px 14px;
    font-size: 0.88rem; font-weight: 700; border-radius: 6px 6px 0 0;
    margin-top: 16px;
}

/* Modal */
.ge-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.45); z-index: 500;
    align-items: center; justify-content: center;
}
.ge-overlay.active { display: flex; }
.ge-modal { background: #fff; border-radius: 8px; width: 400px; max-width: 95vw; box-shadow: 0 8px 32px rgba(0,0,0,0.18); }
.ge-modal-header { padding: 16px 20px 12px; font-size: 1rem; font-weight: 700; border-bottom: 1px solid #eee; color: #333; display: flex; align-items: center; gap: 8px; }
.ge-modal-body { padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; }
.ge-modal-body label { font-size: 0.82rem; font-weight: 600; color: #555; margin-bottom: 2px; display: block; }
.ge-modal-body input, .ge-modal-body select {
    width: 100%; border: none; border-bottom: 1.5px solid #ccc;
    padding: 7px 4px; font-size: 0.9rem; font-family: inherit;
    outline: none; background: transparent;
}
.ge-modal-body input:focus { border-bottom-color: #e65100; }
.ge-modal-footer { padding: 12px 20px 16px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee; }
.btn-modal-cancel { background: #eee; color: #555; border: none; border-radius: 6px; padding: 8px 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit; }
.btn-modal-save { background: #e65100; color: #fff; border: none; border-radius: 6px; padding: 8px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 5px; }
.btn-modal-save:hover { background: #bf360c; }

@if(session('success'))
.flash-ok { background:#d1fae5; border:1px solid #6ee7b7; border-radius:8px; padding:10px 16px; margin-bottom:16px; color:#065f46; font-size:0.85rem; display:flex; align-items:center; gap:8px; }
@endif
</style>
@endpush

@section('content')
<div class="ge-page">

    @if(session('success'))
    <div class="flash-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif

    {{-- Student info card --}}
    <div class="ge-card">
        <div class="ge-icon ge-icon-student"><i class="bi bi-person-badge"></i></div>
        <div class="ge-card-header">
            <span class="ge-card-title">แก้ไขเกรด — รายวิชาที่เรียน</span>
            <div style="display:flex; gap:8px; flex-wrap:wrap">
              <button type="button" class="btn-print" onclick="document.getElementById('printSettingsModal').classList.add('active')">
                <i class="bi bi-printer"></i> พิมพ์ Transcript
            </button>
                <a href="javascript:history.back()" class="btn-back">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </a>
            </div>
        </div>

        <div class="stu-bar">
            <div>
                <div class="stu-bar-name">{{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }}</div>
                <div class="stu-bar-code">รหัส: {{ $student->student_code ?? '-' }}</div>
            </div>
            <div class="stu-bar-gpa">GPA รวม: {{ $gpa }}</div>
        </div>
    </div>

    {{-- Grades by semester --}}
    <div class="ge-card">
        <div class="ge-icon ge-icon-grade"><i class="bi bi-journal-check"></i></div>
        <div class="ge-card-header">
            <span class="ge-card-title">รายวิชาและเกรดแต่ละภาคเรียน</span>
        </div>

        @forelse($grades as $semKey => $semGrades)
        @php
            [$yearName, $termName] = explode('|', $semKey . '|');
            $semCredits = 0; $semPoints = 0;
            foreach($semGrades as $g) {
                $c = $g->teachingAssign->subject->credits ?? 0;
                $semCredits += $c;
                $semPoints += ($g->gpa_point ?? 0) * $c;
            }
            $semGPA = $semCredits > 0 ? round($semPoints / $semCredits, 2) : 0;
        @endphp
        <div class="sem-section-header">
            <i class="bi bi-calendar3"></i>
            ปีการศึกษา {{ $yearName }} ภาคเรียนที่ {{ $termName }}
            &nbsp;—&nbsp; GPA เทอมนี้: {{ $semGPA }} ({{ $semCredits }} หน่วยกิต)
        </div>
        <table class="ge-table">
            <thead>
                <tr>
                    <th style="width:100px">รหัสวิชา</th>
                    <th>ชื่อวิชา</th>
                    <th style="text-align:center;width:70px">หน่วยกิต</th>
                    <th style="text-align:center;width:80px">คะแนน%</th>
                    <th style="text-align:center;width:70px">เกรด</th>
                    <th style="text-align:center;width:80px">สถานะ</th>
                    <th style="text-align:center;width:130px">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($semGrades as $g)
                <tr>
                    <td><strong>{{ $g->teachingAssign->subject->code ?? '-' }}</strong></td>
                    <td>{{ $g->teachingAssign->subject->name_th ?? '-' }}</td>
                    <td style="text-align:center">{{ $g->teachingAssign->subject->credits ?? '-' }}</td>
                    <td style="text-align:center">{{ $g->total_score ?? '-' }}</td>
                    <td style="text-align:center">
                        <span class="grade-badge {{ $g->remark == 'ผ่าน' ? 'grade-pass' : 'grade-fail' }}">
                            {{ $g->grade }}
                        </span>
                    </td>
                    <td style="text-align:center">
                        <span class="{{ $g->remark == 'ผ่าน' ? 'badge-pass' : 'badge-fail' }}">
                            {{ $g->remark }}
                        </span>
                    </td>
                    <td style="text-align:center">
                        <div style="display:inline-flex;gap:5px">
                            <button class="btn-edit-grade"
                                onclick="openEdit({{ $g->grade_id }}, {{ $g->total_score ?? 0 }}, '{{ $g->grade }}')">
                                <i class="bi bi-pencil"></i> แก้ไข
                            </button>
                            <form action="{{ route('grades.destroy', $g->grade_id) }}" method="POST"
                                  onsubmit="return confirm('ลบเกรดวิชา {{ addslashes($g->teachingAssign->subject->name_th ?? '') }}?')"
                                  style="display:inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del-grade">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @empty
        <div style="text-align:center;padding:40px;color:#aaa">
            <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
            ยังไม่มีผลการเรียน
        </div>
        @endforelse
    </div>

</div>

{{-- Edit Grade Modal --}}
<div class="ge-overlay" id="editGradeOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="ge-modal">
        <div class="ge-modal-header"><i class="bi bi-pencil-square"></i> แก้ไขเกรด</div>
        <form method="POST" id="editGradeForm" action="">
            @csrf @method('PUT')
            <div class="ge-modal-body">
                <div>
                    <label>คะแนนรวม (%)</label>
                    <input type="number" name="total_score" id="edit_score" min="0" max="100" step="0.5" required>
                    <small style="color:#888;font-size:0.75rem">* ระบบจะคำนวณเกรดใหม่อัตโนมัติ</small>
                </div>
                <div>
                    <label>เกรด (ถ้าต้องการตั้งเอง)</label>
                    <select name="grade" id="edit_grade">
                        <option value="">-- คำนวณอัตโนมัติจากคะแนน --</option>
                        @foreach(['4','3.5','3','2.5','2','1.5','1','0','I','W','S','U'] as $gr)
                        <option value="{{ $gr }}">{{ $gr }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="ge-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('editGradeOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="bi bi-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(gradeId, score, grade) {
    document.getElementById('editGradeForm').action = '/grades/' + gradeId;
    document.getElementById('edit_score').value = score;
    document.getElementById('edit_grade').value = '';
    document.getElementById('editGradeOverlay').classList.add('active');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.ge-overlay.active').forEach(el => el.classList.remove('active'));
});
</script>
@endsection

<div class="modal-overlay" id="printSettingsModal" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <form action="{{ route('grades.transcript.print', $student->student_id) }}" method="GET" target="_blank">
            <div class="modal-box-body">
                
                <div class="opt-row">
                    <div class="opt-label">ปพ.1 ตัวจริง/สำรอง :</div>
                    <div class="opt-content">
                        <label class="opt-checkbox">
                            <input type="checkbox" name="show_original" value="1"> แสดงเอกสารฉบับจริง
                        </label>
                    </div>
                </div>

                <div class="opt-row">
                    <div class="opt-label">รูปโปรไฟล์ :</div>
                    <div class="opt-content">
                        <label class="opt-checkbox">
                            <input type="checkbox" name="hide_profile" value="1"> ซ่อนรูปโปรไฟล์
                        </label>
                    </div>
                </div>

                <hr style="border-top:1px solid #eee; margin:20px 0;">
                <div style="text-align:center; margin-bottom:20px; font-weight:bold; color:#666; font-size:16px;">แสดงเกรดตามเทอม</div>

                <div class="opt-row">
                    <div class="opt-label" style="text-align: right; padding-right:15px; color:#666;">
                        เลือกเทอม :
                    </div>
                    <div class="opt-content" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        {{-- วนลูปเทอมทั้งหมดที่เด็กคนนี้มีเกรด ออกมาเป็น Checkbox --}}
                        @foreach($grades->keys() as $semKey)
                            @php [$yearName, $termName] = explode('|', $semKey . '|'); @endphp
                            <label class="opt-checkbox">
                                <input type="checkbox" name="selected_semesters[]" value="{{ $semKey }}" checked>
                                ปีการศึกษา {{ $yearName }} / เทอม {{ $termName }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <hr style="border-top:1px solid #eee; margin:20px 0;">

                <div class="opt-row">
                    <div class="opt-label" style="text-align: right; padding-right:15px; color:#666;">หมายเหตุ :</div>
                    <div class="opt-content">
                        <label class="opt-checkbox">
                            <input type="checkbox" name="hide_last_semester" value="1"> ไม่คำนวณ/ไม่แสดงเกรดภาคเรียนสุดท้าย
                        </label>
                        <label class="opt-checkbox">
                            <input type="checkbox" name="show_all_subjects" value="1"> แสดงรายวิชาทั้งหมดจากแผน
                        </label>
                        <label class="opt-checkbox">
                            <input type="checkbox" name="english_report" value="1"> รายงานเป็นภาษาอังกฤษ
                        </label>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-submit-print" onclick="document.getElementById('printSettingsModal').classList.remove('active')">
                    <i class="bi bi-printer"></i> พิมพ์ใบ ปพ.1
                </button>
                <button type="button" class="btn-cancel-print" onclick="document.getElementById('printSettingsModal').classList.remove('active')">
                    ยกเลิก
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* CSS สำหรับ Modal ตั้งค่าการพิมพ์ */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.5); z-index: 9999;
    align-items: center; justify-content: center;
}
.modal-overlay.active { display: flex; }
.modal-box {
    background: #fff; border-radius: 8px; width: 650px; max-width: 95vw;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.modal-box-body { padding: 30px; }
.modal-footer {
    display: flex; justify-content: center; gap: 15px; padding: 20px;
    border-top: 1px solid #eee; background: #fafafa; border-radius: 0 0 8px 8px;
}
.opt-row { display: flex; margin-bottom: 12px; font-size: 15px; color: #444; }
.opt-label { width: 140px; font-weight: normal; color: #444; }
.opt-content { flex: 1; }
.opt-checkbox { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; cursor: pointer; color: #555; }
.opt-checkbox input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: #2196f3; }
.btn-submit-print { background: #4caf50; color: #fff; border: none; padding: 10px 35px; border-radius: 4px; font-size: 15px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 5px;}
.btn-submit-print:hover { background: #43a047; }
.btn-cancel-print { background: #ff5252; color: #fff; border: none; padding: 10px 35px; border-radius: 4px; font-size: 15px; font-weight: bold; cursor: pointer; }
.btn-cancel-print:hover { background: #e53935; }
</style>