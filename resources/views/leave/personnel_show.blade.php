@extends('layouts.sidebar')

@push('styles')
<style>
    .ls-page { padding: 24px 28px; min-height: 100%; }
    .ls-breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; margin-bottom: 20px; color: #555; }
    .ls-breadcrumb a { color: #5482e7; text-decoration: none; font-weight: 500; }
    .ls-breadcrumb a:hover { text-decoration: underline; }
    .ls-breadcrumb span { color: #5482e7; font-weight: 600; }
    .ls-breadcrumb i { font-size: 0.7rem; color: #aaa; }
    .ls-card {
        background: #fff; border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        padding: 30px 24px 24px; position: relative;
        margin-top: 50px; margin-bottom: 28px;
    }
    .ls-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .ls-card-header {
        margin-left: 90px; font-size: 1.05rem; color: #444;
        margin-top: -10px; font-weight: 600;
        display: flex; justify-content: space-between; align-items: center;
    }
    .person-info-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px 24px; margin-top: 20px; padding: 16px; background: #f8faff; border-radius: 8px; }
    .person-info-label { font-size: 0.78rem; color: #888; font-weight: 600; }
    .person-info-value { font-size: 0.9rem; color: #333; font-weight: 500; }
    .summary-chips { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
    .summary-chip { display: flex; align-items: center; gap: 8px; background: #f0f4ff; border-radius: 20px; padding: 6px 14px; font-size: 0.82rem; color: #5482e7; font-weight: 600; }
    .summary-chip .chip-val { font-size: 1rem; font-weight: 700; }

    .ls-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.85rem; }
    .ls-table thead th {
        padding: 12px 10px; font-weight: 600; color: #fff;
        background: #5482e7; text-align: center; white-space: nowrap; border: none;
    }
    .ls-table thead th:first-child { border-top-left-radius: 8px; }
    .ls-table thead th:last-child  { border-top-right-radius: 8px; }
    .ls-table tbody tr td { border-bottom: 1px solid #f2f4f8; }
    .ls-table tbody tr:nth-child(even) td { background: #fafcff; }
    .ls-table tbody tr:hover td { background: #eef6ff; }
    .ls-table tbody td { padding: 11px 10px; color: #555; vertical-align: middle; text-align: center; }

    .badge-approved { background: #dcfce7; color: #16a34a; border-radius: 20px; padding: 4px 12px; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }
    .badge-pending  { background: #fef9c3; color: #b45309; border-radius: 20px; padding: 4px 12px; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }
    .badge-rejected { background: #fee2e2; color: #dc2626; border-radius: 20px; padding: 4px 12px; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }

    .act-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 30px; height: 30px; border-radius: 50%;
        font-size: 0.82rem; text-decoration: none; border: none; cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .act-btn:hover { transform: scale(1.1); box-shadow: 0 2px 8px rgba(0,0,0,0.18); text-decoration: none; }
    .act-view   { background: #f59e0b; color: #fff; }
    .act-print  { background: #f59e0b; color: #fff; }
    .act-delete { background: #ef4444; color: #fff; }

    .ls-empty { text-align: center; color: #aaa; padding: 40px 0; }
    .ls-empty i { font-size: 2rem; display: block; margin-bottom: 8px; }
    .btn-back {
        display: inline-flex; align-items: center; gap: 6px;
        background: #f4f6fb; color: #5482e7; border: 1.5px solid #d0d7e5;
        border-radius: 6px; padding: 8px 18px; font-size: 0.85rem; font-weight: 600;
        text-decoration: none; transition: background 0.15s;
    }
    .btn-back:hover { background: #e8eeff; text-decoration: none; color: #3949ab; }
</style>
@endpush

@section('content')
<div class="ls-page">

    <nav class="ls-breadcrumb">
        <a href="{{ route('leave.personnel.index') }}">ข้อมูลการลาของบุคลากร</a>
        <i class="bi bi-chevron-right"></i>
        <span>{{ $personnel->thai_prefix }}{{ $personnel->thai_firstname }} {{ $personnel->thai_lastname }}</span>
    </nav>

    <div class="ls-card">
        <div class="ls-icon" style="background: #5482e7;"><i class="fas fa-user"></i></div>
        <div class="ls-card-header">
            <strong>{{ $personnel->thai_prefix }}{{ $personnel->thai_firstname }} {{ $personnel->thai_lastname }}</strong>
            <a href="{{ route('leave.personnel.index', ['fiscal_year' => $fiscal_year]) }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
        <div class="person-info-grid">
            <div>
                <div class="person-info-label">รหัสพนักงาน</div>
                <div class="person-info-value">{{ $personnel->employee_code ?? '-' }}</div>
            </div>
            <div>
                <div class="person-info-label">แผนก</div>
                <div class="person-info-value">{{ $personnel->department ?? '-' }}</div>
            </div>
            <div>
                <div class="person-info-label">ปีที่แสดง (พ.ศ.)</div>
                <div class="person-info-value">{{ $fiscal_year }}</div>
            </div>
        </div>
        @if ($summary->isNotEmpty())
            <div class="summary-chips">
                @foreach ($leaveTypes as $lt)
                    @php $d = $summary->get($lt->leave_type_key, 0); @endphp
                    @if ($d > 0)
                        <div class="summary-chip">
                            {{ $lt->leave_type_name }} <span class="chip-val">{{ number_format($d,1) }}</span> วัน
                        </div>
                    @endif
                @endforeach
                <div class="summary-chip" style="background:#fff3e0; color:#f59e0b;">
                    รวมทั้งหมด <span class="chip-val">{{ number_format($summary->sum(),1) }}</span> วัน
                </div>
            </div>
        @endif
    </div>

    <div class="ls-card">
        <div class="ls-icon" style="background: #f59e0b;"><i class="fas fa-calendar-alt"></i></div>
 {{-- ย้ายปุ่มมาตรงนี้แทน --}}
    <div class="ls-card-header">
        <strong>รายการลา — ปี {{ $fiscal_year }}</strong>
        <div style="display:flex; gap:8px; align-items:center;">
            <a href="{{ route('leave.requests.create', ['personnel_id' => $personnel->personnel_id]) }}"
               style="display:inline-flex; align-items:center; gap:6px;
                      background:#5482e7; color:#fff; border-radius:6px;
                      padding:7px 16px; font-size:0.85rem; font-weight:600; text-decoration:none;">
                <i class="fas fa-plus"></i> ยื่นใบลาใหม่
            </a>
            <span style="font-size:0.82rem;color:#888;">พบ {{ $requests->count() }} รายการ</span>
        </div>
    </div>

        

        <div style="margin-top:24px; overflow-x:auto; border-radius:8px; border:1px solid #eaeef2;">
            <table class="ls-table">
                <thead>
                    <tr>
                        <th style="width:50px;">ลำดับ</th>
                        <th>ประเภท</th>
                        <th>วันที่แจ้งลา</th>
                        <th>วันที่เริ่มลา</th>
                        <th>วันสิ้นสุด</th>
                        <th>จำนวน(วัน)</th>
                        <th>ผู้ยื่นคำร้อง</th>
                        <th>ผู้ตรวจสอบ</th>
                        <th>สถานะ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $i => $req)
                        <tr>
                            <td>{{ $i + 1 }}.</td>
                            <td>{{ $req->leaveType->leave_type_name ?? $req->leave_type_key }}</td>
                            <td>{{ \Carbon\Carbon::parse($req->request_date)->addYears(543)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($req->request_date)->format('H:i:s') }} น.</td>
                            <td>{{ \Carbon\Carbon::parse($req->start_date)->addYears(543)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($req->end_date)->addYears(543)->format('d/m/Y') }}</td>
                            <td style="font-weight:600;color:#5482e7;">{{ number_format($req->num_days,1) }}</td>
                            <td>{{ $req->requester ? $req->requester->thai_prefix.$req->requester->thai_firstname.' '.$req->requester->thai_lastname : '-' }}</td>
                            <td>{{ $req->reviewer  ? $req->reviewer->thai_prefix.$req->reviewer->thai_firstname.' '.$req->reviewer->thai_lastname  : '-' }}</td>
                            <td>
                                @if ($req->status === 'อนุมัติ')
                                    <span class="badge-approved">{{ $req->status }}</span>
                                @elseif ($req->status === 'ไม่อนุมัติ')
                                    <span class="badge-rejected">{{ $req->status }}</span>
                                @else
                                    <span class="badge-pending">{{ $req->status }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('leave.requests.show', $req->id) }}" class="act-btn act-view" title="ดูรายละเอียด"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('leave.requests.print', $req->id) }}" target="_blank" class="act-btn act-print" title="พิมพ์"><i class="fas fa-print"></i></a>
                                <form action="{{ route('leave.requests.destroy', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบรายการลานี้?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn act-delete" title="ลบ"><i class="fas fa-times"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="ls-empty">
                                <i class="fas fa-inbox"></i>
                                <div>ยังไม่มีรายการลาในปีนี้</div>
                            </td>
                        </tr>
 
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection