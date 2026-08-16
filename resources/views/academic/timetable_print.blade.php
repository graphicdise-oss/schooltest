<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตารางสอน {{ $section->level->name ?? '' }}/{{ $section->section_number }}</title>
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

        .days { border:1px solid #000; border-collapse:collapse; width:100%; }
        .day-row { display:flex; border-bottom:1px solid #000; align-items:stretch; }
        .day-row:last-child { border-bottom:none; }
        .day-label {
            flex-shrink:0; width:70px; display:flex; align-items:center; justify-content:center;
            background:#f0f0f0; font-weight:bold; font-size:12px; border-right:1px solid #000; padding:6px 2px;
        }
        .day-blocks { flex:1; display:flex; flex-wrap:wrap; gap:6px; align-items:center; padding:6px; }
        .block {
            width:118px; min-height:64px; border-radius:4px; padding:5px 6px;
            font-size:10px; line-height:1.3; display:flex; flex-direction:column;
            justify-content:center; align-items:center; text-align:center; color:#fff;
        }
        .block-code { font-weight:bold; font-size:11px; }
        .block-room, .block-time { font-size:9px; opacity:.9; }
        .block-lunch { background:#eee; color:#555; border:1px solid #ccc; }
        .block-empty { font-size:11px; color:#999; padding:6px; }

        @media print {
            body { padding: 0; }
            @page { size: A4 landscape; margin: 1cm; }
            .no-print { display: none !important; }
            .block { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 16px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">🖨️ พิมพ์ตารางสอน</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #eee; color: #333; border: none; border-radius: 5px; cursor: pointer;">✕ ปิด</button>
    </div>

    <div class="header-container">
        <div class="school-name">{{ config('school.name') }}</div>
        <div class="section-name">ตารางสอน ชั้น {{ $section->level->name ?? '' }}/{{ $section->section_number }}</div>
        <div class="meta">
            ปีการศึกษา {{ $section->semester?->academicYear?->year_name }} ภาคเรียนที่ {{ $section->semester?->semester_name }}
            @if($section->homeroomTeacher)
                · ครูประจำชั้น {{ $section->homeroomTeacher->thai_prefix }}{{ $section->homeroomTeacher->thai_firstname }} {{ $section->homeroomTeacher->thai_lastname }}
            @endif
        </div>
    </div>

    @php
        $palette = ['#1e88e5','#43a047','#e53935','#fb8c00','#8e24aa','#00897b','#3949ab','#f4511e','#039be5','#7cb342','#6d4c41','#00acc1'];
        $colorMap = [];
        foreach ($assigns as $i => $a) { $colorMap[$a->assign_id] = $palette[$i % count($palette)]; }
    @endphp

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
                        <div class="block" style="background:{{ $colorMap[$block->assign->assign_id] }};">
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

</body>
</html>
