<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบบันทึกคะแนนผลการเรียน — {{ $assign->subject->code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Prompt', sans-serif;
            font-size: 12pt;
            color: #000;
            background: #fff;
            padding: 20px;
        }

        /* ===== Print button (hidden when printing) ===== */
        .no-print {
            text-align: center;
            margin-bottom: 16px;
        }
        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #4479DA, #5a95f5);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 28px;
            font-size: 1rem;
            font-family: 'Prompt', sans-serif;
            font-weight: 600;
            cursor: pointer;
            margin-right: 8px;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f5f5f5;
            color: #333;
            border: 1.5px solid #d0d7de;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 1rem;
            font-family: 'Prompt', sans-serif;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        /* ===== Header ===== */
        .doc-header {
            text-align: center;
            margin-bottom: 14px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .doc-header h1 {
            font-size: 16pt;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .doc-header h2 {
            font-size: 13pt;
            font-weight: 600;
            color: #333;
        }

        /* ===== Info row ===== */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px 16px;
            margin-bottom: 14px;
            font-size: 10pt;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 10px 14px;
            background: #f9f9f9;
        }
        .info-item { display: flex; flex-direction: column; gap: 1px; }
        .info-label { font-size: 8.5pt; color: #666; font-weight: 600; }
        .info-value { font-size: 10.5pt; font-weight: 700; color: #000; }

        /* ===== Score table ===== */
        .score-table-wrap {
            overflow-x: auto;
            margin-bottom: 20px;
        }
        table.score-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        table.score-table th,
        table.score-table td {
            border: 1px solid #555;
            padding: 5px 6px;
            text-align: center;
            vertical-align: middle;
        }
        table.score-table thead tr {
            background: #1a1a2e;
            color: #fff;
        }
        table.score-table thead th {
            font-weight: 600;
            font-size: 9pt;
            white-space: nowrap;
        }
        table.score-table tbody tr:nth-child(even) {
            background: #f4f7ff;
        }
        table.score-table tbody td.name-cell {
            text-align: left;
            white-space: nowrap;
        }
        table.score-table tbody td.grade-cell {
            font-weight: 700;
        }
        table.score-table tfoot td {
            background: #eaeaea;
            font-weight: 700;
            font-size: 9pt;
        }

        /* ===== Grade colors (screen only) ===== */
        @media screen {
            .grade-pass  { color: #16a34a; }
            .grade-fail  { color: #dc2626; }
        }

        /* ===== Footer ===== */
        .doc-footer {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            gap: 40px;
        }
        .sign-block {
            text-align: center;
            min-width: 180px;
        }
        .sign-line {
            border-bottom: 1px solid #000;
            margin-bottom: 4px;
            height: 28px;
        }
        .sign-label { font-size: 9.5pt; color: #333; }

        /* ===== Print styles ===== */
        @media print {
            @page { size: A4 landscape; margin: 10mm 12mm; }
            body { padding: 0; font-size: 10pt; }
            .no-print { display: none !important; }
            .info-grid { background: #fff; }
            table.score-table tbody tr:nth-child(even) { background: #f4f7ff; }
            .grade-pass { color: #16a34a !important; }
            .grade-fail { color: #dc2626 !important; }
        }
    </style>
</head>
<body>

    {{-- Print / Back buttons --}}
    <div class="no-print">
        <button onclick="window.print()" class="print-btn">&#128438; พิมพ์ใบเกรด</button>
        <a href="{{ url()->previous() }}" class="back-btn">&#8592; กลับ</a>
    </div>

    {{-- Document header --}}
    <div class="doc-header">
        <h1>ใบบันทึกคะแนนผลการเรียน</h1>
        <h2>Score Sheet</h2>
    </div>

    {{-- Info grid --}}
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">วิชา</span>
            <span class="info-value">{{ $assign->subject->name_th }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">รหัสวิชา</span>
            <span class="info-value">{{ $assign->subject->code }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">ระดับชั้น</span>
            <span class="info-value">{{ $assign->classSection->level->name ?? '—' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">ห้อง</span>
            <span class="info-value">{{ $assign->classSection->section_number ?? '—' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">ครูผู้สอน</span>
            <span class="info-value">{{ $assign->personnel->thai_prefix ?? '' }}{{ $assign->personnel->thai_firstname }} {{ $assign->personnel->thai_lastname }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">ปีการศึกษา</span>
            <span class="info-value">{{ $assign->classSection->semester->academicYear->year_name ?? '—' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">ภาคเรียน</span>
            <span class="info-value">{{ $assign->classSection->semester->semester_name ?? '—' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">จำนวนนักเรียน</span>
            <span class="info-value">{{ $students->count() }} คน</span>
        </div>
    </div>

    {{-- Score table --}}
    <div class="score-table-wrap">
        <table class="score-table">
            <thead>
                <tr>
                    <th rowspan="2">เลขที่</th>
                    <th rowspan="2">รหัสนักเรียน</th>
                    <th rowspan="2" style="min-width:140px; text-align:left">คำนำหน้า ชื่อ-สกุล</th>
                    @foreach($categories as $cat)
                    <th>{{ $cat->name }}<br><small style="font-weight:400">({{ $cat->max_score }} / {{ $cat->weight_pct }}%)</small></th>
                    @endforeach
                    <th rowspan="2">รวม<br>(%)</th>
                    <th rowspan="2">เกรด</th>
                    <th rowspan="2" style="min-width:90px">ลายมือชื่อ</th>
                </tr>
                {{-- No second row needed since we used rowspan on all other headers --}}
            </thead>
            <tbody>
                @foreach($students as $ss)
                @php
                    $s = $ss->student;
                    $fg = $finalGrades[$s->student_id] ?? null;
                    // Calculate weighted total from scoreMatrix
                    $weightedTotal = 0;
                    $weightSum = 0;
                    foreach ($categories as $cat) {
                        $sc = $scoreMatrix[$s->student_id][$cat->category_id] ?? null;
                        if ($sc !== null && $cat->max_score > 0) {
                            $weightedTotal += ($sc / $cat->max_score) * $cat->weight_pct;
                            $weightSum += $cat->weight_pct;
                        }
                    }
                    $totalPct = $fg ? $fg->total_score : ($weightSum > 0 ? round($weightedTotal, 1) : null);
                    $grade    = $fg ? $fg->grade : null;
                    $isPass   = $totalPct !== null && $totalPct >= 50;
                @endphp
                <tr>
                    <td>{{ $ss->student_number }}</td>
                    <td>{{ $s->student_code }}</td>
                    <td class="name-cell">{{ $s->thai_prefix }}{{ $s->thai_firstname }} {{ $s->thai_lastname }}</td>
                    @foreach($categories as $cat)
                    <td>{{ $scoreMatrix[$s->student_id][$cat->category_id] ?? '—' }}</td>
                    @endforeach
                    <td style="font-weight:700">{{ $totalPct !== null ? $totalPct . '%' : '—' }}</td>
                    <td class="grade-cell {{ $grade !== null ? ($isPass ? 'grade-pass' : 'grade-fail') : '' }}">
                        {{ $grade ?? '—' }}
                    </td>
                    <td></td>{{-- signature column --}}
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right">จำนวนนักเรียนทั้งหมด</td>
                    <td colspan="{{ $categories->count() + 3 }}">{{ $students->count() }} คน</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Footer signatures --}}
    <div class="doc-footer">
        <div class="sign-block">
            <div class="sign-line"></div>
            <div class="sign-label">ลงชื่อครูผู้สอน ({{ $assign->personnel->thai_firstname }} {{ $assign->personnel->thai_lastname }})</div>
        </div>
        <div class="sign-block">
            <div class="sign-line"></div>
            <div class="sign-label">วันที่ _____ / _____ / _________</div>
        </div>
    </div>

</body>
</html>
