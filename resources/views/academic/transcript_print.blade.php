<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ใบแสดงผลการเรียน — {{ $student->thai_firstname }} {{ $student->thai_lastname }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'TH Sarabun New', 'Sarabun', 'Tahoma', sans-serif; font-size: 13pt; color: #000; background: #f0f0f0; }

.page {
    width: 210mm; min-height: 297mm;
    background: #fff; margin: 20px auto; padding: 14mm 12mm 12mm;
    box-shadow: 0 2px 16px rgba(0,0,0,0.12);
}

/* Header */
.school-header { text-align: center; margin-bottom: 8px; border-bottom: 2.5px solid #000; padding-bottom: 8px; }
.school-name { font-size: 18pt; font-weight: bold; line-height: 1.3; }
.doc-title { font-size: 14pt; font-weight: bold; margin-top: 2px; }

/* Student info */
.student-info { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 20px; margin: 8px 0; font-size: 12pt; }
.student-info-row { display: flex; gap: 6px; }
.student-info-row .label { font-weight: bold; white-space: nowrap; }
.student-info-full { grid-column: 1 / -1; }
.right-info { text-align: right; }

/* Content in two columns */
.transcript-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 0 10mm; margin-top: 6px; }
.semester-block { margin-bottom: 8px; }
.semester-title { font-size: 12pt; font-weight: bold; text-align: center; text-decoration: underline; margin-bottom: 3px; }

table.course-table { width: 100%; border-collapse: collapse; font-size: 11pt; }
table.course-table th {
    font-weight: bold; font-size: 10.5pt;
    border-bottom: 1px solid #000; padding: 2px 4px;
    text-align: center;
}
table.course-table th.left { text-align: left; }
table.course-table td { padding: 1px 4px; vertical-align: top; }
table.course-table td.center { text-align: center; }
table.course-table td.right { text-align: right; }
table.course-table tr.sem-total td {
    border-top: 1px solid #555; font-weight: bold; font-size: 10pt;
    padding-top: 3px; padding-bottom: 3px;
}

/* Summary bar */
.summary-bar {
    border-top: 2px solid #000; border-bottom: 2px solid #000;
    margin-top: 8px; padding: 5px 0;
    display: grid; grid-template-columns: 1fr 1fr; gap: 4px;
    font-size: 12pt;
}
.summary-item { display: flex; gap: 8px; padding: 0 8px; }
.summary-item .label { font-weight: bold; }

/* Signature section */
.sig-section {
    display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
    margin-top: 14px;
}
.sig-box { border: 1px solid #000; padding: 12px 10px 8px; text-align: center; }
.sig-box .sig-title { font-weight: bold; font-size: 11pt; border-bottom: 1px solid #aaa; padding-bottom: 4px; margin-bottom: 10px; }
.sig-line { border-bottom: 1px dotted #000; margin: 20px 10px 4px; }
.sig-name { font-size: 10pt; color: #444; }
.sig-date { font-size: 10pt; text-align: right; margin-top: 6px; }

/* No-print buttons */
.no-print {
    text-align: center; margin: 16px 0;
    display: flex; gap: 12px; justify-content: center;
}
.btn-print {
    background: #1565c0; color: #fff; border: none; border-radius: 6px;
    padding: 10px 32px; font-size: 14pt; font-weight: bold; cursor: pointer;
    font-family: inherit;
}
.btn-back {
    background: #555; color: #fff; border: none; border-radius: 6px;
    padding: 10px 24px; font-size: 14pt; font-weight: bold; cursor: pointer;
    font-family: inherit; text-decoration: none; display: inline-block;
}

@media print {
    body { background: #fff; }
    .page { margin: 0; box-shadow: none; padding: 10mm 10mm 8mm; }
    .no-print { display: none !important; }
    @page { size: A4 portrait; margin: 0; }
}
</style>
</head>
<body>

<div class="no-print">
    <a href="javascript:history.back()" class="btn-back">← ย้อนกลับ</a>
    <button class="btn-print" onclick="window.print()">🖨 พิมพ์ใบแสดงผลการเรียน</button>
</div>

<div class="page">

    {{-- School Header --}}
    <div class="school-header">
        <div class="school-name">โรงเรียน{{ config('app.name', '') }}</div>
        <div class="doc-title">ใบแสดงผลการเรียน (Transcript)</div>
    </div>

    {{-- Student Info --}}
    <div class="student-info">
        <div class="student-info-row">
            <span class="label">รหัสนักเรียน</span>
            <span>{{ $student->student_code ?? '-' }}</span>
        </div>
        <div class="student-info-row right-info">
            <span class="label">ชั้น/ห้อง</span>
            <span>
                @php
                    $latestSection = $student->studentSections()->with('classSection.level')->latest()->first() ?? null;
                @endphp
                {{ $latestSection?->classSection?->level?->name ?? '-' }}
                / {{ $latestSection?->classSection?->section_number ?? '-' }}
            </span>
        </div>
        <div class="student-info-row student-info-full">
            <span class="label">ชื่อ-นามสกุล</span>
            <span>{{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }}</span>
        </div>
    </div>

    {{-- Two-column course layout --}}
    <div class="transcript-cols">
    @php
        $semList = $grades->values();
        $semKeys = $grades->keys()->values();
        $leftSems  = $semList->filter(fn($v, $k) => $k % 2 === 0);
        $rightSems = $semList->filter(fn($v, $k) => $k % 2 !== 0);
        $leftKeys  = $semKeys->filter(fn($v, $k) => $k % 2 === 0);
        $rightKeys = $semKeys->filter(fn($v, $k) => $k % 2 !== 0);

        $renderSems = function($semCollection, $keyCollection) {
            return ['sems' => $semCollection, 'keys' => $keyCollection];
        };
    @endphp

    {{-- Left column --}}
    <div>
        @foreach($leftSems as $idx => $semGrades)
        @php
            $key = $leftKeys->values()[$idx] ?? '';
            [$yearName, $termName] = explode('|', $key . '|');
            $semCredits = 0; $semPoints = 0;
            foreach($semGrades as $g) {
                $c = $g->teachingAssign->subject->credits ?? 0;
                $semCredits += $c;
                $semPoints += ($g->gpa_point ?? 0) * $c;
            }
            $semGPA = $semCredits > 0 ? round($semPoints / $semCredits, 2) : 0;
        @endphp
        <div class="semester-block">
            <div class="semester-title">ภาคเรียนที่ {{ $termName }} / {{ $yearName }}</div>
            <table class="course-table">
                <thead>
                    <tr>
                        <th class="left" style="width:22%">รหัสวิชา</th>
                        <th class="left">ชื่อวิชา</th>
                        <th style="width:10%">นก.</th>
                        <th style="width:12%">ระดับ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($semGrades as $g)
                    <tr>
                        <td>{{ $g->teachingAssign->subject->code ?? '-' }}</td>
                        <td>{{ $g->teachingAssign->subject->name_th ?? '-' }}</td>
                        <td class="center">{{ $g->teachingAssign->subject->credits ?? '-' }}</td>
                        <td class="center" style="font-weight:bold">{{ $g->grade }}</td>
                    </tr>
                    @endforeach
                    <tr class="sem-total">
                        <td colspan="2" style="font-size:9.5pt">
                            หน่วยกิตสะสม —
                            หน่วยกิตประจำภาค {{ $semCredits }}
                        </td>
                        <td class="center">{{ $semCredits }}</td>
                        <td class="center">{{ $semGPA }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="font-size:9.5pt; color:#333; padding-bottom:2px">
                            เกรดเฉลี่ยสะสม — &nbsp; เกรดเฉลี่ยประจำภาค {{ $semGPA }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endforeach
    </div>

    {{-- Right column --}}
    <div>
        @foreach($rightSems as $idx => $semGrades)
        @php
            $key = $rightKeys->values()[$idx] ?? '';
            [$yearName, $termName] = explode('|', $key . '|');
            $semCredits = 0; $semPoints = 0;
            foreach($semGrades as $g) {
                $c = $g->teachingAssign->subject->credits ?? 0;
                $semCredits += $c;
                $semPoints += ($g->gpa_point ?? 0) * $c;
            }
            $semGPA = $semCredits > 0 ? round($semPoints / $semCredits, 2) : 0;
        @endphp
        <div class="semester-block">
            <div class="semester-title">ภาคเรียนที่ {{ $termName }} / {{ $yearName }}</div>
            <table class="course-table">
                <thead>
                    <tr>
                        <th class="left" style="width:22%">รหัสวิชา</th>
                        <th class="left">ชื่อวิชา</th>
                        <th style="width:10%">นก.</th>
                        <th style="width:12%">ระดับ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($semGrades as $g)
                    <tr>
                        <td>{{ $g->teachingAssign->subject->code ?? '-' }}</td>
                        <td>{{ $g->teachingAssign->subject->name_th ?? '-' }}</td>
                        <td class="center">{{ $g->teachingAssign->subject->credits ?? '-' }}</td>
                        <td class="center" style="font-weight:bold">{{ $g->grade }}</td>
                    </tr>
                    @endforeach
                    <tr class="sem-total">
                        <td colspan="2" style="font-size:9.5pt">
                            หน่วยกิตสะสม —
                            หน่วยกิตประจำภาค {{ $semCredits }}
                        </td>
                        <td class="center">{{ $semCredits }}</td>
                        <td class="center">{{ $semGPA }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="font-size:9.5pt; color:#333; padding-bottom:2px">
                            เกรดเฉลี่ยสะสม — &nbsp; เกรดเฉลี่ยประจำภาค {{ $semGPA }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
    </div>

    {{-- Summary --}}
    <div class="summary-bar">
        <div class="summary-item">
            <span class="label">จำนวนหน่วยกิตรวม</span>
            <span>{{ $totalCredits }}</span>
        </div>
        <div class="summary-item">
            <span class="label">ค่าคะแนนเฉลี่ยสะสม (GPA)</span>
            <span style="font-size:14pt; font-weight:bold; color:#1a237e">{{ $gpa }}</span>
        </div>
    </div>

    {{-- Signatures --}}
    <div class="sig-section">
        <div class="sig-box">
            <div class="sig-title">สำหรับนักเรียน</div>
            <div style="height:30px"></div>
            <div class="sig-line"></div>
            <div class="sig-name">
                ({{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }})
            </div>
            <div class="sig-date">วันที่ ........../........../..........…</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">สำหรับผู้บริหาร / ผู้ได้รับมอบหมาย</div>
            <div style="height:30px"></div>
            <div class="sig-line"></div>
            <div class="sig-name">(..................................................)</div>
            <div class="sig-date">วันที่ ........../........../..........…</div>
        </div>
    </div>

</div>

<div class="no-print" style="margin-bottom:30px">
    <a href="javascript:history.back()" class="btn-back">← ย้อนกลับ</a>
    <button class="btn-print" onclick="window.print()">🖨 พิมพ์ใบแสดงผลการเรียน</button>
</div>

</body>
</html>
