@extends('parent.layout')
@section('title', 'ตารางเรียน')

@section('content')
<div class="pp-card">
    <div class="pp-title" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
        <span>ตารางเรียน</span>
        @if($section)
            <a href="{{ route('parent.timetable.print') }}" target="_blank" style="background:#455a64; color:#fff; border-radius:10px; padding:8px 16px; font-size:.85rem; font-weight:600; text-decoration:none;">
                <i class="bi bi-printer"></i> พิมพ์ตารางเรียน
            </a>
        @endif
    </div>

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
            @endphp

            <div style="display:flex; flex-direction:column; gap:10px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.07); background:#fff; padding:16px;">
                @foreach($days as $day)
                <div style="display:flex; align-items:stretch; gap:12px; border-bottom:1px solid #f0f0f0; padding-bottom:10px;">
                    <div style="flex-shrink:0; width:78px; display:flex; align-items:center; justify-content:center; background:#3949ab; color:#fff; border-radius:8px; font-size:.85rem; font-weight:700;">{{ $day }}</div>
                    <div style="flex:1; display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                        @forelse($dayBlocks[$day] as $block)
                            @if($block->type === 'lunch')
                                <div style="width:132px; min-height:78px; border-radius:8px; padding:6px 8px; font-size:.72rem; line-height:1.35; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; background:#e0e0e0; color:#555;">
                                    <div style="font-size:.8rem; font-weight:700;"><i class="bi bi-cup-hot"></i> พักกลางวัน</div>
                                    <div style="font-size:.62rem; opacity:.85; margin-top:2px;">{{ $block->start->format('H:i') }}–{{ $block->end->format('H:i') }}</div>
                                </div>
                            @else
                                <div style="width:132px; min-height:78px; border-radius:8px; padding:6px 8px; font-size:.72rem; line-height:1.35; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; color:#fff; font-weight:600; background:{{ $colorMap[$block->assign->assign_id] }};">
                                    <div style="font-size:.8rem; font-weight:700;">{{ $block->assign->subject->code ?? '' }}</div>
                                    <div style="font-size:.68rem; opacity:.9;">{{ Str::limit($block->assign->subject->name_th ?? '-', 14) }}</div>
                                    <div style="font-size:.65rem; opacity:.85;">{{ $block->assign->personnel->thai_firstname ?? '' }}</div>
                                    @if($block->slot->room)
                                        <div style="font-size:.62rem; opacity:.85;">ห้อง {{ $block->slot->room }}</div>
                                    @endif
                                    <div style="font-size:.62rem; opacity:.85; margin-top:2px;">{{ $block->start->format('H:i') }}–{{ $block->end->format('H:i') }}</div>
                                </div>
                            @endif
                        @empty
                            <div style="font-size:.78rem; color:#bbb; padding:6px 4px;">— ไม่มีคาบเรียน —</div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
@endsection
