@extends('layouts.sidebar')

@push('styles')
<style>
    .bh-page { padding: 24px 28px; min-height: 100%; }

    .bh-breadcrumb { font-size: 0.85rem; color: #999; margin-bottom: 20px; }
    .bh-breadcrumb a { color: #4b7ce3; text-decoration: none; }
    .bh-breadcrumb a:hover { text-decoration: underline; }

    .bh-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 24px 20px 20px;
        margin-bottom: 28px;
    }

    .bh-info-row { display: flex; gap: 40px; flex-wrap: wrap; margin-bottom: 20px; }
    .bh-info-item .bh-label { font-size: 0.8rem; color: #888; margin-bottom: 4px; }
    .bh-info-item .bh-value {
        background: #f5f5f5; border-radius: 4px; padding: 8px 16px;
        font-size: 0.92rem; font-weight: 600; color: #333; min-width: 220px; display: inline-block;
    }

    .bh-score-box {
        display: flex; align-items: center; gap: 14px; margin-bottom: 20px;
        background: #e0f7fa; border-radius: 6px; padding: 14px 20px;
    }
    .bh-score-box .bh-score-num { font-size: 1.6rem; font-weight: 800; color: #00838f; }

    .bh-table { width: 100%; border-collapse: collapse; font-size: 0.86rem; }
    .bh-table thead th {
        padding: 10px 10px; font-weight: 600; color: #333;
        border-bottom: 2px solid #eee; background: #fafafa; white-space: nowrap;
    }
    .bh-table tbody tr { border-bottom: 1px solid #f5f5f5; }
    .bh-table tbody tr:hover { background: #fef9f0; }
    .bh-table tbody td { padding: 10px 10px; color: #555; vertical-align: middle; }
    .bh-table .no-data { text-align: center; padding: 30px; color: #aaa; }

    .bh-badge { border-radius: 12px; padding: 3px 10px; font-size: 0.76rem; font-weight: 700; }
    .bh-badge-add    { background: #dcfce7; color: #16a34a; }
    .bh-badge-deduct { background: #fee2e2; color: #dc2626; }

    .bh-pagination { display: flex; justify-content: flex-end; margin-top: 14px; font-size: 0.82rem; }
    .bh-back { color: #00838f; text-decoration: none; font-size: 0.88rem; font-weight: 600; }
    .bh-back:hover { text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="bh-page">

    <div class="bh-breadcrumb">
        กิจการนักเรียน &rsaquo; <span style="color:#00838f">ระบบความประพฤติ</span> &rsaquo;
        ประวัติคะแนนความประพฤติ
    </div>

    <div class="bh-card">
        <a href="{{ url()->previous() }}" class="bh-back"><i class="bi bi-arrow-left"></i> กลับ</a>

        <div class="bh-info-row" style="margin-top:16px;">
            <div class="bh-info-item">
                <div class="bh-label">ชื่อ - นามสกุล</div>
                <div class="bh-value">{{ $student->thai_firstname }} {{ $student->thai_lastname }}</div>
            </div>
            <div class="bh-info-item">
                <div class="bh-label">เลขประจำตัวนักเรียน</div>
                <div class="bh-value">{{ $student->student_code }}</div>
            </div>
        </div>

        <div class="bh-score-box">
            <i class="bi bi-award" style="font-size:1.6rem;color:#00838f;"></i>
            <div>
                <div style="font-size:0.82rem;color:#666;">คะแนนความประพฤติ เต็ม {{ number_format($fullScore, 0) }} เริ่มต้น</div>
                <div class="bh-score-num">{{ number_format($fullScore, 2) }}</div>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="bh-table">
                <thead>
                    <tr>
                        <th style="width:60px">ลำดับที่</th>
                        <th>วัน-เวลา</th>
                        <th>ชื่อพฤติกรรม</th>
                        <th>ปีการศึกษา</th>
                        <th>คะแนน</th>
                        <th>คงเหลือ</th>
                        <th>หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $i => $r)
                        <tr>
                            <td>{{ $records->firstItem() + $i }}</td>
                            <td>{{ $r->created_at->format('n/j/Y g:i A') }}</td>
                            <td>{{ $r->item_name_th }}</td>
                            <td>{{ $r->year->year_name ?? '-' }} / {{ $r->semester->semester_name ?? '-' }}</td>
                            <td>
                                <span class="bh-badge {{ $r->type === 'add' ? 'bh-badge-add' : 'bh-badge-deduct' }}">
                                    {{ $r->type === 'add' ? '+' : '-' }}{{ number_format($r->points, 2) }}
                                </span>
                            </td>
                            <td>{{ number_format($r->balance_after, 2) }}</td>
                            <td>{{ $r->note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="no-data">ยังไม่มีประวัติคะแนนความประพฤติ</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bh-pagination">{{ $records->links('pagination::simple-default') }}</div>
    </div>
</div>
@endsection
