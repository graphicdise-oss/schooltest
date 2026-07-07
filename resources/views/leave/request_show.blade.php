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
        font-size: 32px; color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .ls-card-header {
        margin-left: 90px; font-size: 1.05rem; color: #444;
        margin-top: -10px; font-weight: 600;
        display: flex; justify-content: space-between; align-items: center;
    }

    .detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px 32px; margin-top: 24px; }
    .detail-item { display: flex; flex-direction: column; gap: 4px; }
    .detail-label { font-size: 0.8rem; color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
    .detail-value { font-size: 0.95rem; color: #333; font-weight: 500; }

    .badge-approved { background: #dcfce7; color: #16a34a; border-radius: 20px; padding: 4px 14px; font-size: 0.85rem; font-weight: 600; }
    .badge-pending  { background: #fef9c3; color: #b45309; border-radius: 20px; padding: 4px 14px; font-size: 0.85rem; font-weight: 600; }
    .badge-rejected { background: #fee2e2; color: #dc2626; border-radius: 20px; padding: 4px 14px; font-size: 0.85rem; font-weight: 600; }

    .reason-box { background: #f8faff; border-radius: 8px; padding: 14px 16px; font-size: 0.9rem; color: #555; margin-top: 18px; line-height: 1.6; }

    .btn-back {
        display: inline-flex; align-items: center; gap: 6px;
        background: #f4f6fb; color: #5482e7; border: 1.5px solid #d0d7e5;
        border-radius: 6px; padding: 8px 18px; font-size: 0.85rem; font-weight: 600;
        text-decoration: none; transition: background 0.15s;
    }
    .btn-back:hover { background: #e8eeff; text-decoration: none; color: #3949ab; }
    .btn-print {
        display: inline-flex; align-items: center; gap: 6px;
        background: #f59e0b; color: #fff; border: none;
        border-radius: 6px; padding: 8px 18px; font-size: 0.85rem; font-weight: 600;
        text-decoration: none; cursor: pointer; transition: background 0.15s;
    }
    .btn-print:hover { background: #d97706; text-decoration: none; color: #fff; }

    .status-form { margin-top: 24px; padding-top: 20px; border-top: 1px solid #f0f3f7; }
    .status-form select, .status-form textarea {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 8px 12px;
        font-size: 0.88rem; color: #333; width: 100%; font-family: inherit;
        outline: none; box-sizing: border-box;
    }
    .status-form select:focus, .status-form textarea:focus { border-color: #5482e7; }
    .btn-save {
        background: #5482e7; color: #fff; border: none; border-radius: 6px;
        padding: 9px 28px; font-size: 0.88rem; font-weight: 600; cursor: pointer;
        font-family: inherit; margin-top: 12px;
    }
    .btn-save:hover { background: #446bca; }
</style>
@endpush

@section('content')
<div class="ls-page">

    <nav class="ls-breadcrumb">
        <a href="{{ route('leave.personnel.index') }}">ข้อมูลการลา</a>
        <i class="bi bi-chevron-right"></i>
        @if ($personnel)
            <a href="{{ route('leave.personnel.show', $personnel->personnel_id) }}">
                {{ $personnel->thai_prefix }}{{ $personnel->thai_firstname }} {{ $personnel->thai_lastname }}
            </a>
            <i class="bi bi-chevron-right"></i>
        @endif
        <span>รายละเอียดการลา #{{ $request->id }}</span>
    </nav>

    @if(session('success'))
        <div style="background:#dcfce7; color:#16a34a; border-radius:6px; padding:10px 16px; margin-bottom:16px; font-size:0.88rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="ls-card">
        <div class="ls-icon" style="background: #f59e0b;">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="ls-card-header">
            <strong>รายละเอียดการลา</strong>
            <div style="display:flex; gap:8px;">
                <a href="{{ route('leave.requests.print', $request->id) }}" target="_blank" class="btn-print">
                    <i class="fas fa-print"></i> พิมพ์
                </a>
                @if ($personnel)
                    <a href="{{ route('leave.personnel.show', $personnel->personnel_id) }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i> ย้อนกลับ
                    </a>
                @endif
            </div>
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">ประเภทการลา</span>
                <span class="detail-value">{{ $request->leaveType->leave_type_name ?? $request->leave_type_key }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">สถานะ</span>
                <span class="detail-value">
                    @if ($request->status === 'อนุมัติ')
                        <span class="badge-approved">{{ $request->status }}</span>
                    @elseif ($request->status === 'ไม่อนุมัติ')
                        <span class="badge-rejected">{{ $request->status }}</span>
                    @else
                        <span class="badge-pending">{{ $request->status }}</span>
                    @endif
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">วันที่แจ้งลา</span>
                <span class="detail-value">
                    {{ \Carbon\Carbon::parse($request->request_date)->addYears(543)->format('d/m/Y') }}
                    {{ \Carbon\Carbon::parse($request->request_date)->format('H:i:s') }} น.
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">จำนวนวัน</span>
                <span class="detail-value" style="color:#5482e7; font-weight:700; font-size:1.1rem;">
                    {{ number_format($request->num_days, 1) }} วัน
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">วันที่เริ่มลา</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($request->start_date)->addYears(543)->format('d/m/Y') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">วันที่สิ้นสุด</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($request->end_date)->addYears(543)->format('d/m/Y') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">ผู้ยื่นคำร้อง</span>
                <span class="detail-value">
                    @if ($request->requester)
                        {{ $request->requester->thai_prefix }}{{ $request->requester->thai_firstname }} {{ $request->requester->thai_lastname }}
                    @else - @endif
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">ผู้ตรวจสอบ</span>
                <span class="detail-value">
                    @if ($request->reviewer)
                        {{ $request->reviewer->thai_prefix }}{{ $request->reviewer->thai_firstname }} {{ $request->reviewer->thai_lastname }}
                    @else - @endif
                </span>
            </div>
        </div>

        @if ($request->reason)
            <div>
                <div class="detail-label" style="margin-top:18px;">เหตุผลการลา</div>
                <div class="reason-box">{{ $request->reason }}</div>
            </div>
        @endif

        @if ($request->note)
            <div>
                <div class="detail-label" style="margin-top:18px;">หมายเหตุผู้อนุมัติ</div>
                <div class="reason-box" style="background:#fffbeb;">{{ $request->note }}</div>
            </div>
        @endif

        <div class="status-form">
            <div class="detail-label" style="margin-bottom:10px;">อัปเดตสถานะ</div>
            <form method="POST" action="{{ route('leave.requests.updateStatus', $request->id) }}">
                @csrf @method('PATCH')
                <div style="display:grid; grid-template-columns:200px 1fr; gap:12px; align-items:start;">
                    <select name="status">
                        @foreach (['รอการอนุมัติ','อนุมัติ','ไม่อนุมัติ'] as $s)
                            <option value="{{ $s }}" {{ $request->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                    <textarea name="note" rows="2" placeholder="หมายเหตุ (ถ้ามี)">{{ $request->note }}</textarea>
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-check"></i> บันทึกสถานะ</button>
            </form>
        </div>
    </div>
</div>
@endsection