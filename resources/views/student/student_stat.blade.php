@extends('layouts.sidebar')

@push('styles')
<style>
    body { background: #f4f6f9; }
    .page { padding: 24px 28px; }
    .breadcrumb-custom a { color: #00bcd4; text-decoration: none; }
    .breadcrumb-custom a:hover { text-decoration: underline; }
    .breadcrumb-custom i { color: #aaa; margin: 0 6px; font-size: 0.75rem; }

    .floating-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 30px 20px 20px; position: relative;
        margin-top: 50px; margin-bottom: 28px;
    }
    .floating-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .card-header-text {
        margin-left: 90px; font-size: 1.1rem; color: #555;
        margin-top: -10px; font-weight: 600;
    }

    .form-label-sm { font-size: 0.82rem; color: #666; font-weight: 600; margin-bottom: 4px; }
    .form-select-line {
        border: none; border-bottom: 1.5px solid #ccc; border-radius: 0;
        padding: 6px 4px; font-size: 0.88rem; width: 100%;
        background: transparent; outline: none; font-family: inherit;
    }
    .form-select-line:focus { border-bottom-color: #00bcd4; }

    .btn-search-teal {
        background: #00bcd4; color: #fff; border: none; border-radius: 4px;
        padding: 9px 28px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-search-teal:hover { background: #00a5bb; }
    .btn-export {
        background: #4caf50; color: #fff; border: none; border-radius: 4px;
        padding: 9px 20px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;
    }
    .btn-export:hover { background: #43a047; text-decoration: none; color: #fff; }

    /* ตาราง */
    .stat-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
    .stat-table th {
        padding: 10px 14px; border-bottom: 2px solid #e5e7eb;
        font-weight: 600; color: #444; text-align: center;
    }
    .stat-table th.left { text-align: left; }
    .stat-table td {
        padding: 10px 14px; border-bottom: 1px solid #f0f2f5;
        color: #555; text-align: center; vertical-align: middle;
    }
    .stat-table td.level-name { text-align: left; color: #00bcd4; font-weight: 500; }

    /* แถวรวม */
    .row-subtotal td {
        background: #f8faff; font-weight: 700; color: #3b82f6;
        border-top: 1px solid #e0e7ff; border-bottom: 1px solid #e0e7ff;
    }
    .row-subtotal td.level-name { color: #3b82f6; }

    /* แถวรวมทั้งหมด */
    .row-grand td {
        background: #eff6ff; font-weight: 700; color: #1d4ed8;
        border-top: 2px solid #bfdbfe; font-size: 0.95rem;
    }

    .note-text { font-size: 0.8rem; color: #888; text-align: right; margin-bottom: 8px; }

    /* group header */
    .stat-table .group-span {
        border-top: 2px solid #e0e7ff;
    }

    .col-header-group {
        text-align: center; border-bottom: 1px solid #e5e7eb;
        color: #444; font-size: 0.85rem; font-weight: 600;
        padding: 8px 0;
    }

    .empty-box { text-align: center; color: #aaa; padding: 50px 0; }
    .empty-box i { font-size: 2.5rem; display: block; margin-bottom: 10px; }
</style>
@endpush

@section('content')
<div class="page">

    <nav class="breadcrumb-custom mb-3" style="font-size:0.88rem; display:flex; align-items:center; gap:4px;">
        <a href="#">ข้อมูลนักเรียน</a>
        <i class="bi bi-chevron-right"></i>
        <span style="color:#555;">สถิตินักเรียน</span>
    </nav>

    {{-- ค้นหา --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#00bcd4;"><i class="fas fa-search"></i></div>
        <div class="card-header-text">ค้นหา</div>

        <form method="GET" action="{{ route('student-stat.index') }}" style="margin-top:24px;">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="form-label-sm">ปีการศึกษา</div>
                    <select name="year_id" class="form-select-line" onchange="this.form.submit()">
                        <option value="">เลือกปี</option>
                        @foreach ($academicYears as $yr)
                            <option value="{{ $yr->year_id }}" {{ $yearId == $yr->year_id ? 'selected' : '' }}>
                                {{ $yr->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">เทอม</div>
                    <select name="semester_id" class="form-select-line" onchange="this.form.submit()">
                       <option value="">ทั้งหมด</option>
                            @foreach ($semesters as $sem)
                                <option value="{{ $sem->semester_id }}" {{ $semesterId == $sem->semester_id ? 'selected' : '' }}>
                                    {{ $sem->semester_name }}
                                </option>
                            @endforeach
                        </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">ระดับชั้นเรียน</div>
                    <select name="level_id" class="form-select-line">
                        <option value="">ทั้งหมด</option>
                        @foreach ($levels as $lv)
                            <option value="{{ $lv->level_id }}" {{ $levelId == $lv->level_id ? 'selected' : '' }}>
                                {{ $lv->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">เลือกรายงาน</div>
                    <select name="report_type" class="form-select-line">
                        <option value="gender"    {{ $reportType === 'gender'    ? 'selected' : '' }}>รายงานจำนวนนักเรียน</option>
                        <option value="enrollment" {{ $reportType === 'enrollment'? 'selected' : '' }}>สรุปจำนวนตามวันเข้าเรียน</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-label-sm">แสดงนักเรียน</div>
                    <select name="display_type" class="form-select-line">
                        <option value="summary">สรุปจำนวนนักเรียนตามวันเข้าเรียน</option>
                        <option value="all">ทั้งหมด</option>
                    </select>
                </div>
            </div>
            <div style="text-align:center; border-top:1px solid #f0f0f0; padding-top:14px; display:flex; justify-content:center; gap:12px;">
                <button type="submit" class="btn-search-teal"><i class="fas fa-search"></i> ค้นหา</button>
                <a href="{{ route('student-stat.index') }}" style="display:inline-flex; align-items:center; gap:6px; background:#fff; color:#666; border:1.5px solid #d0d7de; border-radius:4px; padding:9px 20px; font-size:0.9rem; font-weight:600; text-decoration:none;">
                    <i class="fas fa-redo"></i> ล้างค่า
                </a>
                <button type="button" class="btn-export" onclick="window.print()">
                    <i class="fas fa-file-excel"></i> EXPORT
                </button>
            </div>
        </form>
    </div>

    {{-- ตารางสถิติ --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#f59e0b;"><i class="fas fa-list"></i></div>
        <div class="card-header-text">รายการ</div>

        <div style="margin-top:20px;">

            @if ($grouped->isEmpty())
                <div class="empty-box">
                    <i class="fas fa-chart-bar" style="color:#00bcd4;"></i>
                    <div style="font-size:1rem; color:#555; margin-top:8px;">
                        กรุณาเลือก <strong>ปีการศึกษา</strong> และ <strong>เทอม</strong> เพื่อดูสถิติ
                    </div>
                </div>
            @else
                <div class="note-text">* ระบบสรุปจำนวนนักเรียนตามวันเข้าเรียน</div>
                <div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:6px;">
                    <table class="stat-table">
                        <thead>
                            <tr>
                                <th class="left" rowspan="2" style="width:140px; border-right:1px solid #e5e7eb;">ระดับชั้น</th>
                                <th rowspan="2" style="width:120px; border-right:1px solid #e5e7eb;">จำนวนห้องเรียน</th>
                                <th colspan="2" class="col-header-group" style="border-bottom:1px solid #e5e7eb;">
                                    รายงานสถิติแสดงจำนวนนักเรียนชาย-หญิง
                                </th>
                                <th rowspan="2">รวมนักเรียน</th>
                            </tr>
                            <tr>
                                <th style="border-right:1px solid #e5e7eb;">ชาย</th>
                                <th style="border-right:1px solid #e5e7eb;">หญิง</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $grandRooms  = 0;
                                $grandMale   = 0;
                                $grandFemale = 0;
                                $grandTotal  = 0;
                            @endphp

                            @foreach ($grouped as $groupName => $rows)
                                @php
                                    $subRooms  = $rows->sum('rooms');
                                    $subMale   = $rows->sum('male');
                                    $subFemale = $rows->sum('female');
                                    $subTotal  = $rows->sum('total');
                                    $grandRooms  += $subRooms;
                                    $grandMale   += $subMale;
                                    $grandFemale += $subFemale;
                                    $grandTotal  += $subTotal;
                                @endphp

                                @foreach ($rows->sortBy('sort_order') as $row)
                                    <tr>
                                        <td class="level-name">{{ $row['level_name'] }}</td>
                                        <td>{{ $row['rooms'] }}</td>
                                        <td>{{ $row['male'] > 0 ? $row['male'] : '' }}</td>
                                        <td>{{ $row['female'] > 0 ? $row['female'] : '' }}</td>
                                        <td style="font-weight:600; color:{{ $row['total'] > 0 ? '#f59e0b' : '#ccc' }};">
                                            {{ $row['total'] > 0 ? $row['total'] : '' }}
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- แถวรวมกลุ่ม --}}
                                <tr class="row-subtotal">
                                    <td class="level-name">รวม</td>
                                    <td>{{ $subRooms }}</td>
                                    <td>{{ $subMale }}</td>
                                    <td>{{ $subFemale }}</td>
                                    <td>{{ $subTotal }}</td>
                                </tr>
                            @endforeach

                            {{-- แถวรวมทั้งหมด --}}
                            <tr class="row-grand">
                                <td class="level-name" style="color:#1d4ed8;">รวมทั้งหมด</td>
                                <td>{{ $grandRooms }}</td>
                                <td>{{ $grandMale }}</td>
                                <td>{{ $grandFemale }}</td>
                                <td>{{ $grandTotal }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection