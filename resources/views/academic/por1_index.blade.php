@extends('layouts.sidebar')
@push('styles')
<style>
.p1-page { padding: 24px 28px; }

.p1-card {
    background: #fff; border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 24px 24px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.p1-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.p1-icon-search { background: #00bcd4; }
.p1-icon-list   { background: #5c6bc0; }
.p1-card-title  { margin-left: 90px; font-size: 1.05rem; color: #555; margin-top: -8px; }

.p1-search-grid {
    display: grid; grid-template-columns: 1fr 1fr 1fr auto;
    gap: 14px 24px; margin-top: 20px; align-items: end;
}
.p1-field label { font-size: 0.82rem; font-weight: 600; color: #444; margin-bottom: 3px; display: block; }
.p1-field select, .p1-field input[type=text] {
    width: 100%; height: 36px; border: none; border-bottom: 1.5px solid #bbb;
    padding: 0 8px; font-size: 0.88rem; font-family: inherit; outline: none;
    background: transparent; box-sizing: border-box;
}
.p1-field select:focus, .p1-field input:focus { border-bottom-color: #00bcd4; }
.btn-search {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px;
    padding: 9px 28px; font-size: 0.88rem; font-weight: 600;
    cursor: pointer; font-family: inherit; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 6px;
}

/* Table */
.p1-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 8px; }
.p1-table thead th {
    padding: 11px 12px; border-bottom: 2px solid #eee;
    color: #333; font-weight: 600; text-align: left; font-size: 0.83rem;
}
.p1-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.p1-table tbody tr:hover { background: #f9fbff; }
.p1-table tbody td { padding: 10px 12px; color: #555; vertical-align: middle; }

.btn-set-doc {
    background: #ff9800; color: #fff; border: none; border-radius: 5px;
    padding: 6px 14px; font-size: 0.8rem; font-weight: 600; cursor: pointer;
    font-family: inherit; display: inline-flex; align-items: center; gap: 5px;
}
.btn-set-doc:hover { background: #e68900; }
.btn-print-por1 {
    background: #00bcd4; color: #fff; border: none; border-radius: 5px;
    padding: 6px 14px; font-size: 0.8rem; font-weight: 600; cursor: pointer;
    font-family: inherit; display: inline-flex; align-items: center; gap: 5px;
    text-decoration: none;
}
.btn-print-por1:hover { background: #0097a7; color: #fff; }
.btn-bulk {
    background: #ff9800; color: #fff; border: none; border-radius: 6px;
    padding: 7px 18px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
}
.btn-bulk:hover { background: #e68900; }
.btn-print-all {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px;
    padding: 7px 18px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    text-decoration: none;
}
.btn-print-all:hover { background: #0097a7; color: #fff; }
.badge-doc {
    background: #e8f5e9; color: #2e7d32; border-radius: 20px;
    padding: 2px 10px; font-size: 0.75rem; font-weight: 700;
}
.badge-nodoc {
    background: #ffeee0; color: #bf360c; border-radius: 20px;
    padding: 2px 10px; font-size: 0.75rem; font-weight: 700;
}

/* Modal */
.p1-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.45); z-index: 9999;
    align-items: center; justify-content: center;
}
.p1-modal-overlay.open { display: flex; }
.p1-modal {
    background: #fff; border-radius: 10px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    width: 400px; max-width: 94vw; padding: 28px 28px 22px;
}
.p1-modal h3 { font-size: 1rem; font-weight: 700; color: #333; margin: 0 0 20px; text-align: center; }
.p1-modal-field { margin-bottom: 16px; }
.p1-modal-field label { font-size: 0.82rem; font-weight: 600; color: #555; display: block; margin-bottom: 5px; }
.p1-modal-field input, .p1-modal-field select {
    width: 100%; border: 1.5px solid #ddd; border-radius: 6px;
    padding: 8px 12px; font-size: 0.9rem; font-family: inherit; outline: none; box-sizing: border-box;
}
.p1-modal-field input:focus, .p1-modal-field select:focus { border-color: #00bcd4; }
.p1-modal-actions { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
.btn-save { background: #4caf50; color: #fff; border: none; border-radius: 6px; padding: 9px 28px; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
.btn-cancel { background: #f44336; color: #fff; border: none; border-radius: 6px; padding: 9px 28px; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
.p1-empty { text-align: center; padding: 40px; color: #aaa; }
</style>
@endpush

@section('content')
<div class="p1-page">

    @if(session('success'))
    <div style="background:#e8f5e9;color:#2e7d32;padding:10px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- ค้นหา --}}
    <div class="p1-card">
        <div class="p1-icon p1-icon-search"><i class="bi bi-search"></i></div>
        <div class="p1-card-title">ค้นหานักเรียน</div>
        <form method="GET" action="{{ route('por1.index') }}">
            <div class="p1-search-grid">
                <div class="p1-field">
                    <label>ปีการศึกษา / เทอม</label>
                    <select name="semester_id" onchange="this.form.submit()">
                        @foreach($semesters as $sem)
                        <option value="{{ $sem->semester_id }}" {{ $semesterId==$sem->semester_id?'selected':'' }}>
                            {{ $sem->academicYear->year_name ?? '' }} เทอม {{ $sem->semester_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="p1-field">
                    <label>ชั้นเรียน</label>
                    <select name="section_id">
                        <option value="">-- เลือกห้อง --</option>
                        @foreach($sections as $sec)
                        <option value="{{ $sec->section_id }}" {{ $sectionId==$sec->section_id?'selected':'' }}>
                            {{ $sec->level->name ?? '' }}/{{ $sec->section_number }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="p1-field">
                    <label>ค้นหาชื่อ / รหัส</label>
                    <input type="text" name="search" placeholder="พิมพ์ชื่อหรือรหัสนักเรียน..." value="{{ $search }}">
                </div>
                <div class="p1-field">
                    <button type="submit" class="btn-search"><i class="bi bi-search"></i> ค้นหา</button>
                </div>
            </div>
        </form>
    </div>

    {{-- รายการนักเรียน --}}
    <div class="p1-card">
        <div class="p1-icon p1-icon-list"><i class="bi bi-file-earmark-text"></i></div>
        <div class="p1-card-title" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-right:0">
            <span>ระเบียนแสดงผลการเรียน (ปพ.1)
                @if($currentSection)
                — {{ $currentSection->level->name ?? '' }}/{{ $currentSection->section_number }}
                @endif
                ({{ $students->count() }} คน)
            </span>
            @if($students->count() && $sectionId)
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button class="btn-bulk" onclick="openBulkModal()">
                    <i class="bi bi-layers"></i> นำชั้นเลขที่เอกสาร
                </button>
                <a href="#" class="btn-print-all">
                    <i class="bi bi-printer"></i> พิมพ์ใบ ปพ.1 ทั้งหมด
                </a>
            </div>
            @endif
        </div>

        @if($students->count())
        <table class="p1-table">
            <thead>
                <tr>
                    <th style="width:60px">ลำดับ</th>
                    <th>รหัสนักเรียน</th>
                    <th>คำนำหน้า</th>
                    <th>ชื่อ - นามสกุล</th>
                    <th style="text-align:center">เลขที่เอกสาร</th>
                    <th style="text-align:center;min-width:220px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $stu)
                @php $doc = $docNumbers[$stu->student_id] ?? null; @endphp
                <tr>
                    <td>{{ $i + 1 }}.</td>
                    <td>{{ $stu->student_code }}</td>
                    <td>{{ $stu->thai_prefix }}</td>
                    <td>{{ $stu->thai_firstname }} {{ $stu->thai_lastname }}</td>
                    <td style="text-align:center">
                        @if($doc && $doc->doc_set)
                            <span class="badge-doc">ชุด {{ $doc->doc_set }} / {{ $doc->doc_number }}</span>
                        @else
                            <span class="badge-nodoc">ยังไม่ตั้ง</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        <button class="btn-set-doc" onclick="openDocModal(
                            {{ $stu->student_id }},
                            {{ $semesterId }},
                            {{ $currentSection->level_id ?? 0 }},
                            '{{ $currentSection->level->name ?? '' }}',
                            '{{ $doc->doc_set ?? '' }}',
                            '{{ $doc->doc_number ?? '' }}'
                        )">
                            <i class="bi bi-pencil-square"></i> ตั้งเลขที่เอกสาร
                        </button>
                        <a href="#" class="btn-print-por1"><i class="bi bi-printer"></i> พิมพ์ใบ ปพ.1</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="p1-empty">
            <i class="bi bi-file-earmark-x" style="font-size:2rem;display:block;margin-bottom:8px"></i>
            กรุณาเลือกชั้นเรียนหรือค้นหาชื่อนักเรียน
        </div>
        @endif
    </div>

</div>

{{-- Modal: ตั้งเลขที่เอกสาร (รายคน) --}}
<div class="p1-modal-overlay" id="docModal">
    <div class="p1-modal">
        <h3><i class="bi bi-pencil-square"></i> ตั้งเลขที่เอกสาร</h3>
        <form method="POST" action="{{ route('por1.setDoc') }}">
            @csrf
            <input type="hidden" name="student_id" id="doc_student_id">
            <input type="hidden" name="semester_id" id="doc_semester_id">
            <input type="hidden" name="level_id" id="doc_level_id">

            <div class="p1-modal-field">
                <label>ระดับชั้นเรียน</label>
                <input type="text" id="doc_level_name" readonly style="background:#f5f5f5;color:#888">
            </div>
            <div class="p1-modal-field">
                <label>ชุดที่</label>
                <input type="text" name="doc_set" id="doc_set_input" placeholder="เช่น 00008" maxlength="20">
            </div>
            <div class="p1-modal-field">
                <label>เลขที่</label>
                <input type="text" name="doc_number" id="doc_number_input" placeholder="เช่น 000001" maxlength="20">
            </div>
            <div class="p1-modal-actions">
                <button type="submit" class="btn-save"><i class="bi bi-check-lg"></i> บันทึก</button>
                <button type="button" class="btn-cancel" onclick="closeDocModal()">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: นำชั้นเลขที่เอกสาร (ทั้งห้อง) --}}
<div class="p1-modal-overlay" id="bulkModal">
    <div class="p1-modal">
        <h3><i class="bi bi-layers"></i> ตั้งเลขชุดทั้งห้อง</h3>
        <form method="POST" action="{{ route('por1.bulkSet') }}">
            @csrf
            <input type="hidden" name="section_id" value="{{ $sectionId }}">
            <input type="hidden" name="semester_id" value="{{ $semesterId }}">

            <div class="p1-modal-field">
                <label>ชุดที่ (ใช้กับทุกคนในห้อง)</label>
                <input type="text" name="doc_set" placeholder="เช่น 00008" maxlength="20" required>
            </div>
            <p style="font-size:0.82rem;color:#888;margin:0 0 8px">
                <i class="bi bi-info-circle"></i> เลขที่จะถูกกำหนดอัตโนมัติตามลำดับเลขที่ในห้อง (00001, 00002, ...)
            </p>
            <div class="p1-modal-actions">
                <button type="submit" class="btn-save"><i class="bi bi-check-lg"></i> บันทึกทั้งหมด</button>
                <button type="button" class="btn-cancel" onclick="closeBulkModal()">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDocModal(studentId, semesterId, levelId, levelName, docSet, docNumber) {
    document.getElementById('doc_student_id').value  = studentId;
    document.getElementById('doc_semester_id').value = semesterId;
    document.getElementById('doc_level_id').value    = levelId;
    document.getElementById('doc_level_name').value  = levelName;
    document.getElementById('doc_set_input').value   = docSet;
    document.getElementById('doc_number_input').value = docNumber;
    document.getElementById('docModal').classList.add('open');
}
function closeDocModal() { document.getElementById('docModal').classList.remove('open'); }

function openBulkModal() { document.getElementById('bulkModal').classList.add('open'); }
function closeBulkModal() { document.getElementById('bulkModal').classList.remove('open'); }

document.querySelectorAll('.p1-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});
</script>
@endsection
