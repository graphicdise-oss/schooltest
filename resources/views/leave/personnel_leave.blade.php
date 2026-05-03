@extends('layouts.sidebar')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/studentdetail/student_index.css') }}">
    <style>
        .lp-summary-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }
        .lp-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            background: #e8f0fe;
            color: #1a56db;
        }
        .lp-chip-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }
        .lp-days-cell {
            font-weight: 600;
            color: #374151;
        }
        .lp-days-cell.zero {
            color: #d1d5db;
            font-weight: 400;
        }
        .lp-days-total {
            font-weight: 700;
            color: #1a56db;
        }
        .lp-days-total.zero {
            color: #d1d5db;
            font-weight: 400;
        }
        .si-table col.col-name-wide { width: 18%; }
        .si-table col.col-dept      { width: 14%; }
        .si-table col.col-leave     { width: 8%; }
        .si-table col.col-total     { width: 8%; }
        .si-table col.col-act       { width: 7%; }
        .si-table col.col-no-sm     { width: 4%; }
    </style>
@endpush

@section('content')
<div class="si-page">

    {{-- Breadcrumb --}}
    <nav class="si-breadcrumb">
        <a href="#">ข้อมูลบุคคล</a>
        <i class="bi bi-chevron-right"></i>
        <a href="#">บุคลากร - อาจารย์</a>
        <i class="bi bi-chevron-right"></i>
        <span>ข้อมูลการลาของบุคลากร</span>
    </nav>

    {{-- Search Card --}}
    <div class="si-card">
        <div class="si-card-icon" style="background:#0cc;">
            <i class="bi bi-search"></i>
        </div>
        <div class="si-card-body">
            <h6 class="si-card-title">ค้นหา</h6>
            <form method="GET" action="{{ route('leave.personnel.index') }}">
                <div class="si-form-grid">

                    <div class="si-form-row">
                        <label>ปี พ.ศ.</label>
                        <select name="fiscal_year" class="si-select">
                            @for ($y = 2560; $y <= 2575; $y++)
                                <option value="{{ $y }}" {{ $fiscalYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="si-form-row">
                        <label>แผนก</label>
                        <select name="department" class="si-select">
                            <option value="">ทั้งหมด</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept }}" {{ $department == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="si-form-row">
                        <label>ชื่อ - นามสกุล / รหัส</label>
                        <input type="text" name="search_name" class="si-input"
                            placeholder="ค้นหา ชื่อ/รหัส"
                            value="{{ $searchName }}">
                    </div>

                    <div class="si-form-row">
                        <label>จากวันที่</label>
                        <input type="date" name="date_from" class="si-input" value="{{ $dateFrom }}">
                    </div>

                    <div class="si-form-row">
                        <label>ถึงวันที่</label>
                        <input type="date" name="date_to" class="si-input" value="{{ $dateTo }}">
                    </div>

                </div>
                <div class="si-search-actions">
                    <button type="submit" class="si-btn-search">
                        <i class="bi bi-search"></i> ค้นหา
                    </button>
                    <a href="{{ route('leave.personnel.index') }}" class="si-btn-reset">
                        <i class="bi bi-arrow-counterclockwise"></i> ล้างค่า
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="si-card">
        <div class="si-card-icon" style="background:#f90;">
            <i class="bi bi-person-lines-fill"></i>
        </div>
        <div class="si-card-body">
            <div class="si-table-header">
                <h6 class="si-card-title">ข้อมูลการลาของบุคลากร — ปี {{ $fiscalYear }}</h6>
                <span class="text-muted" style="font-size:0.82rem;">
                    พบ {{ $personnels->total() }} คน
                </span>
            </div>

            {{-- Leave type chips legend --}}
            @if ($leaveTypes->count())
            <div class="lp-summary-chips">
                @foreach ($leaveTypes->take(8) as $lt)
                    <span class="lp-chip">
                        <span class="lp-chip-dot"></span>
                        {{ $lt->leave_type_name }} ({{ $lt->days_per_year }} วัน/ปี)
                    </span>
                @endforeach
            </div>
            @endif

            <div class="si-table-wrap">
                <table class="si-table">
                    <colgroup>
                        <col class="col-no-sm">
                        <col class="col-name-wide">
                        <col class="col-dept">
                        @foreach ($leaveTypes->take(5) as $lt)
                            <col class="col-leave">
                        @endforeach
                        <col class="col-total">
                        <col class="col-act">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>ชื่อ - นามสกุล</th>
                            <th>แผนก</th>
                            @foreach ($leaveTypes->take(5) as $lt)
                                <th title="{{ $lt->leave_type_name }}">
                                    {{ \Illuminate\Support\Str::limit($lt->leave_type_name, 6, '') }}<br>
                                    <span style="font-size:0.68rem;opacity:.75;">(วัน)</span>
                                </th>
                            @endforeach
                            <th>รวม<br><span style="font-size:0.68rem;opacity:.75;">(วัน)</span></th>
                            <th>รายละเอียด</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($personnels as $index => $p)
                            @php
                                $pSummary = $leaveSummary->get($p->personnel_id, collect());
                                $totalDays = $pSummary->sum('total_days');
                            @endphp
                            <tr>
                                <td>{{ $personnels->firstItem() + $index }}</td>
                                <td style="text-align:left; padding-left:10px;">
                                    {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                                    @if ($p->employee_code)
                                        <br><span style="font-size:0.73rem;color:#888;">{{ $p->employee_code }}</span>
                                    @endif
                                </td>
                                <td>{{ $p->department ?? '-' }}</td>
                                @foreach ($leaveTypes->take(5) as $lt)
                                    @php
                                        $days = $pSummary->where('leave_type_key', $lt->leave_type_key)->sum('total_days');
                                    @endphp
                                    <td class="lp-days-cell {{ $days == 0 ? 'zero' : '' }}">
                                        {{ $days > 0 ? number_format($days, 1) : '-' }}
                                    </td>
                                @endforeach
                                <td class="lp-days-total {{ $totalDays == 0 ? 'zero' : '' }}">
                                    {{ $totalDays > 0 ? number_format($totalDays, 1) : '-' }}
                                </td>
                                <td>
                                    <a href="{{ route('leave.personnel.show', ['personnelId' => $p->personnel_id, 'fiscal_year' => $fiscalYear]) }}"
                                        class="si-action-btn si-action-view" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + $leaveTypes->take(5)->count() + 2 }}" class="si-empty">
                                    <i class="bi bi-inbox"></i>
                                    <div>ไม่พบข้อมูลบุคลากร</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="si-pagination">
                {{ $personnels->links() }}
            </div>
        </div>
    </div>

</div>
@endsection
