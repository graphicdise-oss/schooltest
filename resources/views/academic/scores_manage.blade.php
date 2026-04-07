@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><span>บันทึกคะแนน</span></nav>

    <div class="ac-card">
        <div class="ac-card-header">
            <span><i class="bi bi-pencil-square"></i> {{ $assign->subject->name_th }} ({{ $assign->subject->code }}) — {{ $assign->classSection->level->name }}/{{ $assign->classSection->section_number }}</span>
            <span>ครู: {{ $assign->personnel->thai_firstname }} {{ $assign->personnel->thai_lastname }}</span>
        </div>
        <div class="ac-card-body">

            {{-- หมวดคะแนน --}}
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px">
                <h6 style="font-size:0.9rem; font-weight:700; color:#333; margin:0">หมวดคะแนน</h6>
                <button class="ac-btn ac-btn-success ac-btn-sm" onclick="document.getElementById('catOverlay').classList.add('active')"><i class="bi bi-plus"></i> เพิ่มหมวด</button>
            </div>

            <div class="ac-table-wrap" style="margin-bottom:20px">
                <table class="ac-table">
                    <thead><tr><th>ชื่อหมวด</th><th>คะแนนเต็ม</th><th>น้ำหนัก (%)</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @foreach($categories as $cat)
                        <tr>
                            <td>{{ $cat->name }}</td><td>{{ $cat->max_score }}</td><td>{{ $cat->weight_pct }}%</td>
                            <td>
                                <form action="{{ route('scores.destroyCategory', $cat->category_id) }}" method="POST" style="display:inline" onsubmit="return confirm('ลบ?')">@csrf @method('DELETE')<button class="ac-action-btn ac-action-delete"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ตารางคะแนน --}}
            @if($categories->count() > 0)
            <form method="POST" action="{{ route('scores.save', $assign->assign_id) }}">
                @csrf
                <div class="ac-table-wrap">
                    <table class="ac-table">
                        <thead>
                            <tr>
                                <th>เลขที่</th><th>รหัส</th><th>ชื่อ-นามสกุล</th>
                                @foreach($categories as $cat)<th>{{ $cat->name }}<br><small>({{ $cat->max_score }})</small></th>@endforeach
                                <th>รวม</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $ss)
                            @php $s = $ss->student; @endphp
                            <tr>
                                <td>{{ $ss->student_number }}</td>
                                <td>{{ $s->student_code }}</td>
                                <td style="text-align:left">{{ $s->thai_prefix }}{{ $s->thai_firstname }} {{ $s->thai_lastname }}</td>
                                @foreach($categories as $cat)
                                <td>
                                    <input type="number" name="scores[{{ $s->student_id }}][{{ $cat->category_id }}]"
                                        class="score-input" step="0.5" min="0" max="{{ $cat->max_score }}"
                                        value="{{ $scoreMatrix[$s->student_id][$cat->category_id] ?? '' }}">
                                </td>
                                @endforeach
                                <td style="font-weight:700; color:#4479DA">
                                    @php
                                        $total = 0;
                                        foreach($categories as $cat) {
                                            $total += $scoreMatrix[$s->student_id][$cat->category_id] ?? 0;
                                        }
                                    @endphp
                                    {{ $total }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="ac-save-wrap" style="display:flex; justify-content:center; gap:12px">
                    <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-save"></i> บันทึกคะแนน</button>
                    <button type="button" class="ac-btn ac-btn-success" onclick="document.getElementById('calcForm').submit()"><i class="bi bi-calculator"></i> คำนวณเกรด</button>
                </div>
            </form>

            <form id="calcForm" method="POST" action="{{ route('scores.calculate', $assign->assign_id) }}">@csrf</form>
            @else
            <div class="ac-empty">กรุณาเพิ่มหมวดคะแนนก่อน</div>
            @endif
        </div>
    </div>
</div>

{{-- Modal เพิ่มหมวด --}}
<div class="ac-overlay" id="catOverlay" onclick="if(event.target===this)this.classList.remove('active')">
<div class="ac-modal"><div class="ac-modal-header"><i class="bi bi-plus-circle me-2"></i>เพิ่มหมวดคะแนน</div>
<form method="POST" action="{{ route('scores.storeCategory') }}">@csrf
<input type="hidden" name="assign_id" value="{{ $assign->assign_id }}">
<div class="ac-modal-body">
    <label>ชื่อหมวด *</label><input type="text" name="name" required placeholder="เช่น สอบกลางภาค, งาน, สอบปลายภาค">
    <label>คะแนนเต็ม *</label><input type="number" name="max_score" required value="100" step="0.5">
    <label>น้ำหนัก (%) *</label><input type="number" name="weight_pct" required value="100" step="0.5">
</div>
<div class="ac-modal-footer"><button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('catOverlay').classList.remove('active')">ยกเลิก</button><button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึก</button></div>
</form></div></div>

<script>document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.ac-overlay.active').forEach(el=>el.classList.remove('active'))});</script>
@endsection