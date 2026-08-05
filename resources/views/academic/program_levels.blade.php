@extends('layouts.sidebar')
@push('styles')
<style>
.pp-page { padding: 24px 28px; }

.pp-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 20px 20px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.pp-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff; background: #43a047;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.pp-card-header {
    margin-left: 90px; display: flex; align-items: center;
    justify-content: space-between; margin-top: -8px; margin-bottom: 20px;
    flex-wrap: wrap; gap: 10px;
}
.pp-card-title { font-size: 1.05rem; color: #555; }

.pp-back {
    background: #5c6bc0; color: #fff; border: none; border-radius: 6px;
    padding: 7px 18px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.pp-back:hover { background: #3949ab; color: #fff; }

.btn-add {
    background: #43a047; color: #fff; border: none; border-radius: 6px;
    padding: 8px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-add:hover { background: #2e7d32; color: #fff; }

.pp-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 10px; }
.pp-table thead th {
    padding: 12px 14px; background: #43a047; color: #fff;
    font-weight: 600; text-align: left; font-size: 0.85rem;
}
.pp-table thead th:first-child { border-radius: 6px 0 0 0; }
.pp-table thead th:last-child  { border-radius: 0 6px 0 0; text-align: center; }
.pp-table tbody tr { border-bottom: 1px solid #f0f0f0; cursor: pointer; }
.pp-table tbody tr:hover { background: #f1f8f1; }
.pp-table tbody td { padding: 12px 14px; color: #555; vertical-align: middle; }

.pp-empty { text-align: center; padding: 40px; color: #aaa; }

.badge-plan-count {
    display: inline-block; background: #e8f5e9; color: #2e7d32;
    border-radius: 20px; padding: 3px 14px; font-size: 0.82rem; font-weight: 700;
}

.btn-viewlevel {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px;
    padding: 6px 16px; font-size: 0.8rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-viewlevel:hover { background: #0097a7; color: #fff; }
</style>
@endpush

@section('content')
<div class="pp-page">

    <div class="pp-card">
        <div class="pp-icon"><i class="bi bi-journal-check"></i></div>
        <div class="pp-card-header">
            <span class="pp-card-title">
                จัดการแผนปีการศึกษา หลักสูตร <strong>{{ $program->name }}</strong>
            </span>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('curriculums.create', ['program_id' => $program->program_id, 'return_to' => url()->full()]) }}" class="btn-add">
                    <i class="bi bi-plus-lg"></i> สร้างแผน
                </a>
                <a href="{{ route('programs.index') }}" class="pp-back">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </a>
            </div>
        </div>

        <table class="pp-table">
            <thead>
                <tr>
                    <th style="width:50px">ลำดับ</th>
                    <th>ชั้นเรียน</th>
                    <th style="text-align:center">จำนวนแผน</th>
                    <th>ปีการศึกษาที่มีแผน</th>
                    <th style="text-align:center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($levelGroups as $i => $g)
                <tr onclick="window.location='{{ route('programs.levelPlans', [$program->program_id, $g->level_id]) }}'">
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $g->level->name ?? 'ไม่ระบุระดับ' }}</strong></td>
                    <td style="text-align:center"><span class="badge-plan-count">{{ $g->count }} แผน</span></td>
                    <td>{{ $g->years->implode(', ') ?: '-' }}</td>
                    <td style="text-align:center">
                        <a href="{{ route('programs.levelPlans', [$program->program_id, $g->level_id]) }}" class="btn-viewlevel" onclick="event.stopPropagation()">
                            ดูแผน <i class="bi bi-chevron-right"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="pp-empty">
                        <i class="bi bi-journal-x" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                        ยังไม่มีแผนในหลักสูตรนี้ — กด "สร้างแผน" เพื่อเริ่มสร้างแผนแรก
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
