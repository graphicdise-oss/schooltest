<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
@verbatim
<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
<x:Name>คะแนน</x:Name>
<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
@endverbatim
<style>
body { font-family: 'TH Sarabun New', Tahoma, sans-serif; font-size: 13pt; }
table { border-collapse: collapse; }
th, td { border: 1px solid #333; padding: 4px 6px; text-align: center; vertical-align: middle; }
.name-col { text-align: left; }
.header-green { background: #4caf50; color: #fff; font-weight: bold; }
.header-yellow { background: #f9a825; color: #fff; font-weight: bold; }
.header-orange { background: #e65100; color: #fff; font-weight: bold; }
.max-row { background: #f5f5f5; font-size: 11pt; }
.score-cell { background: #f1f8e9; }
.total-cell { background: #fffde7; font-weight: bold; }
.grade-cell { background: #fff3e0; font-weight: bold; }
</style>
</head>
<body>
<table>
    <tr>
        <td colspan="{{ 4 + $categories->count() + 2 }}" style="font-size:14pt;font-weight:bold;border:none;text-align:center">
            บัญชีรายชื่อนักเรียน — วิชา {{ $assign->subject->name_th }} ({{ $assign->subject->code }})
        </td>
    </tr>
    <tr>
        <td colspan="{{ 4 + $categories->count() + 2 }}" style="border:none;text-align:center">
            ชั้น {{ $assign->classSection->level->name }}/{{ $assign->classSection->section_number }}
            &nbsp;&nbsp; ปีการศึกษา {{ $assign->classSection->semester->academicYear->year_name ?? '' }}
            ภาคเรียน {{ $assign->classSection->semester->semester_name ?? '' }}
            &nbsp;&nbsp; ครูผู้สอน: {{ $assign->personnel->thai_prefix ?? '' }}{{ $assign->personnel->thai_firstname }} {{ $assign->personnel->thai_lastname }}
        </td>
    </tr>
    <tr>
        <th rowspan="2">ที่</th>
        <th rowspan="2">เลขประจำตัวประชาชน</th>
        <th rowspan="2">รหัสนักเรียน</th>
        <th rowspan="2" class="name-col">ชื่อ – สกุล</th>
        @foreach($categories as $cat)
        <th class="header-green">{{ $cat->name }}<br>({{ $cat->max_score }})</th>
        @endforeach
        <th class="header-yellow">รวม<br>(100)</th>
        <th class="header-orange">เกรด</th>
    </tr>
    <tr class="max-row">
        @foreach($categories as $cat)
        <td>เต็ม {{ $cat->max_score }}</td>
        @endforeach
        <td>เต็ม 100</td>
        <td>-</td>
    </tr>
    @php $rowCount = max(30, $students->count()); @endphp
    @for($i = 0; $i < $rowCount; $i++)
    @php
        $ss      = $students[$i] ?? null;
        $student = $ss?->student;
        $fg      = $student ? ($finalGrades[$student->student_id] ?? null) : null;
    @endphp
    <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $student?->id_card_number ?? '' }}</td>
        <td>{{ $student?->student_code ?? '' }}</td>
        <td class="name-col">
            @if($student){{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }}@endif
        </td>
        @foreach($categories as $cat)
        @php $sc = $student ? ($scoreMatrix[$student->student_id][$cat->category_id] ?? null) : null; @endphp
        <td class="score-cell">{{ $sc !== null ? $sc : '' }}</td>
        @endforeach
        <td class="total-cell">{{ $fg?->total_score ?? '' }}</td>
        <td class="grade-cell">{{ $fg?->grade ?? '' }}</td>
    </tr>
    @endfor
    <tr>
        <td colspan="{{ 4 + $categories->count() + 2 }}" style="text-align:left;border-top:2px solid #000">
            จำนวนนักเรียน {{ $students->count() }} คน
        </td>
    </tr>
</table>
</body>
</html>
