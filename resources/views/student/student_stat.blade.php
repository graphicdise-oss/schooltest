@extends('layouts.sidebar')

@push('styles')
<style>
    .stat-page { padding: 24px 28px; min-height: 100%; }
    .stat-filter-card {
        background: #fff; border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        padding: 18px 20px; margin-bottom: 22px;
        display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;
    }
    .filter-group { display: flex; flex-direction: column; gap: 4px; }
    .filter-label { font-size: 0.78rem; color: #888; font-weight: 600; text-transform: uppercase; }
    .form-select-line {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 7px 10px;
        font-size: 0.88rem; color: #333; outline: none; font-family: inherit;
        min-width: 160px;
    }
    .form-select-line:focus { border-color: #5482e7; }
    .btn-report {
        background: #5482e7; color: #fff; border: none; border-radius: 6px;
        padding: 8px 22px; font-size: 0.88rem; font-weight: 600;
        cursor: pointer; font-family: inherit;
    }
    .btn-report:hover { background: #446bca; }

    .stat-card {
        background: #fff; border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        padding: 24px; position: relative; margin-top: 48px;
    }
    .stat-icon {
        position: absolute; top: -24px; left: 20px;
        width: 60px; height: 60px; border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        font-size: 26px; color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        background: linear-gradient(135deg, #5482e7, #3949ab);
    }
    .stat-card-header {
        margin-left: 80px; font-size: 1rem; font-weight: 700; color: #333;
        margin-top: -6px; margin-bottom: 20px;
    }

    .stat-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
    .stat-table th {
        background: #f4f6fb; color: #555; font-weight: 700;
        padding: 10px 14px; text-align: center; border-bottom: 2px solid #e5e9f2;
        font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.03em;
    }
    .stat-table th:first-child { text-align: left; }
    .stat-table td {
        padding: 9px 14px; border-bottom: 1px solid #f0f3f7;
        text-align: center; color: #444;
    }
    .stat-table td:first-child { text-align: left; font-weight: 500; }
    .stat-table tr:hover td { background: #f8faff; }
    .group-header td {
        background: #eef2fc; font-weight: 700; color: #3949ab;
        padding: 7px 14px; font-size: 0.82rem; text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .subtotal-row td { background: #f4f8ff; font-weight: 700; color: #5482e7; }
    .grand-total-row td { background: #3949ab; color: #fff; font-weight: 800; font-size: 0.95rem; }
    .grand-total-row td:first-child { border-radius: 6px 0 0 6px; }
    .grand-total-row td:last-child  { border-radius: 0 6px 6px 0; }

    .num-male   { color: #2563eb; font-weight: 700; }
    .num-female { color: #db2777; font-weight: 700; }
    .num-total  { color: #059669; font-weight: 800; }

    .empty-state { text-align: center; color: #aaa; padding: 48px 0; font-size: 0.9rem; }
</style>
@endpush

@section('content')
<div class="stat-page">

    <form method="GET" action="{{ route('student-stat.index') }}">
        <div class="stat-filter-card">
            <div class="filter-group">
                <span class="filter-label">ปีการศึกษา</span>
                <select name="year_id" class="form-select-line" onchange="this.form.submit()">
                    @foreach ($years as $yr)
                        <option value="{{ $yr->year_id }}" {{ $yearId == $yr->year_id ? 'selected' : '' }}>
                            {{ $yr->year_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">เทอม</span>
                <select name="semester_id" class="form-select-line">
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem->semester_id }}" {{ $semesterId == $sem->semester_id ? 'selected' : '' }}>
                            {{ $sem->semester_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">ระดับชั้น</span>
                <select name="level_id" class="form-select-line">
                    <option value="">ทั้งหมด</option>
                    @foreach ($levels as $lv)
                        <option value="{{ $lv->level_id }}" {{ $levelId == $lv->level_id ? 'selected' : '' }}>
                            {{ $lv->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-report">
                <i class="fas fa-chart-bar"></i> แสดงรายงาน
            </button>
        </div>
    </form>

    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-card-header">สถิตินักเรียน</div>

        @if ($levelsWithStats->where('total', '>', 0)->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox fa-2x" style="margin-bottom:10px; display:block;"></i>
                ไม่พบข้อมูลนักเรียนในเงื่อนไขที่เลือก
            </div>
        @else
        <table class="stat-table">
            <thead>
                <tr>
                    <th>ระดับชั้น</th>
                    <th>ชาย</th>
                    <th>หญิง</th>
                    <th>รวม</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($levelGroups as $group => $groupLevels)
                    <tr class="group-header">
                        <td colspan="4">{{ $group ?: 'ไม่ระบุกลุ่ม' }}</td>
                    </tr>
                    @foreach ($groupLevels as $lv)
                        @if ($lv->total > 0)
                        <tr>
                            <td>{{ $lv->name }}</td>
                            <td class="num-male">{{ number_format($lv->male_count) }}</td>
                            <td class="num-female">{{ number_format($lv->female_count) }}</td>
                            <td class="num-total">{{ number_format($lv->total) }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr class="subtotal-row">
                        <td>รวม {{ $group }}</td>
                        <td class="num-male">{{ number_format($groupLevels->sum('male_count')) }}</td>
                        <td class="num-female">{{ number_format($groupLevels->sum('female_count')) }}</td>
                        <td class="num-total">{{ number_format($groupLevels->sum('total')) }}</td>
                    </tr>
                @endforeach
                <tr class="grand-total-row">
                    <td>รวมทั้งหมด</td>
                    <td>{{ number_format($grandMale) }}</td>
                    <td>{{ number_format($grandFemale) }}</td>
                    <td>{{ number_format($grandTotal) }}</td>
                </tr>
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
