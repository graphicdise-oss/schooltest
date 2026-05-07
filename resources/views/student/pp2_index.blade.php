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
    .pp2-header {
        margin-left: 90px; font-size: 1.15rem;
        color: #555; margin-top: -10px;
    }
    .pp2-header h5 { font-size: 1.3rem; font-weight: 600; color: #333; margin-bottom: 2px; }

    .form-label { font-weight: 600; color: #444; margin-bottom: 4px; }
    .form-control { border-radius: 4px; border: 1px solid #ccc; }
    .form-control:focus { border-color: #3f51b5; box-shadow: 0 0 0 2px rgba(63,81,181,0.15); }

    .btn-save {
        background: #3f51b5; color: #fff; border: none;
        padding: 8px 24px; border-radius: 4px; font-size: 0.95rem;
        cursor: pointer;
    }
    .btn-save:hover { background: #303f9f; color: #fff; }

    .alert-success {
        background: #e8f5e9; border: 1px solid #a5d6a7;
        color: #2e7d32; border-radius: 4px; padding: 10px 16px;
        margin-bottom: 16px;
    }
</style>
@endpush

@section('content')
<div class="pp2-page">
    <div class="pp2-card">
        <div class="pp2-icon">📄</div>
        <div class="pp2-header">
            <h5>ตั้งค่าใบ ปพ.2</h5>
            <span style="color:#888;font-size:0.9rem;">ข้อมูลโรงเรียนที่แสดงในใบประกาศนียบัตร</span>
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

            <div style="margin-top:24px;">
                <button type="submit" class="btn-save">💾 บันทึก</button>
            </div>
        </form>
    </div>

    {{-- ส่วนค้นหานักเรียนเพื่อพิมพ์ ปพ.2 --}}
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

        @if(isset($studentSections) && $studentSections->count() > 0)
        <table style="width:100%;border-collapse:collapse;font-size:0.95rem;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="padding:10px;border-bottom:2px solid #ddd;text-align:left;">รหัส</th>
                    <th style="padding:10px;border-bottom:2px solid #ddd;text-align:left;">ชื่อ-สกุล</th>
                    <th style="padding:10px;border-bottom:2px solid #ddd;text-align:left;">ระดับชั้น</th>
                    <th style="padding:10px;border-bottom:2px solid #ddd;text-align:center;">พิมพ์</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studentSections as $ss)
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:10px;">{{ $ss->student?->student_code }}</td>
                    <td style="padding:10px;">
                        {{ ($ss->student?->thai_prefix ?? '') . ($ss->student?->thai_firstname ?? '') . ' ' . ($ss->student?->thai_lastname ?? '') }}
                    </td>
                    <td style="padding:10px;">{{ $ss->classSection?->level?->name }}</td>
                    <td style="padding:10px;text-align:center;">
                        <a href="{{ route('pp2.print', $ss->id) }}" target="_blank"
                            style="background:#e91e63;color:#fff;padding:5px 14px;border-radius:4px;text-decoration:none;font-size:0.9rem;">
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
@endsection
