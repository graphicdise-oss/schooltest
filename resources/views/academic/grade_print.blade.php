<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ใบบันทึกคะแนน — {{ $assign->subject->name_th }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'TH Sarabun New','Sarabun','Tahoma',sans-serif; font-size: 13pt; background: #e0e0e0; }

.page {
    width: 297mm; min-height: 210mm; background: #fff;
    margin: 16px auto; padding: 10mm 8mm 8mm;
    box-shadow: 0 2px 16px rgba(0,0,0,0.15);
}

/* ===== Header ===== */
.sheet-header { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 6px; }
.logo-box { width: 70px; height: 70px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 9pt; color: #aaa; flex-shrink: 0; }
.header-text { flex: 1; text-align: center; }
.header-text .title1 { font-size: 15pt; font-weight: bold; }
.header-text .title2 { font-size: 13pt; margin-top: 2px; }
.header-text .title3 { font-size: 12pt; margin-top: 2px; }

/* ===== Score Table ===== */
.score-table { width: 100%; border-collapse: collapse; font-size: 10.5pt; margin-top: 8px; }
.score-table th, .score-table td {
    border: 1px solid #333; text-align: center; vertical-align: middle;
    padding: 2px 3px;
}
.score-table th { font-weight: bold; background: #fff; }

/* Rotated header text */
.rotate-header {
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    white-space: nowrap;
    font-size: 9.5pt;
    height: 60px;
    display: flex; align-items: center; justify-content: center;
}

/* Column colors */
.col-no       { width: 22px; background: #fff; }
.col-idcard   { width: 36px; }
.col-code     { width: 32px; }
.col-name     { width: 110px; text-align: left !important; padding-left: 4px !important; }
.col-score    { background: #c8e6c9; width: 26px; } /* green */
.col-total    { background: #fff9c4; width: 28px; font-weight: bold; } /* yellow */
.col-grade    { background: #fff9c4; width: 28px; font-weight: bold; } /* yellow */

.col-score th  { background: #4caf50; color: #fff; }
.col-total th  { background: #f9a825; color: #fff; }
.col-grade th  { background: #e65100; color: #fff; }

.max-row td  { font-size: 9pt; background: #f5f5f5; }
.data-row td { height: 20px; }
.data-row td.score-val { background: #f1f8e9; }
.data-row td.total-val { background: #fffde7; font-weight: bold; }
.data-row td.grade-val {
    background: #fff3e0; font-weight: bold; font-size: 11pt;
}
.grade-pass { color: #1b5e20; }
.grade-fail { color: #b71c1c; }

.footer-row { margin-top: 10px; font-size: 11pt; display: flex; justify-content: space-between; }

/* No-print */
.no-print { text-align: center; padding: 12px 0; display: flex; justify-content: center; gap: 12px; }
.btn-print { background: #1565c0; color: #fff; border: none; border-radius: 6px; padding: 10px 28px; font-size: 13pt; font-weight: bold; cursor: pointer; font-family: inherit; }
.btn-excel { background: #2e7d32; color: #fff; border: none; border-radius: 6px; padding: 10px 28px; font-size: 13pt; font-weight: bold; cursor: pointer; font-family: inherit; text-decoration: none; display: inline-block; }
.btn-back  { background: #555; color: #fff; border: none; border-radius: 6px; padding: 10px 20px; font-size: 13pt; font-weight: bold; cursor: pointer; font-family: inherit; text-decoration: none; display: inline-block; }

@media print {
    body { background: #fff; }
    .page { margin: 0; box-shadow: none; padding: 8mm 6mm; }
    .no-print { display: none !important; }
    @page { size: A4 landscape; margin: 0; }
}
</style>
</head>
<body>

<div class="no-print">
    <a href="javascript:history.back()" class="btn-back">← ย้อนกลับ</a>
    <a href="{{ route('grades.excel', $assign->assign_id) }}" class="btn-excel">⬇ Export Excel</a>
    <button class="btn-print" onclick="window.print()">🖨 พิมพ์</button>
</div>

<div class="page">
    {{-- Header --}}
    <div class="sheet-header">
        <div class="logo-box">โลโก้<br>โรงเรียน</div>
        <div class="header-text">
            <div class="title1">
                บัญชีรายชื่อนักเรียนชั้น {{ $assign->classSection->level->name ?? '' }}
                ปีการศึกษา {{ $assign->classSection->semester->academicYear->year_name ?? '' }}
            </div>
            <div class="title2">
                โรงเรียน{{ config('app.name','') }}&emsp;
                สำนักงานเขตพื้นที่การศึกษา................................
            </div>
            <div class="title3">
                วิชา: <strong>{{ $assign->subject->name_th }}</strong>
                ({{ $assign->subject->code }})
                &emsp; ห้อง: {{ $assign->classSection->level->name }}/{{ $assign->classSection->section_number }}
                &emsp; ภาคเรียน: {{ $assign->classSection->semester->semester_name ?? '' }}
            </div>
            <div style="font-size:11pt;margin-top:2px">
                ครูผู้สอน: {{ $assign->personnel->thai_prefix ?? '' }}{{ $assign->personnel->thai_firstname }} {{ $assign->personnel->thai_lastname }}
            </div>
        </div>
    </div>

    {{-- Score Table --}}
    <table class="score-table">
        <thead>
            <tr>
                <th class="col-no" rowspan="2">ที่</th>
                <th class="col-idcard" rowspan="2" style="font-size:8.5pt">เลขประจำตัว<br>ประชาชน</th>
                <th class="col-code" rowspan="2" style="font-size:8.5pt">รหัส<br>นักเรียน</th>
                <th class="col-name" rowspan="2">ชื่อ – สกุล</th>
                @foreach($categories as $cat)
                <th class="col-score" style="background:#4caf50;color:#fff">
                    <div class="rotate-header">{{ $cat->name }}</div>
                </th>
                @endforeach
                <th class="col-total" style="background:#f9a825;color:#fff">
                    <div class="rotate-header">รวม</div>
                </th>
                <th class="col-grade" style="background:#e65100;color:#fff">
                    <div class="rotate-header">เกรด</div>
                </th>
            </tr>
            <tr class="max-row">
                @foreach($categories as $cat)
                <td class="col-score">{{ $cat->max_score }}</td>
                @endforeach
                <td class="col-total">100</td>
                <td class="col-grade"></td>
            </tr>
        </thead>
        <tbody>
            @php
                $rowCount = max(30, $students->count());
            @endphp
            @for($i = 0; $i < $rowCount; $i++)
            @php
                $ss      = $students[$i] ?? null;
                $student = $ss?->student;
                $fg      = $student ? ($finalGrades[$student->student_id] ?? null) : null;
            @endphp
            <tr class="data-row">
                <td>{{ $i + 1 }}</td>
                <td>{{ $student?->id_card_number ?? '' }}</td>
                <td>{{ $student?->student_code ?? '' }}</td>
                <td class="col-name">
                    @if($student)
                        {{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }}
                    @endif
                </td>
                @foreach($categories as $cat)
                @php $sc = $student ? ($scoreMatrix[$student->student_id][$cat->category_id] ?? null) : null; @endphp
                <td class="score-val">{{ $sc !== null ? $sc : '' }}</td>
                @endforeach
                <td class="total-val">{{ $fg?->total_score ?? '' }}</td>
                <td class="grade-val {{ ($fg?->remark == 'ผ่าน') ? 'grade-pass' : ($fg ? 'grade-fail' : '') }}">
                    {{ $fg?->grade ?? '' }}
                </td>
            </tr>
            @endfor
        </tbody>
    </table>

    <div class="footer-row">
        @php
            $maleCount   = $students->filter(fn($ss) => ($ss->student->gender ?? '') === 'ชาย')->count();
            $femaleCount = $students->filter(fn($ss) => ($ss->student->gender ?? '') === 'หญิง')->count();
        @endphp
        <span>จำนวนนักเรียน {{ $students->count() }} คน&ensp; ชาย {{ $maleCount }} คน&ensp; หญิง {{ $femaleCount }} คน</span>
        <span>ลงชื่อ ............................................. ครูผู้สอน</span>
    </div>
</div>

<div class="no-print" style="margin-bottom:24px">
    <a href="javascript:history.back()" class="btn-back">← ย้อนกลับ</a>
    <a href="{{ route('grades.excel', $assign->assign_id) }}" class="btn-excel">⬇ Export Excel</a>
    <button class="btn-print" onclick="window.print()">🖨 พิมพ์</button>
</div>

</body>
</html>
