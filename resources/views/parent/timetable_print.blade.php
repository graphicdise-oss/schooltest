<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตารางเรียน {{ $section->level->name ?? '' }}/{{ $section->section_number ?? '' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0; padding: 20px; font-size: 12px; color: #000;
        }
        .header-container { text-align: center; margin-bottom: 16px; }
        .school-name { font-size: 18px; font-weight: bold; }
        .section-name { font-size: 15px; margin-top: 4px; }
        .meta { font-size: 12px; color: #444; margin-top: 4px; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 4px 2px; text-align: center; vertical-align: middle; word-break: break-word; }
        th.day-col, td.day-col { width: 70px; background: #f0f0f0; font-weight: bold; }
        th { font-size: 10px; background: #f0f0f0; }
        .slot { font-size: 10px; line-height: 1.3; padding: 3px 2px; }
        .slot-code { font-weight: bold; }
        .slot-room, .slot-time { font-size: 9px; color: #333; }

        @media print {
            body { padding: 0; }
            @page { size: A4 landscape; margin: 1cm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 16px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">🖨️ พิมพ์ตารางเรียน</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #eee; color: #333; border: none; border-radius: 5px; cursor: pointer;">✕ ปิด</button>
    </div>

    <div class="header-container">
        <div class="school-name">{{ config('school.name') }}</div>
        @if($section)
            <div class="section-name">ตารางเรียน ชั้น {{ $section->level->name ?? '' }}/{{ $section->section_number }}</div>
            <div class="meta">
                ปีการศึกษา {{ $section->semester?->academicYear?->year_name }} ภาคเรียนที่ {{ $section->semester?->semester_name }}
                · นักเรียน {{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }}
            </div>
        @else
            <div class="section-name">ตารางเรียน</div>
            <div class="meta">ยังไม่มีข้อมูลห้องเรียนปัจจุบัน</div>
        @endif
    </div>

    @if($section && $assigns->isNotEmpty())
        @php
            $skipCells = [];
            foreach ($slotGrid as $d => $daySlots) {
                foreach ($daySlots as $startIdx => $cell) {
                    $span = $cell['span'] ?? 1;
                    for ($s = 1; $s < $span; $s++) {
                        $skipCells[$d][$startIdx + $s] = true;
                    }
                }
            }
        @endphp

        <table>
            <thead>
                <tr>
                    <th class="day-col">วัน / เวลา</th>
                    @foreach($units as $u)
                        <th>{{ $u }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($days as $day)
                    <tr>
                        <td class="day-col">{{ $day }}</td>
                        @foreach($units as $i => $u)
                            @if(isset($skipCells[$day][$i]))
                                @continue
                            @endif
                            @php $cell = $slotGrid[$day][$i] ?? null; @endphp
                            @if($cell)
                                @php
                                    $tStart = \Carbon\Carbon::parse($cell['slot']->start_time)->format('H:i');
                                    $tEnd   = \Carbon\Carbon::parse($cell['slot']->end_time)->format('H:i');
                                @endphp
                                <td colspan="{{ $cell['span'] ?? 1 }}" class="slot">
                                    <div class="slot-code">{{ $cell['assign']->subject->code ?? '' }}</div>
                                    <div>{{ Str::limit($cell['assign']->subject->name_th ?? '-', 16) }}</div>
                                    <div class="slot-room">{{ $cell['assign']->personnel->thai_firstname ?? '' }}</div>
                                    @if($cell['slot']->room)
                                        <div class="slot-room">ห้อง {{ $cell['slot']->room }}</div>
                                    @endif
                                    <div class="slot-time">{{ $tStart }}–{{ $tEnd }}</div>
                                </td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>
