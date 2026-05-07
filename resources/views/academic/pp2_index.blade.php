@extends('layouts.sidebar')

@push('styles')
<style>
    body { background: #f4f6f9; }
    .page { padding: 24px 28px; }

    .floating-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 30px 20px 20px; position: relative;
        margin-top: 50px; margin-bottom: 28px;
    }
    .floating-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .card-header-text {
        margin-left: 90px; font-size: 1.1rem; color: #555;
        margin-top: -10px; font-weight: 600;
    }

    .form-label-sm { font-size: 0.82rem; color: #666; font-weight: 600; margin-bottom: 4px; }
    .form-select-line {
        border: none; border-bottom: 1.5px solid #ccc; border-radius: 0;
        padding: 6px 4px; font-size: 0.88rem; width: 100%;
        background: transparent; outline: none; font-family: inherit;
    }
    .form-select-line:focus { border-bottom-color: #e53935; }
    .form-input-line {
        border: none; border-bottom: 1.5px solid #ccc; border-radius: 0;
        padding: 6px 4px; font-size: 0.88rem; width: 100%;
        background: transparent; outline: none; font-family: inherit;
    }
    .form-input-line:focus { border-bottom-color: #e53935; }

    .btn-search {
        background: #e53935; color: #fff; border: none; border-radius: 4px;
        padding: 9px 28px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-search:hover { background: #c62828; }

    .pp2-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
    .pp2-table th {
        padding: 10px 14px; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;
        font-weight: 600; color: #555; text-align: center; background: #fafafa;
    }
    .pp2-table th.left { text-align: left; }
    .pp2-table td {
        padding: 10px 14px; border-bottom: 1px solid #f0f2f5;
        color: #444; text-align: center; vertical-align: middle;
    }
    .pp2-table td.left { text-align: left; }

    .badge-doc { background: #d1fae5; color: #065f46; border-radius: 4px; padding: 3px 8px; font-size: 0.78rem; }
    .badge-no-doc { background: #fee2e2; color: #991b1b; border-radius: 4px; padding: 3px 8px; font-size: 0.78rem; }

    .btn-set    { background: #f59e0b; color: #fff; border: none; border-radius: 4px; padding: 5px 12px; font-size: 0.8rem; cursor: pointer; }
    .btn-print  { background: #3b82f6; color: #fff; border: none; border-radius: 4px; padding: 5px 12px; font-size: 0.8rem; cursor: pointer; text-decoration: none; display:inline-flex; align-items:center; gap:4px; }
    .btn-set:hover   { background: #d97706; }
    .btn-print:hover { background: #2563eb; color:#fff; text-decoration:none; }

    .empty-box { text-align: center; color: #aaa; padding: 50px 0; }
    .empty-box i { font-size: 2.5rem; display: block; margin-bottom: 10px; }

    /* Modal */
    .modal-backdrop-custom { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1040; align-items: center; justify-content: center; }
    .modal-backdrop-custom.show { display: flex; }
    .modal-box { background: #fff; border-radius: 8px; width: 420px; max-width: 95vw; box-shadow: 0 8px 30px rgba(0,0,0,0.15); padding: 28px; }
    .modal-title { font-size: 1rem; font-weight: 700; color: #333; margin-bottom: 20px; }
    .modal-field { margin-bottom: 16px; }
    .modal-label { font-size: 0.8rem; color: #777; font-weight: 600; margin-bottom: 4px; display: block; }
    .modal-input { width: 100%; border: 1px solid #ddd; border-radius: 4px; padding: 8px 10px; font-size: 0.88rem; outline: none; font-family: inherit; }
    .modal-input:focus { border-color: #e53935; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    .btn-save   { background: #e53935; color: #fff; border: none; border-radius: 4px; padding: 8px 24px; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
    .btn-cancel { background: #f3f4f6; color: #555; border: none; border-radius: 4px; padding: 8px 18px; font-size: 0.88rem; cursor: pointer; }
</style>
@endpush

@section('content')
<div class="page">

    @if (session('success'))
        <div style="background:#d1fae5; color:#065f46; padding:10px 16px; border-radius:6px; margin-bottom:16px; font-size:0.88rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ค้นหา --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#e53935;"><i class="fas fa-graduation-cap"></i></div>
        <div class="card-header-text">ใบ ป.พ.2 (หลักฐานแสดงผลการเรียน)</div>

        <form method="GET" action="{{ route('pp2.index') }}" style="margin-top:24px;" id="filterForm">
            <div class="row g-3 mb-3">
                <div class="col-md-2">
                    <div class="form-label-sm">ปีการศึกษา</div>
                    <select name="year_id" class="form-select-line" onchange="this.form.submit()">
                        <option value="">เลือกปี</option>
                        @foreach ($academicYears as $yr)
                            <option value="{{ $yr->year_id }}" {{ $yearId == $yr->year_id ? 'selected' : '' }}>{{ $yr->year_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">เทอม</div>
                    <select name="semester_id" class="form-select-line" onchange="this.form.submit()">
                        <option value="">ทั้งหมด</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->semester_id }}" {{ $semesterId == $sem->semester_id ? 'selected' : '' }}>{{ $sem->semester_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">ระดับชั้น</div>
                    <select name="level_id" class="form-select-line" onchange="this.form.submit()">
                        <option value="">เลือกชั้น</option>
                        @foreach ($levels as $lv)
                            <option value="{{ $lv->level_id }}" {{ $levelId == $lv->level_id ? 'selected' : '' }}>{{ $lv->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">ห้องเรียน</div>
                    <select name="section_id" class="form-select-line" onchange="this.form.submit()">
                        <option value="">เลือกห้อง</option>
                        @foreach ($sections as $sec)
                            <option value="{{ $sec->section_id }}" {{ $sectionId == $sec->section_id ? 'selected' : '' }}>
                                {{ $sec->level->name ?? '' }}/{{ $sec->section_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-label-sm">ค้นหาชื่อ / รหัสนักเรียน</div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="พิมพ์ชื่อหรือรหัส..." class="form-input-line">
                </div>
            </div>
            <input type="hidden" name="year_id" value="{{ $yearId }}" id="hiddenYearId" style="display:none;">
            <div style="text-align:center; border-top:1px solid #f0f0f0; padding-top:14px; display:flex; justify-content:center; gap:12px;">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> ค้นหา</button>
                <a href="{{ route('pp2.index') }}"
                    style="display:inline-flex; align-items:center; gap:6px; background:#fff; color:#666;
                           border:1.5px solid #d0d7de; border-radius:4px; padding:9px 20px;
                           font-size:0.9rem; font-weight:600; text-decoration:none;">
                    <i class="fas fa-redo"></i> ล้างค่า
                </a>
            </div>
        </form>
    </div>

    {{-- ตาราง --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#e53935;"><i class="fas fa-file-certificate"></i></div>
        <div class="card-header-text">รายชื่อนักเรียน</div>

        <div style="margin-top:20px;">
            @if (!$sectionId)
                <div class="empty-box">
                    <i class="fas fa-hand-point-up" style="color:#ccc;"></i>
                    <div style="font-size:1rem; color:#555; margin-top:8px;">กรุณาเลือกห้องเรียนเพื่อแสดงรายชื่อ</div>
                </div>
            @elseif ($studentSections->isEmpty())
                <div class="empty-box">
                    <i class="fas fa-inbox" style="color:#ccc;"></i>
                    <div style="font-size:1rem; color:#555; margin-top:8px;">ไม่พบนักเรียนในห้องเรียนนี้</div>
                </div>
            @else
                <table class="pp2-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">ลำดับ</th>
                            <th style="width:120px;">รหัสนักเรียน</th>
                            <th class="left">ชื่อ-นามสกุล</th>
                            <th style="width:160px;">เลขที่เอกสาร</th>
                            <th style="width:200px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($studentSections as $i => $ss)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $ss->student?->student_code ?? '-' }}</td>
                                <td class="left">
                                    {{ $ss->student?->thai_prefix ?? '' }}{{ $ss->student?->thai_firstname ?? '-' }}
                                    {{ $ss->student?->thai_lastname ?? '' }}
                                </td>
                                <td>
                                    @if ($ss->pp2_doc)
                                        <span class="badge-doc">{{ $ss->pp2_doc->doc_number }}</span>
                                    @else
                                        <span class="badge-no-doc">ยังไม่ตั้งเลขที่</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn-set"
                                        onclick="openDocModal({{ $ss->student_id }}, {{ $ss->section_id }}, '{{ addslashes($ss->pp2_doc?->doc_number ?? '') }}', '{{ $ss->pp2_doc?->issued_date?->format('Y-m-d') ?? '' }}')">
                                        <i class="fas fa-hashtag"></i> ตั้งเลขที่
                                    </button>
                                    @if ($ss->pp2_doc)
                                    <a href="{{ route('pp2.print', [$ss->student_id, $ss->section_id]) }}"
                                       target="_blank" class="btn-print">
                                        <i class="fas fa-print"></i> พิมพ์
                                    </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

{{-- Modal ตั้งเลขที่เอกสาร --}}
<div class="modal-backdrop-custom" id="modalDoc">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-hashtag" style="color:#e53935;"></i> ตั้งเลขที่เอกสาร ป.พ.2</div>
        <form method="POST" action="{{ route('pp2.setDocNumber') }}">
            @csrf
            <input type="hidden" name="student_id" id="doc_student_id">
            <input type="hidden" name="section_id" id="doc_section_id">
            <div class="modal-field">
                <label class="modal-label">เลขที่เอกสาร <span style="color:red">*</span></label>
                <input type="text" name="doc_number" id="doc_number" class="modal-input" placeholder="เช่น 012/2567" required>
            </div>
            <div class="modal-field">
                <label class="modal-label">วันที่ออกเอกสาร <span style="color:red">*</span></label>
                <input type="date" name="issued_date" id="doc_issued_date" class="modal-input" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeDocModal()">ยกเลิก</button>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDocModal(studentId, sectionId, docNumber, issuedDate) {
    document.getElementById('doc_student_id').value = studentId;
    document.getElementById('doc_section_id').value = sectionId;
    document.getElementById('doc_number').value = docNumber;
    document.getElementById('doc_issued_date').value = issuedDate;
    document.getElementById('modalDoc').classList.add('show');
}
function closeDocModal() { document.getElementById('modalDoc').classList.remove('show'); }
document.getElementById('modalDoc').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('show');
});
</script>
@endsection
