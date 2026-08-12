@extends('layouts.sidebar')

@push('styles')
<style>
    .pf-page { padding: 24px 28px; min-height: 100%; max-width: 720px; }
    .pf-breadcrumb { font-size: 0.85rem; color: #999; margin-bottom: 20px; }
    .pf-breadcrumb span { color: #4b7ce3; font-weight: 600; }

    .pf-card {
        background: #fff; border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        padding: 30px 28px; margin-top: 50px;
    }
    .pf-icon {
        position: absolute; top: -25px; left: 24px;
        width: 70px; height: 70px; border-radius: 6px;
        background: #5482e7; display: flex; align-items: center; justify-content: center;
        font-size: 30px; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .pf-title { margin-left: 90px; font-size: 1.05rem; color: #444; font-weight: 700; margin-top: -6px; margin-bottom: 24px; }

    .pf-photo-row { display: flex; align-items: center; gap: 20px; margin-bottom: 24px; }
    .pf-photo {
        width: 90px; height: 90px; border-radius: 50%; object-fit: cover;
        border: 3px solid #92b2f2; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .pf-photo-placeholder {
        width: 90px; height: 90px; border-radius: 50%; background: #eef2f9; color: #9aa7bd;
        display: flex; align-items: center; justify-content: center; font-size: 2rem;
        border: 3px solid #92b2f2;
    }
    .pf-photo-input { font-size: 0.82rem; color: #666; }

    .pf-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 24px; }
    .pf-row { display: flex; flex-direction: column; gap: 5px; }
    .pf-row.full { grid-column: 1 / -1; }
    .pf-label { font-size: 0.82rem; font-weight: 600; color: #555; }
    .pf-input, .pf-select {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 9px 12px;
        font-size: 0.88rem; color: #333; font-family: inherit; outline: none;
        box-sizing: border-box; transition: border 0.2s;
    }
    .pf-input:focus, .pf-select:focus { border-color: #5482e7; box-shadow: 0 0 0 3px rgba(84,130,231,0.12); }

    .pf-actions { margin-top: 28px; display: flex; justify-content: flex-end; }
    .btn-pf-save {
        background: #5482e7; color: #fff; border: none; border-radius: 6px;
        padding: 10px 32px; font-size: 0.9rem; font-weight: 600; cursor: pointer;
        font-family: inherit; display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-pf-save:hover { background: #446bca; }
</style>
@endpush

@section('content')
<div class="pf-page">

    <div class="pf-breadcrumb">
        บัญชีผู้ใช้ &rsaquo; <span>แก้ไขข้อมูลส่วนตัว</span>
    </div>

    @if(session('success'))
    <div style="background:#e8f5e9;color:#2e7d32;padding:10px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div style="background:#fdecea;color:#b71c1c;padding:10px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem">
        <i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}
    </div>
    @endif

    <div class="pf-card" style="position:relative;">
        <div class="pf-icon"><i class="fa-solid fa-user-pen"></i></div>
        <div class="pf-title">แก้ไขข้อมูลส่วนตัว</div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="pf-photo-row">
                @if($personnel->personnel_image)
                    <img src="{{ asset('storage/' . $personnel->personnel_image) }}" alt="รูปโปรไฟล์" class="pf-photo">
                @else
                    <div class="pf-photo-placeholder"><i class="fa-solid fa-user"></i></div>
                @endif
                <div>
                    <div class="pf-label" style="margin-bottom:6px;">รูปโปรไฟล์</div>
                    <input type="file" name="photo" accept="image/*" class="pf-photo-input">
                </div>
            </div>

            <div class="pf-grid">
                <div class="pf-row">
                    <label class="pf-label">คำนำหน้า</label>
                    <input type="text" name="thai_prefix" class="pf-input" value="{{ old('thai_prefix', $personnel->thai_prefix) }}">
                </div>
                <div class="pf-row"></div>

                <div class="pf-row">
                    <label class="pf-label">ชื่อ (ไทย) <span style="color:red">*</span></label>
                    <input type="text" name="thai_firstname" required class="pf-input" value="{{ old('thai_firstname', $personnel->thai_firstname) }}">
                </div>
                <div class="pf-row">
                    <label class="pf-label">นามสกุล (ไทย) <span style="color:red">*</span></label>
                    <input type="text" name="thai_lastname" required class="pf-input" value="{{ old('thai_lastname', $personnel->thai_lastname) }}">
                </div>

                <div class="pf-row">
                    <label class="pf-label">ชื่อ (English)</label>
                    <input type="text" name="eng_firstname" class="pf-input" value="{{ old('eng_firstname', $personnel->eng_firstname) }}">
                </div>
                <div class="pf-row">
                    <label class="pf-label">นามสกุล (English)</label>
                    <input type="text" name="eng_lastname" class="pf-input" value="{{ old('eng_lastname', $personnel->eng_lastname) }}">
                </div>

                <div class="pf-row">
                    <label class="pf-label">เบอร์โทรศัพท์</label>
                    <input type="text" name="phone" class="pf-input" value="{{ old('phone', $personnel->phone) }}">
                </div>
                <div class="pf-row">
                    <label class="pf-label">อีเมล</label>
                    <input type="email" name="email" class="pf-input" value="{{ old('email', $personnel->email) }}">
                </div>

                <div class="pf-row">
                    <label class="pf-label">วันเกิด</label>
                    <input type="date" name="date_of_birth" class="pf-input"
                        value="{{ old('date_of_birth', $personnel->date_of_birth?->format('Y-m-d')) }}">
                </div>
                <div class="pf-row">
                    <label class="pf-label">กรุ๊ปเลือด</label>
                    <input type="text" name="blood_group" class="pf-input" value="{{ old('blood_group', $personnel->blood_group) }}">
                </div>

                <div class="pf-row">
                    <label class="pf-label">สัญชาติ</label>
                    <input type="text" name="nationality" class="pf-input" value="{{ old('nationality', $personnel->nationality) }}">
                </div>
                <div class="pf-row">
                    <label class="pf-label">เชื้อชาติ</label>
                    <input type="text" name="ethnicity" class="pf-input" value="{{ old('ethnicity', $personnel->ethnicity) }}">
                </div>

                <div class="pf-row">
                    <label class="pf-label">ศาสนา</label>
                    <input type="text" name="religion" class="pf-input" value="{{ old('religion', $personnel->religion) }}">
                </div>
            </div>

            <div class="pf-actions">
                <button type="submit" class="btn-pf-save"><i class="bi bi-check-lg"></i> บันทึกข้อมูล</button>
            </div>
        </form>
    </div>
</div>
@endsection
