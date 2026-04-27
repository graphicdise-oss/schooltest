@extends('layouts.sidebar')
@push('styles')
<style>
.ss-page { padding: 24px 28px; }

.ss-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 22px 22px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.ss-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.ss-icon-blue  { background: #1976d2; }
.ss-icon-green { background: #388e3c; }

.ss-card-header {
    margin-left: 90px; display: flex; align-items: center;
    justify-content: space-between; margin-top: -8px; margin-bottom: 16px;
    flex-wrap: wrap; gap: 8px;
}
.ss-card-title { font-size: 1.05rem; color: #555; font-weight: 600; }

/* Info strip */
.sec-info {
    display: flex; flex-wrap: wrap; gap: 16px;
    background: #f5f7ff; border-radius: 8px; padding: 12px 16px;
    font-size: 0.88rem; color: #444;
}
.sec-info-item { display: flex; align-items: center; gap: 6px; }
.sec-info-item i { color: #5c6bc0; }

/* Subject cards grid */
.subj-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}
.subj-card {
    border: 1.5px solid #e3e8f5; border-radius: 10px;
    padding: 16px; background: #fafbff;
    display: flex; flex-direction: column; gap: 8px;
}
.subj-card:hover { border-color: #5c6bc0; box-shadow: 0 2px 10px rgba(92,107,192,0.12); }

.subj-code {
    font-size: 0.75rem; font-weight: 700; color: #fff;
    background: #5c6bc0; border-radius: 4px; padding: 2px 8px;
    display: inline-block;
}
.subj-name { font-size: 0.97rem; font-weight: 600; color: #1a237e; }
.subj-teacher { font-size: 0.82rem; color: #777; }
.subj-cats {
    font-size: 0.78rem; color: #888;
    display: flex; align-items: center; gap: 6px;
}
.cat-badge {
    background: #e8f5e9; color: #2e7d32; border-radius: 10px;
    padding: 2px 8px; font-size: 0.75rem; font-weight: 600;
}
.cat-badge.none { background: #fff3e0; color: #e65100; }

.subj-actions { display: flex; gap: 8px; margin-top: 4px; }
.btn-score {
    flex: 1; background: #1976d2; color: #fff; border: none;
    border-radius: 6px; padding: 8px 0; font-size: 0.82rem;
    font-weight: 600; cursor: pointer; font-family: inherit;
    text-decoration: none; text-align: center;
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
}
.btn-score:hover { background: #1565c0; color: #fff; }
.btn-print {
    background: #e8eaf6; color: #3949ab; border: none;
    border-radius: 6px; padding: 8px 12px; font-size: 0.82rem;
    font-weight: 600; cursor: pointer; font-family: inherit;
    text-decoration: none; text-align: center;
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
}
.btn-print:hover { background: #c5cae9; color: #1a237e; }

.ss-empty { text-align: center; padding: 40px; color: #aaa; }

.btn-back {
    background: #f5f5f5; color: #555; border: 1px solid #ddd;
    border-radius: 6px; padding: 8px 16px; font-size: 0.85rem;
    font-weight: 600; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-back:hover { background: #eee; color: #333; }
</style>
@endpush

@section('content')
<div class="ss-page">

    {{-- Header card --}}
    <div class="ss-card">
        <div class="ss-icon ss-icon-blue"><i class="bi bi-door-open-fill"></i></div>
        <div class="ss-card-header">
            <span class="ss-card-title">
                รายวิชาในห้อง {{ $section->level->name ?? '' }}/{{ $section->section_number }}
            </span>
            <a href="{{ url()->previous() }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
        </div>

        <div class="sec-info">
            <div class="sec-info-item">
                <i class="bi bi-building"></i>
                <span>ห้อง <strong>{{ $section->level->name ?? '' }}/{{ $section->section_number }}</strong></span>
            </div>
            <div class="sec-info-item">
                <i class="bi bi-calendar3"></i>
                <span>ปีการศึกษา <strong>{{ $section->semester->academicYear->year_name ?? '' }}</strong>
                ภาคเรียน <strong>{{ $section->semester->semester_name ?? '' }}</strong></span>
            </div>
            <div class="sec-info-item">
                <i class="bi bi-people-fill"></i>
                <span>นักเรียน <strong>{{ $students }}</strong> คน</span>
            </div>
            <div class="sec-info-item">
                <i class="bi bi-book-fill"></i>
                <span>{{ $assigns->count() }} วิชา</span>
            </div>
        </div>
    </div>

    {{-- Subject list --}}
    <div class="ss-card">
        <div class="ss-icon ss-icon-green"><i class="bi bi-journal-bookmark-fill"></i></div>
        <div class="ss-card-header">
            <span class="ss-card-title">เลือกวิชาเพื่อบันทึกคะแนน</span>
        </div>

        @if($assigns->count())
        <div class="subj-grid">
            @foreach($assigns as $assign)
            <div class="subj-card">
                <div>
                    <span class="subj-code">{{ $assign->subject->code ?? '' }}</span>
                </div>
                <div class="subj-name">{{ $assign->subject->name_th ?? '' }}</div>
                <div class="subj-teacher">
                    <i class="bi bi-person-fill" style="color:#5c6bc0"></i>
                    {{ $assign->personnel->thai_prefix ?? '' }}{{ $assign->personnel->thai_firstname ?? '' }}
                    {{ $assign->personnel->thai_lastname ?? '' }}
                </div>
                <div class="subj-cats">
                    @if($assign->scoreCategories->count())
                    <span class="cat-badge">
                        <i class="bi bi-check2-square"></i>
                        {{ $assign->scoreCategories->count() }} หมวดคะแนน
                    </span>
                    @else
                    <span class="cat-badge none">
                        <i class="bi bi-exclamation-circle"></i>
                        ยังไม่ตั้งค่าคะแนน
                    </span>
                    @endif
                </div>
                <div class="subj-actions">
                    <a href="{{ route('scores.manage', $assign->assign_id) }}" class="btn-score">
                        <i class="bi bi-pencil-square"></i> บันทึกคะแนน
                    </a>
                    <a href="{{ route('grades.print', $assign->assign_id) }}" class="btn-print" target="_blank">
                        <i class="bi bi-printer"></i> พิมพ์
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="ss-empty">
            <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
            ยังไม่มีวิชาที่สอนในห้องนี้
        </div>
        @endif
    </div>

</div>
@endsection
