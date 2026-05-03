@extends('layouts.sidebar')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/studentdetail/student_index.css') }}">
    <style>
        .lp-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }
        .lp-stat-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            text-align: center;
        }
        .lp-stat-box .days {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a56db;
            line-height: 1.2;
        }
        .lp-stat-box .label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 4px;
        }
        .lp-stat-box .quota {
            font-size: 0.7rem;
            color: #aaa;
        }
        .status-approved  { background:#d1fae5; color:#065f46; }
        .status-pending   { background:#fef3c7; color:#92400e; }
        .status-rejected  { background:#fee2e2; color:#991b1b; }
    </style>
@endpush

@section('content')
<div class="si-page">

    {{-- Breadcrumb --}}
    <nav class="si-breadcrumb">
        <a href="#">ข้อมูลบุคคล</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('leave.personnel.index', ['fiscal_year' => $fiscalYear]) }}">ข้อมูลการลาของบุคลากร</a>
        <i class="bi bi-chevron-right"></i>
        <span>{{ $personnel->thai_firstname }} {{ $personnel->thai_lastname }}</span>
    </nav>

    {{-- Person Info Card --}}
    <div class="si-card">
        <div class="si-card-icon" style="background:#4479DA;">
            <i class="bi bi-person-circle"></i>
        </div>
        <div class="si-card-body">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <div>
                    <h5 style="font-weight:700; color:#1a1a2e; margin:0;">
                        {{ $personnel->thai_prefix }}{{ $personnel->thai_firstname }} {{ $personnel->thai_lastname }}
                    </h5>
                    <p style="color:#6b7280; font-size:0.85rem; margin:4px 0 0;">
                        รหัส: {{ $personnel->employee_code ?? '-' }} &nbsp;|&nbsp;
                        แผนก: {{ $personnel->department ?? '-' }} &nbsp;|&nbsp;
                        ตำแหน่ง: {{ $personnel->position ?? '-' }}
                    </p>
                </div>
                <div>
                    <form method="GET" action="{{ route('leave.personnel.show', $personnel->personnel_id) }}" class="d-flex align-items-center gap-2">
                        <label style="font-size:0.82rem; font-weight:600; white-space:nowrap;">ปี พ.ศ.</label>
                        <select name="fiscal_year" class="si-select" style="width:120px;" onchange="this.form.submit()">
                            @for ($y = 2560; $y <= 2575; $y++)
                                <option value="{{ $y }}" {{ $fiscalYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </form>
                </div>
            </div>

            {{-- Leave Summary Stats --}}
            <div class="lp-stat-grid">
                @foreach ($leaveTypes->take(8) as $lt)
                    @php $used = $summary->get($lt->leave_type_key, 0); @endphp
                    <div class="lp-stat-box">
                        <div class="days" style="{{ $used > 0 ? '' : 'color:#d1d5db;' }}">
                            {{ $used > 0 ? number_format($used, 1) : '0' }}
                        </div>
                        <div class="label">{{ $lt->leave_type_name }}</div>
                        <div class="quota">โควตา {{ $lt->days_per_year }} วัน</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Leave History Table --}}
    <div class="si-card">
        <div class="si-card-icon" style="background:#f90;">
            <i class="bi bi-calendar3"></i>
        </div>
        <div class="si-card-body">
            <h6 class="si-card-title">ประวัติการลา — ปี {{ $fiscalYear }}</h6>

            <div class="si-table-wrap">
                <table class="si-table">
                    <colgroup>
                        <col style="width:5%">
                        <col style="width:16%">
                        <col style="width:14%">
                        <col style="width:12%">
                        <col style="width:12%">
                        <col style="width:8%">
                        <col style="width:20%">
                        <col style="width:13%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>ประเภทการลา</th>
                            <th>วันที่เริ่มลา</th>
                            <th>วันที่สิ้นสุด</th>
                            <th>จำนวนวัน</th>
                            <th>สถานะ</th>
                            <th>เหตุผล</th>
                            <th>วันที่แจ้งลา</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaves as $index => $leave)
                            <tr>
                                <td>{{ $leaves->firstItem() + $index }}</td>
                                <td>{{ $leave->leave_type_name }}</td>
                                <td>{{ $leave->start_date ? $leave->start_date->format('d/m/Y') : '-' }}</td>
                                <td>{{ $leave->end_date ? $leave->end_date->format('d/m/Y') : '-' }}</td>
                                <td>{{ number_format($leave->days_count, 1) }}</td>
                                <td>
                                    @php
                                        $statusClass = match($leave->status) {
                                            'approved' => 'status-approved',
                                            'rejected' => 'status-rejected',
                                            default    => 'status-pending',
                                        };
                                        $statusLabel = match($leave->status) {
                                            'approved' => 'อนุมัติ',
                                            'rejected' => 'ไม่อนุมัติ',
                                            default    => 'รอการอนุมัติ',
                                        };
                                    @endphp
                                    <span class="si-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td style="text-align:left; padding-left:10px; white-space:normal;">
                                    {{ $leave->reason ?? '-' }}
                                </td>
                                <td>{{ $leave->created_at ? $leave->created_at->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="si-empty">
                                    <i class="bi bi-inbox"></i>
                                    <div>ไม่พบข้อมูลการลา</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="si-pagination">
                {{ $leaves->links() }}
            </div>
        </div>
    </div>

</div>
@endsection
