@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">
<style>
.pg-page { padding: 24px 28px; }

.pg-card {
    background: #fff; border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 20px 20px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.pg-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: #fff; background: #43a047;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.pg-card-header {
    margin-left: 90px; display: flex; align-items: center;
    justify-content: space-between; margin-top: -8px; margin-bottom: 20px;
    flex-wrap: wrap; gap: 10px;
}
.pg-card-title { font-size: 1.05rem; color: #555; }

.pg-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 10px; }
.pg-table thead th {
    padding: 12px 14px; background: #43a047; color: #fff;
    font-weight: 600; text-align: left; font-size: 0.85rem;
}
.pg-table thead th:first-child { border-radius: 6px 0 0 0; }
.pg-table thead th:last-child  { border-radius: 0 6px 0 0; text-align: center; }
.pg-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.pg-table tbody tr:hover { background: #f1f8f1; }
.pg-table tbody td { padding: 12px 14px; color: #555; vertical-align: middle; }

.btn-row { display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.btn-add {
    background: #43a047; color: #fff; border: none; border-radius: 6px;
    padding: 8px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-add:hover { background: #2e7d32; color: #fff; }

.btn-editprogram {
    background: #039be5; color: #fff; border: none; border-radius: 6px;
    padding: 6px 14px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    font-family: inherit;
    display: inline-flex; align-items: center; gap: 4px;
}
.btn-editprogram:hover { background: #0277bd; }

.btn-viewplans {
    background: #5c6bc0; color: #fff; border: none; border-radius: 6px;
    padding: 6px 14px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    font-family: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 4px;
}
.btn-viewplans:hover { background: #3949ab; color: #fff; }

.btn-delete {
    background: #e53935; color: #fff; border: none; border-radius: 6px;
    padding: 6px 14px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    font-family: inherit;
    display: inline-flex; align-items: center; gap: 4px;
}
.btn-delete:hover { background: #b71c1c; }

.badge-plan {
    display: inline-block; background: #e8eaf6; color: #3949ab;
    border-radius: 20px; padding: 2px 12px; font-size: 0.78rem; font-weight: 700;
}

.pg-empty { text-align: center; padding: 40px; color: #aaa; }

/* Modal */
.pg-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.45); z-index: 500;
    align-items: center; justify-content: center;
}
.pg-overlay.active { display: flex; }
.pg-modal {
    background: #fff; border-radius: 8px; width: 420px; max-width: 95vw;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
}
.pg-modal-header {
    padding: 18px 22px 14px; font-size: 1rem; font-weight: 700;
    border-bottom: 1px solid #eee; color: #333;
    display: flex; align-items: center; gap: 8px;
}
.pg-modal-body { padding: 18px 22px; display: flex; flex-direction: column; gap: 14px; }
.pg-modal-body label { font-size: 0.82rem; font-weight: 600; color: #555; margin-bottom: 2px; display: block; }
.pg-modal-body input {
    width: 100%; border: none; border-bottom: 1.5px solid #ccc;
    padding: 7px 4px; font-size: 0.88rem; font-family: inherit;
    outline: none; background: transparent;
}
.pg-modal-body input:focus { border-bottom-color: #43a047; }
.pg-modal-footer {
    padding: 14px 22px 18px; display: flex; justify-content: flex-end; gap: 10px;
    border-top: 1px solid #eee;
}
.btn-modal-cancel {
    background: #eee; color: #555; border: none; border-radius: 6px;
    padding: 8px 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit;
}
.btn-modal-ok {
    background: #43a047; color: #fff; border: none; border-radius: 6px;
    padding: 8px 22px; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-modal-ok:hover { background: #2e7d32; }
</style>
@endpush

@section('content')
<div class="pg-page">

    {{-- การ์ดตั้งค่าปีการศึกษา/ภาคเรียน — ตัวเลือกปีที่นี่ใช้กรองจำนวน "แผน" ในตารางด้านล่างด้วยในตัว --}}
    <div class="ac-card">
        <div class="ac-card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #334155;">
            <span style="font-weight: 600;"><i class="bi bi-sliders me-1"></i> ตั้งค่าปีการศึกษา และ ภาคเรียน</span>
        </div>
        <div class="ac-card-body" style="padding: 20px;">
            <div style="display: flex; flex-wrap: wrap; gap: 30px;">

                <div style="flex: 1; min-width: 300px; padding-right: 20px; border-right: 1px dashed #cbd5e1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label style="font-size: 0.85rem; font-weight: 700; color: #475569;">1. เลือกปีการศึกษา (ใช้กรองจำนวน "แผน" ในตารางด้านล่างด้วย)</label>
                        <button type="button" class="ac-btn ac-btn-sm" style="background:#f1f5f9; color:#475569; padding:4px 10px; font-size:0.75rem; border:1px solid #cbd5e1;" onclick="document.getElementById('addYearOverlay').classList.add('active')">
                            <i class="bi bi-plus-circle"></i> เพิ่มปีใหม่
                        </button>
                    </div>

                    <select id="yearSelect" class="ac-select" onchange="window.location.href = '{{ url()->current() }}?year_id=' + this.value" style="background:#fff; font-size:0.9rem; width:100%;">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->year_id }}" {{ (string) $year->year_id === (string) $currentYearId ? 'selected' : '' }}>
                                {{ $year->year_name }}{{ $year->is_current ? ' ⭐ (ปีปัจจุบัน)' : '' }}
                            </option>
                        @endforeach
                    </select>

                    @if($selectedYear)
                        <div style="margin-top: 10px; display: flex; gap: 8px;">
                            @unless($selectedYear->is_current)
                                <form method="POST" action="{{ url('academic-years') }}/{{ $selectedYear->year_id }}/current" style="display:inline;">
                                    @csrf @method('PUT')
                                    <button type="submit" class="ac-btn ac-btn-sm" style="background:#10b981; color:white; font-size:0.75rem;"><i class="bi bi-check-circle"></i> ตั้งเป็นปีปัจจุบัน</button>
                                </form>
                            @endunless
                            <form method="POST" action="{{ url('academic-years') }}/{{ $selectedYear->year_id }}" style="display:inline;" onsubmit="return confirm('ยืนยันการลบปีการศึกษานี้?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="ac-btn ac-btn-sm" style="background:#ef4444; color:white; font-size:0.75rem;"><i class="bi bi-trash"></i> ลบปีนี้</button>
                            </form>
                        </div>
                    @endif
                </div>

                <div style="flex: 1; min-width: 300px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label style="font-size: 0.85rem; font-weight: 700; color: #475569;">2. เลือกภาคเรียน</label>
                        <button type="button" class="ac-btn ac-btn-sm" style="background:#f1f5f9; color:#475569; padding:4px 10px; font-size:0.75rem; border:1px solid #cbd5e1;" onclick="document.getElementById('addSemOverlay').classList.add('active')">
                            <i class="bi bi-plus-circle"></i> เพิ่มเทอมใหม่
                        </button>
                    </div>

                    @if(!$selectedYear)
                        <div style="padding: 8px 12px; background:#f8fafc; color:#64748b; font-size:0.85rem; border-radius:6px; border:1px solid #e2e8f0;">
                            <i class="bi bi-info-circle"></i> กรุณาเลือกปีการศึกษาทางซ้ายก่อน เพื่อจัดการภาคเรียน
                        </div>
                    @elseif($selectedYear->semesters->isEmpty())
                        <div style="padding: 8px 12px; background:#f8fafc; color:#64748b; font-size:0.85rem; border-radius:6px; border:1px solid #e2e8f0;">
                            <i class="bi bi-info-circle"></i> ปีนี้ยังไม่มีภาคเรียน — กด "เพิ่มเทอมใหม่" ด้านบนเพื่อเพิ่ม
                        </div>
                    @else
                        {{-- เลือกภาคเรียนปัจจุบัน (ถ้ามี) เป็นค่าเริ่มต้นให้อัตโนมัติ ไม่ให้ค้างที่ช่องว่างทั้งที่มีเทอมปัจจุบันอยู่แล้ว --}}
                        <select id="semSelect" class="ac-select" onchange="handleSemChange(this)" style="background:#fff; font-size:0.9rem; width:100%;">
                            @foreach($selectedYear->semesters->sortBy('semester_name') as $sem)
                                <option value="{{ $sem->semester_id }}" data-current="{{ $sem->is_current ? '1' : '0' }}" {{ $sem->is_current ? 'selected' : '' }}>
                                    เทอม {{ $sem->semester_name }} {{ $sem->is_current ? '⭐ (ปัจจุบัน)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <div id="semActionButtons" style="margin-top: 10px; display: none; gap: 8px;">
                        <form id="formSetSem" method="POST" style="display:inline;">
                            @csrf @method('PUT')
                            <button type="submit" class="ac-btn ac-btn-sm" style="background:#10b981; color:white; font-size:0.75rem;"><i class="bi bi-check-circle"></i> ตั้งเป็นเทอมปัจจุบัน</button>
                        </form>
                        <form id="formDelSem" method="POST" style="display:inline;" onsubmit="return confirm('ยืนยันการลบเทอมนี้?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="ac-btn ac-btn-sm" style="background:#ef4444; color:white; font-size:0.75rem;"><i class="bi bi-trash"></i> ลบเทอม</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="pg-card">
        <div class="pg-icon"><i class="bi bi-journal-check"></i></div>
        <div class="pg-card-header">
            <span class="pg-card-title">จัดการหลักสูตร/แผน</span>
            <button class="btn-add" onclick="document.getElementById('addProgramOverlay').classList.add('active')">
                <i class="bi bi-plus-lg"></i> เพิ่มหลักสูตร
            </button>
        </div>

        @if($unassignedCount > 0)
        <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:12px 16px; margin-bottom:18px; color:#9a3412; font-size:0.85rem; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
            <span><i class="bi bi-info-circle-fill"></i> มีแผนการเรียน {{ $unassignedCount }} แผน ที่สร้างไว้ก่อนหน้านี้และยังไม่ได้ผูกกับหลักสูตรใด — ให้เข้าไปแก้ไขแผนนั้นๆ แล้วเลือกหลักสูตรที่ต้องการผูกเข้ากลุ่ม (ข้อมูลยังอยู่ครบ ไม่ได้หายไปไหน)</span>
        </div>
        @endif

        <table class="pg-table">
            <thead>
                <tr>
                    <th style="width:50px">ลำดับ</th>
                    <th>หลักสูตร</th>
                    <th style="text-align:center">แผน{{ $selectedYear ? ' (ปี ' . $selectedYear->year_name . ')' : '' }}</th>
                    <th style="text-align:center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $p->name }}</strong>
                        @if($p->description)
                            <div style="font-size:0.78rem;color:#999;margin-top:2px">{{ $p->description }}</div>
                        @endif
                    </td>
                    <td style="text-align:center">
                        <a href="{{ route('programs.plans', $p->program_id) }}" style="text-decoration:none">
                            <span class="badge-plan">{{ $p->curriculums_count }}</span>
                        </a>
                    </td>
                    <td style="text-align:center">
                        <div class="btn-row" style="justify-content:center">
                            <a href="{{ route('programs.plans', $p->program_id) }}" class="btn-viewplans">
                                <i class="bi bi-journal-check"></i> ดูแผน
                            </a>
                            <button type="button" class="btn-editprogram"
                                onclick="openEditProgram({{ $p->program_id }}, {{ Js::from($p->name) }}, {{ Js::from($p->description ?? '') }})">
                                <i class="bi bi-pencil"></i> แก้ไข
                            </button>
                            <form action="{{ route('programs.destroy', $p->program_id) }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('ยืนยันลบหลักสูตร {{ addslashes($p->name) }}?')">
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
                    <td colspan="4" class="pg-empty">
                        <i class="bi bi-journal-x" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                        ยังไม่มีหลักสูตร
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- Modal เพิ่มหลักสูตร --}}
<div class="pg-overlay" id="addProgramOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="pg-modal">
        <div class="pg-modal-header"><i class="bi bi-plus-circle"></i> เพิ่มหลักสูตร</div>
        <form method="POST" action="{{ route('programs.store') }}">
            @csrf
            <div class="pg-modal-body">
                <div>
                    <label>ชื่อหลักสูตร *</label>
                    <input type="text" name="name" required placeholder="เช่น EP, IEP, วิทย์-คณิต ม.ปลาย">
                </div>
                <div>
                    <label>คำอธิบาย</label>
                    <input type="text" name="description" placeholder="(ไม่บังคับ)">
                </div>
            </div>
            <div class="pg-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('addProgramOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-ok"><i class="bi bi-check-lg"></i> เพิ่ม</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal แก้ไขหลักสูตร --}}
<div class="pg-overlay" id="editProgramOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="pg-modal">
        <div class="pg-modal-header"><i class="bi bi-pencil"></i> แก้ไขหลักสูตร</div>
        <form method="POST" id="editProgramForm">
            @csrf @method('PUT')
            <div class="pg-modal-body">
                <div>
                    <label>ชื่อหลักสูตร *</label>
                    <input type="text" name="name" id="editProgramName" required>
                </div>
                <div>
                    <label>คำอธิบาย</label>
                    <input type="text" name="description" id="editProgramDescription" placeholder="(ไม่บังคับ)">
                </div>
            </div>
            <div class="pg-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('editProgramOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="btn-modal-ok"><i class="bi bi-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal เพิ่มปีการศึกษา --}}
<div class="ac-overlay" id="addYearOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="ac-modal" onclick="event.stopPropagation()">
        <div class="ac-modal-header" style="background: #f8fafc; color:#333;"><i class="bi bi-calendar-plus me-2"></i> สร้างปีการศึกษาใหม่</div>
        <form method="POST" action="{{ route('academic-years.storeYear') }}">
            @csrf
            <div class="ac-modal-body">
                <label>ปีการศึกษา (พ.ศ.) *</label>
                <input type="text" name="year_name" class="ac-input" required placeholder="เช่น 2568">

                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-top:14px; background:#f0fdf4; padding:10px; border-radius:8px; border:1px solid #dcfce7;">
                    <input type="checkbox" name="is_current" value="1" style="width:18px; height:18px; accent-color:#10b981; margin:0;" checked>
                    <span style="font-size:0.85rem; font-weight:600; color:#065f46;">ตั้งเป็นปีการศึกษาปัจจุบันทันที</span>
                </label>
            </div>
            <div class="ac-modal-footer">
                <button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('addYearOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="ac-btn" style="background:#10b981; color:#fff"><i class="bi bi-check-lg"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal เพิ่มภาคเรียน --}}
<div class="ac-overlay" id="addSemOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="ac-modal" onclick="event.stopPropagation()">
        <div class="ac-modal-header" style="background: #f8fafc; color:#333;"><i class="bi bi-node-plus me-2"></i> เพิ่มภาคเรียน</div>
        <form method="POST" action="{{ route('academic-years.storeSemester') }}">
            @csrf
            <div class="ac-modal-body">
                <label>ปีการศึกษา *</label>
                <select name="year_id" class="ac-select" required>
                    <option value="">-- เลือกปีการศึกษา --</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->year_id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->year_name }}</option>
                    @endforeach
                </select>

                <label style="margin-top:12px;">ภาคเรียน / เทอม *</label>
                <input type="text" name="semester_name" class="ac-input" required placeholder="เช่น 1, 2 หรือ ฤดูร้อน">

                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-top:14px; background:#f0fdf4; padding:10px; border-radius:8px; border:1px solid #dcfce7;">
                    <input type="checkbox" name="is_current" value="1" style="width:18px; height:18px; accent-color:#10b981; margin:0;" checked>
                    <span style="font-size:0.85rem; font-weight:600; color:#065f46;">ตั้งเป็นเทอมปัจจุบันทันที</span>
                </label>
            </div>
            <div class="ac-modal-footer">
                <button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('addSemOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="ac-btn" style="background:#10b981; color:#fff"><i class="bi bi-check-lg"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditProgram(id, name, description) {
    document.getElementById('editProgramForm').action = '{{ url("programs") }}/' + id;
    document.getElementById('editProgramName').value = name;
    document.getElementById('editProgramDescription').value = description || '';
    document.getElementById('editProgramOverlay').classList.add('active');
}

function handleSemChange(selectObj) {
    const actionDiv = document.getElementById('semActionButtons');
    if (!selectObj.value) { actionDiv.style.display = 'none'; return; }

    const selectedOption = selectObj.options[selectObj.selectedIndex];
    const isCurrent = selectedOption.getAttribute('data-current') === '1';
    const selectedId = selectObj.value;

    document.getElementById('formSetSem').action = `{{ url('academic-years/semester') }}/${selectedId}/current`;
    document.getElementById('formDelSem').action = `{{ url('academic-years/semester') }}/${selectedId}`;

    actionDiv.style.display = 'flex';
    document.getElementById('formSetSem').style.display = isCurrent ? 'none' : 'inline-block';
}

document.addEventListener('DOMContentLoaded', () => {
    const sSelect = document.getElementById('semSelect');
    if (sSelect && sSelect.value) handleSemChange(sSelect);
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.pg-overlay.active, .ac-overlay.active').forEach(el => el.classList.remove('active'));
});
</script>
@endsection
