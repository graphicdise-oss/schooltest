@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; max-width:820px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:24px; margin-bottom:20px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:18px; }
    label { font-weight:600; color:#444; font-size:.88rem; margin-bottom:5px; }
    .form-control, .form-select { border-radius:8px; }
    .switch-wrap { display:flex; align-items:center; gap:12px; background:#eef4ff; border-radius:10px; padding:14px 18px; }
    .btn-save { background:#4caf50; border:none; border-radius:8px; padding:11px 30px; font-weight:700; color:#fff; }
    .btn-save:hover { background:#43a047; }
    .public-link { background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:12px 14px; font-size:.9rem; word-break:break-all; }
</style>
@endpush

@section('content')
<div class="page">
    <nav class="breadcrumb-custom mb-3">
        <a href="#">รับสมัครนักเรียน</a><i class="bi bi-chevron-right"></i>
        <span style="color:#555;">ตั้งค่าการรับสมัคร</span>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card2">
        <div class="card-title"><i class="fas fa-link"></i> ลิงก์หน้ารับสมัคร (ส่งให้ผู้สมัคร)</div>
        <div class="public-link">
            <a href="{{ route('admission.form') }}" target="_blank">{{ route('admission.form') }}</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admissions.saveSettings') }}">
        @csrf
        <div class="card2">
            <div class="card-title"><i class="fas fa-sliders-h"></i> การตั้งค่ารับสมัคร</div>

            <div class="switch-wrap mb-4">
                <div class="form-check form-switch m-0">
                    <input type="checkbox" class="form-check-input" role="switch" id="is_open" name="is_open" value="1"
                           style="width:3rem;height:1.5rem;" {{ $setting->is_open ? 'checked' : '' }}>
                </div>
                <label for="is_open" class="m-0" style="font-size:1rem;">เปิดรับสมัคร (เปิด/ปิดระบบ)</label>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label>ปีการศึกษาที่รับสมัคร</label>
                    <select name="year_id" class="form-select">
                        <option value="">— ไม่ระบุ —</option>
                        @foreach($years as $y)
                            <option value="{{ $y->year_id }}" {{ (string)$setting->year_id === (string)$y->year_id ? 'selected' : '' }}>
                                ปีการศึกษา {{ $y->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>วันเริ่มรับสมัคร</label>
                    <input type="date" name="open_date" class="form-control"
                           value="{{ optional($setting->open_date)->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label>วันปิดรับสมัคร</label>
                    <input type="date" name="close_date" class="form-control"
                           value="{{ optional($setting->close_date)->format('Y-m-d') }}">
                </div>

                <div class="col-12">
                    <label>ระดับชั้นที่เปิดรับ (ข้อความ)</label>
                    <input type="text" name="levels_note" class="form-control"
                           value="{{ $setting->levels_note }}" placeholder="เช่น อนุบาล 1, ป.1, ม.1, ม.4">
                </div>

                <div class="col-12">
                    <label>คำชี้แจง / ระเบียบการ</label>
                    <textarea name="instructions" class="form-control" rows="6"
                              placeholder="รายละเอียดการรับสมัคร เอกสารที่ต้องเตรียม ฯลฯ">{{ $setting->instructions }}</textarea>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn-save"><i class="fas fa-check me-1"></i> บันทึกการตั้งค่า</button>
            </div>
        </div>
    </form>
</div>
@endsection
