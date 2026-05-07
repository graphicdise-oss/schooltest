@extends('layouts.sidebar')

@push('styles')
<style>
    body { background: #f4f6f9; }
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
        font-size: 32px; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .card-header-text { margin-left: 90px; font-size: 1.1rem; color: #555; margin-top: -10px; }
    .input-material {
        border: none; border-bottom: 1px solid #ccc; border-radius: 0;
        padding-left: 0; box-shadow: none !important; background: transparent;
    }
    .input-material:focus { border-bottom-color: #00bcd4; border-bottom-width: 2px; }
    .table > thead > tr > th { border-bottom: 2px solid #eee; color: #333; font-weight: 500; padding-bottom: 15px; }
    .table > tbody > tr > td { vertical-align: middle; color: #555; border-bottom: 1px solid #f5f5f5; padding: 12px 10px; }
    .btn-doc { background: #ff9800; color: #fff; border: none; border-radius: 4px; padding: 6px 14px; font-size: 0.82rem; font-weight: 600; cursor: pointer; font-family: inherit; white-space: nowrap; }
    .btn-doc:hover { background: #e68900; }
    .btn-print-pp2 { background: #00bcd4; color: #fff; border: none; border-radius: 4px; padding: 6px 14px; font-size: 0.82rem; font-weight: 600; cursor: pointer; font-family: inherit; text-decoration: none; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px; }
    .btn-print-pp2:hover { background: #00a5bb; color: #fff; text-decoration: none; }

    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9999; justify-content: center; align-items: center; }
    .modal-overlay.active { display: flex; }
    .modal-box { background: #fff; border-radius: 12px; width: 440px; max-width: 92vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden; }
    .modal-header { background: #ff9800; color: #fff; padding: 16px 20px; font-size: 1rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
    .modal-body { padding: 24px; }
    .field-group { margin-bottom: 16px; }
    .field-group label { font-size: 0.85rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px; }
    .field-group input { width: 100%; border: 1.5px solid #e0e0e0; border-radius: 8px; padding: 9px 12px; font-size: 0.88rem; font-family: inherit; outline: none; box-sizing: border-box; }
    .field-group input:focus { border-color: #ff9800; }
    .modal-footer { padding: 12px 24px 20px; display: flex; justify-content: flex-end; gap: 10px; }
    .btn-orange { background: #ff9800; color: #fff; border: none; border-radius: 8px; padding: 9px 28px; font-size: 0.88rem; font-weight: 600; cursor: pointer; font-family: inherit; }
    .btn-cancel { background: #eee; color: #555; border: none; border-radius: 8px; padding: 9px 20px; font-size: 0.88rem; font-weight: 600; cursor: pointer; font-family: inherit; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ค้นหา --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#00bcd4;"><i class="bi bi-search"></i></div>
        <div class="card-header-text mb-4">ค้นหา</div>

        <form action="{{ route('pp2.index') }}" method="GET">
            <div class="row px-md-3 mt-4 g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark mb-0">ปีการศึกษา</label>
                    <select name="year_id" class="form-select input-material mt-2" onchange="this.form.submit()">
                        @foreach ($academicYears as $yr)
                            <option value="{{ $yr->year_id }}" {{ $yearId == $yr->year_id ? 'selected' : '' }}>{{ $yr->year_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark mb-0">ระดับชั้น</label>
                    <select name="level_id" class="form-select input-material mt-2" onchange="this.form.submit()">
                        <option value="">-- เลือกระดับชั้น --</option>
                        @foreach ($levels as $lv)
                            <option value="{{ $lv->level_id }}" {{ $levelId == $lv->level_id ? 'selected' : '' }}>{{ $lv->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark mb-0">ชั้นเรียน</label>
                    <select name="section_id" class="form-select input-material mt-2" onchange="this.form.submit()">
                        <option value="">-- เลือกชั้นเรียน --</option>
                        @foreach ($sections as $sec)
                            <option value="{{ $sec->section_id }}" {{ $sectionId == $sec->section_id ? 'selected' : '' }}>
                                {{ $sec->level->name ?? '' }}/{{ $sec->section_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark mb-0">ชื่อ-นามสกุล</label>
                    <input type="text" name="search" class="form-control input-material mt-2"
                        placeholder="ชื่อ-นามสกุล/รหัสนักเรียน" value="{{ $search }}">
                </div>
            </div>
            <div class="text-center mt-3">
                <button type="submit" class="btn text-white px-4 py-2 rounded-1" style="background:#00bcd4;">
                    <i class="bi bi-search me-1"></i> ค้นหา
                </button>
            </div>
        </form>
    </div>

    {{-- ตาราง --}}
    <div class="floating-card mb-5">
        <div class="floating-icon" style="background:#4caf50;"><i class="bi bi-file-earmark-text"></i></div>
        <div class="d-flex justify-content-between align-items-center card-header-text mb-4">
            <div>ระเบียนใบประกาศนียบัตร (ปว.2)</div>
            @if ($sectionId && $students->isNotEmpty())
                <a href="#" onclick="window.print()" class="btn text-white px-3 py-2 rounded-1"
                    style="background:#ff9800; margin-top:-15px; font-size:0.88rem;">
                    <i class="bi bi-printer me-1"></i> สรุปรายงาน ป.พ.2
                </a>
            @endif
        </div>

        @if (!$sectionId)
            <div class="text-center text-muted py-5">
                <i class="bi bi-arrow-up-circle fs-1 d-block mb-2"></i>
                กรุณาเลือก <strong>ระดับชั้น</strong> และ <strong>ชั้นเรียน</strong> เพื่อดูรายชื่อ
            </div>
        @elseif ($students->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                ไม่พบนักเรียนในชั้นเรียนนี้
            </div>
        @else
            <div class="table-responsive px-2">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center">ลำดับ</th>
                            <th class="text-center">รหัสนักเรียน</th>
                            <th>ชื่อ-สกุล</th>
                            <th class="text-center">เลขที่เอกสาร</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $i => $ss)
                            @php $doc = $docs->get($ss->student_id); @endphp
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td class="text-center">{{ $ss->student?->student_code ?? '-' }}</td>
                                <td>
                                    {{ $ss->student?->thai_prefix ?? '' }}{{ $ss->student?->thai_firstname ?? '-' }}
                                    {{ $ss->student?->thai_lastname ?? '' }}
                                </td>
                                <td class="text-center">
                                    {{ $doc?->doc_number ?? '-' }}
                                </td>
                                <td class="text-center" style="white-space:nowrap;">
                                    <button class="btn-doc me-1"
                                        onclick="openDocModal({{ $ss->student_id }}, {{ $sectionId }}, '{{ $doc?->doc_number ?? '' }}', '{{ $doc?->issued_date?->format('Y-m-d') ?? '' }}')">
                                        <i class="bi bi-hash"></i> ตั้งเลขที่เอกสาร
                                    </button>
                                    <a href="{{ route('pp2.print', [$ss->student_id, $sectionId]) }}"
                                        target="_blank" class="btn-print-pp2">
                                        <i class="bi bi-printer"></i> พิมพ์ใบ ป.พ.2
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Modal ตั้งเลขที่เอกสาร --}}
<div class="modal-overlay" id="docModal" onclick="closeDocModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <span>ตั้งเลขที่เอกสาร ป.พ.2</span>
            <button onclick="closeDocModal()" style="background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer;">✕</button>
        </div>
        <form action="{{ route('pp2.setDocNumber') }}" method="POST">
            @csrf
            <input type="hidden" name="student_id" id="docStudentId">
            <input type="hidden" name="section_id" id="docSectionId">
            <div class="modal-body">
                <div class="field-group">
                    <label>เลขที่เอกสาร <span style="color:red;">*</span></label>
                    <input type="text" name="doc_number" id="docNumber" placeholder="เช่น ปพ.2-001" required>
                </div>
                <div class="field-group">
                    <label>วันที่ออกเอกสาร</label>
                    <input type="date" name="issued_date" id="docIssuedDate">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeDocModal()">ยกเลิก</button>
                <button type="submit" class="btn-orange">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDocModal(studentId, sectionId, docNumber, issuedDate) {
        document.getElementById('docStudentId').value = studentId;
        document.getElementById('docSectionId').value = sectionId;
        document.getElementById('docNumber').value = docNumber;
        document.getElementById('docIssuedDate').value = issuedDate;
        document.getElementById('docModal').classList.add('active');
    }
    function closeDocModal(e) {
        if (e && e.target !== e.currentTarget) return;
        document.getElementById('docModal').classList.remove('active');
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDocModal(); });
</script>
@endsection