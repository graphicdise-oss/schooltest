@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">
<style>
.rp-page { padding: 24px 28px; }
.rp-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 20px 20px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.rp-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff; background: #f9a825;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.rp-card-header { margin-left: 90px; margin-top: -8px; margin-bottom: 20px; }
.rp-card-title { font-size: 1.05rem; color: #555; }
.rp-card-title small { display:block; font-size:0.78rem; color:#999; font-weight:400; margin-top:2px; }

.btn-export {
    background: #2e7d32; color: #fff; border: none; border-radius: 6px;
    padding: 10px 26px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 8px;
}
.btn-export:hover { background: #1b5e20; color: #fff; }
.btn-export.disabled { background: #bbb; pointer-events: none; }

.rp-empty { text-align: center; padding: 24px; color: #aaa; font-size: 0.85rem; }
</style>
@endpush

@section('content')
<div class="rp-page">
    <div class="rp-card">
        <div class="rp-icon"><i class="bi bi-file-earmark-excel"></i></div>
        <div class="rp-card-header">
            <span class="rp-card-title">
                รายงานคะแนนเฉลี่ย 2 ภาคเรียน
                <small>เลือกห้องเรียน แล้วส่งออกเป็นไฟล์ Excel — รวมคะแนนภาคเรียนที่ 1 และ 2 ของปีการศึกษาเดียวกัน</small>
            </span>
        </div>

        <form method="GET" action="{{ route('academic-reports.avg-score') }}">
            <div class="ac-grid-3">
                <div class="ac-field">
                    <label>ปีการศึกษา</label>
                    <select name="year_id" class="ac-select" onchange="this.form.submit()">
                        @foreach($academicYears as $ay)
                        <option value="{{ $ay->year_id }}" {{ (string) $yearId === (string) $ay->year_id ? 'selected' : '' }}>{{ $ay->year_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ac-field">
                    <label>ระดับชั้น</label>
                    <select name="level_id" class="ac-select" onchange="this.form.submit()">
                        <option value="">-- ทุกระดับ --</option>
                        @foreach($levels as $lv)
                        <option value="{{ $lv->level_id }}" {{ (string) $levelId === (string) $lv->level_id ? 'selected' : '' }}>{{ $lv->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ac-field">
                    <label>ห้องเรียน (ภาคเรียนที่ 1)</label>
                    <select name="section_id" class="ac-select" onchange="this.form.submit()">
                        <option value="">-- เลือกห้องเรียน --</option>
                        @foreach($sections as $sec)
                        <option value="{{ $sec->section_id }}" {{ (string) $sectionId === (string) $sec->section_id ? 'selected' : '' }}>{{ $sec->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        @if(!$sem1)
        <div class="rp-empty">ปีการศึกษานี้ยังไม่มีภาคเรียนที่ 1 ในระบบ</div>
        @elseif($sections->isEmpty())
        <div class="rp-empty">ยังไม่มีห้องเรียนของภาคเรียนที่ 1 ปีการศึกษานี้</div>
        @else
        <div class="ac-save-wrap">
            <a class="btn-export {{ $sectionId ? '' : 'disabled' }}"
               href="{{ $sectionId ? route('academic-reports.avg-score.export', ['section_id' => $sectionId]) : '#' }}">
                <i class="bi bi-download"></i> ส่งออกไฟล์ Excel
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
