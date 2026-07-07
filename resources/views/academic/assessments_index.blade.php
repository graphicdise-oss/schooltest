@extends('layouts.sidebar')

@push('styles')
<style>
    body { background: #f4f6f9; }
    .page { padding: 24px 28px; }
    .breadcrumb-custom a { color: #00bcd4; text-decoration: none; font-size: 0.95rem; }
    .breadcrumb-custom i { color: #888; margin: 0 8px; font-size: 0.8rem; }

    .card { background:#fff; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.05); padding:20px 22px; margin-bottom:22px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:16px; display:flex; align-items:center; gap:8px; }

    .filter-row { display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end; }
    .filter-field label { display:block; font-size:0.85rem; color:#555; font-weight:600; margin-bottom:5px; }
    .filter-select {
        border:1px solid #d0d7e5; border-radius:6px; padding:9px 12px; font-size:0.9rem;
        color:#333; font-family:inherit; outline:none; min-width:240px; background:#fff;
    }
    .filter-select:focus { border-color:#4b7ce3; }

    .a-table { width:100%; border-collapse:collapse; font-size:0.9rem; }
    .a-table thead th {
        background:#f2f6ff; color:#082b75; font-weight:600; padding:11px 12px;
        text-align:left; white-space:nowrap; border-bottom:2px solid #e0e7ff;
    }
    .a-table tbody td { padding:9px 12px; border-bottom:1px solid #f0f2f7; color:#444; vertical-align:middle; }
    .a-table tbody tr:hover { background:#f7faff; }
    .cell-select {
        border:1px solid #d0d7e5; border-radius:6px; padding:6px 8px; font-size:0.86rem;
        color:#333; font-family:inherit; outline:none; width:100%; background:#fff;
    }
    .cell-select:focus { border-color:#4b7ce3; }

    .btn-save {
        background:#4caf50; color:#fff; border:none; border-radius:6px; padding:10px 26px;
        font-size:0.92rem; font-weight:700; cursor:pointer; font-family:inherit;
        display:inline-flex; align-items:center; gap:7px;
    }
    .btn-save:hover { background:#43a047; }
    .empty-hint { color:#94a3b8; padding:26px 0; text-align:center; }
</style>
@endpush

@section('content')
@php
    $levels = ['', 'ดีเยี่ยม', 'ดี', 'ผ่าน', 'ไม่ผ่าน'];
    $passOpts = ['', 'ผ่าน', 'ไม่ผ่าน'];
@endphp
<div class="page">

    <nav class="breadcrumb-custom mb-3">
        <a href="#">วิชาการ</a>
        <i class="bi bi-chevron-right"></i>
        <span style="color:#555;">บันทึกผลการประเมิน (อ่าน/คุณลักษณะ/กิจกรรม)</span>
    </nav>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ตัวกรอง --}}
    <div class="card">
        <div class="card-title"><i class="fas fa-filter" style="color:#4b7ce3;"></i> เลือกภาคเรียนและห้องเรียน</div>
        <form method="GET" action="{{ route('assessments.index') }}" class="filter-row">
            <div class="filter-field">
                <label>ภาคเรียน</label>
                <select name="semester_id" class="filter-select" onchange="this.form.submit()">
                    @forelse ($semesters as $s)
                        <option value="{{ $s->semester_id }}" {{ (string)$semesterId === (string)$s->semester_id ? 'selected' : '' }}>
                            ปีการศึกษา {{ $s->academicYear->year_name ?? '-' }} / ภาคเรียน {{ $s->semester_name }}
                        </option>
                    @empty
                        <option value="">— ยังไม่มีภาคเรียน —</option>
                    @endforelse
                </select>
            </div>
            <div class="filter-field">
                <label>ห้องเรียน</label>
                <select name="section_id" class="filter-select" onchange="this.form.submit()">
                    <option value="">— เลือกห้องเรียน —</option>
                    @foreach ($sections as $sec)
                        <option value="{{ $sec->section_id }}" {{ (string)$sectionId === (string)$sec->section_id ? 'selected' : '' }}>
                            {{ $sec->level->name ?? '' }}/{{ $sec->section_number }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    {{-- ตารางกรอกผลประเมิน --}}
    <div class="card">
        <div class="card-title"><i class="fas fa-clipboard-check" style="color:#4b7ce3;"></i> ผลการประเมินรายคน</div>

        @if (!$sectionId)
            <div class="empty-hint"><i class="fas fa-hand-pointer"></i> กรุณาเลือกห้องเรียนด้านบนก่อน</div>
        @elseif ($students->isEmpty())
            <div class="empty-hint"><i class="fas fa-inbox"></i> ไม่มีนักเรียนในห้องนี้</div>
        @else
            <form method="POST" action="{{ route('assessments.save') }}">
                @csrf
                <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                <input type="hidden" name="section_id" value="{{ $sectionId }}">

                <div style="overflow-x:auto;">
                    <table class="a-table">
                        <thead>
                            <tr>
                                <th style="width:55px;">เลขที่</th>
                                <th style="width:110px;">รหัส</th>
                                <th>ชื่อ - สกุล</th>
                                <th style="width:170px;">การอ่าน คิดวิเคราะห์ฯ</th>
                                <th style="width:170px;">คุณลักษณะอันพึงประสงค์</th>
                                <th style="width:150px;">กิจกรรมพัฒนาผู้เรียน</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $stu)
                                @php $a = $assessments->get($stu->student_id); @endphp
                                <tr>
                                    <td>{{ $stu->student_number }}</td>
                                    <td>{{ $stu->student_code }}</td>
                                    <td>{{ $stu->thai_prefix }}{{ $stu->thai_firstname }} {{ $stu->thai_lastname }}</td>
                                    <td>
                                        <select name="assess[{{ $stu->student_id }}][reading]" class="cell-select">
                                            @foreach ($levels as $opt)
                                                <option value="{{ $opt }}" {{ ($a->reading_thinking ?? '') === $opt ? 'selected' : '' }}>{{ $opt === '' ? '—' : $opt }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="assess[{{ $stu->student_id }}][char]" class="cell-select">
                                            @foreach ($levels as $opt)
                                                <option value="{{ $opt }}" {{ ($a->desired_char ?? '') === $opt ? 'selected' : '' }}>{{ $opt === '' ? '—' : $opt }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="assess[{{ $stu->student_id }}][activity]" class="cell-select">
                                            @foreach ($passOpts as $opt)
                                                <option value="{{ $opt }}" {{ ($a->activity ?? '') === $opt ? 'selected' : '' }}>{{ $opt === '' ? '—' : $opt }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:18px; text-align:right;">
                    <button type="submit" class="btn-save"><i class="fas fa-check"></i> บันทึกผลการประเมิน</button>
                </div>
            </form>
        @endif
    </div>

</div>
@endsection
