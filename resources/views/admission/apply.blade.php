<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กรอกใบสมัคร — {{ config('school.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #2563eb; --brand-dark: #1e40af; --ink: #082b75; --sub: #4b6aa5;
        }
        body { font-family:'Prompt',sans-serif; background:#eef3fb; margin:0; padding-bottom:110px; }
        .hero { background:linear-gradient(135deg,#1e40af 0%,#2563eb 55%,#3b82f6 100%); padding:34px 16px 64px; text-align:center; color:#fff; position:relative; }
        .hero::after { content:""; position:absolute; left:0; right:0; bottom:-1px; height:40px; background:#eef3fb; border-radius:50% 50% 0 0 / 100% 100% 0 0; }
        .hero img.logo { height:56px; margin-bottom:8px; filter:drop-shadow(0 2px 6px rgba(0,0,0,.2)); }
        .hero h1 { font-weight:700; font-size:1.5rem; margin:4px 0 2px; }
        .hero p { opacity:.9; margin:0; font-size:.92rem; }
        .back-link { display:inline-flex; align-items:center; gap:6px; color:#fff; text-decoration:none; font-size:.85rem; opacity:.85; margin-bottom:14px; }
        .back-link:hover { opacity:1; color:#fff; }

        .wrap { max-width:780px; margin:-40px auto 0; padding:0 14px; position:relative; z-index:2; }

        .steps { display:flex; justify-content:center; gap:6px; margin-bottom:22px; flex-wrap:wrap; }
        .steps span { background:rgba(255,255,255,.9); color:var(--ink); font-size:.72rem; font-weight:600; padding:5px 12px; border-radius:20px; display:inline-flex; align-items:center; gap:5px; box-shadow:0 2px 8px rgba(8,43,117,.08); }
        .steps span i { font-size:.85rem; }

        .card2 { background:#fff; border-radius:18px; box-shadow:0 8px 28px rgba(8,43,117,.10); padding:26px 26px 22px; margin-bottom:20px; border:1px solid #eef1f8; }
        .sec-head { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
        .sec-num { width:36px; height:36px; min-width:36px; border-radius:10px; background:linear-gradient(135deg,var(--brand),var(--brand-dark)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.05rem; box-shadow:0 4px 10px rgba(37,99,235,.28); }
        .sec-titles h2 { color:var(--ink); font-weight:700; font-size:1.05rem; margin:0; }
        .sec-titles p { color:var(--sub); font-size:.8rem; margin:0; }
        .sec-optional { margin-left:auto; font-size:.72rem; color:#94a3b8; background:#f1f5f9; padding:3px 10px; border-radius:12px; white-space:nowrap; }

        label { font-weight:500; color:#334155; font-size:.86rem; margin-bottom:4px; }
        .form-control, .form-select { border-radius:9px; border-color:#dfe6f2; font-size:.92rem; padding:9px 12px; }
        .form-control:focus, .form-select:focus { border-color:var(--brand); box-shadow:0 0 0 .18rem rgba(37,99,235,.12); }
        .req { color:#e11d48; }
        .field-hint { font-size:.72rem; color:#94a3b8; margin-top:3px; }

        .submit-bar { position:fixed; left:0; right:0; bottom:0; background:#fff; border-top:1px solid #e6ebf5; box-shadow:0 -6px 20px rgba(8,43,117,.08); padding:12px 16px; z-index:10; }
        .submit-bar .inner { max-width:780px; margin:0 auto; display:flex; align-items:center; gap:14px; }
        .submit-bar .legend { font-size:.75rem; color:#64748b; flex:1; }
        .btn-submit { background:linear-gradient(135deg,var(--brand),var(--brand-dark)); border:none; border-radius:12px; padding:13px 30px; font-weight:600; color:#fff; white-space:nowrap; box-shadow:0 6px 16px rgba(37,99,235,.3); }
        .btn-submit:hover { filter:brightness(1.06); color:#fff; }

        @media (max-width: 576px) {
            .submit-bar .inner { flex-direction:column; align-items:stretch; gap:8px; }
            .submit-bar .legend { text-align:center; }
        }
    </style>
</head>
<body>

<div class="hero">
    <div class="wrap" style="margin-top:0; padding:0;">
        <a href="{{ route('admission.form') }}" class="back-link"><i class="bi bi-arrow-left"></i> กลับหน้าประชาสัมพันธ์</a>
    </div>
    @if(config('school.logo'))
        <img src="{{ asset(config('school.logo')) }}" alt="logo" class="logo" onerror="this.style.display='none'">
    @endif
    <h1>กรอกใบสมัครเรียน</h1>
    <p>{{ config('school.name') }}
        @if($setting->academicYear) · ปีการศึกษา {{ $setting->academicYear->year_name }} @endif
    </p>
</div>

<div class="wrap">

    <div class="steps">
        <span><i class="bi bi-person-badge"></i> ข้อมูลนักเรียน</span>
        <span><i class="bi bi-house-door"></i> ที่อยู่</span>
        <span><i class="bi bi-mortarboard"></i> การศึกษาเดิม</span>
        <span><i class="bi bi-heart-pulse"></i> สุขภาพ</span>
        <span><i class="bi bi-people"></i> ผู้ปกครอง</span>
    </div>

    <form method="POST" action="{{ route('admission.submit') }}">
        @csrf

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- ===================== 1. ข้อมูลนักเรียน ===================== --}}
        <div class="card2">
            <div class="sec-head">
                <div class="sec-num"><i class="bi bi-person-badge"></i></div>
                <div class="sec-titles">
                    <h2>ข้อมูลผู้สมัคร</h2>
                    <p>ข้อมูลตัวตนของนักเรียนตามบัตรประชาชน</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <label>คำนำหน้า <span class="req">*</span></label>
                    <select name="thai_prefix" class="form-select" required>
                        @foreach(['เด็กชาย','เด็กหญิง','นาย','นางสาว'] as $p)
                            <option value="{{ $p }}" {{ old('thai_prefix') === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>ชื่อ <span class="req">*</span></label>
                    <input type="text" name="thai_firstname" class="form-control" value="{{ old('thai_firstname') }}" required>
                </div>
                <div class="col-md-5">
                    <label>นามสกุล <span class="req">*</span></label>
                    <input type="text" name="thai_lastname" class="form-control" value="{{ old('thai_lastname') }}" required>
                </div>

                <div class="col-md-4">
                    <label>ชื่อเล่น</label>
                    <input type="text" name="thai_nickname" class="form-control" value="{{ old('thai_nickname') }}">
                </div>
                <div class="col-md-4">
                    <label>เพศ <span class="req">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="ชาย" {{ old('gender') === 'ชาย' ? 'selected' : '' }}>ชาย</option>
                        <option value="หญิง" {{ old('gender') === 'หญิง' ? 'selected' : '' }}>หญิง</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>วันเกิด <span class="req">*</span></label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}" required>
                </div>

                <div class="col-md-5">
                    <label>เลขบัตรประชาชน (13 หลัก) <span class="req">*</span></label>
                    <input type="text" name="id_card_number" class="form-control" value="{{ old('id_card_number') }}"
                           maxlength="13" pattern="[0-9]{13}" inputmode="numeric" required>
                </div>
                <div class="col-md-4">
                    <label>สัญชาติ</label>
                    <input type="text" name="nationality" class="form-control" value="{{ old('nationality', 'ไทย') }}">
                </div>
                <div class="col-md-3">
                    <label>เชื้อชาติ</label>
                    <input type="text" name="ethnicity" class="form-control" value="{{ old('ethnicity', 'ไทย') }}">
                </div>

                <div class="col-md-4">
                    <label>ศาสนา</label>
                    <input type="text" name="religion" class="form-control" value="{{ old('religion', 'พุทธ') }}">
                </div>
                <div class="col-md-4">
                    <label>เบอร์โทรผู้สมัคร</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="col-md-4">
                    <label>ระดับชั้นที่สมัคร</label>
                    <select name="level_id" class="form-select">
                        <option value="">— เลือก —</option>
                        @foreach($levels as $lv)
                            <option value="{{ $lv->level_id }}" {{ (string)old('level_id') === (string)$lv->level_id ? 'selected' : '' }}>{{ $lv->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ===================== 2. ที่อยู่ปัจจุบัน ===================== --}}
        <div class="card2">
            <div class="sec-head">
                <div class="sec-num"><i class="bi bi-house-door"></i></div>
                <div class="sec-titles">
                    <h2>ที่อยู่ปัจจุบัน</h2>
                    <p>ที่อยู่ที่สามารถติดต่อ/จัดส่งเอกสารได้</p>
                </div>
                <span class="sec-optional">ไม่บังคับ</span>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <label>บ้านเลขที่</label>
                    <input type="text" name="house_number" class="form-control" value="{{ old('house_number') }}">
                </div>
                <div class="col-md-2">
                    <label>หมู่ที่</label>
                    <input type="text" name="village_no" class="form-control" value="{{ old('village_no') }}">
                </div>
                <div class="col-md-3">
                    <label>ซอย</label>
                    <input type="text" name="soi" class="form-control" value="{{ old('soi') }}">
                </div>
                <div class="col-md-4">
                    <label>ถนน</label>
                    <input type="text" name="road" class="form-control" value="{{ old('road') }}">
                </div>

                <div class="col-md-4">
                    <label>ตำบล/แขวง</label>
                    <input type="text" name="subdistrict_id" class="form-control" value="{{ old('subdistrict_id') }}">
                </div>
                <div class="col-md-4">
                    <label>อำเภอ/เขต</label>
                    <input type="text" name="district_id" class="form-control" value="{{ old('district_id') }}">
                </div>
                <div class="col-md-4">
                    <label>จังหวัด</label>
                    <input type="text" name="province_id" class="form-control" value="{{ old('province_id') }}">
                </div>

                <div class="col-md-4">
                    <label>รหัสไปรษณีย์</label>
                    <input type="text" name="postal_code" class="form-control" maxlength="10" value="{{ old('postal_code') }}">
                </div>
                <div class="col-md-4">
                    <label>เบอร์โทรที่บ้าน</label>
                    <input type="text" name="home_phone" class="form-control" value="{{ old('home_phone') }}">
                </div>
            </div>
        </div>

        {{-- ===================== 3. การศึกษาเดิม ===================== --}}
        <div class="card2">
            <div class="sec-head">
                <div class="sec-num"><i class="bi bi-mortarboard"></i></div>
                <div class="sec-titles">
                    <h2>ข้อมูลการศึกษาเดิม</h2>
                    <p>โรงเรียน/ระดับชั้นล่าสุดที่กำลังศึกษาหรือจบมา</p>
                </div>
                <span class="sec-optional">ไม่บังคับ</span>
            </div>

            <div class="row g-3">
                <div class="col-md-8">
                    <label>สถานศึกษาเดิม</label>
                    <input type="text" name="school_name" class="form-control" value="{{ old('school_name') }}" placeholder="เช่น โรงเรียน...">
                </div>
                <div class="col-md-4">
                    <label>ระดับชั้นล่าสุด</label>
                    <input type="text" name="education_level" class="form-control" value="{{ old('education_level') }}" placeholder="เช่น อนุบาล 3">
                </div>
            </div>
        </div>

        {{-- ===================== 4. ข้อมูลสุขภาพ ===================== --}}
        <div class="card2">
            <div class="sec-head">
                <div class="sec-num"><i class="bi bi-heart-pulse"></i></div>
                <div class="sec-titles">
                    <h2>ข้อมูลสุขภาพ</h2>
                    <p>ใช้สำหรับดูแลนักเรียนเบื้องต้น</p>
                </div>
                <span class="sec-optional">ไม่บังคับ</span>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <label>กรุ๊ปเลือด</label>
                    <select name="blood_group" class="form-select">
                        <option value="">— ไม่ทราบ —</option>
                        @foreach(['A','B','AB','O'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>โรคประจำตัว</label>
                    <input type="text" name="chronic_disease" class="form-control" value="{{ old('chronic_disease') }}">
                </div>
                <div class="col-md-3">
                    <label>แพ้อาหาร</label>
                    <input type="text" name="food_allergy" class="form-control" value="{{ old('food_allergy') }}">
                </div>
                <div class="col-md-3">
                    <label>แพ้ยา</label>
                    <input type="text" name="medicine_allergy" class="form-control" value="{{ old('medicine_allergy') }}">
                </div>
            </div>
        </div>

        {{-- ===================== 5. ข้อมูลผู้ปกครอง ===================== --}}
        <div class="card2">
            <div class="sec-head">
                <div class="sec-num"><i class="bi bi-people"></i></div>
                <div class="sec-titles">
                    <h2>ข้อมูลผู้ปกครอง</h2>
                    <p>ผู้ที่โรงเรียนสามารถติดต่อได้</p>
                </div>
                <span class="sec-optional">ไม่บังคับ</span>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <label>เกี่ยวข้องเป็น</label>
                    <select name="guardian_type" class="form-select">
                        @foreach(['ผู้ปกครอง','บิดา','มารดา'] as $gt)
                            <option value="{{ $gt }}" {{ old('guardian_type', 'ผู้ปกครอง') === $gt ? 'selected' : '' }}>{{ $gt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>คำนำหน้า</label>
                    <select name="g_prefix" class="form-select">
                        @foreach(['','นาย','นาง','นางสาว'] as $p)
                            <option value="{{ $p }}" {{ old('g_prefix') === $p ? 'selected' : '' }}>{{ $p === '' ? '—' : $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>ชื่อ</label>
                    <input type="text" name="g_firstname" class="form-control" value="{{ old('g_firstname') }}">
                </div>
                <div class="col-md-4">
                    <label>นามสกุล</label>
                    <input type="text" name="g_lastname" class="form-control" value="{{ old('g_lastname') }}">
                </div>

                <div class="col-md-3">
                    <label>ความเกี่ยวข้อง (ระบุเพิ่มเติม)</label>
                    <input type="text" name="g_relationship" class="form-control" value="{{ old('g_relationship') }}" placeholder="เช่น ปู่ / ย่า / ญาติ">
                </div>
                <div class="col-md-3">
                    <label>เบอร์โทรผู้ปกครอง</label>
                    <input type="text" name="g_phone" class="form-control" value="{{ old('g_phone') }}">
                </div>
                <div class="col-md-3">
                    <label>อาชีพ</label>
                    <input type="text" name="g_occupation" class="form-control" value="{{ old('g_occupation') }}">
                </div>
                <div class="col-md-3">
                    <label>สถานที่ทำงาน</label>
                    <input type="text" name="g_workplace" class="form-control" value="{{ old('g_workplace') }}">
                </div>
            </div>
        </div>

        <div class="submit-bar">
            <div class="inner">
                <div class="legend"><span class="req">*</span> จำเป็นต้องกรอก ส่วนที่เหลือกรอกเพิ่มเติมได้ภายหลัง</div>
                <button type="submit" class="btn btn-submit"><i class="bi bi-send-fill me-1"></i> ส่งใบสมัคร</button>
            </div>
        </div>
    </form>
</div>
</body>
</html>
