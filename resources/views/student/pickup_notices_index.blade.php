@extends('layouts.sidebar')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold mb-3" style="color:#082b75;">การรับ-ส่งนักเรียน (ที่ผู้ปกครองแจ้งเข้ามา)</h4>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">วันที่</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">ระดับชั้น</label>
                    <select name="level_id" class="form-select">
                        <option value="">-- ทุกระดับชั้น --</option>
                        @foreach($levels as $l)
                            <option value="{{ $l->level_id }}" {{ (string) $levelId === (string) $l->level_id ? 'selected' : '' }}>{{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> ดู</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($notices->isEmpty())
                <div class="text-muted">ไม่มีการแจ้งรับ-ส่งนักเรียนในวันที่เลือก</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>เวลา</th>
                                <th>นักเรียน</th>
                                <th>ห้อง</th>
                                <th>ผู้มารับ</th>
                                <th>ความเกี่ยวข้อง</th>
                                <th>เบอร์โทร</th>
                                <th>หมายเหตุ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notices as $n)
                            @php $section = $n->student?->studentSections->first()?->classSection; @endphp
                            <tr>
                                <td>{{ $n->pickup_time ? \Carbon\Carbon::parse($n->pickup_time)->format('H:i') : '-' }}</td>
                                <td>{{ $n->student->thai_prefix ?? '' }}{{ $n->student->thai_firstname ?? '-' }} {{ $n->student->thai_lastname ?? '' }}</td>
                                <td>{{ $section?->full_name ?? '-' }}</td>
                                <td class="fw-semibold">{{ $n->pickup_person_name }}</td>
                                <td>{{ $n->relationship ?? '-' }}</td>
                                <td>{{ $n->phone ?? '-' }}</td>
                                <td>{{ $n->note ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
