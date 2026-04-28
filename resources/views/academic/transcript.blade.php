@extends('layouts.sidebar')
@push('styles')
<style>
.tr-page { padding: 24px 28px; }
.tr-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 22px 22px; position: relative;
    margin-top: 50px; margin-bottom: 20px;
}
.tr-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.tr-icon-student { background: #1565c0; }
.tr-icon-sem     { background: #37474f; }
.tr-card-header {
    margin-left: 90px; display: flex; align-items: center;
    justify-content: space-between; margin-top: -8px; margin-bottom: 16px;
    flex-wrap: wrap; gap: 8px;
}
.tr-card-title { font-size: 1.05rem; color: #555; font-weight: 600; }

.btn-print {
    background: #1565c0; color: #fff; border: none; border-radius: 6px;
    padding: 8px 18px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-print:hover { background: #0d47a1; color: #fff; }

.btn-edit {
    background: #e65100; color: #fff; border: none; border-radius: 6px;
    padding: 8px 18px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-edit:hover { background: #bf360c; color: #fff; }

.btn-back {
    background: #5c6bc0; color: #fff; border: none; border-radius: 6px;
    padding: 8px 18px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-back:hover { background: #3949ab; color: #fff; }

.stu-info-grid {
    display: grid; grid-template-columns: auto 1fr auto;
    align-items: center; gap: 20px;
}
.stu-name { font-size: 1.1rem; font-weight: 700; color: #1a237e; }
.stu-code { font-size: 0.82rem; color: #666; margin-top: 2px; }
.gpa-circle {
    width: 80px; height: 80px; border-radius: 50%;
    background: linear-gradient(135deg, #1565c0, #42a5f5);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    color: #fff; box-shadow: 0 3px 10px rgba(21,101,192,0.3);
}
.gpa-num  { font-size: 1.4rem; font-weight: 700; line-height: 1; }
.gpa-label{ font-size: 0.65rem; opacity: 0.85; margin-top: 2px; }

/* Semester table */
.sem-header {
    background: #37474f; color: #fff; padding: 8px 14px;
    font-size: 0.9rem; font-weight: 700; border-radius: 6px 6px 0 0;
    display: flex; justify-content: space-between; align-items: center;
}
.tr-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.tr-table thead th {
    padding: 9px 12px; background: #eceff1; color: #455a64;
    font-weight: 600; text-align: left; font-size: 0.83rem;
}
.tr-table thead th:last-child { text-align: center; }
.tr-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.tr-table tbody tr:hover { background: #f5f7ff; }
.tr-table tbody td { padding: 9px 12px; color: #555; vertical-align: middle; }
.tr-table tfoot td { padding: 8px 12px; background: #f0f4ff; font-weight: 700; color: #1a237e; }

.grade-chip {
    display: inline-flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border-radius: 50%; font-weight: 700; font-size: 0.9rem;
}
.grade-pass { background: #e8f5e9; color: #2e7d32; }
.grade-fail { background: #ffebee; color: #c62828; }

.badge-pass { display: inline-block; background: #e8f5e9; color: #2e7d32; border-radius: 20px; padding: 2px 10px; font-size: 0.75rem; font-weight: 700; }
.badge-fail { display: inline-block; background: #ffebee; color: #c62828; border-radius: 20px; padding: 2px 10px; font-size: 0.75rem; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="tr-page">

    {{-- Student info card --}}
    <div class="tr-card">
        <div class="tr-icon tr-icon-student"><i class="bi bi-person-badge"></i></div>
        <div class="tr-card-header">
            <span class="tr-card-title">ใบแสดงผลการเรียน</span>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="{{ route('grades.student.edit', $student->student_id) }}" class="btn-edit">
                    <i class="bi bi-pencil-square"></i> แก้ไขเกรด
                </a>
               <button type="button" class="btn-print" onclick="document.getElementById('printSettingsModal').classList.add('active')">
                    <i class="bi bi-printer"></i> พิมพ์ Transcript
                </button>
                <a href="javascript:history.back()" class="btn-back">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </a>
            </div>
        </div>

        <div class="stu-info-grid">
            <div class="gpa-circle">
                <span class="gpa-num">{{ $gpa }}</span>
                <span class="gpa-label">GPA</span>
            </div>
            <div>
                <div class="stu-name">{{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }}</div>
                <div class="stu-code">รหัสนักเรียน: {{ $student->student_code ?? '-' }}</div>
                <div style="font-size:0.8rem;color:#888;margin-top:2px">หน่วยกิตรวม: {{ $totalCredits }}</div>
            </div>
        </div>
    </div>

    {{-- Grades by semester --}}
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
    <div class="tr-card" style="padding-top:0;overflow:hidden">
        <div class="sem-header" style="margin:-0px -22px 0;padding:12px 22px">
            <span><i class="bi bi-calendar3"></i> ปีการศึกษา {{ $yearName }} ภาคเรียนที่ {{ $termName }}</span>
            <span style="font-size:0.82rem;opacity:0.85">GPA เทอมนี้: {{ $semGPA }}</span>
        </div>
        <div style="margin-top:16px">
        <table class="tr-table">
            <thead>
                <tr>
                    <th style="width:100px">รหัสวิชา</th>
                    <th>ชื่อวิชา</th>
                    <th style="text-align:center;width:80px">หน่วยกิต</th>
                    <th style="text-align:center;width:80px">คะแนน%</th>
                    <th style="text-align:center;width:70px">เกรด</th>
                    <th style="text-align:center;width:80px">GPA</th>
                    <th style="text-align:center;width:80px">สถานะ</th>
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
                        <span class="grade-chip {{ $g->remark == 'ผ่าน' ? 'grade-pass' : 'grade-fail' }}">
                            {{ $g->grade }}
                        </span>
                    </td>
                    <td style="text-align:center">{{ $g->gpa_point }}</td>
                    <td style="text-align:center">
                        <span class="{{ $g->remark == 'ผ่าน' ? 'badge-pass' : 'badge-fail' }}">{{ $g->remark }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align:right">รวมภาคเรียนนี้</td>
                    <td style="text-align:center">{{ $semCredits }}</td>
                    <td></td>
                    <td></td>
                    <td style="text-align:center;color:#1565c0;font-size:1rem">{{ $semGPA }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
    @empty
    <div class="tr-card">
        <div style="text-align:center;padding:40px;color:#aaa">
            <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
            ยังไม่มีผลการเรียน
        </div>
    </div>
    @endforelse

</div>
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
@endsection
