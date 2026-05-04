@extends('layouts.sidebar')

@push('styles')
<style>
    .ls-page { padding: 24px 28px; min-height: 100%; }
    .ls-breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; margin-bottom: 20px; color: #555; }
    
    /* เปลี่ยนสีลิงก์ Breadcrumb ให้เป็นฟ้าชิวๆ */
    .ls-breadcrumb a { color: #4479DA; text-decoration: none; font-weight: 500; }
    .ls-breadcrumb span { color: #4479DA; font-weight: 600; }
    .ls-breadcrumb i { font-size: 0.7rem; color: #aaa; }
    
    .ls-card {
        background: #fff; border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        padding: 30px 24px 24px; position: relative;
        margin-top: 50px; margin-bottom: 28px;
    }
    
    /* เพิ่มพื้นหลังไล่สีให้ Icon ดูโดดเด่นเข้าธีม */
    .ls-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: #fff; 
        background: linear-gradient(135deg, #4479DA 0%, #5a95f5 100%);
        box-shadow: 0 4px 10px rgba(68, 121, 218, 0.25);
    }
    
    .ls-card-header {
        margin-left: 90px; font-size: 1.05rem; color: #444;
        margin-top: -10px; font-weight: 600;
        display: flex; justify-content: space-between; align-items: center;
    }
    
    /* เปลี่ยนสีหัวข้อและเส้นขอบซ้าย */
    .ls-section-title {
        font-size: 1rem; font-weight: 700; color: #4479DA;
        border-left: 4px solid #4479DA; padding-left: 10px;
        margin-bottom: 18px; margin-top: 16px;
    }
    
    .ls-person-info { background: #f8f9ff; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; }
    
    /* เปลี่ยนสีชื่อบุคคล */
    .ls-person-name { font-size: 1.1rem; font-weight: 700; color: #4479DA; margin: 0 0 6px; }
    .ls-person-meta { font-size: 0.85rem; color: #6b7280; }
    .ls-person-meta span { margin-right: 16px; }
    
    .ls-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 8px; }
    .ls-stat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-align: center; }
    
    /* เปลี่ยนสีตัวเลขสถิติ */
    .ls-stat-days { font-size: 1.8rem; font-weight: 700; color: #4479DA; line-height: 1.1; }
    .ls-stat-days.zero { color: #d1d5db; }
    .ls-stat-label { font-size: 0.75rem; color: #6b7280; margin-top: 5px; }
    .ls-stat-quota { font-size: 0.7rem; color: #aaa; margin-top: 2px; }
    
    .ls-stat-bar { height: 4px; background: #e5e7eb; border-radius: 4px; margin-top: 8px; overflow: hidden; }
    /* เพิ่มสีให้หลอด Bar เป็นโทนฟ้า */
    .ls-stat-bar-fill { height: 100%; border-radius: 4px; background: linear-gradient(135deg, #4479DA 0%, #5a95f5 100%); }
    
    .ls-select-sm {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 6px 28px 6px 10px;
        font-size: 0.85rem; color: #333; font-family: inherit; outline: none; background: #fff; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%23666' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 8px center;
    }
    
    .ls-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .ls-table thead th {
        padding: 12px 10px; font-weight: 700; color: #fff;
        background: linear-gradient(135deg, #4479DA 0%, #5a95f5 100%);
        text-align: center; border: none;
    }
    .ls-table tbody tr { border-bottom: 1px solid #f2f4f8; }
    .ls-table tbody tr:nth-child(even) { background: #fafcff; }
    .ls-table tbody tr:hover { background: #eef6ff; }
    .ls-table tbody td { padding: 11px 10px; color: #555; vertical-align: middle; text-align: center; }
    .ls-table tbody td.td-left { text-align: left; }
    
    .badge-approved { background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 20px; font-size: 0.77rem; font-weight: 600; }
    .badge-pending  { background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 0.77rem; font-weight: 600; }
    .badge-rejected { background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 20px; font-size: 0.77rem; font-weight: 600; }
    
    .ls-empty { text-align: center; padding: 40px 0; color: #aaa; }
    .ls-empty i { font-size: 2rem; display: block; margin-bottom: 8px; }
    
    .ls-pagination { display: flex; justify-content: flex-end; margin-top: 16px; }
    .ls-pagination .pagination { gap: 4px; margin-bottom: 0; }
    .ls-pagination .page-link { border-radius: 6px !important; font-size: 0.85rem; padding: 6px 12px; }
    .ls-pagination .page-item.active .page-link { background: #4479DA; border-color: #4479DA; color: #fff; }
    
    .btn-back {
        background: #f3f4f6; color: #555; border: none; border-radius: 6px;
        padding: 8px 18px; font-size: 0.85rem; font-weight: 600;
        cursor: pointer; font-family: inherit; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-back:hover { background: #e5e7eb; color: #333; text-decoration: none; }
    
    @media (max-width: 1000px) { .ls-stat-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
@endpush

@section('content')
<div class="ls-page">

    <nav class="ls-breadcrumb">
        <a href="#">ข้อมูลบุคคล</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('leave.personnel.index', ['fiscal_year' => $fiscalYear]) }}">ข้อมูลการลาของบุคลากร</a>
        <i class="bi bi-chevron-right"></i>
        <span>{{ $personnel->thai_firstname }} {{ $personnel->thai_lastname }}</span>
    </nav>

    <div class="ls-card">
        <div class="ls-icon" style="background: #4479DA;"><i class="fas fa-user-circle"></i></div>
        <div class="ls-card-header">
            <strong>ข้อมูลการลา</strong>
            <form method="GET" action="{{ route('leave.personnel.show', $personnel->personnel_id) }}" style="display:flex;align-items:center;gap:8px;">
                <label style="font-size:0.82rem;font-weight:600;color:#555;white-space:nowrap;">ปี พ.ศ.</label>
                <select name="fiscal_year" class="ls-select-sm" onchange="this.form.submit()">
                    @for ($y = 2560; $y <= 2575; $y++)
                        <option value="{{ $y }}" {{ $fiscalYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>

        <div style="margin-top: 24px;">
            <div class="ls-person-info">
                <p class="ls-person-name">{{ $personnel->thai_prefix }}{{ $personnel->thai_firstname }} {{ $personnel->thai_lastname }}</p>
                <div class="ls-person-meta">
                    <span><i class="fas fa-id-badge" style="margin-right:4px;color:#4479DA;"></i>รหัส: {{ $personnel->employee_code ?? '-' }}</span>
                    <span><i class="fas fa-building" style="margin-right:4px;color:#4479DA;"></i>แผนก: {{ $personnel->department ?? '-' }}</span>
                    <span><i class="fas fa-briefcase" style="margin-right:4px;color:#4479DA;"></i>ตำแหน่ง: {{ $personnel->position ?? '-' }}</span>
                </div>
            </div>

            <div class="ls-section-title">สรุปวันลา ปี {{ $fiscalYear }}</div>
            <div class="ls-stat-grid">
                @foreach ($leaveTypes as $lt)
                    @php
                        $used  = (float) $summary->get($lt->leave_type_key, 0);
                        $quota = $lt->days_per_year;
                        $pct   = $quota > 0 ? min(100, round($used / $quota * 100)) : 0;
                        $color = $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#4479DA');
                    @endphp
                    <div class="ls-stat-box">
                        <div class="ls-stat-days {{ $used == 0 ? 'zero' : '' }}">{{ $used > 0 ? number_format($used, 1) : '0' }}</div>
                        <div class="ls-stat-label">{{ $lt->leave_type_name }}</div>
                        <div class="ls-stat-quota">โควตา {{ $quota }} วัน</div>
                        <div class="ls-stat-bar">
                            <div class="ls-stat-bar-fill" style="width:{{ $pct }}%; background:{{ $color }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="ls-card">
        <div class="ls-icon" style="background: #f59e0b;"><i class="fas fa-calendar-alt"></i></div>
        <div class="ls-card-header">
            <strong>ประวัติการลา — ปี {{ $fiscalYear }}</strong>
            <a href="{{ route('leave.personnel.index', ['fiscal_year' => $fiscalYear]) }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> กลับ
            </a>
        </div>

        <div style="margin-top: 24px; overflow-x: auto; border-radius: 8px; border: 1px solid #eaeef2;">
            <table class="ls-table">
                <thead>
                    <tr>
                        <th style="width:50px;">ลำดับ</th>
                        <th>ประเภทการลา</th>
                        <th>วันที่เริ่มลา</th>
                        <th>วันที่สิ้นสุด</th>
                        <th>จำนวนวัน</th>
                        <th>สถานะ</th>
                        <th>เหตุผล</th>
                        <th>วันที่บันทึก</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaves as $index => $leave)
                        <tr>
                            <td>{{ $leaves->firstItem() + $index }}</td>
                            <td>{{ $leave->leave_type_name }}</td>
                            <td>{{ $leave->start_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $leave->end_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ number_format($leave->days_count, 1) }}</td>
                            <td>
                                @php
                                    $cls = match($leave->status) { 'approved' => 'badge-approved', 'rejected' => 'badge-rejected', default => 'badge-pending' };
                                    $lbl = match($leave->status) { 'approved' => 'อนุมัติ', 'rejected' => 'ไม่อนุมัติ', default => 'รอการอนุมัติ' };
                                @endphp
                                <span class="{{ $cls }}">{{ $lbl }}</span>
                            </td>
                            <td class="td-left">{{ $leave->reason ?? '-' }}</td>
                            <td>{{ $leave->created_at?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="ls-empty">
                                <i class="fas fa-inbox"></i>
                                <div>ไม่พบข้อมูลการลา</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ls-pagination">{{ $leaves->links() }}</div>
    </div>

</div>
@endsection