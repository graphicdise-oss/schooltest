@extends('layouts.sidebar')

@push('styles')
    <style>
        .pp2-page {
            padding: 24px 28px;
            min-height: 100%;
        }

        .pp2-card {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 30px 20px 20px;
            position: relative;
            margin-top: 50px;
            border: none;
            margin-bottom: 28px;
        }

        .pp2-icon {
            position: absolute;
            top: -25px;
            left: 20px;
            width: 70px;
            height: 70px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            background: #3f51b5;
        }

        .pp2-header {
            margin-left: 90px;
            font-size: 1.15rem;
            color: #555;
            margin-top: -10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pp2-header h5 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }

        .btn-setting {
            background: #3f51b5;
            color: #fff;
            border: none;
            padding: 7px 18px;
            border-radius: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-setting:hover { background: #303f9f; }

        .btn-save {
            background: #3f51b5;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .btn-save:hover { background: #303f9f; }

        .btn-search {
            background: #3f51b5;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .btn-search:hover { background: #303f9f; }

        .form-control, .form-select {
            border-radius: 4px;
            border: 1px solid #ccc;
            padding: 7px 10px;
            width: 100%;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3f51b5;
            outline: none;
            box-shadow: 0 0 0 2px rgba(63, 81, 181, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #444;
            margin-bottom: 4px;
            display: block;
            font-size: 0.9rem;
        }

        .date-section-bar {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 6px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .date-section-bar label {
            font-weight: 600;
            color: #555;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .btn-date-save {
            background: #43a047;
            color: #fff;
            border: none;
            padding: 7px 16px;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-date-save:hover { background: #2e7d32; }

        .btn-bulk-number {
            background: #7b1fa2;
            color: #fff;
            border: none;
            padding: 7px 16px;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-bulk-number:hover { background: #4a148c; }

        .student-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .student-table th {
            padding: 10px;
            border-bottom: 2px solid #ddd;
            text-align: left;
            background: #3f51b5;
            color: #fff;
            font-weight: 600;
        }

        .student-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .btn-print {
            background: #66bb6a;
            color: #fff;
            padding: 5px 14px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-print:hover { background: #43a047; color: #fff; }

        .btn-set-number {
            background: #ff9800;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            border: none;
            font-size: 0.85rem;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-set-number:hover { background: #e65100; }

        .doc-number-badge {
            display: inline-block;
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #90caf9;
            border-radius: 4px;
            padding: 2px 8px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 4px;
        }

        .alert-success {
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
            color: #2e7d32;
            border-radius: 4px;
            padding: 10px 16px;
            margin-bottom: 16px;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.show { display: flex; }

        .modal-box {
            background: #fff;
            border-radius: 8px;
            padding: 28px;
            width: 560px;
            max-width: 95vw;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .modal-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }

        .modal-close {
            position: absolute;
            top: 14px;
            right: 18px;
            font-size: 1.4rem;
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
        }

        .modal-close:hover { color: #333; }

        .modal-footer {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-cancel {
            background: #eee;
            color: #555;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-cancel:hover { background: #ddd; }

        .row { display: flex; flex-wrap: wrap; margin: 0 -8px; }

        .col-md-3, .col-md-4, .col-md-6, .col-md-12 {
            padding: 0 8px;
            margin-bottom: 12px;
        }

        .col-md-3  { width: 25%; }
        .col-md-4  { width: 33.33%; }
        .col-md-6  { width: 50%; }
        .col-md-12 { width: 100%; }

        @media (max-width: 768px) {
            .col-md-3, .col-md-4, .col-md-6, .col-md-12 { width: 100%; }
        }
    </style>
@endpush

@section('content')
    <div class="pp2-page">

        <div class="pp2-card">
            <div class="pp2-icon">🎓</div>
            <div class="pp2-header">
                <div>
                    <h5>ออกใบ ปพ.2</h5>
                    <span style="color:#888;font-size:0.9rem;">เลือกห้องเรียนและพิมพ์ประกาศนียบัตร</span>
                </div>
                <button class="btn-setting" onclick="openSettingModal()">⚙️ ตั้งค่าข้อมูลโรงเรียน</button>
            </div>

            <hr style="margin:20px 0 20px;">

            @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif
            @if(session('success_section'))
                <div class="alert-success">{{ session('success_section') }}</div>
            @endif

            {{-- Filter --}}
            <form method="GET" action="{{ route('pp2.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">ปีการศึกษา</label>
                        <select name="year_id" class="form-select" onchange="this.form.submit()">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->year_id }}" {{ $yearId == $year->year_id ? 'selected' : '' }}>
                                    {{ $year->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ระดับชั้น</label>
                        <select name="level_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- เลือกระดับ --</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->level_id }}" {{ $levelId == $level->level_id ? 'selected' : '' }}>
                                    {{ $level->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ห้องเรียน</label>
                        <select name="section_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- เลือกห้อง --</option>
                            @foreach($sections as $sec)
                                <option value="{{ $sec->section_id }}" {{ $sectionId == $sec->section_id ? 'selected' : '' }}>
                                    {{ $sec->level?->name }}/{{ $sec->section_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ค้นหา</label>
                        <div style="display:flex;gap:6px;">
                            <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ชื่อ / รหัส">
                            <button type="submit" class="btn-search">🔍</button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="semester_id" value="{{ $semesterId }}">
            </form>

            @if($sectionId)
                @php
                    $selectedSection = $sections->firstWhere('section_id', $sectionId);
                    $currentDate = $selectedSection?->pp2SectionSetting?->issued_date?->format('Y-m-d');
                @endphp
                <form method="POST" action="{{ route('pp2.saveSectionDate', $sectionId) }}">
                    @csrf
                    <input type="hidden" name="year_id" value="{{ $yearId }}">
                    <input type="hidden" name="level_id" value="{{ $levelId }}">
                    <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                    <div class="date-section-bar">
                        <label>📅 วันจบการศึกษาของห้องนี้ :</label>
                        <input type="date" name="issued_date" class="form-control" style="max-width:200px;" value="{{ $currentDate }}">
                        <button type="submit" class="btn-date-save">บันทึกวันที่</button>
                        @if($currentDate)
                            <span style="color:#2e7d32;font-size:0.85rem;font-weight:600;">✅ ตั้งค่าแล้ว</span>
                        @else
                            <span style="color:#aaa;font-size:0.85rem;">ยังไม่ได้ตั้งค่า (ใช้วันปัจจุบัน)</span>
                        @endif
                        <button type="button" class="btn-bulk-number" onclick="openBulkDocModal()">🔢 ตั้งเลขปพ. ชั้นเรียนนี้ทั้งหมด</button>
                    </div>
                </form>
            @endif

            @if($students->count() > 0)
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>รหัส</th>
                            <th>ชื่อ-สกุล</th>
                            <th style="text-align:center;">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $ss)
                            @php
                                $doc = $docs->get($ss->student_id);
                                $fullName = ($ss->student?->thai_prefix ?? '') . ($ss->student?->thai_firstname ?? '') . ' ' . ($ss->student?->thai_lastname ?? '');
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $ss->student?->student_code }}</td>
                                <td>{{ $fullName }}</td>
                                <td style="text-align:center;">
                                    <div style="display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap;justify-content:center;">
                                        @if($doc?->doc_number)
                                            <span class="doc-number-badge">เลขที่ {{ $doc->doc_number }}</span>
                                        @endif
                                        <a href="{{ route('pp2.print', [$ss->student_id, $ss->section_id]) }}" class="btn-print">🖨️ พิมพ์</a>
                                        <button type="button" class="btn-set-number"
                                            onclick="openDocNumberModal({{ $ss->student_id }}, {{ $ss->section_id }}, {{ json_encode($fullName) }}, {{ json_encode($doc?->doc_number ?? '') }})">🔢 ตั้งเลขที่ ปพ.</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif($sectionId)
                <p style="color:#aaa;margin-top:12px;">ไม่มีนักเรียนในห้องนี้</p>
            @else
                <p style="color:#aaa;margin-top:12px;">เลือกห้องเรียนเพื่อแสดงรายชื่อ</p>
            @endif
        </div>
    </div>

    {{-- Modal ตั้งค่าโรงเรียน --}}
    <div class="modal-overlay" id="settingModal">
        <div class="modal-box">
            <button class="modal-close" onclick="closeSettingModal()">✕</button>
            <div class="modal-title">⚙️ ตั้งค่าข้อมูลโรงเรียน</div>
            <form method="POST" action="{{ route('pp2.saveSetting') }}">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">ชื่อโรงเรียน</label>
                        <input type="text" name="school_name" class="form-control"
                            value="{{ old('school_name', $setting?->school_name) }}"
                            placeholder="เช่น โรงเรียนสาธิตมหาวิทยาลัยราชภัฏวไลยอลงกรณ์ ในพระบรมราชูปถัมภ์">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">จังหวัด</label>
                        <input type="text" name="province" class="form-control"
                            value="{{ old('province', $setting?->province) }}" placeholder="เช่น ปทุมธานี">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ชื่อผู้อำนวยการ</label>
                        <select name="director_name" class="form-select">
                            <option value="">-- เลือกบุคลากร --</option>
                            @foreach($directors as $p)
                                @php
                                    $fullName = ($p->thai_prefix ?? '') . ($p->thai_firstname ?? '') . ' ' . ($p->thai_lastname ?? '');
                                @endphp
                                <option value="{{ $fullName }}" {{ $setting?->director_name === $fullName ? 'selected' : '' }}>
                                    {{ $fullName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">สังกัด</label>
                        <input type="text" name="affiliation" class="form-control"
                            value="{{ old('affiliation', $setting?->affiliation) }}"
                            placeholder="เช่น สำนักงานปลัดกระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeSettingModal()">ยกเลิก</button>
                    <button type="submit" class="btn-save">💾 บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal ตั้งเลขที่ ปพ. รายคน --}}
    <div class="modal-overlay" id="docNumberModal">
        <div class="modal-box" style="width:420px;">
            <button class="modal-close" onclick="closeDocNumberModal()">✕</button>
            <div class="modal-title">🔢 ตั้งเลขที่ ปพ.2</div>
            <form method="POST" action="{{ route('pp2.setDocNumber') }}">
                @csrf
                <input type="hidden" name="student_id" id="modalStudentId">
                <input type="hidden" name="section_id" id="modalSectionId">
                <div style="margin-bottom:12px;">
                    <label class="form-label">นักเรียน</label>
                    <div id="modalStudentName" style="font-weight:600;color:#333;padding:6px 0;"></div>
                </div>
                <div style="margin-bottom:4px;">
                    <label class="form-label">เลขที่ ปพ.2</label>
                    <input type="text" name="doc_number" id="modalDocNumber" class="form-control" placeholder="กรอกเลขที่เอกสาร">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeDocNumberModal()">ยกเลิก</button>
                    <button type="submit" class="btn-save">💾 บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal ตั้งเลขปพ. ทั้งห้อง --}}
    <div class="modal-overlay" id="bulkDocModal">
        <div class="modal-box" style="width:440px;">
            <button class="modal-close" onclick="closeBulkDocModal()">✕</button>
            <div class="modal-title">🔢 ตั้งเลขปพ. ชั้นเรียนนี้ทั้งหมด</div>
            <p style="color:#666;font-size:0.9rem;margin-bottom:16px;">เลขที่ที่กรอกจะใช้เหมือนกันทุกคนในห้องนี้</p>
            <form method="POST" action="{{ route('pp2.bulkSetDocNumber') }}">
                @csrf
                <input type="hidden" name="section_id" value="{{ $sectionId }}">
                <input type="hidden" name="year_id" value="{{ $yearId }}">
                <input type="hidden" name="level_id" value="{{ $levelId }}">
                <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                <div style="margin-bottom:16px;">
                    <label class="form-label">เลขที่ ปพ.2 (ใช้เหมือนกันทุกคน)</label>
                    <input type="text" name="doc_number" class="form-control" placeholder="เช่น 2568/001">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeBulkDocModal()">ยกเลิก</button>
                    <button type="submit" class="btn-save">🔢 ตั้งเลขทั้งหมด</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openSettingModal() {
            document.getElementById('settingModal').classList.add('show');
        }
        function closeSettingModal() {
            document.getElementById('settingModal').classList.remove('show');
        }
        document.getElementById('settingModal').addEventListener('click', function(e) {
            if (e.target === this) closeSettingModal();
        });

        function openDocNumberModal(studentId, sectionId, studentName, currentDocNumber) {
            document.getElementById('modalStudentId').value = studentId;
            document.getElementById('modalSectionId').value = sectionId;
            document.getElementById('modalStudentName').textContent = studentName;
            document.getElementById('modalDocNumber').value = currentDocNumber;
            document.getElementById('docNumberModal').classList.add('show');
        }
        function closeDocNumberModal() {
            document.getElementById('docNumberModal').classList.remove('show');
        }
        document.getElementById('docNumberModal').addEventListener('click', function(e) {
            if (e.target === this) closeDocNumberModal();
        });

        function openBulkDocModal() {
            document.getElementById('bulkDocModal').classList.add('show');
        }
        function closeBulkDocModal() {
            document.getElementById('bulkDocModal').classList.remove('show');
        }
        document.getElementById('bulkDocModal').addEventListener('click', function(e) {
            if (e.target === this) closeBulkDocModal();
        });
    </script>
@endsection
