@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">ข้อมูลบุคคล</a><i class="bi bi-chevron-right"></i><a href="{{ route('promotions.index') }}">เลื่อนชั้น/ย้ายห้อง</a><i class="bi bi-chevron-right"></i><span>ประวัติ</span></nav>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-clock-history"></i> ประวัติการเลื่อนชั้น/ย้ายห้อง/จบการศึกษา</div>
        <div class="ac-card-body">
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>#</th><th>วันที่</th><th>นักเรียน</th><th>ประเภท</th><th>จากห้อง</th><th>ไปห้อง</th><th>หมายเหตุ</th><th>ดำเนินการโดย</th></tr></thead>
                    <tbody>
                        @forelse($promotions as $i => $p)
                        <tr>
                            <td>{{ $promotions->firstItem() + $i }}</td>
                            <td>{{ $p->promo_date->format('d/m/Y') }}</td>
                            <td style="text-align:left">{{ $p->student->thai_prefix ?? '' }}{{ $p->student->thai_firstname ?? '' }} {{ $p->student->thai_lastname ?? '' }}</td>
                            <td>
                                <span class="ac-badge
                                    {{ $p->promo_type == 'เลื่อนชั้น' ? 'ac-badge-active' : '' }}
                                    {{ $p->promo_type == 'ย้ายห้อง' ? 'ac-badge-info' : '' }}
                                    {{ $p->promo_type == 'บันทึกจบ' ? 'ac-badge-warn' : '' }}
                                    {{ $p->promo_type == 'ซ้ำชั้น' ? 'ac-badge-inactive' : '' }}
                                ">{{ $p->promo_type }}</span>
                            </td>
                            <td>{{ $p->fromSection ? $p->fromSection->level->name . '/' . $p->fromSection->section_number : '-' }}</td>
                            <td>{{ $p->toSection ? $p->toSection->level->name . '/' . $p->toSection->section_number : 'จบ/ออก' }}</td>
                            <td>{{ $p->remark ?? '-' }}</td>
                            <td>{{ $p->created_by ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="ac-empty">ไม่มีประวัติ</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="ac-pagination">{{ $promotions->links() }}</div>
        </div>
    </div>
</div>
@endsection