@extends('layouts.sidebar')

@push('styles')
<style>
    .hv-page { padding: 24px 28px; }
    .hv-breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; margin-bottom: 20px; color: #555; }
    .hv-breadcrumb a { color: #5482e7; text-decoration: none; font-weight: 500; }
    .hv-breadcrumb span { color: #5482e7; font-weight: 600; }
    .hv-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); padding: 22px; margin-bottom: 20px; }
    .hv-card-title { color: #082b75; font-weight: 700; font-size: 1.05rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; justify-content: space-between; flex-wrap: wrap; }
    .hv-form-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px 20px; margin-bottom: 18px; }
    .hv-form-row { display: flex; flex-direction: column; gap: 5px; }
    .hv-label { font-size: 0.85rem; font-weight: 600; color: #444; }
    .hv-input, .hv-select { border: 1px solid #d0d7e5; border-radius: 6px; padding: 8px 12px; font-size: 0.9rem; font-family: inherit; outline: none; box-sizing: border-box; width: 100%; background:#fff; }
    .hv-search-actions { display: flex; justify-content: center; gap: 12px; border-top: 1px solid #f0f3f7; padding-top: 16px; }
    .btn-search { background: #5482e7; color: #fff; border: none; border-radius: 6px; padding: 9px 30px; font-size: 0.88rem; font-weight: 600; cursor: pointer; font-family: inherit; }
    .btn-search:hover { background: #446bca; }
    .btn-reset { background: #fff; color: #666; border: 1.5px solid #d0d7de; border-radius: 6px; padding: 9px 24px; font-size: 0.88rem; font-weight: 600; cursor: pointer; font-family: inherit; text-decoration: none; }

    .summary-pills { display: flex; gap: 10px; flex-wrap: wrap; }
    .summary-pill { display: inline-flex; align-items: center; gap: 6px; background: #eef6ff; color: #3949ab; border-radius: 20px; padding: 5px 14px; font-size: 0.82rem; font-weight: 700; }
    .summary-pill.warn { background: #fff3e0; color: #e65100; }

    .hv-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.85rem; }
    .hv-table thead th { padding: 12px 10px; font-weight: 600; color: #fff; background: #5482e7; text-align: center; white-space: nowrap; }
    .hv-table thead th:first-child { border-top-left-radius: 8px; }
    .hv-table thead th:last-child { border-top-right-radius: 8px; }
    .hv-table tbody tr td { border-bottom: 1px solid #f2f4f8; }
    .hv-table tbody tr:nth-child(even) td { background: #fafcff; }
    .hv-table tbody td { padding: 10px 12px; color: #555; vertical-align: middle; text-align: center; }
    .hv-table tbody td.td-left { text-align: left; }
    .badge-followup { background: #fff3e0; color: #e65100; border-radius: 20px; padding: 2px 10px; font-size: 0.76rem; font-weight: 700; }
    .hv-pagination { display: flex; justify-content: flex-end; margin-top: 16px; }
    .hv-empty { text-align: center; color: #94a3b8; padding: 30px 0; }
</style>
@endpush

@section('content')
<div class="hv-page">
    <nav class="hv-breadcrumb">
        <a href="#">กิจการนักเรียน</a>
        <i class="bi bi-chevron-right"></i>
        <span>สรุปผลการเยี่ยมบ้าน</span>
    </nav>

    <div class="hv-card">
        <div class="hv-card-title"><i class="fas fa-filter" style="color:#4b7ce3;"></i> ค้นหา</div>
        <form method="GET" action="{{ route('home-visits.results-summary') }}">
            <div class="hv-form-grid">
                <div class="hv-form-row">
                    <label class="hv-label">ระดับชั้น</label>
                    <select name="level_id" class="hv-select">
                        <option value="">ทั้งหมด</option>
                        @foreach($levels as $lv)
                            <option value="{{ $lv->level_id }}" {{ $levelId == $lv->level_id ? 'selected' : '' }}>{{ $lv->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="hv-form-row">
                    <label class="hv-label">ห้องเรียน</label>
                    <select name="section_id" class="hv-select">
                        <option value="">ทั้งหมด</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec->section_id }}" {{ $sectionId == $sec->section_id ? 'selected' : '' }}>{{ $sec->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="hv-form-row">
                    <label class="hv-label">สภาพเศรษฐกิจ</label>
                    <select name="economic_status" class="hv-select">
                        <option value="">ทั้งหมด</option>
                        <option value="ดี" {{ request('economic_status') == 'ดี' ? 'selected' : '' }}>ดี</option>
                        <option value="ปานกลาง" {{ request('economic_status') == 'ปานกลาง' ? 'selected' : '' }}>ปานกลาง</option>
                        <option value="ยากจน" {{ request('economic_status') == 'ยากจน' ? 'selected' : '' }}>ยากจน</option>
                    </select>
                </div>
                <div class="hv-form-row">
                    <label class="hv-label">&nbsp;</label>
                    <label style="display:flex; align-items:center; gap:8px; font-size:0.88rem; color:#444; height:38px;">
                        <input type="checkbox" name="need_followup_only" value="1" {{ request('need_followup_only') ? 'checked' : '' }}>
                        เฉพาะที่ต้องติดตาม
                    </label>
                </div>
                <div class="hv-form-row">
                    <label class="hv-label">จากวันที่</label>
                    <input type="date" name="date_from" class="hv-input" value="{{ request('date_from') }}">
                </div>
                <div class="hv-form-row">
                    <label class="hv-label">ถึงวันที่</label>
                    <input type="date" name="date_to" class="hv-input" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="hv-search-actions">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> ค้นหา</button>
                <a href="{{ route('home-visits.results-summary') }}" class="btn-reset"><i class="fas fa-redo"></i> ล้างค่า</a>
            </div>
        </form>
    </div>

    <div class="hv-card">
        <div class="hv-card-title">
            <span><i class="fas fa-chart-simple" style="color:#4b7ce3;"></i> สรุปผลการเยี่ยมบ้าน</span>
            <span class="summary-pills">
                @foreach($economicSummary as $status => $count)
                    <span class="summary-pill">{{ $status ?: 'ไม่ระบุ' }}: {{ $count }}</span>
                @endforeach
                @if($followupCount > 0)
                    <span class="summary-pill warn">ต้องติดตาม: {{ $followupCount }}</span>
                @endif
                <span class="summary-pill">รวม {{ $visits->total() }} รายการ</span>
            </span>
        </div>

        <div style="overflow-x:auto; border-radius: 8px; border: 1px solid #eaeef2;">
            <table class="hv-table">
                <thead>
                    <tr>
                        <th style="width:100px;">วันที่เยี่ยม</th>
                        <th>ชื่อ - นามสกุล</th>
                        <th>อาศัยอยู่กับ</th>
                        <th style="width:100px;">เศรษฐกิจ</th>
                        <th>ปัญหาที่พบ</th>
                        <th style="width:100px;">ติดตาม</th>
                        <th style="width:130px;">ผู้บันทึก</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $v)
                        <tr>
                            <td>{{ $v->visit_date->format('d/m/Y') }}</td>
                            <td class="td-left">{{ $v->student?->thai_prefix }}{{ $v->student?->thai_firstname }} {{ $v->student?->thai_lastname }}</td>
                            <td class="td-left">{{ $v->family_status ?? '-' }}</td>
                            <td>{{ $v->economic_status ?? '-' }}</td>
                            <td class="td-left">{{ $v->problems_found ?? '-' }}</td>
                            <td>@if($v->need_followup)<span class="badge-followup">ต้องติดตาม</span>@else - @endif</td>
                            <td>{{ $v->personnel?->thai_firstname ?? $v->created_by ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="hv-empty">ไม่พบข้อมูลการเยี่ยมบ้าน</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="hv-pagination">{{ $visits->links() }}</div>
    </div>
</div>
@endsection
