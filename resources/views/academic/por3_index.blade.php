@extends('layouts.sidebar')
@push('styles')
<style>
.p3-page { padding: 24px 28px; }

.p3-card {
    background: #fff; border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 24px 24px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.p3-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.p3-icon-search { background: #00bcd4; }
.p3-icon-list   { background: #43a047; }
.p3-card-title  { margin-left: 90px; font-size: 1.05rem; color: #555; margin-top: -8px; }

.p3-search-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 14px 24px; margin-top: 20px; align-items: end;
}
.p3-search-row2 {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 14px 24px; margin-top: 14px; align-items: end;
}
.p3-field label { font-size: 0.82rem; font-weight: 600; color: #444; margin-bottom: 3px; display: block; }
.p3-field select, .p3-field input[type=text] {
    width: 100%; height: 36px; border: none; border-bottom: 1.5px solid #bbb;
    padding: 0 8px; font-size: 0.88rem; font-family: inherit; outline: none;
    background: transparent; box-sizing: border-box;
}
.p3-field select:focus, .p3-field input:focus { border-bottom-color: #00bcd4; }
.btn-search {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px;
    padding: 9px 28px; font-size: 0.88rem; font-weight: 600;
    cursor: pointer; font-family: inherit; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 6px;
    height: 36px;
}

/* Table */
.p3-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 8px; }
.p3-table thead th {
    padding: 11px 12px; border-bottom: 2px solid #eee;
    color: #333; font-weight: 600; text-align: left; font-size: 0.83rem;
}
.p3-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.p3-table tbody tr:hover { background: #f9fbff; }
.p3-table tbody td { padding: 10px 12px; color: #555; vertical-align: middle; }
.p3-table tbody td.center { text-align: center; }

/* Print dropdown button */
.btn-print-wrap { position: relative; display: inline-block; }
.btn-print-main {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px 0 0 6px;
    padding: 6px 14px; font-size: 0.8rem; font-weight: 600; cursor: pointer;
    font-family: inherit; display: inline-flex; align-items: center; gap: 5px;
}
.btn-print-caret {
    background: #0097a7; color: #fff; border: none; border-radius: 0 6px 6px 0;
    padding: 6px 10px; font-size: 0.8rem; cursor: pointer;
    border-left: 1px solid rgba(255,255,255,0.3);
}
.btn-print-dropdown {
    display: none; position: absolute; top: 100%; right: 0;
    background: #fff; border-radius: 6px; min-width: 140px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15); z-index: 100; margin-top: 4px;
}
.btn-print-dropdown.open { display: block; }
.btn-print-dropdown a {
    display: block; padding: 10px 16px; font-size: 0.88rem; color: #333;
    text-decoration: none; font-family: inherit;
}
.btn-print-dropdown a:hover { background: #f5f5f5; }

.btn-print-all {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px;
    padding: 7px 18px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    text-decoration: none;
}
.btn-print-all:hover { background: #0097a7; color: #fff; }

.p3-empty { text-align: center; padding: 40px; color: #aaa; }
</style>
@endpush

@section('content')
<div class="p3-page">

    @if(session('success'))
    <div style="background:#e8f5e9;color:#2e7d32;padding:10px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('settings_saved'))
    <div style="background:#e8f5e9;color:#2e7d32;padding:10px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem">
        <i class="bi bi-check-circle"></i> บันทึกการตั้งค่าผู้อนุมัติสำเร็จ
    </div>
    @endif
    @if(session('school_saved'))
    <div style="background:#e8f5e9;color:#2e7d32;padding:10px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem">
        <i class="bi bi-check-circle"></i> บันทึกข้อมูลโรงเรียนสำเร็จ
    </div>
    @endif

    {{-- ค้นหา --}}
    <div class="p3-card">
        <div class="p3-icon p3-icon-search"><i class="bi bi-search"></i></div>
        <div class="p3-card-title">ค้นหานักเรียน</div>
        <form method="GET" action="{{ route('por3.index') }}" id="searchForm">
            {{-- แถว 1: ปี | เทอม | ระดับ | ห้องเรียน --}}
            <div class="p3-search-grid">
                <div class="p3-field">
                    <label>ปีการศึกษา</label>
                    <select name="year_id" onchange="this.form.submit()">
                        @foreach($academicYears as $ay)
                        <option value="{{ $ay->year_id }}" {{ $yearId == $ay->year_id ? 'selected' : '' }}>
                            {{ $ay->year_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="p3-field">
                    <label>เทอม</label>
                    <select name="term" onchange="this.form.submit()">
                        <option value="1" {{ $term == '1' ? 'selected' : '' }}>เทอม 1</option>
                        <option value="2" {{ $term == '2' ? 'selected' : '' }}>เทอม 2</option>
                    </select>
                </div>
                <div class="p3-field">
                    <label>ระดับชั้น</label>
                    <select name="level_id" onchange="this.form.submit()">
                        <option value="">-- ทุกระดับ --</option>
                        @foreach($levels as $lv)
                        <option value="{{ $lv->level_id }}" {{ $levelId == $lv->level_id ? 'selected' : '' }}>
                            {{ $lv->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="p3-field">
                    <label>ห้องเรียน</label>
                    <select name="section_id">
                        <option value="all" {{ $sectionId == 'all' ? 'selected' : '' }}>-- ทุกห้องเรียน --</option>
                        @foreach($sections as $sec)
                        <option value="{{ $sec->section_id }}" {{ $sectionId == $sec->section_id ? 'selected' : '' }}>
                            {{ $sec->level->name ?? '' }}/{{ $sec->section_number }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- แถว 2: ค้นหาชื่อ + ปุ่ม --}}
            <div class="p3-search-row2">
                <div class="p3-field">
                    <label>ค้นหาชื่อ / รหัส</label>
                    <input type="text" name="search" placeholder="พิมพ์ชื่อหรือรหัสนักเรียน..." value="{{ $search }}">
                </div>
                <div class="p3-field" style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="submit" class="btn-search"><i class="bi bi-search"></i> ค้นหา</button>
                    <button type="button" class="btn-search" style="background:#1565c0;white-space:nowrap;" onclick="openSchoolModal()">
                        <i class="bi bi-building"></i> ตั้งค่าโรงเรียน
                    </button>
                    <button type="button" class="btn-search" style="background:#7b1fa2;white-space:nowrap;" onclick="openApproverModal()">
                        <i class="bi bi-pen"></i> ตั้งค่าผู้อนุมัติ
                        @if($savedApprover)
                        <span style="font-size:0.75rem;opacity:0.85;">({{ $savedApprover->thai_firstname }})</span>
                        @endif
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- รายการนักเรียน --}}
    <div class="p3-card">
        <div class="p3-icon p3-icon-list"><i class="bi bi-award"></i></div>
        <div class="p3-card-title" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-right:0">
            <span>แบบรายงานผู้สำเร็จการศึกษา (ปพ.3)
                @if($currentSection)
                — {{ $currentSection->level->name ?? '' }}/{{ $currentSection->section_number }}
                @endif
                ({{ $students->count() }} คน)
            </span>
            @if($sectionId && $sectionId !== 'all' && $students->count())
            <div class="btn-print-wrap" id="mainPrintWrap">
                <button class="btn-print-main" onclick="openPrintModal()">
                    <i class="bi bi-printer"></i> พิมพ์ ปพ.3 ทั้งหมด
                </button>
                <button class="btn-print-caret" onclick="toggleDropdown('mainPrintWrap')">
                    <i class="bi bi-caret-down-fill" style="font-size:0.7rem;"></i>
                </button>
                <div class="btn-print-dropdown" id="dropdownMain">
                    <a href="#" onclick="openPrintModal(); return false;">
                        <i class="bi bi-file-earmark-pdf" style="color:#e53935;"></i> พิมพ์ / PDF
                    </a>
                    <a href="{{ route('por3.exportExcel') }}?section_id={{ $sectionId }}">
                        <i class="bi bi-file-earmark-excel" style="color:#43a047;"></i> Excel
                    </a>
                </div>
            </div>
            @elseif(!$sectionId || $sectionId === 'all')
            <span style="font-size:0.82rem;color:#f57c00;">
                <i class="bi bi-exclamation-circle"></i> กรุณาเลือกชั้นเรียนก่อนพิมพ์
            </span>
            @endif
        </div>

        @if($students->count())
        <table class="p3-table">
            <thead>
                <tr>
                    <th style="width:60px">ลำดับ</th>
                    <th>รหัสนักเรียน</th>
                    <th>บัตรประชาชน</th>
                    <th>คำนำหน้า</th>
                    <th>ชื่อ - นามสกุล</th>
                    <th>ระดับชั้น/ห้อง</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $row)
                @php $stu = $row['student']; $sec = $row['section']; @endphp
                <tr>
                    <td>{{ $i + 1 }}.</td>
                    <td>{{ $stu->student_code }}</td>
                    <td>{{ $stu->id_card_number ?? '-' }}</td>
                    <td>{{ $stu->thai_prefix ?? '' }}</td>
                    <td>{{ $stu->thai_firstname }} {{ $stu->thai_lastname }}</td>
                    <td>{{ $sec->level->name ?? '' }}/{{ $sec->section_number ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="p3-empty">
            <i class="bi bi-file-earmark-x" style="font-size:2rem;display:block;margin-bottom:8px"></i>
            กรุณาเลือกห้องเรียนหรือค้นหาชื่อนักเรียน
        </div>
        @endif
    </div>

</div>

{{-- Modal ตั้งค่าโรงเรียน --}}
<div id="schoolModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.2);width:520px;max-width:94vw;padding:28px 28px 22px;">
        <h3 style="font-size:1rem;font-weight:700;color:#333;margin:0 0 20px;text-align:center;">
            <i class="bi bi-building"></i> ตั้งค่าข้อมูลโรงเรียน
        </h3>
        <form method="POST" action="{{ route('por3.saveSchoolSettings') }}">
            @csrf
            @php $sc = $savedSchool ?? []; @endphp
            <div style="margin-bottom:14px;">
                <label style="font-size:0.82rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">ชื่อโรงเรียน</label>
                <input type="text" name="school_name" value="{{ $sc['name'] ?? '' }}"
                    style="width:100%;height:38px;border:1.5px solid #ddd;border-radius:6px;padding:0 10px;font-size:0.88rem;font-family:inherit;outline:none;box-sizing:border-box;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <label style="font-size:0.82rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">ตำบล/แขวง</label>
                    <input type="text" name="tambon" value="{{ $sc['tambon'] ?? '' }}"
                        style="width:100%;height:38px;border:1.5px solid #ddd;border-radius:6px;padding:0 10px;font-size:0.88rem;font-family:inherit;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:0.82rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">อำเภอ/เขต</label>
                    <input type="text" name="amphoe" value="{{ $sc['amphoe'] ?? '' }}"
                        style="width:100%;height:38px;border:1.5px solid #ddd;border-radius:6px;padding:0 10px;font-size:0.88rem;font-family:inherit;outline:none;box-sizing:border-box;">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
                <div>
                    <label style="font-size:0.82rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">จังหวัด</label>
                    <input type="text" name="changwat" value="{{ $sc['changwat'] ?? '' }}"
                        style="width:100%;height:38px;border:1.5px solid #ddd;border-radius:6px;padding:0 10px;font-size:0.88rem;font-family:inherit;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:0.82rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">สำนักงานเขตพื้นที่การศึกษา</label>
                    <input type="text" name="education_area" value="{{ $sc['education_area'] ?? '' }}"
                        style="width:100%;height:38px;border:1.5px solid #ddd;border-radius:6px;padding:0 10px;font-size:0.88rem;font-family:inherit;outline:none;box-sizing:border-box;">
                </div>
            </div>
            <div style="display:flex;gap:10px;justify-content:center;">
                <button type="submit" style="background:#1565c0;color:#fff;border:none;border-radius:6px;padding:9px 28px;font-size:0.88rem;font-weight:600;cursor:pointer;">
                    <i class="bi bi-check-lg"></i> บันทึก
                </button>
                <button type="button" onclick="closeSchoolModal()" style="background:#f44336;color:#fff;border:none;border-radius:6px;padding:9px 28px;font-size:0.88rem;font-weight:600;cursor:pointer;">
                    ยกเลิก
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal ตั้งค่าผู้อนุมัติ (บันทึกลง session) --}}
<div id="approverModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.2);width:480px;max-width:94vw;padding:28px 28px 22px;">
        <h3 style="font-size:1rem;font-weight:700;color:#333;margin:0 0 20px;text-align:center;">
            <i class="bi bi-pen"></i> ตั้งค่าผู้อนุมัติการจบหลักสูตร
        </h3>
        <form method="POST" action="{{ route('por3.savePrintSettings') }}">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="font-size:0.82rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">
                    ผู้อนุมัติการจบหลักสูตร (ผู้อำนวยการ/อาจารย์ใหญ่)
                </label>
                <select name="approver_id"
                    style="width:100%;height:38px;border:1.5px solid #ddd;border-radius:6px;padding:0 10px;font-size:0.88rem;font-family:inherit;outline:none;">
                    <option value="">-- ไม่ระบุ --</option>
                    @foreach($personnels as $p)
                    <option value="{{ $p->personnel_id }}" {{ $savedApproverId == $p->personnel_id ? 'selected' : '' }}>
                        {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                        @if($p->position) ({{ $p->position }}) @endif
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:20px;">
                <label style="font-size:0.82rem;font-weight:600;color:#555;display:block;margin-bottom:5px;">
                    วันที่อนุมัติ
                </label>
                <input type="date" name="approve_date" value="{{ $savedApproveDate }}"
                    style="width:100%;height:38px;border:1.5px solid #ddd;border-radius:6px;padding:0 10px;font-size:0.88rem;font-family:inherit;outline:none;box-sizing:border-box;">
            </div>
            <div style="display:flex;gap:10px;justify-content:center;">
                <button type="submit" style="background:#7b1fa2;color:#fff;border:none;border-radius:6px;padding:9px 28px;font-size:0.88rem;font-weight:600;cursor:pointer;">
                    <i class="bi bi-check-lg"></i> บันทึก
                </button>
                <button type="button" onclick="closeApproverModal()" style="background:#f44336;color:#fff;border:none;border-radius:6px;padding:9px 28px;font-size:0.88rem;font-weight:600;cursor:pointer;">
                    ยกเลิก
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openSchoolModal() {
    document.getElementById('schoolModal').style.display = 'flex';
}
function closeSchoolModal() {
    document.getElementById('schoolModal').style.display = 'none';
}
document.getElementById('schoolModal').addEventListener('click', function(e) {
    if (e.target === this) closeSchoolModal();
});
function openApproverModal() {
    document.getElementById('approverModal').style.display = 'flex';
}
function closeApproverModal() {
    document.getElementById('approverModal').style.display = 'none';
}
document.getElementById('approverModal').addEventListener('click', function(e) {
    if (e.target === this) closeApproverModal();
});
function openPrintModal() {
    window.location.href = '{{ route('por3.print') }}?section_id={{ $sectionId }}';
}
function toggleDropdown(wrapperId) {
    const wrap = document.getElementById(wrapperId);
    const dropdown = wrap.querySelector('.btn-print-dropdown');
    const isOpen = dropdown.classList.contains('open');
    document.querySelectorAll('.btn-print-dropdown.open').forEach(d => d.classList.remove('open'));
    if (!isOpen) dropdown.classList.add('open');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.btn-print-wrap') && !e.target.closest('#printModal')) {
        document.querySelectorAll('.btn-print-dropdown.open').forEach(d => d.classList.remove('open'));
    }
});
</script>
@endsection
