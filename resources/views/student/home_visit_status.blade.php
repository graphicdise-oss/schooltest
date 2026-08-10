@extends('layouts.sidebar')

@push('styles')
<style>
    .hv-page { padding: 24px 28px; }
    .hv-breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; margin-bottom: 20px; color: #555; }
    .hv-breadcrumb a { color: #5482e7; text-decoration: none; font-weight: 500; }
    .hv-breadcrumb span { color: #5482e7; font-weight: 600; }
    .hv-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); padding: 22px; margin-bottom: 20px; }
    .hv-card-title { color: #082b75; font-weight: 700; font-size: 1.05rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; justify-content: space-between; }
    .hv-form-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px 20px; align-items: end; }
    .hv-form-row { display: flex; flex-direction: column; gap: 5px; }
    .hv-label { font-size: 0.85rem; font-weight: 600; color: #444; }
    .hv-select { border: 1px solid #d0d7e5; border-radius: 6px; padding: 8px 12px; font-size: 0.9rem; font-family: inherit; outline: none; box-sizing: border-box; width: 100%; background:#fff; }
    .btn-search { background: #5482e7; color: #fff; border: none; border-radius: 6px; padding: 9px 24px; font-size: 0.88rem; font-weight: 600; cursor: pointer; font-family: inherit; height: 38px; }
    .btn-search:hover { background: #446bca; }

    .summary-pill { display: inline-flex; align-items: center; gap: 8px; background: #eef6ff; color: #3949ab; border-radius: 20px; padding: 6px 16px; font-size: 0.88rem; font-weight: 700; }
    .summary-pill.warn { background: #fff3e0; color: #e65100; }

    .hv-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.85rem; }
    .hv-table thead th { padding: 12px 10px; font-weight: 600; color: #fff; background: #5482e7; text-align: center; white-space: nowrap; }
    .hv-table thead th:first-child { border-top-left-radius: 8px; }
    .hv-table thead th:last-child { border-top-right-radius: 8px; }
    .hv-table tbody tr td { border-bottom: 1px solid #f2f4f8; }
    .hv-table tbody tr:nth-child(even) td { background: #fafcff; }
    .hv-table tbody td { padding: 10px 12px; color: #555; vertical-align: middle; text-align: center; }
    .hv-table tbody td.td-name { text-align: left; }
    .status-visited { background: #e8f5e9; color: #2e7d32; border-radius: 20px; padding: 3px 12px; font-size: 0.78rem; font-weight: 700; }
    .status-pending { background: #ffebee; color: #c62828; border-radius: 20px; padding: 3px 12px; font-size: 0.78rem; font-weight: 700; }
    .hv-empty { text-align: center; color: #94a3b8; padding: 30px 0; }
</style>
@endpush

@section('content')
<div class="hv-page">
    <nav class="hv-breadcrumb">
        <a href="#">กิจการนักเรียน</a>
        <i class="bi bi-chevron-right"></i>
        <span>สรุปสถานะการเยี่ยมบ้าน</span>
    </nav>

    <div class="hv-card">
        <div class="hv-card-title"><i class="fas fa-filter" style="color:#4b7ce3;"></i> เลือกห้องเรียน</div>
        <form method="GET" action="{{ route('home-visits.status-summary') }}">
            <div class="hv-form-grid">
                <div class="hv-form-row">
                    <label class="hv-label">ปีการศึกษา</label>
                    <select name="year_id" class="hv-select" onchange="this.form.submit()">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->year_id }}" {{ $yearId == $ay->year_id ? 'selected' : '' }}>{{ $ay->year_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="hv-form-row">
                    <label class="hv-label">ภาคเรียน</label>
                    <select name="semester_id" class="hv-select" onchange="this.form.submit()">
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->semester_id }}" {{ $semesterId == $sem->semester_id ? 'selected' : '' }}>{{ $sem->semester_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="hv-form-row">
                    <label class="hv-label">ระดับชั้น</label>
                    <select name="level_id" class="hv-select" onchange="this.form.submit()">
                        <option value="">-- เลือกระดับชั้น --</option>
                        @foreach($levels as $lv)
                            <option value="{{ $lv->level_id }}" {{ $levelId == $lv->level_id ? 'selected' : '' }}>{{ $lv->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="hv-form-row">
                    <label class="hv-label">ห้องเรียน</label>
                    <select name="section_id" class="hv-select" onchange="this.form.submit()">
                        <option value="">-- เลือกห้องเรียน --</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec->section_id }}" {{ $sectionId == $sec->section_id ? 'selected' : '' }}>{{ $sec->level->name }}/{{ $sec->section_number }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    <div class="hv-card">
        <div class="hv-card-title">
            <span><i class="fas fa-house-user" style="color:#4b7ce3;"></i> สถานะการเยี่ยมบ้าน</span>
            <span style="display:flex; gap:10px;">
                <span class="summary-pill">เยี่ยมแล้ว {{ $visitedCount }}/{{ $totalCount }} คน</span>
                @if($totalCount - $visitedCount > 0)
                    <span class="summary-pill warn">ยังไม่เยี่ยม {{ $totalCount - $visitedCount }} คน</span>
                @endif
            </span>
        </div>

        <div style="overflow-x:auto; border-radius: 8px; border: 1px solid #eaeef2;">
            <table class="hv-table">
                <thead>
                    <tr>
                        <th style="width:60px;">เลขที่</th>
                        <th>ชื่อ - นามสกุล</th>
                        <th style="width:120px;">จำนวนครั้งที่เยี่ยม</th>
                        <th style="width:120px;">เยี่ยมล่าสุด</th>
                        <th style="width:120px;">สถานะ</th>
                        <th style="width:110px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $row)
                        <tr>
                            <td>{{ $row['student_number'] }}</td>
                            <td class="td-name">{{ $row['student']->thai_prefix }}{{ $row['student']->thai_firstname }} {{ $row['student']->thai_lastname }}</td>
                            <td>{{ $row['visit_count'] }}</td>
                            <td>{{ $row['last_visit'] ? \Carbon\Carbon::parse($row['last_visit'])->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($row['visit_count'] > 0)
                                    <span class="status-visited">เยี่ยมแล้ว</span>
                                @else
                                    <span class="status-pending">ยังไม่เยี่ยม</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('home-visits.index', ['student_id' => $row['student']->student_id]) }}" class="btn-search" style="padding:5px 14px;font-size:0.78rem;">
                                    บันทึก
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="hv-empty">เลือกห้องเรียนเพื่อดูรายชื่อนักเรียน</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
