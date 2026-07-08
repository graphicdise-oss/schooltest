@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; max-width:860px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:24px; margin-bottom:20px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:18px; }
    label { font-weight:600; color:#444; font-size:.88rem; margin-bottom:5px; }
    .form-control, .form-select { border-radius:8px; }
    .switch-wrap { display:flex; align-items:center; gap:12px; background:#eef4ff; border-radius:10px; padding:14px 18px; }
    .btn-save { background:#4caf50; border:none; border-radius:8px; padding:11px 30px; font-weight:700; color:#fff; }
    .btn-save:hover { background:#43a047; }
    .btn-up { background:#2563eb; border:none; border-radius:8px; padding:9px 20px; font-weight:600; color:#fff; }
    .public-link { background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:12px 14px; font-size:.9rem; word-break:break-all; }
    .doc-row { display:flex; align-items:center; gap:12px; padding:10px 12px; border:1px solid #eef1f7; border-radius:8px; margin-bottom:8px; }
    .doc-row .t { flex:1; color:#082b75; }
    .doc-row .lv { background:#eef4ff; color:#2563eb; border-radius:20px; padding:2px 10px; font-size:.78rem; }
    .banner-prev { max-height:130px; border-radius:10px; border:1px solid #e6ebf5; margin-bottom:10px; }
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
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card2">
        <div class="card-title"><i class="fas fa-link"></i> ลิงก์หน้ารับสมัคร (ส่งให้ผู้สมัคร)</div>
        <div class="public-link">
            <a href="{{ route('admission.form') }}" target="_blank">{{ route('admission.form') }}</a>
        </div>
    </div>

    {{-- ===== การตั้งค่า + ประชาสัมพันธ์ ===== --}}
    <form method="POST" action="{{ route('admissions.saveSettings') }}" enctype="multipart/form-data">
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
                    <input type="date" name="open_date" class="form-control" value="{{ optional($setting->open_date)->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label>วันปิดรับสมัคร</label>
                    <input type="date" name="close_date" class="form-control" value="{{ optional($setting->close_date)->format('Y-m-d') }}">
                </div>

                <div class="col-12">
                    <label>ระดับชั้นที่เปิดรับ (ข้อความ)</label>
                    <input type="text" name="levels_note" class="form-control" value="{{ $setting->levels_note }}" placeholder="เช่น อนุบาล 1, ป.1, ม.1, ม.4">
                </div>
            </div>
        </div>

        <div class="card2">
            <div class="card-title"><i class="fas fa-bullhorn"></i> เนื้อหาหน้าประชาสัมพันธ์</div>
            <div class="row g-3">
                <div class="col-12">
                    <label>รูปแบนเนอร์ (โปสเตอร์รับสมัคร)</label>
                    @if($setting->banner_image)
                        <div><img src="{{ asset('storage/' . $setting->banner_image) }}" class="banner-prev" alt="banner"></div>
                    @endif
                    <input type="file" name="banner_image" class="form-control" accept="image/*">
                    <small class="text-muted">ไฟล์รูปภาพ ไม่เกิน 4 MB (อัปโหลดใหม่เพื่อเปลี่ยน)</small>
                </div>
                <div class="col-12">
                    <label>คำชี้แจง / ประชาสัมพันธ์</label>
                    <textarea name="instructions" class="form-control" rows="5" placeholder="รายละเอียดการรับสมัคร กำหนดการ ฯลฯ">{{ $setting->instructions }}</textarea>
                </div>
                <div class="col-12">
                    <label>เอกสาร/หลักฐานที่ต้องเตรียม</label>
                    <textarea name="required_docs" class="form-control" rows="4" placeholder="เช่น&#10;- สำเนาทะเบียนบ้าน 1 ฉบับ&#10;- สำเนาสูติบัตร 1 ฉบับ&#10;- รูปถ่าย 1 นิ้ว 2 รูป">{{ $setting->required_docs }}</textarea>
                </div>
            </div>
            <div class="mt-4 text-end">
                <button type="submit" class="btn-save"><i class="fas fa-check me-1"></i> บันทึกการตั้งค่า</button>
            </div>
        </div>
    </form>

    {{-- ===== รูปในคำชี้แจง ===== --}}
    <div class="card2">
        <div class="card-title"><i class="fas fa-image"></i> รูปในคำชี้แจง (โปสเตอร์ / QR ค่าสมัคร ฯลฯ)</div>

        <form method="POST" action="{{ route('admissions.imgUpload') }}" enctype="multipart/form-data" class="row g-2 align-items-end mb-3">
            @csrf
            <div class="col-md-5">
                <label>คำอธิบายรูป (ไม่บังคับ)</label>
                <input type="text" name="title" class="form-control" placeholder="เช่น QR ค่าสมัคร">
            </div>
            <div class="col-md-5">
                <label>เลือกรูป</label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-up w-100"><i class="fas fa-upload"></i> เพิ่มรูป</button>
            </div>
        </form>

        @forelse($images as $img)
            <div class="doc-row">
                <img src="{{ asset('storage/' . $img->file_path) }}" style="width:54px;height:54px;object-fit:cover;border-radius:8px;" alt="">
                <span class="t">{{ $img->title }}</span>
                <form method="POST" action="{{ route('admissions.docDelete', $img->id) }}" onsubmit="return confirm('ลบรูปนี้?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="ลบ"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        @empty
            <p class="text-muted mb-0">ยังไม่มีรูปในคำชี้แจง</p>
        @endforelse
    </div>

    {{-- ===== ไฟล์ระเบียบการตามระดับชั้น ===== --}}
    <div class="card2">
        <div class="card-title"><i class="fas fa-paperclip"></i> ไฟล์ระเบียบการ / เอกสารแนบ (ตามระดับชั้น)</div>

        <form method="POST" action="{{ route('admissions.docUpload') }}" enctype="multipart/form-data" class="row g-2 align-items-end mb-3">
            @csrf
            <div class="col-md-4">
                <label>หัวข้อ/ชื่อไฟล์</label>
                <input type="text" name="title" class="form-control" placeholder="เช่น ระเบียบการ ม.1" required>
            </div>
            <div class="col-md-3">
                <label>ระดับชั้น</label>
                <select name="level_id" class="form-select">
                    <option value="">ทั่วไป</option>
                    @foreach($levels as $lv)
                        <option value="{{ $lv->level_id }}">{{ $lv->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>ไฟล์ (PDF/รูป)</label>
                <input type="file" name="file" class="form-control" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-up w-100"><i class="fas fa-upload"></i> อัปโหลด</button>
            </div>
        </form>

        @forelse($documents as $doc)
            <div class="doc-row">
                <span style="color:#dc2626;">📄</span>
                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="t" style="text-decoration:none;">{{ $doc->title }}</a>
                <span class="lv">{{ $doc->level->name ?? 'ทั่วไป' }}</span>
                <form method="POST" action="{{ route('admissions.docDelete', $doc->id) }}" onsubmit="return confirm('ลบไฟล์นี้?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="ลบ"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        @empty
            <p class="text-muted mb-0">ยังไม่มีไฟล์แนบ</p>
        @endforelse
    </div>

</div>
@endsection
