@extends('layouts.sidebar')
@push('styles')
<style>
.cby-page { padding: 24px 28px; }

.cby-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 20px 20px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.cby-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.cby-icon-list { background: #00897b; }
.cby-card-header {
    margin-left: 90px; display: flex; align-items: center;
    justify-content: space-between; margin-top: -8px; margin-bottom: 20px;
    flex-wrap: wrap; gap: 10px;
}
.cby-card-title { font-size: 1.05rem; color: #555; }

.cby-back {
    background: #5c6bc0; color: #fff; border: none; border-radius: 6px;
    padding: 7px 18px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.cby-back:hover { background: #3949ab; color: #fff; }

.cby-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 10px; }
.cby-table thead th {
    padding: 12px 14px; background: #00897b; color: #fff;
    font-weight: 600; text-align: left; font-size: 0.85rem;
}
.cby-table thead th:first-child { border-radius: 6px 0 0 0; }
.cby-table thead th:last-child  { border-radius: 0 6px 0 0; text-align: center; }
.cby-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.cby-table tbody tr:hover { background: #f0faf8; }
.cby-table tbody td { padding: 12px 14px; color: #555; vertical-align: middle; }

.badge-level {
    display: inline-block; background: #e0f2f1; color: #00695c;
    border-radius: 4px; padding: 2px 10px; font-size: 0.78rem; font-weight: 600;
}
.badge-subject {
    display: inline-block; background: #e8eaf6; color: #3949ab;
    border-radius: 20px; padding: 2px 12px; font-size: 0.78rem; font-weight: 700;
}

.btn-row { display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.btn-edit {
    background: #fb8c00; color: #fff; border: none; border-radius: 6px;
    padding: 6px 14px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 4px;
}
.btn-edit:hover { background: #e65100; color: #fff; }

.btn-copy {
    background: #039be5; color: #fff; border: none; border-radius: 6px;
    padding: 6px 14px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    font-family: inherit;
    display: inline-flex; align-items: center; gap: 4px;
}
.btn-copy:hover { background: #0277bd; }

.btn-delete {
    background: #e53935; color: #fff; border: none; border-radius: 6px;
    padding: 6px 14px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    font-family: inherit;
    display: inline-flex; align-items: center; gap: 4px;
}
.btn-delete:hover { background: #b71c1c; }

.btn-add {
    background: #43a047; color: #fff; border: none; border-radius: 6px;
    padding: 8px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-add:hover { background: #2e7d32; color: #fff; }

.cby-empty { text-align: center; padding: 40px; color: #aaa; }

/* Level filter pills */
.level-filter { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
.level-pill {
    padding: 5px 16px; border-radius: 20px; font-size: 0.82rem; font-weight: 600;
    border: 1.5px solid #b2dfdb; color: #00695c; background: #fff; cursor: pointer;
    font-family: inherit; transition: background 0.15s;
}
.level-pill:hover, .level-pill.active { background: #00897b; color: #fff; border-color: #00897b; }
</style>
@endpush

@section('content')
<div class="cby-page">

    {{-- Header card --}}
    <div class="cby-card">
        <div class="cby-icon cby-icon-list"><i class="bi bi-journal-text"></i></div>
        <div class="cby-card-header">
            <div>
                <a href="{{ route('curriculums.index') }}" class="cby-back">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </a>
                <span class="cby-card-title" style="margin-left:12px">
                    หลักสูตร ปีการศึกษา <strong>{{ $year }}</strong>
                </span>
            </div>
            <a href="{{ route('curriculums.create') }}" class="btn-add">
                <i class="bi bi-plus-lg"></i> เพิ่มหลักสูตร
            </a>
        </div>

        {{-- Level filter --}}
        @if($levels->count())
        <div class="level-filter" style="margin-left:0">
            <button class="level-pill active" onclick="filterLevel(this, '')">ทุกระดับ</button>
            @foreach($levels as $lv)
            <button class="level-pill" onclick="filterLevel(this, '{{ $lv->level_id }}')">{{ $lv->name }}</button>
            @endforeach
        </div>
        @endif

        <table class="cby-table">
            <thead>
                <tr>
                    <th style="width:50px">ลำดับ</th>
                    <th>ชื่อหลักสูตร / แผนการเรียน</th>
                    <th style="text-align:center">ระดับชั้น</th>
                    <th style="text-align:center">จำนวนวิชา</th>
                    <th style="text-align:center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($curriculums as $i => $c)
                <tr data-level="{{ $c->level_id ?? '' }}">
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $c->name }}</strong>
                        @if($c->description)
                            <div style="font-size:0.78rem;color:#999;margin-top:2px">{{ $c->description }}</div>
                        @endif
                    </td>
                    <td style="text-align:center">
                        <span class="badge-level">{{ $c->level->name ?? 'ทุกระดับ' }}</span>
                    </td>
                    <td style="text-align:center">
                        <span class="badge-subject">{{ $c->curriculumSubjects->count() }} วิชา</span>
                    </td>
                    <td style="text-align:center">
                        <div class="btn-row" style="justify-content:center">
                            <a href="{{ route('curriculums.edit', $c->curriculum_id) }}" class="btn-edit">
                                <i class="bi bi-pencil"></i> แก้ไข
                            </a>
                            <form action="{{ route('curriculums.copy', $c->curriculum_id) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="btn-copy">
                                    <i class="bi bi-files"></i> คัดลอกแผน
                                </button>
                            </form>
                            <form action="{{ route('curriculums.destroy', $c->curriculum_id) }}" method="POST"
                                  style="display:inline"
                                  onsubmit="return confirm('ยืนยันลบหลักสูตร {{ addslashes($c->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-delete">
                                    <i class="bi bi-trash"></i> ลบ
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="cby-empty">
                        <i class="bi bi-journal-x" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                        ไม่มีหลักสูตรในปีนี้
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
<script>
function filterLevel(btn, levelId) {
    // active pill
    document.querySelectorAll('.level-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');

    // filter rows
    document.querySelectorAll('.cby-table tbody tr[data-level]').forEach(row => {
        row.style.display = (!levelId || row.dataset.level == levelId) ? '' : 'none';
    });

    // re-number visible rows
    let n = 1;
    document.querySelectorAll('.cby-table tbody tr[data-level]').forEach(row => {
        if (row.style.display !== 'none') {
            row.querySelector('td:first-child').textContent = n++;
        }
    });
}
</script>
@endsection
