@extends('parent.layout')
@section('title', 'ปฏิทิน / วันหยุด')

@section('content')
@php
    $thaiMonths = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    $first = \Carbon\Carbon::create($year, $month, 1);
    $daysInMonth = $first->daysInMonth;
    $startWeekday = $first->dayOfWeek; // 0=Sun
    $prev = $first->copy()->subMonth();
    $next = $first->copy()->addMonth();
@endphp
<div class="pp-card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
        <a href="{{ route('parent.calendar', ['month' => $prev->format('n'), 'year' => $prev->format('Y')]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
        <div class="pp-title" style="border:none; margin:0;">{{ $thaiMonths[$month] }} {{ $year + 543 }}</div>
        <a href="{{ route('parent.calendar', ['month' => $next->format('n'), 'year' => $next->format('Y')]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
    </div>

    <table class="table table-bordered" style="table-layout:fixed;">
        <thead class="table-light">
            <tr>
                @foreach(['อา','จ','อ','พ','พฤ','ศ','ส'] as $wd)
                    <th class="text-center">{{ $wd }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $day = 1; @endphp
            @for($w = 0; $w < 6 && $day <= $daysInMonth; $w++)
                <tr>
                    @for($d = 0; $d < 7; $d++)
                        @if($w == 0 && $d < $startWeekday)
                            <td></td>
                        @elseif($day > $daysInMonth)
                            <td></td>
                        @else
                            @php
                                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                $holidayTitle = $holidayMap[$dateStr] ?? null;
                            @endphp
                            <td style="height:70px; vertical-align:top; {{ $holidayTitle ? 'background:#fef2f2;' : '' }}">
                                <div style="font-weight:600;">{{ $day }}</div>
                                @if($holidayTitle)
                                    <div style="font-size:.75rem; color:#b91c1c;">{{ $holidayTitle }}</div>
                                @endif
                            </td>
                            @php $day++; @endphp
                        @endif
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>
</div>

<div class="pp-card">
    <div class="pp-title">รายการวันหยุดปีการศึกษานี้</div>
    @if($holidays->isEmpty())
        <div class="text-muted">ยังไม่มีข้อมูลวันหยุด</div>
    @else
        <ul class="list-group">
            @foreach($holidays as $h)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $h->title }}</span>
                    <span class="text-muted">
                        {{ $h->start_date->format('d/m/Y') }}
                        @if($h->end_date && !$h->end_date->equalTo($h->start_date)) - {{ $h->end_date->format('d/m/Y') }} @endif
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
