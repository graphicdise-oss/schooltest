@extends('layouts.sidebar')

@push('styles')
<style>
    .pp2-page { padding: 24px 28px; min-height: 100%; }

    .pp2-card {
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 30px 20px 20px; position: relative;
        margin-top: 50px; border: none; margin-bottom: 28px;
    }
    .pp2-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        background: #3f51b5;
    }
    .pp2-header { margin-left: 90px; font-size: 1.15rem; color: #555; margin-top: -10px; }
    .pp2-header h5 { font-size: 1.3rem; font-weight: 600; color: #333; margin-bottom: 2px; }

    .form-label { font-weight: 600; color: #444; margin-bottom: 4px; }
    .form-control { border-radius: 4px; border: 1px solid #ccc; }
    .form-control:focus { border-color: #3f51b5; box-shadow: 0 0 0 2px rgba(63,81,181,0.15); }

    .btn-save {
        background: #3f51b5; color: #fff; border: none;
        padding: 8px 24px; border-radius: 4px; font-size: 0.95rem; cursor: pointer;
    }
    .btn-save:hover { background: #303f9f; color: #fff; }

    .btn-sm-save {
        background: #43a047; color: #fff; border: none;
        padding: 5px 14px; border-radius: 4px; font-size: 0.85rem; cursor: pointer;
        white-space: nowrap;
    }
    .btn-sm-save:hover { background: #2e7d32; }

    .alert-success {
        background: #e8f5e9; border: 1px solid #a5d6a7;
        color: #2e7d32; border-radius: 4px; padding: 10px 16px; margin-bottom: 16px;
    }

    /* ปีการศึกษา accordion */
    .year-block { margin-bottom: 12px; border: 1px solid #e0e0e0; border-radius: 6px; overflow: hidden; }
    .year-header {
        background: #3f51b5; color: #fff;
        padding: 12px 18px; font-weight: 600; font-size: 1rem;
        cursor: pointer; display: flex; justify-content: space-between; align-items: center;
        user-select: none;
    }
    .year-header:hover { background: #303f9f; }
    .year-body { padding: 0; display: none; }
    .year-body.open { display: block; }

    .semester-block { border-top: 1px solid #eee; }
    .semester-label {
        background: #f5f5f5; padding: 8px 18px;
        font-size: 0.9rem; font-weight: 600; color: #555;
        border-bottom: 1px solid #eee;
    }

    .section-row {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 18px; border-bottom: 1px solid #f0f0f0;
        flex-wrap: wrap;
    }
    .section-row:last-child { border-bottom: none; }
    .section-name { min-width: 120px; font-weight: 600; color: #333; }
    .section-date-form { display: flex; align-items: center; gap: 8px; flex: 1; }
    .date-badge {
        font-size: 0.8rem; color: #888; white-space: nowrap;
    }
    .date-badge.has-date { color: #2e7d32; font-weight: 600; }

    /* ตารางนักเรียน */
    .student-table { width:100%; border-collapse:collapse; font-size:0.95rem; }
    .student-table th { padding:10px; border-bottom:2px solid #ddd; text-align:left; background:#f5f5f5; }
    .student-table td { padding:10px; border-bottom:1px solid #eee; }
    .btn-print {
        background:#e91e63; color:#fff; padding:5px 14px;
        border-radius:4px; text-decoration:none; font-size:0.9rem;
    }
    .btn-print:hover { background:#c2185b; color:#fff; }
</style>
@endpush

@section('content')
<div class="pp2-page">

    {{-- ===== ตั้งค่าข้อมูลโรงเรียน ===== --}}
    <div class="pp2-card">
        <div class="pp2-icon">⚙️</div>
        <div class="pp2-header">
            <h5>ตั้งค่าข้อมูลโรงเรียน</h5>
            <span style="color:#888;font-size:0.9rem;">ข้อมูลที่แสดงในใบ ปพ.2</span>
        </div>
        <hr style="margin:20px 0 24px;">

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('pp2.saveSettings') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">ชื่อโรงเรียน</label>
                    <input type="text" name="school_name" class="form-control"
                        value="{{ old('school_name', $settings?->school_name) }}"
                        placeholder="เช่น โรงเรียนสาธิตมหาวิทยาลัยราชภัฏวไลยอลงกรณ์ ในพระบรมราชูปถัมภ์">
                </div>
                <div class="col-md-4">
                    <label class="form-label">จังหวัด</label>
                    <input type="text" name="province" class="form-control"
                        value="{{ old('province', $settings?->province) }}"
                        placeholder="เช่น ปทุมธานี">
                </div>
                <div class="col-md-8">
                    <label class="form-label">สังกัด</label>
                    <input type="text" name="affiliation" class="form-control"
                        value="{{ old('affiliation', $settings?->affiliation) }}"
                        placeholder="เช่น สำนักงานปลัดกระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ชื่อผู้อำนวยการ</label>
                    <input type="text" name="director_name" class="form-control"
                        value="{{ old('director_name', $settings?->director_name) }}"
                        placeholder="เช่น นางสาววรานิษฐ์ ธนชัยวรพันธ์">
                </div>
            </div>
            <div style="margin-top:20px;">
                <button type="submit" class="btn-save">💾 บันทึกข้อมูลโรงเรียน</button>
            </div>
        </form>
    </div>

    {{-- ===== ตั้งค่าวันออกเอกสารแยกตามห้อง ===== --}}
    <div class="pp2-card">
        <div class="pp2-icon" style="background:#f57c00;">📅</div>
        <div class="pp2-header">
            <h5>ตั้งค่าวันออกเอกสาร ปพ.2</h5>
            <span style="color:#888;font-size:0.9rem;">กำหนดวันที่แยกตามปีการศึกษาและห้องเรียน</span>
        </div>
        <hr style="margin:20px 0 24px;">

        @if(session('success_section'))
            <div class="alert-success">{{ session('success_section') }}</div>
        @endif

        @forelse($academicYears as $year)
        <div class="year-block">
            <div class="year-header" onclick="toggleYear({{ $year->year_id }})">
                <span>ปีการศึกษา {{ $year->year_name }}</span>
                <span id="arrow-{{ $year->year_id }}">▼</span>
            </div>
            <div class="year-body" id="year-body-{{ $year->year_id }}">
                @forelse($year->semesters as $semester)
                <div class="semester-block">
                    <div class="semester-label">{{ $semester->semester_name }}</div>

                    @forelse($semester->classSections as $section)
                    <div class="section-row">
                        <div class="section-name">
                            {{ $section->level?->name }}/{{ $section->section_number }}
                        </div>
                        <form method="POST"
                            action="{{ route('pp2.saveSectionDate', $section->section_id) }}"
                            class="section-date-form">
                            @csrf
                            <input type="date" name="issued_date" class="form-control"
                                style="max-width:200px;"
                                value="{{ $section->pp2SectionSetting?->issued_date?->format('Y-m-d') }}">
                            <button type="submit" class="btn-sm-save">บันทึก</button>
                        </form>
                        <span class="date-badge {{ $section->pp2SectionSetting?->issued_date ? 'has-date' : '' }}">
                            @if($section->pp2SectionSetting?->issued_date)
                                ✅ {{ $section->pp2SectionSetting->issued_date->locale('th')->translatedFormat('j F Y') }}
                            @else
                                ยังไม่ได้ตั้งค่า (ใช้วันปัจจุบัน)
                            @endif
                        </span>
                    </div>
                    @empty
                    <div style="padding:12px 18px;color:#aaa;font-size:0.9rem;">ไม่มีห้องเรียนในภาคเรียนนี้</div>
                    @endforelse
                </div>
                @empty
                <div style="padding:12px 18px;color:#aaa;font-size:0.9rem;">ไม่มีภาคเรียน</div>
                @endforelse
            </div>
        </div>
        @empty
        <p style="color:#aaa;">ยังไม่มีปีการศึกษา</p>
        @endforelse
    </div>

    {{-- ===== ค้นหานักเรียนเพื่อพิมพ์ ===== --}}
    <div class="pp2-card">
        <div class="pp2-icon" style="background:#e91e63;">🎓</div>
        <div class="pp2-header">
            <h5>พิมพ์ใบ ปพ.2</h5>
            <span style="color:#888;font-size:0.9rem;">ค้นหานักเรียนและพิมพ์ประกาศนียบัตร</span>
        </div>
        <hr style="margin:20px 0 24px;">

        <form method="GET" action="{{ route('pp2.index') }}" class="row g-3" style="margin-bottom:20px;">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control"
                    value="{{ request('search') }}"
                    placeholder="ค้นหาชื่อ / รหัสนักเรียน">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-save" style="padding:8px 16px;">🔍 ค้นหา</button>
            </div>
        </form>

        @if($studentSections->count() > 0)
        <table class="student-table">
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อ-สกุล</th>
                    <th>ห้องเรียน</th>
                    <th>วันออกเอกสาร</th>
                    <th style="text-align:center;">พิมพ์</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studentSections as $ss)
                @php
                    $sDate = $ss->classSection?->pp2SectionSetting?->issued_date;
                @endphp
                <tr>
                    <td>{{ $ss->student?->student_code }}</td>
                    <td>{{ ($ss->student?->thai_prefix ?? '') . ($ss->student?->thai_firstname ?? '') . ' ' . ($ss->student?->thai_lastname ?? '') }}</td>
                    <td>{{ $ss->classSection?->level?->name }}/{{ $ss->classSection?->section_number }}</td>
                    <td>
                        @if($sDate)
                            <span style="color:#2e7d32;font-weight:600;">
                                {{ $sDate->locale('th')->translatedFormat('j F Y') }}
                            </span>
                        @else
                            <span style="color:#aaa;">วันปัจจุบัน</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <a href="{{ route('pp2.print', $ss->id) }}" target="_blank" class="btn-print">
                            🖨️ พิมพ์
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @elseif(request('search'))
            <p style="color:#888;">ไม่พบนักเรียนที่ค้นหา</p>
        @endif
    </div>
</div>

<script>
function toggleYear(yearId) {
    const body = document.getElementById('year-body-' + yearId);
    const arrow = document.getElementById('arrow-' + yearId);
    if (body.classList.contains('open')) {
        body.classList.remove('open');
        arrow.textContent = '▼';
    } else {
        body.classList.add('open');
        arrow.textContent = '▲';
    }
}

// เปิดปีปัจจุบันอัตโนมัติ (ปีแรกสุด)
document.addEventListener('DOMContentLoaded', function () {
    const firstYear = document.querySelector('.year-header');
    if (firstYear) firstYear.click();
});
</script>
@endsection
