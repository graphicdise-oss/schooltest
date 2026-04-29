<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>บัญชีรายชื่อและแบบบันทึกผลการเรียน</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0; padding: 20px; font-size: 13px; color: #000;
        }
        .header-container { text-align: center; margin-bottom: 20px; position: relative; }
        .logo { position: absolute; left: 50px; top: 0; width: 80px; }
        .title { font-size: 16px; font-weight: bold; line-height: 1.5; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px 6px; text-align: center; vertical-align: middle; }
        
        .col-name { text-align: left; white-space: nowrap; width: 25%; }
        .col-score { width: 30px; }
        
        /* สลับสีหัวตารางแบบในรูป */
        th.bg-green { background-color: #28a745 !important; color: white; writing-mode: vertical-lr; transform: rotate(180deg); height: 80px; padding: 5px 2px; }
        th.bg-yellow { background-color: #ffff00 !important; }
        td.bg-yellow { background-color: #ffff00 !important; font-weight: bold; }
        
        .summary-footer { margin-top: 20px; display: flex; justify-content: flex-end; gap: 20px; font-weight: bold; }
        
        @media print {
            body { padding: 0; }
            @page { size: A4 landscape; margin: 1cm; }
            .no-print { display: none !important; }
            th.bg-green { background-color: #28a745 !important; -webkit-print-color-adjust: exact; }
            th.bg-yellow, td.bg-yellow { background-color: #ffff00 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">🖨️ พิมพ์แบบบันทึกคะแนน</button>
    </div>

    <div class="header-container">
        <img src="{{ asset('img/logo/vrulogo.png') }}" class="logo" alt="Logo">
        <div class="title">
            บัญชีรายชื่อนักเรียนชั้น {{ $assign->classSection->level->name }}/{{ $assign->classSection->section_number }} 
            ปีการศึกษา {{ $assign->classSection->semester->academicYear->year_name }}<br>
            โรงเรียน................................................... สังกัด...................................................<br>
            วิชา {{ $assign->subject->name_th }} ({{ $assign->subject->code }}) <br>
            ครูผู้สอน: {{ $assign->personnel->thai_firstname }} {{ $assign->personnel->thai_lastname }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 5%;">ที่</th>
                <th rowspan="2" style="width: 15%;">เลขประจำตัวประชาชน</th>
                <th rowspan="2" style="width: 10%;">รหัสนักเรียน</th>
                <th rowspan="2" class="col-name">ชื่อ - สกุล</th>
                
                @foreach($categories as $cat)
                    <th class="bg-green">{{ $cat->name }}</th>
                @endforeach
                
                <th class="bg-yellow" style="writing-mode: vertical-lr; transform: rotate(180deg); height: 80px;">รวม</th>
                <th rowspan="2">เกรด</th>
            </tr>
            <tr>
                @foreach($categories as $cat)
                    <th>{{ $cat->max_score }}</th>
                @endforeach
                <th class="bg-yellow">100</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $ss)
                @php 
                    $s = $ss->student; 
                    $total = 0;
                    $finalGrade = $assign->finalGrades->where('student_id', $s->student_id)->first();
                @endphp
                <tr>
                    <td>{{ $ss->student_number }}</td>
                    <td>{{ $s->id_card_number }}</td>
                    <td>{{ $s->student_code }}</td>
                    <td class="col-name">{{ $s->thai_prefix }}{{ $s->thai_firstname }} {{ $s->thai_lastname }}</td>
                    
                    @foreach($categories as $cat)
                        @php 
                            $score = $scoreMatrix[$s->student_id][$cat->category_id] ?? ''; 
                            $total += (float)$score;
                        @endphp
                        <td>{{ $score }}</td>
                    @endforeach
                    
                    <td class="bg-yellow">{{ $total > 0 ? $total : '' }}</td>
                    <td class="bg-yellow" style="color: red;">{{ $finalGrade ? $finalGrade->grade : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-footer">
        <div>จำนวนนักเรียน ........ คน</div>
        <div>ชาย ........ คน</div>
        <div>หญิง ........ คน</div>
    </div>

    <script>
        // พิมพ์อัตโนมัติเมื่อเปิดหน้านี้ขึ้นมา
        window.onload = function() {
            setTimeout(function() { window.print(); }, 500);
        }
    </script>
</body>
</html>