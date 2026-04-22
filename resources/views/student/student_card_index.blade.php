@extends('layouts.sidebar')

@push('styles')
<style>
    .sc-page { padding: 24px 28px; }

    .sc-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        padding: 30px 20px 20px; position: relative;
        margin-top: 50px; margin-bottom: 28px;
    }
    .sc-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px; color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .sc-icon-search { background: #00bcd4; }
    .sc-icon-list   { background: #607d8b; }
    .sc-card-title  { margin-left: 90px; font-size: 1.05rem; color: #555; margin-top: -8px; }

    /* Search grid */
    .sc-search-grid {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 14px 40px; margin-top: 20px;
    }
    .sc-field label { font-size: 0.82rem; font-weight: 600; color: #444; margin-bottom: 3px; display: block; }
    .sc-field input, .sc-field select {
        width: 100%; height: 36px; border: none; border-bottom: 1.5px solid #bbb;
        padding: 0 8px; font-size: 0.88rem; font-family: inherit; outline: none;
        background: transparent; box-sizing: border-box;
    }
    .sc-field input:focus, .sc-field select:focus { border-bottom-color: #00bcd4; }
    .sc-field-full { grid-column: 1 / -1; }
    .sc-search-center { display: flex; justify-content: center; margin-top: 22px; }
    .btn-search {
        background: #00bcd4; color: #fff; border: none; border-radius: 6px;
        padding: 10px 36px; font-size: 0.9rem; font-weight: 600;
        cursor: pointer; font-family: inherit;
    }

    /* Table */
    .sc-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 20px; }
    .sc-table thead th { padding: 12px 10px; border-bottom: 2px solid #eee; color: #333; font-weight: 600; text-align: left; }
    .sc-table tbody tr { border-bottom: 1px solid #f0f0f0; }
    .sc-table tbody tr:hover { background: #f9f9f9; }
    .sc-table tbody td { padding: 11px 10px; color: #555; vertical-align: middle; }

    /* Buttons */
    .btn-print-sb    { background: #ff9800; color: #fff; border: none; border-radius: 5px; padding: 7px 14px; font-size: 0.8rem; font-weight: 600; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 5px; }
    .btn-print-school{ background: #4caf50; color: #fff; border: none; border-radius: 5px; padding: 7px 14px; font-size: 0.8rem; font-weight: 600; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 5px; }
    .btn-print-sb:hover     { background: #e68900; }
    .btn-print-school:hover { background: #43a047; }

    .sc-bulk-row { display: flex; gap: 10px; margin-bottom: 10px; justify-content: flex-end; flex-wrap: wrap; }
</style>
@endpush

@section('content')
<div class="sc-page">

    {{-- ===== ค้นหา ===== --}}
    <div class="sc-card">
        <div class="sc-icon sc-icon-search"><i class="bi bi-search"></i></div>
        <div class="sc-card-title">ค้นหา</div>

        <form method="GET" action="{{ route('student-cards.index') }}" id="searchForm">
            <div class="sc-search-grid">
                <div class="sc-field">
                    <label>ปีการศึกษา</label>
                    <select name="year_id" onchange="this.form.submit()">
                        <option value="">-- เลือก --</option>
                        @foreach($academicYears as $y)
                            <option value="{{ $y->year_id }}" {{ request('year_id')==$y->year_id ? 'selected':'' }}>
                                {{ $y->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sc-field">
                    <label>เทอม</label>
                    <select name="semester_id" onchange="this.form.submit()">
                        <option value="">-- เลือก --</option>
                        @foreach($semesters as $sm)
                            <option value="{{ $sm->semester_id }}" {{ request('semester_id')==$sm->semester_id ? 'selected':'' }}>
                                {{ $sm->semester_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sc-field">
                    <label>ระดับชั้นเรียน</label>
                    <select name="level_id" onchange="this.form.submit()">
                        <option value="">-- เลือก --</option>
                        @foreach($levels as $lv)
                            <option value="{{ $lv->level_id }}" {{ request('level_id')==$lv->level_id ? 'selected':'' }}>
                                {{ $lv->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sc-field">
                    <label>ชั้นเรียน</label>
                    <select name="section_id">
                        <option value="">-- เลือก --</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec->section_id }}" {{ request('section_id')==$sec->section_id ? 'selected':'' }}>
                                {{ $sec->level->name ?? '' }} / {{ $sec->section_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sc-field sc-field-full">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" name="search" placeholder="ค้นหา ชื่อ/รหัส" value="{{ request('search') }}">
                </div>
            </div>
            <div class="sc-search-center">
                <button type="submit" class="btn-search"><i class="bi bi-search me-1"></i>ค้นหา</button>
            </div>
        </form>
    </div>

    {{-- ===== รายการ ===== --}}
    <div class="sc-card">
        <div class="sc-icon sc-icon-list"><i class="bi bi-list-ul"></i></div>
        <div class="sc-card-title">รายการ</div>

        @if($students->count() > 0)
        {{-- ปุ่มพิมพ์ทั้งหมด --}}
        <div class="sc-bulk-row">
            <a href="{{ route('student-cards.print-all', request()->query()) }}" target="_blank" class="btn-print-school">
                <i class="bi bi-printer-fill"></i> พิมพ์บัตรนักเรียนทั้งหมด
            </a>
        </div>
        @endif

        <table class="sc-table">
            <thead>
                <tr>
                    <th style="width:60px">ลำดับ</th>
                    <th>รหัสนักเรียน</th>
                    <th>ชื่อ - นามสกุล</th>
                    <th style="width:180px;text-align:center">พิมพ์บัตร</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $i => $row)
                @php $s = $row->student; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $s?->student_code ?? '-' }}</td>
                    <td>{{ ($s?->thai_prefix ?? '') . ($s?->thai_firstname ?? '') . ' ' . ($s?->thai_lastname ?? '') }}</td>
                    <td style="text-align:center">
                        <a href="{{ route('student-cards.print-one', $s->student_id) }}" target="_blank"
                           class="btn-print-school">
                            <i class="bi bi-printer-fill"></i> พิมพ์บัตรนักเรียน
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:30px;color:#aaa">กรุณาเลือกชั้นเรียนเพื่อดูรายชื่อ</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
