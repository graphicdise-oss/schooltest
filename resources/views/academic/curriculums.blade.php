@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><span>จัดการหลักสูตร/ปีการศึกษา</span></nav>

    {{-- ข้อความแจ้งเตือน --}}
    @if(session('success'))
    <div style="background:#d1fae5; border:1px solid #6ee7b7; border-radius:8px; padding:10px 16px; margin-bottom:16px; color:#065f46; font-size:0.85rem; display:flex; align-items:center; gap:8px;">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#fee2e2; border:1px solid #f87171; border-radius:8px; padding:10px 16px; margin-bottom:16px; color:#991b1b; font-size:0.85rem; display:flex; align-items:center; gap:8px;">
        <i class="bi bi-x-circle-fill"></i> {{ session('error') }}
    </div>
    @endif

 {{-- ดึงข้อมูลปีและเทอมปัจจุบันมาแสดงเป็นค่าเริ่มต้น --}}
    @php
        if ($currentYearId === 'all') {
            $currentYearText = '-- ดูหลักสูตรทั้งหมด --';
        } else {
            $selectedYearModel = $academicYears->where('year_id', $currentYearId)->first();
            $currentYearText = $selectedYearModel ? $selectedYearModel->year_name : '';
        }
        
        $currentSemText = '';
        foreach($academicYears as $y) {
            $currSem = $y->semesters->where('is_current', true)->first();
            if($currSem) { 
                $currentSemText = $currSem->semester_name . ' (' . $y->year_name . ')'; 
                break; 
            }
        }
    @endphp

    {{-- 1. Card ตั้งค่าปีการศึกษา (แบบพิมพ์ค้นหาได้) --}}
    <div class="ac-card" style="margin-bottom: 24px;">
        <div class="ac-card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #334155;">
            <span style="font-weight: 600;"><i class="bi bi-sliders me-1"></i> ตั้งค่าปีการศึกษา และ ภาคเรียน</span>
        </div>
        <div class="ac-card-body" style="padding: 20px;">
            <div style="display: flex; flex-wrap: wrap; gap: 30px;">
                
                {{-- ค้นหา/เลือกปีการศึกษา --}}
                <div style="flex: 1; min-width: 300px; padding-right: 20px; border-right: 1px dashed #cbd5e1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label style="font-size: 0.85rem; font-weight: 700; color: #475569;">1. เลือกปีการศึกษา (พิมพ์ค้นหาได้)</label>
                        <button type="button" class="ac-btn ac-btn-sm" style="background:#f1f5f9; color:#475569; padding:4px 10px; font-size:0.75rem; border:1px solid #cbd5e1;" onclick="document.getElementById('addYearOverlay').classList.add('active')">
                            <i class="bi bi-plus-circle"></i> เพิ่มปีใหม่
                        </button>
                    </div>
                    
                    {{-- Input แบบพิมพ์ได้ --}}
                    <input type="text" id="yearInput" list="yearList" class="ac-input" placeholder="-- พิมพ์ตัวเลขปีการศึกษาเพื่อค้นหา --" value="{{ $currentYearText }}" oninput="handleYearChange(this)" onfocus="this.value=''" autocomplete="off" style="background:#fff; font-size:0.9rem; width:100%; border: 1px solid #cbd5e1; padding: 8px 12px; border-radius: 6px;">
                    <datalist id="yearList">
                        @foreach($academicYears as $year)
                            <option data-id="{{ $year->year_id }}" data-current="{{ $year->is_current ? '1' : '0' }}" value="{{ $year->year_name }}">
                                {{ $year->is_current ? '⭐ ปีปัจจุบัน' : '' }}
                            </option>
                        @endforeach
                    </datalist>

                    <div id="yearActionButtons" style="margin-top: 10px; display: none; gap: 8px;">
                        <form id="formSetYear" method="POST" style="display:inline;">
                            @csrf @method('PUT')
                            <button type="submit" class="ac-btn ac-btn-sm" style="background:#10b981; color:white; font-size:0.75rem;"><i class="bi bi-check-circle"></i> ตั้งเป็นปีปัจจุบัน</button>
                        </form>
                        <form id="formDelYear" method="POST" style="display:inline;" onsubmit="return confirm('ยืนยันการลบปีการศึกษานี้?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="ac-btn ac-btn-sm" style="background:#ef4444; color:white; font-size:0.75rem;"><i class="bi bi-trash"></i> ลบปีนี้</button>
                        </form>
                    </div>
                </div>

                
              {{-- เลือกภาคเรียน (แสดงแบบ Dropdown เฉพาะของปีที่เลือก) --}}
                <div style="flex: 1; min-width: 300px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label style="font-size: 0.85rem; font-weight: 700; color: #475569;">2. เลือกภาคเรียน</label>
                        <button type="button" class="ac-btn ac-btn-sm" style="background:#f1f5f9; color:#475569; padding:4px 10px; font-size:0.75rem; border:1px solid #cbd5e1;" onclick="document.getElementById('addSemOverlay').classList.add('active')">
                            <i class="bi bi-plus-circle"></i> เพิ่มเทอมใหม่
                        </button>
                    </div>
                    
                    @if($currentYearId === 'all' || !isset($selectedYearModel))
                        <div style="padding: 8px 12px; background:#f8fafc; color:#64748b; font-size:0.85rem; border-radius:6px; border:1px solid #e2e8f0;">
                            <i class="bi bi-info-circle"></i> กรุณาเลือกปีการศึกษาทางซ้ายก่อน เพื่อจัดการภาคเรียน
                        </div>
                    @else
                        <select id="semSelect" class="ac-select" onchange="handleSemChange(this)" style="background:#fff; font-size:0.9rem; width:100%;">
                            <option value="">-- เลือกเทอม (เฉพาะปี {{ $selectedYearModel->year_name }}) --</option>
                            @foreach($selectedYearModel->semesters->sortBy('semester_name') as $sem)
                                <option value="{{ $sem->semester_id }}" data-current="{{ $sem->is_current ? '1' : '0' }}">
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

    {{-- 2. Card หลักสูตรทั้งหมด --}}
    <div class="ac-card">
        <div class="ac-card-header">
            <span><i class="bi bi-journal-text"></i> หลักสูตร / แผนการเรียนทั้งหมด</span>
            <a href="{{ route('curriculums.create') }}" class="ac-btn ac-btn-success"><i class="bi bi-plus-lg"></i> สร้างหลักสูตร</a>
        </div>
        <div class="ac-card-body">
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>#</th><th>ชื่อหลักสูตร</th><th>ระดับชั้น</th><th>ปีที่ใช้</th><th>จำนวนวิชา</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($curriculums as $i => $c)
                        <tr>
                            <td>{{ $curriculums->firstItem() + $i }}</td>
                            <td style="text-align:left">{{ $c->name }}</td>
                            <td>{{ $c->level->name ?? 'ทุกระดับ' }}</td>
                            <td>{{ $c->year_applied ?? '-' }}</td>
                            <td><span class="ac-badge ac-badge-info">{{ $c->curriculumSubjects->count() ?? 0 }} วิชา</span></td>
                            <td><span class="ac-badge {{ $c->is_active ? 'ac-badge-active' : 'ac-badge-inactive' }}">{{ $c->is_active ? 'ใช้งาน' : 'ปิด' }}</span></td>
                            <td>
                                <a href="{{ route('curriculums.edit', $c->curriculum_id) }}" class="ac-action-btn ac-action-edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('curriculums.destroy', $c->curriculum_id) }}" method="POST" style="display:inline" onsubmit="return confirm('ลบหลักสูตร?')">@csrf @method('DELETE')<button class="ac-action-btn ac-action-delete"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                        @empty<tr><td colspan="7" class="ac-empty">ไม่มีข้อมูล</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            <div class="ac-pagination">{{ $curriculums->links() }}</div>
        </div>
    </div>
</div>

{{-- =============================================== --}}
{{-- MODALS สำหรับเพิ่มข้อมูลปีและเทอม --}}
{{-- =============================================== --}}

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

{{-- JavaScript สำหรับคุมช่องพิมพ์ค้นหา --}}
{{-- JavaScript สำหรับคุมช่องพิมพ์ค้นหาและ Dropdown --}}
<script>
    function handleYearChange(inputObj) {
        const actionDiv = document.getElementById('yearActionButtons');
        const options = document.getElementById('yearList').options;
        let selectedId = null;
        let isCurrent = false;

        for (let i = 0; i < options.length; i++) {
            if (options[i].value === inputObj.value) {
                selectedId = options[i].getAttribute('data-id');
                isCurrent = options[i].getAttribute('data-current') === '1';
                break;
            }
        }

        if (!selectedId) { actionDiv.style.display = 'none'; return; }
        
        // ถ้ารหัสปีที่เลือก ไม่ตรงกับที่กำลังแสดงอยู่ ให้โหลดเว็บใหม่
        if ('{{ $currentYearId }}' !== selectedId) {
            window.location.href = '?year_id=' + selectedId;
            return; 
        }

        if (selectedId === 'all') {
            actionDiv.style.display = 'none';
        } else {
            document.getElementById('formSetYear').action = `{{ url('academic-years') }}/${selectedId}/current`;
            document.getElementById('formDelYear').action = `{{ url('academic-years') }}/${selectedId}`;
            actionDiv.style.display = 'flex';
            document.getElementById('formSetYear').style.display = isCurrent ? 'none' : 'inline-block';
        }
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
        const yInput = document.getElementById('yearInput');
        if(yInput && yInput.value) handleYearChange(yInput);
        
        const sSelect = document.getElementById('semSelect');
        if(sSelect && sSelect.value) handleSemChange(sSelect);
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') document.querySelectorAll('.ac-overlay.active').forEach(el => el.classList.remove('active'));
    });
</script>
@endsection