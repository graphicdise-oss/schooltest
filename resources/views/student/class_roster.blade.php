@extends('layouts.sidebar')

@push('styles')
<style>
    body { background: #f4f6f9; }
    .page { padding: 24px 28px; }
    .breadcrumb-custom a { color: #00bcd4; text-decoration: none; }
    .breadcrumb-custom a:hover { text-decoration: underline; }
    .breadcrumb-custom i { color: #aaa; margin: 0 6px; font-size: 0.75rem; }

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
    .form-select-line, .form-input-line {
        border: none; border-bottom: 1.5px solid #ccc; border-radius: 0;
        padding: 6px 4px; font-size: 0.88rem; width: 100%;
        background: transparent; outline: none; font-family: inherit;
    }
    .form-select-line:focus, .form-input-line:focus { border-bottom-color: #00bcd4; }

    .btn-search-teal {
        background: #00bcd4; color: #fff; border: none; border-radius: 4px;
        padding: 9px 28px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-search-teal:hover { background: #00a5bb; }

    .btn-export {
        background: #4caf50; color: #fff; border: none; border-radius: 4px;
        padding: 9px 20px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;
    }
    .btn-export:hover { background: #43a047; text-decoration: none; color: #fff; }

    /* Section tabs */
    .section-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
    .section-tab {
        padding: 7px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;
        border: 1.5px solid #d0d7e5; color: #555; text-decoration: none; cursor: pointer;
        transition: all 0.15s; white-space: nowrap;
    }
    .section-tab:hover { border-color: #5482e7; color: #5482e7; text-decoration: none; }
    .section-tab.active { background: #7c3aed; border-color: #7c3aed; color: #fff; }

    /* ตาราง */
    .roster-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; white-space: nowrap; }
    .roster-table thead th {
        background: #fff; border-bottom: 2px solid #e5e7eb;
        padding: 10px 10px; color: #444; font-weight: 600; position: sticky; top: 0;
    }
    .roster-table thead th .sort-btn {
        display: inline-flex; flex-direction: column; gap: 1px; margin-left: 4px; cursor: pointer; opacity: 0.4;
    }
    .roster-table thead th .sort-btn:hover { opacity: 1; }
    .roster-table tbody td {
        padding: 10px 10px; border-bottom: 1px solid #f3f4f6; color: #555; vertical-align: middle;
    }
    .roster-table tbody tr:hover td { background: #f8fffe; }

    .badge-active   { background: #dcfce7; color: #16a34a; border-radius: 12px; padding: 3px 10px; font-size: 0.78rem; font-weight: 600; }
    .badge-inactive { background: #fee2e2; color: #dc2626; border-radius: 12px; padding: 3px 10px; font-size: 0.78rem; font-weight: 600; }
    .badge-other    { background: #f3f4f6; color: #6b7280; border-radius: 12px; padding: 3px 10px; font-size: 0.78rem; font-weight: 600; }

    .table-wrap { overflow-x: auto; border-radius: 6px; border: 1px solid #e5e7eb; }
    .empty-box { text-align: center; color: #aaa; padding: 50px 0; }
    .empty-box i { font-size: 2.5rem; display: block; margin-bottom: 10px; }

    .info-chip {
        display: inline-flex; align-items: center; gap: 6px;
        background: #eff6ff; color: #3b82f6; border-radius: 6px;
        padding: 5px 12px; font-size: 0.82rem; font-weight: 600;
    }
    .count-chip {
        display: inline-flex; align-items: center; gap: 6px;
        background: #f0fdf4; color: #16a34a; border-radius: 6px;
        padding: 5px 12px; font-size: 0.82rem; font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="page">

    <nav class="breadcrumb-custom mb-3" style="font-size:0.88rem; display:flex; align-items:center; gap:4px;">
        <a href="#">ข้อมูลนักเรียน</a>
        <i class="bi bi-chevron-right"></i>
        <span style="color:#555;">รายชื่อนักเรียนรายห้อง</span>
    </nav>

    {{-- ค้นหา --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#00bcd4;"><i class="fas fa-search"></i></div>
        <div class="card-header-text">ค้นหา</div>

        <form method="GET" action="{{ route('class-roster.index') }}" id="searchForm" style="margin-top:24px;">
            <div class="row g-3 mb-3">
                <div class="col-md-2">
                    <div class="form-label-sm">ปีการศึกษา</div>
                    <select name="year_id" class="form-select-line" onchange="submitForm()">
                        <option value="">เลือกปี</option>
                        @foreach ($academicYears as $yr)
                            <option value="{{ $yr->year_id }}" {{ $yearId == $yr->year_id ? 'selected' : '' }}>
                                {{ $yr->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">เทอม</div>
                    <select name="semester_id" class="form-select-line" onchange="submitForm()">
                        <option value="">เลือกเทอม</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->semester_id }}" {{ $semesterId == $sem->semester_id ? 'selected' : '' }}>
                                {{ $sem->semester_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">ระดับชั้นเรียน</div>
                    <select name="level_id" class="form-select-line" onchange="submitForm()">
                        <option value="">เลือกระดับชั้น</option>
                        @foreach ($levels as $lv)
                            <option value="{{ $lv->level_id }}" {{ $levelId == $lv->level_id ? 'selected' : '' }}>
                                {{ $lv->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">ชื่อ-นามสกุล / รหัส</div>
                    <input type="text" name="search" class="form-input-line" placeholder="ค้นหา ชื่อ/รหัส" value="{{ $search }}">
                </div>
                <div class="col-md-2">
                    <div class="form-label-sm">สถานะนักเรียน</div>
                    <select name="status" class="form-select-line">
                        <option value="">ทั้งหมด</option>
                        <option value="กำลังศึกษา" {{ $status === 'กำลังศึกษา' ? 'selected' : '' }}>กำลังศึกษา</option>
                        <option value="จบการศึกษา"  {{ $status === 'จบการศึกษา'  ? 'selected' : '' }}>จบการศึกษา</option>
                        <option value="ลาออก"       {{ $status === 'ลาออก'       ? 'selected' : '' }}>ลาออก</option>
                        <option value="พักการเรียน" {{ $status === 'พักการเรียน' ? 'selected' : '' }}>พักการเรียน</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="section_id" id="sectionInput" value="{{ $sectionId }}">
            <div style="text-align:center; border-top:1px solid #f0f0f0; padding-top:14px; display:flex; justify-content:center; gap:12px;">
                <button type="submit" class="btn-search-teal"><i class="fas fa-search"></i> ค้นหา</button>
                <a href="{{ route('class-roster.index') }}" style="display:inline-flex; align-items:center; gap:6px; background:#fff; color:#666; border:1.5px solid #d0d7de; border-radius:4px; padding:9px 20px; font-size:0.9rem; font-weight:600; text-decoration:none;">
                    <i class="fas fa-redo"></i> ล้างค่า
                </a>
            </div>
        </form>
    </div>

    {{-- รายชื่อ --}}
    <div class="floating-card">
        <div class="floating-icon" style="background:#f59e0b;"><i class="fas fa-list"></i></div>
        <div class="card-header-text">รายการ</div>

        <div style="margin-top:20px;">

            @if ($sections->isEmpty() && !$levelId)
                <div class="empty-box">
                    <i class="fas fa-arrow-up" style="color:#00bcd4;"></i>
                    <div style="font-size:1rem; color:#555; margin-top:8px;">กรุณาเลือก <strong>ปีการศึกษา + เทอม + ระดับชั้น</strong> ด้านบนก่อน</div>
                </div>
            @elseif ($sections->isEmpty())
                <div class="empty-box">
                    <i class="fas fa-inbox"></i>
                    <div>ไม่พบห้องเรียนในระดับชั้นนี้</div>
                </div>
            @else
                {{-- Section Tabs --}}
                <div class="section-tabs">
                    @foreach ($sections as $sec)
                        <a href="javascript:void(0)"
                           class="section-tab {{ $sectionId == $sec->section_id ? 'active' : '' }}"
                           onclick="selectSection({{ $sec->section_id }})">
                            {{ $sec->level->name ?? '' }}/{{ $sec->section_number }}
                            @if ($sec->curriculum)
                                <span style="font-weight:400;font-size:0.78rem;">{{ $sec->curriculum->name ?? '' }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                {{-- Info bar --}}
                @if ($selectedSection)
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <span class="info-chip">
                                <i class="fas fa-chalkboard"></i>
                                ห้อง {{ $selectedSection->level->name ?? '' }}/{{ $selectedSection->section_number }}
                            </span>
                            <span class="count-chip">
                                <i class="fas fa-users"></i>
                                {{ $students->count() }} คน
                            </span>
                        </div>
                        <a href="{{ route('class-roster.index', array_merge(request()->query(), ['export' => 'excel'])) }}"
                           class="btn-export">
                            <i class="fas fa-file-excel"></i> EXPORT
                        </a>
                    </div>
                @endif

                {{-- ตาราง --}}
                @if ($students->isEmpty())
                    <div class="empty-box">
                        <i class="fas fa-user-slash"></i>
                        <div>ไม่พบนักเรียนในห้องนี้</div>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="roster-table">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>เลขที่</th>
                                    <th>สถานะ</th>
                                    <th>รหัสบัตรประชาชน</th>
                                    <th>รหัสนักเรียน</th>
                                    <th>วันที่เข้าเรียน</th>
                                    <th>เพศ</th>
                                    <th>คำนำหน้า</th>
                                    <th>ชื่อ</th>
                                    <th>นามสกุล</th>
                                    <th>ชื่อเล่น</th>
                                    <th>ชื่อ(อังกฤษ)</th>
                                    <th>นามสกุล(อังกฤษ)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $i => $ss)
                                    @php $s = $ss->student; @endphp
                                    <tr>
                                        <td style="text-align:center;">{{ $i + 1 }}</td>
                                        <td style="text-align:center;">{{ $ss->student_number }}</td>
                                        <td style="text-align:center;">
                                            @php $sts = $s->status ?? 'กำลังศึกษา'; @endphp
                                            @if ($sts === 'กำลังศึกษา')
                                                <span class="badge-active">{{ $sts }}</span>
                                            @elseif (in_array($sts, ['ลาออก','จบการศึกษา']))
                                                <span class="badge-inactive">{{ $sts }}</span>
                                            @else
                                                <span class="badge-other">{{ $sts }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $s->id_card_number ?? '-' }}</td>
                                        <td>{{ $s->student_code ?? '-' }}</td>
                                        <td style="text-align:center;">
                                            {{ $s->enrollment_date
                                                ? \Carbon\Carbon::parse($s->enrollment_date)->addYears(543)->format('d/m/Y')
                                                : '-' }}
                                        </td>
                                        <td style="text-align:center;">{{ $s->gender ?? '-' }}</td>
                                        <td>{{ $s->thai_prefix ?? '' }}</td>
                                        <td>
                                            <a href="{{ route('students.show', $s->student_id) }}"
                                               style="color:#00bcd4; text-decoration:none; font-weight:500;">
                                                {{ $s->thai_firstname ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $s->thai_lastname ?? '-' }}</td>
                                        <td>{{ $s->nickname ?? '-' }}</td>
                                        <td>{{ $s->eng_firstname ?? '-' }}</td>
                                        <td>{{ $s->eng_lastname ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif

        </div>
    </div>

</div>

@push('scripts')
<script>
    function submitForm() {
        document.getElementById('searchForm').submit();
    }

    function selectSection(sectionId) {
        document.getElementById('sectionInput').value = sectionId;
        document.getElementById('searchForm').submit();
    }
</script>
@endpush
@endsection