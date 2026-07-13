@extends('parent.layout')
@section('title', 'ผลการเรียน')

@section('content')
<div class="pp-card">
    <div class="pp-title">ผลการเรียน</div>

    @if($semesters->isEmpty())
        <div class="text-muted">ยังไม่มีข้อมูลผลการเรียน</div>
    @else
        <form method="GET" action="{{ route('parent.grades') }}" class="mb-3" style="max-width:340px;">
            <select name="semester_id" class="form-select" onchange="this.form.submit()">
                @foreach($semesters as $sem)
                    <option value="{{ $sem->semester_id }}" {{ (string)$semesterId === (string)$sem->semester_id ? 'selected' : '' }}>
                        {{ $sem->academicYear->year_name ?? '' }} ภาคเรียนที่ {{ $sem->semester_name }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>รหัสวิชา</th>
                        <th>รายวิชา</th>
                        <th style="width:90px;">หน่วยกิต</th>
                        <th style="width:90px;">ผลการเรียน</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $r)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $r->code }}</td>
                            <td style="text-align:left">{{ $r->name }}</td>
                            <td class="text-center">{{ $r->is_activity ? '-' : number_format($r->credits, 1) }}</td>
                            <td class="text-center fw-bold">{{ $r->grade }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">ไม่มีข้อมูล</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="display:flex; gap:20px; font-weight:600; color:#082b75;">
            <div>หน่วยกิตรวม: {{ number_format($totalCredits, 1) }}</div>
            <div>เกรดเฉลี่ย (GPA): {{ number_format($gpa, 2) }}</div>
        </div>
    @endif
</div>
@endsection
