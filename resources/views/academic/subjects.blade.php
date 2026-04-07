@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><span>จัดการรายวิชา</span></nav>

    <div class="ac-card">
        <div class="ac-card-header">
            <span><i class="bi bi-book"></i> รายวิชาทั้งหมด</span>
            <button class="ac-btn ac-btn-success" onclick="document.getElementById('addOverlay').classList.add('active')"><i class="bi bi-plus-lg"></i> เพิ่มรายวิชา</button>
        </div>
        <div class="ac-card-body">
            <form method="GET" action="{{ route('subjects.index') }}" class="ac-grid-3" style="margin-bottom:16px">
                <div class="ac-field"><label>กลุ่มสาระ</label><select name="group" class="ac-select" onchange="this.form.submit()"><option value="">ทั้งหมด</option>@foreach($groups as $g)<option value="{{ $g }}" {{ request('group')==$g?'selected':'' }}>{{ $g }}</option>@endforeach</select></div>
                <div class="ac-field"><label>ค้นหา</label><input type="text" name="search" class="ac-input" placeholder="รหัสวิชา / ชื่อวิชา" value="{{ request('search') }}"></div>
                <div class="ac-field" style="justify-content:flex-end"><button type="submit" class="ac-btn ac-btn-primary" style="margin-top:20px"><i class="bi bi-search"></i> ค้นหา</button></div>
            </form>

            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>#</th><th>รหัสวิชา</th><th>ชื่อวิชา (ไทย)</th><th>ชื่อวิชา (อังกฤษ)</th><th>กลุ่มสาระ</th><th>หน่วยกิต</th><th>ชม./สัปดาห์</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($subjects as $i => $s)
                        <tr>
                            <td>{{ $subjects->firstItem() + $i }}</td><td>{{ $s->code }}</td><td>{{ $s->name_th }}</td><td>{{ $s->name_en ?? '-' }}</td>
                            <td>{{ $s->subject_group ?? '-' }}</td><td>{{ $s->credits }}</td><td>{{ $s->hours_per_week ?? '-' }}</td>
                            <td><span class="ac-badge {{ $s->is_active ? 'ac-badge-active' : 'ac-badge-inactive' }}">{{ $s->is_active ? 'ใช้งาน' : 'ปิด' }}</span></td>
                            <td>
                                <form action="{{ route('subjects.toggle', $s->subject_id) }}" method="POST" style="display:inline">@csrf @method('PUT')<button class="ac-action-btn {{ $s->is_active ? 'ac-action-delete' : 'ac-action-view' }}" title="{{ $s->is_active ? 'ปิด' : 'เปิด' }}"><i class="bi {{ $s->is_active ? 'bi-x' : 'bi-check' }}"></i></button></form>
                                <button class="ac-action-btn ac-action-edit" onclick="openEdit({{ $s->subject_id }},'{{ $s->code }}','{{ addslashes($s->name_th) }}','{{ addslashes($s->name_en ?? '') }}','{{ $s->subject_group }}',{{ $s->credits }},{{ $s->hours_per_week ?? 0 }})"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('subjects.destroy', $s->subject_id) }}" method="POST" style="display:inline" onsubmit="return confirm('ลบ?')">@csrf @method('DELETE')<button class="ac-action-btn ac-action-delete"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                        @empty<tr><td colspan="9" class="ac-empty">ไม่มีข้อมูล</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            <div class="ac-pagination">{{ $subjects->withQueryString()->links() }}</div>
        </div>
    </div>
</div>

{{-- Modal เพิ่ม --}}
<div class="ac-overlay" id="addOverlay" onclick="if(event.target===this)this.classList.remove('active')">
<div class="ac-modal"><div class="ac-modal-header"><i class="bi bi-plus-circle me-2"></i>เพิ่มรายวิชา</div>
<form method="POST" action="{{ route('subjects.store') }}">@csrf
<div class="ac-modal-body">
    <label>รหัสวิชา *</label><input type="text" name="code" required placeholder="เช่น ท11101">
    <label>ชื่อวิชา (ไทย) *</label><input type="text" name="name_th" required>
    <label>ชื่อวิชา (อังกฤษ)</label><input type="text" name="name_en">
    <label>กลุ่มสาระ</label><input type="text" name="subject_group" placeholder="เช่น ภาษาไทย, คณิตศาสตร์">
    <label>หน่วยกิต *</label><input type="number" name="credits" step="0.5" required value="1">
    <label>ชม./สัปดาห์</label><input type="number" name="hours_per_week" step="0.5">
</div>
<div class="ac-modal-footer"><button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('addOverlay').classList.remove('active')">ยกเลิก</button><button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึก</button></div>
</form></div></div>

{{-- Modal แก้ไข --}}
<div class="ac-overlay" id="editOverlay" onclick="if(event.target===this)this.classList.remove('active')">
<div class="ac-modal"><div class="ac-modal-header"><i class="bi bi-pencil-square me-2"></i>แก้ไขรายวิชา</div>
<form method="POST" id="editForm">@csrf @method('PUT')
<div class="ac-modal-body">
    <label>รหัสวิชา *</label><input type="text" name="code" id="eCode" required>
    <label>ชื่อวิชา (ไทย) *</label><input type="text" name="name_th" id="eNameTh" required>
    <label>ชื่อวิชา (อังกฤษ)</label><input type="text" name="name_en" id="eNameEn">
    <label>กลุ่มสาระ</label><input type="text" name="subject_group" id="eGroup">
    <label>หน่วยกิต *</label><input type="number" name="credits" id="eCredits" step="0.5" required>
    <label>ชม./สัปดาห์</label><input type="number" name="hours_per_week" id="eHours" step="0.5">
</div>
<div class="ac-modal-footer"><button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('editOverlay').classList.remove('active')">ยกเลิก</button><button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึก</button></div>
</form></div></div>

<script>
function openEdit(id,code,nth,nen,grp,cr,hr){
    document.getElementById('editForm').action='{{ url("subjects") }}/'+id;
    document.getElementById('eCode').value=code;
    document.getElementById('eNameTh').value=nth;
    document.getElementById('eNameEn').value=nen;
    document.getElementById('eGroup').value=grp;
    document.getElementById('eCredits').value=cr;
    document.getElementById('eHours').value=hr;
    document.getElementById('editOverlay').classList.add('active');
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.ac-overlay.active').forEach(el=>el.classList.remove('active'))});
</script>
@endsection