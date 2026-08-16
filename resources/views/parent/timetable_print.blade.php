<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตารางเรียน {{ $section->full_name ?? '' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        html, body { width: 100%; overflow-x: hidden; }
        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0; padding: 16px; font-size: 11px; color: #000;
        }
        .header-container { text-align: center; margin-bottom: 12px; }
        .school-name { font-size: 16px; font-weight: bold; }
        .section-name { font-size: 13px; margin-top: 3px; }
        .meta { font-size: 11px; color: #444; margin-top: 3px; }

        .days { border:1px solid #000; border-collapse:collapse; width:100%; }
        .day-row { display:flex; border-bottom:1px solid #000; align-items:stretch; }
        .day-row:last-child { border-bottom:none; }
        .day-label {
            flex-shrink:0; width:56px; display:flex; align-items:center; justify-content:center;
            background:#f0f0f0; font-weight:bold; font-size:11px; border-right:1px solid #000; padding:4px 2px;
        }
        .day-blocks { flex:1; display:flex; flex-wrap:wrap; gap:4px; align-items:center; padding:4px; }
        .block {
            width:86px; min-height:44px; border-radius:3px; padding:3px 4px;
            font-size:8.5px; line-height:1.25; display:flex; flex-direction:column;
            justify-content:center; align-items:center; text-align:center;
            background:#fff; color:#000; border:1px solid #000;
        }
        .block-code { font-weight:bold; font-size:9px; }
        .block-room, .block-time { font-size:8px; color:#333; }
        .block-lunch { background:#f5f5f5; color:#555; border:1px dashed #999; }
        .block-empty { font-size:10px; color:#999; padding:4px; }

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
        <div class="school-name">{{ $school['name'] ?? config('school.name') }}</div>
        @if($section)
            <div class="section-name">ตารางเรียน ชั้น {{ $section->full_name ?? '' }}</div>
            <div class="meta">
                ปีการศึกษา {{ $section->semester?->academicYear?->year_name }} ภาคเรียนที่ {{ $section->semester?->semester_name }}
                @if($section->homeroomTeacher)
                    · ครูประจำชั้น {{ $section->homeroomTeacher->thai_prefix }}{{ $section->homeroomTeacher->thai_firstname }} {{ $section->homeroomTeacher->thai_lastname }}
                @endif
                · นักเรียน {{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }}
            </div>
        @else
            <div class="section-name">ตารางเรียน</div>
            <div class="meta">ยังไม่มีข้อมูลห้องเรียนปัจจุบัน</div>
        @endif
    </div>

    @if($section)
    <div class="days">
        @foreach($days as $day)
        <div class="day-row">
            <div class="day-label">{{ $day }}</div>
            <div class="day-blocks">
                @forelse($dayBlocks[$day] as $block)
                    @if($block->type === 'lunch')
                        <div class="block block-lunch">
                            <div class="block-code">พักกลางวัน</div>
                            <div class="block-time">{{ $block->start->format('H:i') }}–{{ $block->end->format('H:i') }}</div>
                        </div>
                    @else
                        <div class="block">
                            <div class="block-code">{{ $block->assign->subject->code ?? '' }}</div>
                            <div>{{ Str::limit($block->assign->subject->name_th ?? '-', 16) }}</div>
                            <div class="block-room">{{ $block->assign->personnel->thai_firstname ?? '' }}</div>
                            @if($block->slot->room)
                                <div class="block-room">ห้อง {{ $block->slot->room }}</div>
                            @endif
                            <div class="block-time">{{ $block->start->format('H:i') }}–{{ $block->end->format('H:i') }}</div>
                        </div>
                    @endif
                @empty
                    <div class="block-empty">— ไม่มีคาบเรียน —</div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
    @endif

</body>
</html>
