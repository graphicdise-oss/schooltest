<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
<x:Name>คะแนนเฉลี่ย</x:Name>
<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
@endverbatim
<style>
body { font-family: 'TH Sarabun New', Tahoma, sans-serif; font-size: 12pt; }
table { border-collapse: collapse; }
th, td { border: 1px solid #333; padding: 3px 5px; text-align: center; vertical-align: middle; white-space: nowrap; }
.name-col { text-align: left; white-space: nowrap; }
.title-row { font-size: 14pt; font-weight: bold; border: none; }
.subtitle-row { border: none; }
.header-fixed { background: #dcdcdc; font-weight: bold; }
.header-basic { background: #4caf50; color: #fff; font-weight: bold; }
.header-extra { background: #1e88e5; color: #fff; font-weight: bold; }
.subject-name { background: #f5f5f5; font-weight: bold; font-size: 10.5pt; }
.credit-row { background: #fafafa; font-size: 9.5pt; color: #666; }
.subhead-row { background: #fff9c4; font-size: 9.5pt; }
.max-row { background: #f5f5f5; font-size: 9pt; color: #777; }
.sum-cell { background: #f1f8e9; }
.total-cell { background: #fffde7; font-weight: bold; }
.gpa-cell { background: #fff3e0; font-weight: bold; }
.rank-cell { background: #fce4ec; font-weight: bold; }
</style>
</head>
<body>
@php
    $fixedCols = 7; // ลำดับ, รหัสนักเรียน, ชื่อ-สกุล, รวม, เฉลี่ย, ระดับ, ลำดับคะแนน
    $totalCols = $fixedCols + $allSubjects->count() * 5;
@endphp
<table>
    <tr>
        <td colspan="{{ $totalCols }}" class="title-row">รายงานคะแนนเฉลี่ย 2 ภาคเรียน</td>
    </tr>
    <tr>
        <td colspan="{{ $totalCols }}" class="subtitle-row">
            ภาคเรียนที่ 1-2 ปีการศึกษา {{ $year->year_name }}
            &nbsp;&nbsp; ชั้นเรียน {{ $section1->full_name }}
        </td>
    </tr>
    <tr>
        <th rowspan="3" class="header-fixed">ลำดับ</th>
        <th rowspan="3" class="header-fixed">รหัสนักเรียน</th>
        <th rowspan="3" class="header-fixed">ชื่อ-สกุล</th>
        <th rowspan="3" class="header-fixed">รวม<br>(เต็ม {{ $maxScore }} คะแนน)</th>
        <th rowspan="3" class="header-fixed">เฉลี่ย</th>
        <th rowspan="3" class="header-fixed">ระดับ</th>
        <th rowspan="3" class="header-fixed">ลำดับคะแนน</th>
        @if($basicSubjects->count())
        <th colspan="{{ $basicSubjects->count() * 5 }}" class="header-basic">รายวิชาพื้นฐาน</th>
        @endif
        @if($extraSubjects->count())
        <th colspan="{{ $extraSubjects->count() * 5 }}" class="header-extra">รายวิชาเพิ่มเติม</th>
        @endif
    </tr>
    <tr>
        @foreach($allSubjects as $subj)
        <th colspan="5" class="subject-name">{{ $subj->name_th }}</th>
        @endforeach
    </tr>
    <tr>
        @foreach($allSubjects as $subj)
        <th colspan="5" class="credit-row">{{ rtrim(rtrim(number_format($subj->credits ?? 0, 1), '0'), '.') }} หน่วยกิต</th>
        @endforeach
    </tr>
    <tr class="subhead-row">
        <td colspan="7" style="border:none"></td>
        @foreach($allSubjects as $subj)
        <th>ภาค 1</th><th>ภาค 2</th><th>รวม</th><th>เฉลี่ย</th><th>ระดับ</th>
        @endforeach
    </tr>
    <tr class="max-row">
        <td colspan="7" style="border:none"></td>
        @foreach($allSubjects as $subj)
        <td>(100)</td><td>(100)</td><td>(200)</td><td>(100)</td><td>(4.0)</td>
        @endforeach
    </tr>
    @forelse($rows as $i => $r)
    <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $r['student']->student_code }}</td>
        <td class="name-col">{{ $r['student']->thai_prefix }}{{ $r['student']->thai_firstname }} {{ $r['student']->thai_lastname }}</td>
        <td class="total-cell">{{ $r['total'] }}</td>
        <td class="total-cell">{{ number_format($r['avg'], 2) }}</td>
        <td class="gpa-cell">{{ number_format($r['gpa'], 2) }}</td>
        <td class="rank-cell">{{ $r['rank'] }}</td>
        @foreach($r['subjectCols'] as $c)
        <td>{{ $c['s1'] ?? '-' }}</td>
        <td>{{ $c['s2'] ?? '-' }}</td>
        <td class="sum-cell">{{ $c['sum'] ?? '-' }}</td>
        <td class="sum-cell">{{ $c['avg'] !== null ? $c['avg'] : '-' }}</td>
        <td class="sum-cell">{{ $c['point'] !== null ? number_format($c['point'], 1) : '-' }}</td>
        @endforeach
    </tr>
    @empty
    <tr>
        <td colspan="{{ $totalCols }}">ไม่มีนักเรียนในห้องนี้</td>
    </tr>
    @endforelse
</table>
</body>
</html>
