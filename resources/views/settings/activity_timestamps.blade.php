@extends('layouts.sidebar')

@push('styles')
<style>
    body { background: #f4f6f9; }
    .page { padding: 24px 28px; }
    .breadcrumb-custom a { color: #00bcd4; text-decoration: none; font-size: 0.95rem; }
    .breadcrumb-custom i { color: #888; margin: 0 8px; font-size: 0.8rem; }

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
    .card-header-text { margin-left: 90px; font-size: 1.1rem; color: #555; margin-top: -10px; font-weight: 600; }

    .table > thead > tr > th {
        border-bottom: 2px solid #eee; color: #333; font-weight: 600;
        white-space: nowrap; padding-bottom: 14px;
    }
    .table > tbody > tr > td {
        vertical-align: middle; color: #555;
        border-bottom: 1px solid #f5f5f5; padding: 13px 10px;
    }
    .ats-select {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 8px 12px;
        font-size: 0.9rem; color: #333; font-family: inherit; outline: none; min-width: 220px;
    }
    .ats-badge { background: #eef4ff; color: #2563eb; border-radius: 20px; padding: 3px 12px; font-size: 0.8rem; font-weight: 600; }
    .ats-method { border-radius: 4px; padding: 2px 8px; font-size: 0.75rem; font-weight: 700; }
    .ats-method-post { background: #dcfce7; color: #15803d; }
    .ats-method-put, .ats-method-patch { background: #fef3c7; color: #b45309; }
    .ats-method-delete { background: #fee2e2; color: #b91c1c; }
</style>
@endpush

@section('content')
<div class="page">

    <nav class="breadcrumb-custom mb-3">
        <a href="#">ตั้งค่า</a>
        <i class="bi bi-chevron-right"></i>
        <span style="color:#555;">ไทม์สแตมป์ (ใครบันทึก/แก้ไขหน้าไหน)</span>
    </nav>

    <div class="floating-card">
        <div class="floating-icon" style="background:#00bcd4;"><i class="fas fa-clock"></i></div>
        <div class="card-header-text">ค้นหา</div>
        <form method="GET" action="{{ route('activity-timestamps.index') }}" style="margin-top:20px; display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
            <div>
                <label style="font-size:0.85rem;color:#555;font-weight:600;display:block;margin-bottom:4px;">คน</label>
                <select name="personnel_id" class="ats-select" onchange="this.form.submit()">
                    <option value="">-- ทุกคน --</option>
                    @foreach ($people as $p)
                        <option value="{{ $p->personnel_id }}" {{ (string)$personnelId === (string)$p->personnel_id ? 'selected' : '' }}>
                            {{ $p->personnel_name }}{{ $p->employee_code ? ' (' . $p->employee_code . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:0.85rem;color:#555;font-weight:600;display:block;margin-bottom:4px;">หน้า</label>
                <select name="page_group" class="ats-select" onchange="this.form.submit()">
                    <option value="">-- ทุกหน้า --</option>
                    @foreach ($pageGroups as $g)
                        <option value="{{ $g['group'] }}" {{ $pageGroup === $g['group'] ? 'selected' : '' }}>{{ $g['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <span class="ats-badge">ทั้งหมด {{ number_format($logs->total()) }} รายการ</span>
        </form>
    </div>

    <div class="floating-card">
        <div class="floating-icon" style="background:#f59e0b;"><i class="fas fa-list-check"></i></div>
        <div class="card-header-text">รายการ (นับเฉพาะครั้งแรกที่บันทึกของแต่ละคนต่อแต่ละหน้า)</div>

        <div style="margin-top:20px; overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:60px;">ลำดับ</th>
                        <th>ผู้บันทึก</th>
                        <th>หน้า</th>
                        <th>รายละเอียด (route)</th>
                        <th style="width:90px;text-align:center;">วิธี</th>
                        <th style="width:170px;">บันทึกครั้งแรกเมื่อ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $i => $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $i }}</td>
                            <td>{{ $log->personnel_name }}{{ $log->employee_code ? ' (' . $log->employee_code . ')' : '' }}</td>
                            <td>{{ $log->page_label }}</td>
                            <td style="color:#999;font-size:0.82rem">{{ $log->route_name }}</td>
                            <td style="text-align:center;">
                                <span class="ats-method ats-method-{{ strtolower($log->method) }}">{{ $log->method }}</span>
                            </td>
                            <td>{{ $log->first_recorded_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fs-2 d-block mb-2"></i>
                                ยังไม่มีข้อมูล
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div style="margin-top:16px">{{ $logs->links() }}</div>
        @endif
    </div>

</div>
@endsection
