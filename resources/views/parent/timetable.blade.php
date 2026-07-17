@extends('parent.layout')
@section('title', 'ตารางเรียน')

@section('content')
<div class="pp-card">
    <div class="pp-title">ตารางเรียน</div>

    @if(!$section)
        <div class="text-muted">ยังไม่มีข้อมูลห้องเรียนปัจจุบัน</div>
    @else
        <div style="color:#475569; margin-bottom:14px;">
            ปีการศึกษา <strong>{{ $section->semester?->academicYear?->year_name }}</strong>
            ภาคเรียนที่ <strong>{{ $section->semester?->semester_name }}</strong>
            · ห้อง <strong>{{ $section->level?->name }}/{{ $section->section_number }}</strong>
        </div>

        @if($assigns->isEmpty())
            <div class="text-muted">ยังไม่มีข้อมูลตารางเรียน</div>
        @else
            @php
                $palette = ['#1e88e5','#43a047','#e53935','#fb8c00','#8e24aa','#00897b','#3949ab','#f4511e','#039be5','#7cb342','#6d4c41','#00acc1'];
                $colorMap = [];
                foreach ($assigns as $i => $a) { $colorMap[$a->assign_id] = $palette[$i % count($palette)]; }

                $skipCells = [];
                foreach ($slotGrid as $d => $daySlots) {
                    foreach ($daySlots as $i => $cell) {
                        $span = $cell['span'] ?? 1;
                        for ($s = 1; $s < $span; $s++) {
                            $skipCells[$d][$i + $s] = true;
                        }
                    }
                }
            @endphp

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center" style="min-width:1700px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width:90px;">วัน / เวลา</th>
                            @foreach($units as $u)
                                <th>{{ $u }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $day)
                        <tr>
                            <th class="table-light">{{ $day }}</th>
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
                                    <td colspan="{{ $cell['span'] ?? 1 }}" style="padding:0;">
                                        <div style="background:{{ $colorMap[$cell['assign']->assign_id] }}; color:#fff; padding:6px 4px; height:100%; font-size:.78rem; line-height:1.35;">
                                            <div style="font-weight:700;">{{ $cell['assign']->subject->code ?? '' }}</div>
                                            <div>{{ Str::limit($cell['assign']->subject->name_th ?? '-', 14) }}</div>
                                            <div style="opacity:.85;">{{ $cell['assign']->personnel->thai_firstname ?? '' }}</div>
                                            @if($cell['slot']->room)
                                                <div style="font-size:.7rem; opacity:.8;">ห้อง {{ $cell['slot']->room }}</div>
                                            @endif
                                            <div style="font-size:.7rem; opacity:.8;">{{ $tStart }}–{{ $tEnd }}</div>
                                        </div>
                                    </td>
                                @else
                                    <td></td>
                                @endif
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>
@endsection
