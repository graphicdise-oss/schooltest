<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รับสมัครนักเรียนออนไลน์ — {{ config('school.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background: #eaf4ff; padding: 24px 12px; }
        .wrap { max-width: 720px; margin: 0 auto; }
        .card2 { background:#fff; border-radius:16px; box-shadow:0 6px 24px rgba(8,43,117,.10); padding:28px; margin-bottom:18px; }
        .head { text-align:center; margin-bottom:22px; }
        .head h1 { color:#082b75; font-weight:700; font-size:1.5rem; margin:6px 0 2px; }
        .head p { color:#4b6aa5; margin:0; }
        .sec-title { color:#082b75; font-weight:600; border-left:4px solid #4b7ce3; padding-left:10px; margin:18px 0 12px; }
        label { font-weight:500; color:#334155; font-size:.92rem; }
        .form-control, .form-select { border-radius:8px; }
        .req { color:#e11d48; }
        .btn-submit { background:#2563eb; border:none; border-radius:10px; padding:12px; font-weight:600; width:100%; }
        .btn-submit:hover { background:#1e4fd0; }
        .closed { text-align:center; padding:40px 20px; }
        .closed i { font-size:48px; color:#f59e0b; }
        .instructions { white-space:pre-wrap; color:#475569; background:#f8fafc; border-radius:10px; padding:14px; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="head">
        @if(config('school.logo'))
            <img src="{{ asset(config('school.logo')) }}" alt="logo" style="height:70px;" onerror="this.style.display='none'">
        @endif
        <h1>รับสมัครนักเรียนออนไลน์</h1>
        <p>{{ config('school.name') }}
            @if($setting->academicYear) · ปีการศึกษา {{ $setting->academicYear->year_name }} @endif
        </p>
    </div>

    @if(session('error'))
        <div class="alert alert-warning">{{ session('error') }}</div>
    @endif

    @if(!$setting->isAcceptingNow())
        <div class="card2 closed">
            <i class="bi">⚠️</i>
            <h4 class="mt-3" style="color:#082b75;">ขณะนี้ยังไม่เปิดรับสมัคร</h4>
            <p class="text-muted mb-0">
                @if($setting->open_date && now()->startOfDay()->lt($setting->open_date))
                    จะเปิดรับสมัครวันที่ {{ $setting->open_date->format('d/m/') }}{{ $setting->open_date->year + 543 }}
                @elseif($setting->close_date && now()->startOfDay()->gt($setting->close_date))
                    ปิดรับสมัครแล้วเมื่อวันที่ {{ $setting->close_date->format('d/m/') }}{{ $setting->close_date->year + 543 }}
                @else
                    กรุณาติดต่อทางโรงเรียน
                @endif
            </p>
        </div>
    @else

        @if($setting->instructions)
            <div class="card2">
                <div class="sec-title">คำชี้แจง / ระเบียบการ</div>
                <div class="instructions">{{ $setting->instructions }}</div>
                @if($setting->levels_note)
                    <p class="mt-3 mb-0"><strong>ระดับชั้นที่เปิดรับ:</strong> {{ $setting->levels_note }}</p>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('admission.submit') }}">
            @csrf
            <div class="card2">
                <div class="sec-title">ข้อมูลผู้สมัคร</div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

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

                    <div class="col-md-3">
                        <label>เพศ <span class="req">*</span></label>
                        <select name="gender" class="form-select" required>
                            <option value="ชาย" {{ old('gender') === 'ชาย' ? 'selected' : '' }}>ชาย</option>
                            <option value="หญิง" {{ old('gender') === 'หญิง' ? 'selected' : '' }}>หญิง</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label>เลขบัตรประชาชน (13 หลัก) <span class="req">*</span></label>
                        <input type="text" name="id_card_number" class="form-control" value="{{ old('id_card_number') }}"
                               maxlength="13" pattern="[0-9]{13}" inputmode="numeric" required>
                    </div>
                    <div class="col-md-4">
                        <label>วันเกิด <span class="req">*</span></label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}" required>
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

            <div class="card2">
                <div class="sec-title">ข้อมูลผู้ปกครอง</div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>คำนำหน้า</label>
                        <select name="g_prefix" class="form-select">
                            @foreach(['','นาย','นาง','นางสาว'] as $p)
                                <option value="{{ $p }}" {{ old('g_prefix') === $p ? 'selected' : '' }}>{{ $p === '' ? '—' : $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>ชื่อ</label>
                        <input type="text" name="g_firstname" class="form-control" value="{{ old('g_firstname') }}">
                    </div>
                    <div class="col-md-5">
                        <label>นามสกุล</label>
                        <input type="text" name="g_lastname" class="form-control" value="{{ old('g_lastname') }}">
                    </div>
                    <div class="col-md-4">
                        <label>ความเกี่ยวข้อง</label>
                        <input type="text" name="g_relationship" class="form-control" value="{{ old('g_relationship') }}" placeholder="เช่น บิดา / มารดา">
                    </div>
                    <div class="col-md-4">
                        <label>เบอร์โทรผู้ปกครอง</label>
                        <input type="text" name="g_phone" class="form-control" value="{{ old('g_phone') }}">
                    </div>
                </div>
            </div>

            <div class="card2">
                <button type="submit" class="btn btn-primary btn-submit">ส่งใบสมัคร</button>
            </div>
        </form>
    @endif

</div>
</body>
</html>
