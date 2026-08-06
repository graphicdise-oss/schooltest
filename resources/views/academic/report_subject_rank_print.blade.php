<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายงานจัดอันดับคะแนนรายวิชา — {{ $subject->name_th }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'TH Sarabun New','Sarabun','Tahoma',sans-serif; font-size: 14pt; background: #e0e0e0; }

.page {
    width: 210mm; min-height: 297mm; background: #fff;
    margin: 16px auto; padding: 14mm 12mm;
    box-shadow: 0 2px 16px rgba(0,0,0,0.15);
}

.sheet-header { text-align: center; margin-bottom: 10px; }
.sheet-header .title1 { font-size: 17pt; font-weight: bold; }
.sheet-header .title2 { font-size: 13pt; margin-top: 3px; }
.sheet-header .title3 { font-size: 13pt; margin-top: 3px; }

.rank-table { width: 100%; border-collapse: collapse; font-size: 12.5pt; margin-top: 10px; }
.rank-table th, .rank-table td { border: 1px solid #333; padding: 4px 6px; text-align: center; vertical-align: middle; }
.rank-table th { background: #8e24aa; color: #fff; font-weight: bold; }
.rank-table td.name-col { text-align: left; }
.rank-table tbody tr:nth-child(even) { background: #f8f4fb; }
.rank-table .score-col { font-weight: bold; background: #fce4ec; }
.top3 td { font-weight: bold; }
.rank-1 td.rank-col { color: #b8860b; }
.rank-2 td.rank-col { color: #757575; }
.rank-3 td.rank-col { color: #a0522d; }

.footer-row { margin-top: 18px; font-size: 12.5pt; display: flex; justify-content: space-between; }

.no-print { text-align: center; padding: 12px 0; display: flex; justify-content: center; gap: 12px; }
.btn-print { background: #1565c0; color: #fff; border: none; border-radius: 6px; padding: 10px 28px; font-size: 13pt; font-weight: bold; cursor: pointer; font-family: inherit; }
.btn-back  { background: #555; color: #fff; border: none; border-radius: 6px; padding: 10px 20px; font-size: 13pt; font-weight: bold; cursor: pointer; font-family: inherit; text-decoration: none; display: inline-block; }

@media print {
    body { background: #fff; }
    .page { margin: 0; box-shadow: none; padding: 10mm 12mm; }
    .no-print { display: none !important; }
    @page { size: A4 portrait; margin: 0; }
}
</style>
</head>
<body>

<div class="no-print">
    <a href="javascript:history.back()" class="btn-back">← ย้อนกลับ</a>
    <button class="btn-print" onclick="window.print()">🖨 พิมพ์ / บันทึกเป็น PDF</button>
</div>

<div class="page">
    <div class="sheet-header">
        <div class="title1">รายงานจัดอันดับคะแนนรายวิชา</div>
        <div class="title2">{{ $school['name'] ?? '' }}</div>
        <div class="title3">
            วิชา <strong>{{ $subject->name_th }}</strong> ({{ $subject->code }})
            &emsp; ภาคเรียนที่ {{ $semester->semester_name }} ปีการศึกษา {{ $year->year_name }}
        </div>
    </div>

    <table class="rank-table">
        <thead>
            <tr>
                <th style="width:60px">ลำดับ</th>
                <th style="width:100px">รหัสนักเรียน</th>
                <th>ชื่อ – สกุล</th>
                <th style="width:110px">ห้อง</th>
                <th style="width:80px">คะแนน</th>
                <th style="width:70px">ระดับ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
            @php $g = $r['grade']; $student = $g->student; @endphp
            <tr class="{{ $r['rank'] <= 3 ? 'top3 rank-'.$r['rank'] : '' }}">
                <td class="rank-col">{{ $r['rank'] }}</td>
                <td>{{ $student->student_code }}</td>
                <td class="name-col">{{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }}</td>
                <td>{{ $g->teachingAssign->classSection->full_name ?? '-' }}</td>
                <td class="score-col">{{ $g->total_score }}</td>
                <td>{{ $g->grade }}</td>
            </tr>
            @empty
            <tr><td colspan="6">ยังไม่มีข้อมูลคะแนนของวิชานี้ในภาคเรียนนี้</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-row">
        <span>จำนวนนักเรียน {{ count($rows) }} คน</span>
        <span>ลงชื่อ ............................................. ครูผู้สอน</span>
    </div>
</div>

<div class="no-print" style="margin-bottom:24px">
    <a href="javascript:history.back()" class="btn-back">← ย้อนกลับ</a>
    <button class="btn-print" onclick="window.print()">🖨 พิมพ์ / บันทึกเป็น PDF</button>
</div>

</body>
</html>
