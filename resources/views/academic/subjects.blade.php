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
                                <button class="ac-action-btn ac-action-edit" onclick="openEdit({{ $s->subject_id }},'{{ $s->code }}','{{ addslashes($s->name_th) }}','{{ addslashes($s->name_short ?? '') }}','{{ addslashes($s->code_en ?? '') }}','{{ addslashes($s->name_en ?? '') }}','{{ $s->subject_group }}','{{ $s->subject_type }}',{{ $s->credits }},{{ $s->hours_per_week ?? 0 }},{{ $s->hours_per_year ?? 0 }})"><i class="bi bi-pencil"></i></button>
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

@php
$subjectGroups = ['ภาษาไทย','คณิตศาสตร์','วิทยาศาสตร์และเทคโนโลยี','สังคมศึกษา ศาสนา และวัฒนธรรม','สุขศึกษาและพลศึกษา','ศิลปะ','การงานอาชีพ','ภาษาต่างประเทศ','การศึกษาค้นคว้าด้วยตนเอง (IS)'];
$subjectTypes = ['พื้นฐาน','เพิ่มเติม','กิจกรรม'];
$creditOptions = ['0','0.5','1','1.5','2','2.5','3','3.5','4'];
@endphp

{{-- Modal เพิ่ม --}}
<div class="ac-overlay" id="addOverlay" onclick="if(event.target===this)this.classList.remove('active')">
<div class="ac-modal"><div class="ac-modal-header"><i class="bi bi-plus-circle me-2"></i>เพิ่มรายวิชา</div>
<form method="POST" action="{{ route('subjects.store') }}">@csrf
<div class="ac-modal-body">
    <div class="ac-grid-2">
        <div class="ac-field"><label>รหัสวิชา *</label><input type="text" name="code" class="ac-input" required placeholder="เช่น ท11101"></div>
        <div class="ac-field"><label>รหัสวิชา (อังกฤษ)</label><input type="text" name="code_en" class="ac-input" placeholder="เช่น TH11101"></div>
    </div>
    <div class="ac-grid-2">
        <div class="ac-field"><label>ชื่อวิชา (ไทย) *</label><input type="text" name="name_th" class="ac-input" required></div>
        <div class="ac-field"><label>ชื่อเล่น / ชื่อย่อ</label><input type="text" name="name_short" class="ac-input" placeholder="เช่น ภาษาไทย"></div>
    </div>
    <div class="ac-field"><label>ชื่อวิชา (อังกฤษ)</label><input type="text" name="name_en" class="ac-input"></div>
    <div class="ac-grid-2">
        <div class="ac-field"><label>กลุ่มสาระการเรียนรู้</label>
            <select name="subject_group" class="ac-select">
                <option value="">-- เลือกกลุ่มสาระ --</option>
                @foreach($subjectGroups as $g)<option value="{{ $g }}">{{ $g }}</option>@endforeach
            </select>
        </div>
        <div class="ac-field"><label>ประเภทรายวิชา</label>
            <select name="subject_type" class="ac-select">
                <option value="">-- เลือกประเภท --</option>
                @foreach($subjectTypes as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
            </select>
        </div>
    </div>
    <div class="ac-grid-3">
        <div class="ac-field"><label>หน่วยกิต *</label>
            <select name="credits" class="ac-select" required>
                @foreach($creditOptions as $c)<option value="{{ $c }}" {{ $c=='1'?'selected':'' }}>{{ $c }}</option>@endforeach
            </select>
        </div>
        <div class="ac-field"><label>ชม./สัปดาห์</label><input type="number" name="hours_per_week" class="ac-input" step="0.5" min="0"></div>
        <div class="ac-field"><label>ชม./ปี</label><input type="number" name="hours_per_year" class="ac-input" step="0.5" min="0"></div>
    </div>
</div>
<div class="ac-modal-footer">
    <button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('addOverlay').classList.remove('active')">ยกเลิก</button>
    <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึก</button>
</div>
</form></div></div>

{{-- Modal แก้ไข --}}
<div class="ac-overlay" id="editOverlay" onclick="if(event.target===this)this.classList.remove('active')">
<div class="ac-modal"><div class="ac-modal-header"><i class="bi bi-pencil-square me-2"></i>แก้ไขรายวิชา</div>
<form method="POST" id="editForm">@csrf @method('PUT')
<div class="ac-modal-body">
    <div class="ac-grid-2">
        <div class="ac-field"><label>รหัสวิชา *</label><input type="text" name="code" id="eCode" class="ac-input" required></div>
        <div class="ac-field"><label>รหัสวิชา (อังกฤษ)</label><input type="text" name="code_en" id="eCodeEn" class="ac-input"></div>
    </div>
    <div class="ac-grid-2">
        <div class="ac-field"><label>ชื่อวิชา (ไทย) *</label><input type="text" name="name_th" id="eNameTh" class="ac-input" required></div>
        <div class="ac-field"><label>ชื่อเล่น / ชื่อย่อ</label><input type="text" name="name_short" id="eNameShort" class="ac-input"></div>
    </div>
    <div class="ac-field"><label>ชื่อวิชา (อังกฤษ)</label><input type="text" name="name_en" id="eNameEn" class="ac-input"></div>
    <div class="ac-grid-2">
        <div class="ac-field"><label>กลุ่มสาระการเรียนรู้</label>
            <select name="subject_group" id="eGroup" class="ac-select">
                <option value="">-- เลือกกลุ่มสาระ --</option>
                @foreach($subjectGroups as $g)<option value="{{ $g }}">{{ $g }}</option>@endforeach
            </select>
        </div>
        <div class="ac-field"><label>ประเภทรายวิชา</label>
            <select name="subject_type" id="eType" class="ac-select">
                <option value="">-- เลือกประเภท --</option>
                @foreach($subjectTypes as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
            </select>
        </div>
    </div>
    <div class="ac-grid-3">
        <div class="ac-field"><label>หน่วยกิต *</label>
            <select name="credits" id="eCredits" class="ac-select" required>
                @foreach($creditOptions as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
            </select>
        </div>
        <div class="ac-field"><label>ชม./สัปดาห์</label><input type="number" name="hours_per_week" id="eHours" class="ac-input" step="0.5" min="0"></div>
        <div class="ac-field"><label>ชม./ปี</label><input type="number" name="hours_per_year" id="eHoursYear" class="ac-input" step="0.5" min="0"></div>
    </div>
</div>
<div class="ac-modal-footer">
    <button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('editOverlay').classList.remove('active')">ยกเลิก</button>
    <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึก</button>
</div>
</form></div></div>

<script>
function openEdit(id,code,nth,nshort,cen,nen,grp,type,cr,hr,hry){
    document.getElementById('editForm').action='{{ url("subjects") }}/'+id;
    document.getElementById('eCode').value=code;
    document.getElementById('eCodeEn').value=cen;
    document.getElementById('eNameTh').value=nth;
    document.getElementById('eNameShort').value=nshort;
    document.getElementById('eNameEn').value=nen;
    document.getElementById('eGroup').value=grp;
    document.getElementById('eType').value=type;
    document.getElementById('eCredits').value=cr;
    document.getElementById('eHours').value=hr||'';
    document.getElementById('eHoursYear').value=hry||'';
    document.getElementById('editOverlay').classList.add('active');
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.ac-overlay.active').forEach(el=>el.classList.remove('active'))});
</script>
@endsection