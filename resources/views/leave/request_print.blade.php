<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบลา — {{ $request->leaveType->leave_type_name ?? $request->leave_type_key }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'TH Sarabun New', 'Sarabun', sans-serif; font-size: 16pt; margin: 0; padding: 0; }
        .page { width: 21cm; min-height: 29.7cm; margin: auto; padding: 2cm 2.5cm; }

        h2 { text-align: center; font-size: 20pt; margin-bottom: 6px; }
        .sub-title { text-align: center; font-size: 14pt; color: #555; margin-bottom: 28px; }

        .row { display: flex; gap: 8px; margin-bottom: 10px; align-items: baseline; }
        .label { min-width: 180px; font-weight: bold; }
        .val { flex: 1; border-bottom: 1px dotted #999; padding-bottom: 2px; }

        .box { border: 1px solid #999; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; }

        .sig-row { display: flex; justify-content: space-between; margin-top: 60px; }
        .sig-col { text-align: center; width: 200px; }
        .sig-line { border-bottom: 1px solid #333; width: 100%; margin-bottom: 6px; }
        .sig-label { font-size: 12pt; color: #555; }

        .badge { display: inline-block; border-radius: 20px; padding: 2px 14px; font-size: 14pt; font-weight: bold; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-pending  { background: #fef3c7; color: #92400e; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>
<div class="page">
    <h2>ใบลา{{ $request->leaveType->leave_type_name ?? $request->leave_type_key }}</h2>
    <div class="sub-title">
        เลขที่ {{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }} &nbsp;|&nbsp;
        วันที่แจ้งลา {{ \Carbon\Carbon::parse($request->request_date)->addYears(543)->format('d/m/Y') }}
        {{ \Carbon\Carbon::parse($request->request_date)->format('H:i') }} น.
    </div>

    <div class="box">
        <div class="row">
            <span class="label">ชื่อ - นามสกุล</span>
            <span class="val">
                @if ($request->requester)
                    {{ $request->requester->thai_prefix }}{{ $request->requester->thai_firstname }} {{ $request->requester->thai_lastname }}
                @else - @endif
            </span>
        </div>
        <div class="row">
            <span class="label">รหัสพนักงาน</span>
            <span class="val">{{ $request->requester->employee_code ?? '-' }}</span>
            <span class="label" style="min-width:120px;">แผนก</span>
            <span class="val">{{ $request->requester->department ?? '-' }}</span>
        </div>
    </div>

    <div class="box">
        <div class="row">
            <span class="label">ประเภทการลา</span>
            <span class="val">{{ $request->leaveType->leave_type_name ?? $request->leave_type_key }}</span>
        </div>
        <div class="row">
            <span class="label">วันที่เริ่มลา</span>
            <span class="val">{{ \Carbon\Carbon::parse($request->start_date)->addYears(543)->format('d เดือน m พ.ศ. Y') }}</span>
        </div>
        <div class="row">
            <span class="label">วันที่สิ้นสุด</span>
            <span class="val">{{ \Carbon\Carbon::parse($request->end_date)->addYears(543)->format('d เดือน m พ.ศ. Y') }}</span>
        </div>
        <div class="row">
            <span class="label">จำนวนวัน</span>
            <span class="val">{{ number_format($request->num_days, 1) }} วัน</span>
        </div>
        <div class="row">
            <span class="label">เหตุผล</span>
            <span class="val">{{ $request->reason ?? '-' }}</span>
        </div>
    </div>

    <div class="box">
        <div class="row">
            <span class="label">สถานะ</span>
            <span class="val">
                @if ($request->status === 'อนุมัติ')
                    <span class="badge badge-approved">{{ $request->status }}</span>
                @elseif ($request->status === 'ไม่อนุมัติ')
                    <span class="badge badge-rejected">{{ $request->status }}</span>
                @else
                    <span class="badge badge-pending">{{ $request->status }}</span>
                @endif
            </span>
        </div>
        @if ($request->note)
            <div class="row">
                <span class="label">หมายเหตุ</span>
                <span class="val">{{ $request->note }}</span>
            </div>
        @endif
    </div>

    <div class="sig-row">
        <div class="sig-col">
            <div class="sig-line">&nbsp;</div>
            <div>ผู้ยื่นคำร้อง</div>
            <div class="sig-label">
                @if ($request->requester)
                    ({{ $request->requester->thai_prefix }}{{ $request->requester->thai_firstname }} {{ $request->requester->thai_lastname }})
                @endif
            </div>
        </div>
        <div class="sig-col">
            <div class="sig-line">&nbsp;</div>
            <div>ผู้ตรวจสอบ</div>
            <div class="sig-label">
                @if ($request->reviewer)
                    ({{ $request->reviewer->thai_prefix }}{{ $request->reviewer->thai_firstname }} {{ $request->reviewer->thai_lastname }})
                @endif
            </div>
        </div>
        <div class="sig-col">
            <div class="sig-line">&nbsp;</div>
            <div>ผู้อนุมัติ</div>
            <div class="sig-label">(.....................................)</div>
        </div>
    </div>
</div>
<script>window.onload = () => window.print();</script>
</body>
</html>
