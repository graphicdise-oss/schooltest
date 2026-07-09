@extends('layouts.sidebar')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/personnel/detail.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>
    <style>
        /* บังคับจัดกึ่งกลางการ์ดโปรไฟล์ (รูป + ชื่อ + ปุ่ม) */
        .pn-profile-card { display:flex !important; flex-direction:column; align-items:center; text-align:center; }
        .pn-profile-img-wrap { display:flex; justify-content:center; width:100%; }
        .pn-photo-actions { text-align:center; margin-top:10px; display:flex; gap:8px; justify-content:center; flex-wrap:wrap; }
        .pn-btn-photo { background:#6c757d; color:#fff; border:none; border-radius:6px; padding:6px 14px; font-size:.85rem; cursor:pointer; }
        .pn-btn-photo:hover { background:#5c636a; }
        .pn-btn-photo.save { background:#16a34a; }
        .pn-btn-photo.save:hover { background:#12833c; }
        .pn-crop-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; }
        .pn-crop-overlay.open { display:flex; }
        .pn-crop-box { background:#fff; border-radius:12px; width:520px; max-width:94vw; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.3); }
        .pn-crop-head { background:#082b75; color:#fff; padding:12px 16px; font-weight:600; }
        .pn-crop-body { padding:14px; }
        .pn-crop-body img { max-width:100%; display:block; max-height:60vh; }
        .pn-crop-foot { padding:12px 16px; display:flex; justify-content:flex-end; gap:8px; }
    </style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ===== ครอป/บันทึกรูปบุคลากร =====
    let _pnCropper = null;
    let _pnCroppedData = '';

    function pnOpenCropper(e) {
        const file = e.target.files[0]; if (!file) return;
        const reader = new FileReader();
        reader.onload = function () {
            const img = document.getElementById('pn-cropper-image');
            img.src = reader.result;
            document.getElementById('pn-crop-overlay').classList.add('open');
            setTimeout(function () {
                if (_pnCropper) { _pnCropper.destroy(); }
                _pnCropper = new Cropper(img, { aspectRatio: 1, viewMode: 1, autoCropArea: 1, background: false });
            }, 200);
        };
        reader.readAsDataURL(file);
        e.target.value = '';
    }
    function pnCloseCropper() {
        document.getElementById('pn-crop-overlay').classList.remove('open');
        if (_pnCropper) { _pnCropper.destroy(); _pnCropper = null; }
    }
    function pnApplyCrop() {
        if (!_pnCropper) return;
        const canvas = _pnCropper.getCroppedCanvas({ width: 400, height: 400, imageSmoothingQuality: 'high' });
        _pnCroppedData = canvas.toDataURL('image/jpeg', 0.9);
        document.getElementById('pn-preview').src = _pnCroppedData;
        pnCloseCropper();
    }
    function pnSavePhoto() {
        if (!_pnCroppedData) { alert('กรุณาเลือกรูปและครอปก่อน'); return; }
        const btn = document.getElementById('pn-btn-save-photo');
        fetch(btn.dataset.url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ image_cropped: _pnCroppedData })
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                document.getElementById('pn-preview').src = res.url + '?t=' + Date.now();
                if (window.Swal) Swal.fire({ icon: 'success', title: 'บันทึกรูปสำเร็จ', timer: 1200, showConfirmButton: false });
                else alert('บันทึกรูปสำเร็จ');
            } else { alert(res.message || 'บันทึกไม่สำเร็จ'); }
        })
        .catch(() => alert('เกิดข้อผิดพลาดในการบันทึกรูป'));
    }
</script>
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ!',
            text: '{{ session('success') }}',
            timer: 2500,
            showConfirmButton: false
        });
    });
</script>
@endif
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'error',
            title: 'ไม่สามารถบันทึกได้',
            html: '<ul style="text-align:left;margin:0;padding-left:20px">' +
                @foreach($errors->all() as $error)
                    '<li>{{ $error }}</li>' +
                @endforeach
            '',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#e53935',
        });
    });
</script>
@endif
@endpush

@section('content')
<div class="pn-page" x-data="{ activeTab: 'profile' }">

    {{-- Breadcrumb --}}
    <nav class="pn-breadcrumb">
        <a href="#">ข้อมูลบุคคล</a>
        <i class="bi bi-chevron-right"></i>
        <a href="#">บุคลากร - อาจารย์</a>
        <i class="bi bi-chevron-right"></i>
        <span>{{ isset($personnel) ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูล' }}บุคลากร</span>
    </nav>

    {{-- ===== รูปโปรไฟล์ ===== --}}
    @php
        $pnPlaceholder = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns=%27http://www.w3.org/2000/svg%27%20width=%27150%27%20height=%27150%27%3E%3Crect%20width=%27150%27%20height=%27150%27%20fill=%27%23eef1f7%27/%3E%3Ccircle%20cx=%2775%27%20cy=%2758%27%20r=%2728%27%20fill=%27%23c3ccdb%27/%3E%3Cpath%20d=%27M35%20126c0-23%2018-35%2040-35s40%2012%2040%2035z%27%20fill=%27%23c3ccdb%27/%3E%3C/svg%3E';
    @endphp
    <div class="pn-profile-card">
        <div class="pn-profile-img-wrap">
            <img src="{{ (isset($personnel) && $personnel->personnel_image) ? asset('storage/' . $personnel->personnel_image) : $pnPlaceholder }}"
                 alt="รูปบุคลากร" class="pn-profile-img" id="pn-preview"
                 onerror="this.onerror=null;this.src='{{ $pnPlaceholder }}';">
        </div>
        <div class="pn-profile-name">
            {{ ($personnel->thai_prefix ?? '') . ' ' . ($personnel->thai_firstname ?? 'ชื่อ') . ' ' . ($personnel->thai_lastname ?? 'นามสกุล') }}
        </div>
        <div class="pn-profile-role">{{ $personnel->position ?? 'ตำแหน่ง' }} — {{ $personnel->department ?? 'แผนก' }}</div>

        <input type="file" id="pn-image-upload" accept="image/*" style="display:none" onchange="pnOpenCropper(event)">
        <div class="pn-photo-actions">
            <button type="button" class="pn-btn-photo" onclick="document.getElementById('pn-image-upload').click()">
                <i class="bi bi-crop"></i> อัปโหลด/ครอปรูป
            </button>
            @if(isset($personnel))
            <button type="button" class="pn-btn-photo save" id="pn-btn-save-photo"
                data-url="{{ route('personnels.photo', $personnel->personnel_id) }}" onclick="pnSavePhoto()">
                <i class="bi bi-save"></i> บันทึกรูป
            </button>
            @endif
        </div>
    </div>

    {{-- Modal ครอปรูปบุคลากร --}}
    <div class="pn-crop-overlay" id="pn-crop-overlay">
        <div class="pn-crop-box">
            <div class="pn-crop-head"><i class="bi bi-crop"></i> ครอปรูปบุคลากร</div>
            <div class="pn-crop-body"><img id="pn-cropper-image"></div>
            <div class="pn-crop-foot">
                <button type="button" class="pn-btn-photo" onclick="pnCloseCropper()">ยกเลิก</button>
                <button type="button" class="pn-btn-photo save" onclick="pnApplyCrop()"><i class="bi bi-check-lg"></i> ใช้รูปนี้</button>
            </div>
        </div>
    </div>

    {{-- ===== Tab Navigation ===== --}}
    <div class="pn-tabs">
        <button @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'pn-tab-active' : ''" class="pn-tab">
            <i class="bi bi-person-vcard"></i> ข้อมูลพื้นฐานประวัติ
        </button>
        <button @click="activeTab = 'education'" :class="activeTab === 'education' ? 'pn-tab-active' : ''" class="pn-tab"
            {{ !isset($personnel) ? 'disabled' : '' }}>
            <i class="bi bi-mortarboard"></i> การศึกษา/อบรม/ดูงาน
        </button>
        <button @click="activeTab = 'position'" :class="activeTab === 'position' ? 'pn-tab-active' : ''" class="pn-tab"
            {{ !isset($personnel) ? 'disabled' : '' }}>
            <i class="bi bi-briefcase"></i> ตำแหน่งงาน/การสอน
        </button>
        <button @click="activeTab = 'license'" :class="activeTab === 'license' ? 'pn-tab-active' : ''" class="pn-tab"
            {{ !isset($personnel) ? 'disabled' : '' }}>
            <i class="bi bi-award"></i> ใบอนุญาต/เครื่องราชฯ
        </button>
    </div>

    {{-- ╔══════════════════════════════════════════╗
       ║  TAB 1 — ข้อมูลพื้นฐานประวัติ + ที่อยู่    ║
       ╚══════════════════════════════════════════╝ --}}
    {{-- ╔══════════════════════════════════════════╗
       ║  TAB 1 — ข้อมูลพื้นฐานประวัติ + ที่อยู่    ║
       ╚══════════════════════════════════════════╝ --}}
    <div x-show="activeTab === 'profile'" x-cloak>
        <form method="POST"
              action="{{ isset($personnel) ? route('personnels.update', $personnel->personnel_id) : route('personnels.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if(isset($personnel)) @method('PUT') @endif

            {{-- 1. ประวัติส่วนตัว --}}
            <div class="pn-card">
                <div class="pn-card-header"><i class="bi bi-person-lines-fill"></i> ประวัติส่วนตัว</div>
                <div class="pn-card-body">
                    <div class="pn-grid-3">
                        <div class="pn-field">
                            <label>ประเภทบุคลากร</label>
                            <select name="personnel_type" class="pn-select">
                                <option value="">-- เลือก --</option>
                                @foreach(['ครู','ครูพิเศษ','บุคลากร','ลูกจ้าง'] as $t)
                                    <option value="{{ $t }}" {{ old('personnel_type', $personnel->personnel_type ?? '') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="pn-field"><label>รหัสพนักงาน</label><input type="text" name="employee_code" class="pn-input" value="{{ old('employee_code', $personnel->employee_code ?? '') }}"></div>
                     <div class="pn-field">
                        <label>ตำแหน่ง</label>
                        <select name="position" class="pn-select">
                            <option value="">-- เลือกตำแหน่ง --</option>
                            @foreach(\App\Models\Personne\Position::where('is_active', true)->orderBy('sort_order')->get() as $pos)
                                <option value="{{ $pos->name }}"
                                    {{ old('position', $personnel->position ?? '') == $pos->name ? 'selected' : '' }}>
                                    {{ $pos->name }} ({{ $pos->employee_type }})
                                </option>
                            @endforeach
                        </select>
                    </div>


                                {{-- เพิ่ม <div class="pn-field"> ตรงนี้ --}}
                            <div class="pn-field">
                                <label>ประเภทบุคลากร / แผนก</label>
                                <select name="type_id" class="pn-select">
                                    <option value="">-- เลือกประเภท/แผนก --</option>
                                    @foreach(\App\Models\Personne\PersonnelType::where('is_active', true)->orderBy('sort_order')->get() as $type)
                                        <option value="{{ $type->type_id }}" {{ old('type_id', $personnel->type_id ?? '') == $type->type_id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        
                        
                            <div class="pn-field">
                                <label>เพศ</label>
                                <select name="gender" class="pn-select">
                                    <option value="">-- เลือก --</option>
                                    @foreach(['ชาย','หญิง'] as $g)
                                        <option value="{{ $g }}" {{ old('gender', $personnel->gender ?? '') == $g ? 'selected' : '' }}>{{ $g }}</option>
                                    @endforeach
                                </select>
                            </div>

                        <div class="pn-field">
                            <label>คำนำหน้า</label>
                            <select name="thai_prefix" class="pn-select">
                                @foreach(\App\Models\Prefix::where('is_active', true)->where(function($q){ $q->where('role', 'personnel')->orWhere('role', 'all'); })->orderBy('sort_order')->get() as $prefix)
                                    <option value="{{ $prefix->name_th }}" {{ old('thai_prefix', $personnel->thai_prefix ?? '') == $prefix->name_th ? 'selected' : '' }}>{{ $prefix->name_th }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pn-field"><label>ชื่อ <span class="text-danger">*</span></label><input type="text" name="thai_firstname" class="pn-input" value="{{ old('thai_firstname', $personnel->thai_firstname ?? '') }}" required></div>
                        <div class="pn-field"><label>นามสกุล <span class="text-danger">*</span></label><input type="text" name="thai_lastname" class="pn-input" value="{{ old('thai_lastname', $personnel->thai_lastname ?? '') }}" required></div>
                        <div class="pn-field"><label>ชื่อ(อังกฤษ)</label><input type="text" name="eng_firstname" class="pn-input" value="{{ old('eng_firstname', $personnel->eng_firstname ?? '') }}"></div>
                        <div class="pn-field"><label>นามสกุล(อังกฤษ)</label><input type="text" name="eng_lastname" class="pn-input" value="{{ old('eng_lastname', $personnel->eng_lastname ?? '') }}"></div>
                        <div class="pn-field"><label>เลขบัตรประชาชน</label><input type="text" name="id_card_number" class="pn-input" maxlength="13" value="{{ old('id_card_number', $personnel->id_card_number ?? '') }}"></div>
                        <div class="pn-field"><label>เลขหนังสือเดินทาง</label><input type="text" name="passport_number" class="pn-input" value="{{ old('passport_number', $personnel->passport_number ?? '') }}"></div>
                        <div class="pn-field"><label>ประเทศหนังสือเดินทาง</label><input type="text" name="passport_country" class="pn-input" value="{{ old('passport_country', $personnel->passport_country ?? '') }}"></div>
                        <div class="pn-field"><label>วันหมดอายุหนังสือเดินทาง</label><input type="date" name="passport_expiry" class="pn-input" value="{{ old('passport_expiry', optional($personnel->passport_expiry ?? null)->format('Y-m-d')) }}"></div>
                        <div class="pn-field"><label>เลขที่วีซ่า</label><input type="text" name="visa_number" class="pn-input" value="{{ old('visa_number', $personnel->visa_number ?? '') }}"></div>
                        <div class="pn-field"><label>เลขใบอนุญาตทำงาน</label><input type="text" name="work_permit_number" class="pn-input" value="{{ old('work_permit_number', $personnel->work_permit_number ?? '') }}"></div>
                        <div class="pn-field"><label>วันหมดอายุใบอนุญาตทำงาน</label><input type="date" name="work_permit_expiry" class="pn-input" value="{{ old('work_permit_expiry', optional($personnel->work_permit_expiry ?? null)->format('Y-m-d')) }}"></div>
                        <div class="pn-field"><label>วันเกิด</label><input type="date" name="date_of_birth" class="pn-input" value="{{ old('date_of_birth', optional($personnel->date_of_birth ?? null)->format('Y-m-d')) }}"></div>
                        <div class="pn-field">
                            <label>กรุ๊ปเลือด</label>
                            <select name="blood_group" class="pn-select">
                                <option value="">-- เลือก --</option>
                                @foreach(['A','B','AB','O'] as $bg)
                                    <option value="{{ $bg }}" {{ old('blood_group', $personnel->blood_group ?? '') == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pn-field"><label>สัญชาติ</label><input type="text" name="nationality" class="pn-input" value="{{ old('nationality', $personnel->nationality ?? '') }}"></div>
                        <div class="pn-field"><label>เชื้อชาติ</label><input type="text" name="ethnicity" class="pn-input" value="{{ old('ethnicity', $personnel->ethnicity ?? '') }}"></div>
                        <div class="pn-field"><label>ศาสนา</label><input type="text" name="religion" class="pn-input" value="{{ old('religion', $personnel->religion ?? '') }}"></div>
                        <div class="pn-field"><label>หมายเลขโทรศัพท์</label><input type="text" name="phone" class="pn-input" value="{{ old('phone', $personnel->phone ?? '') }}"></div>
                        <div class="pn-field"><label>อีเมล์</label><input type="email" name="email" class="pn-input" value="{{ old('email', $personnel->email ?? '') }}"></div>
                        <div class="pn-field"><label>ตารางเวลา</label><input type="text" name="schedule" class="pn-input" value="{{ old('schedule', $personnel->schedule ?? '') }}"></div>
                        <div class="pn-field"><label>รูปภาพ</label><input type="file" name="personnel_image" class="pn-input" accept="image/*"></div>
                    </div>
                    
                    {{-- เพิ่มปุ่มบันทึกท้ายการ์ดประวัติส่วนตัว --}}
                    <div class="pn-save-wrap mt-4" style="text-align: right;">
                        <button type="submit" class="pn-btn-save"><i class="bi bi-check-lg"></i> บันทึกข้อมูลประวัติส่วนตัว</button>
                    </div>
                </div>
            </div>

            {{-- 2. ที่อยู่ตามทะเบียนบ้าน --}}
            @php $regAddr = isset($personnel) ? $personnel->addresses->where('address_type', 'registered')->first() : null; @endphp
            <div class="pn-card">
                <div class="pn-card-header"><i class="bi bi-house-door"></i> ที่อยู่ตามทะเบียนบ้าน</div>
                <div class="pn-card-body">
                    <input type="hidden" name="addresses[registered][address_type]" value="registered">
                    <div class="pn-grid-3">
                        <div class="pn-field"><label>บ้านเลขที่</label><input type="text" name="addresses[registered][house_no]" class="pn-input" value="{{ old('addresses.registered.house_no', $regAddr->house_no ?? '') }}"></div>
                        <div class="pn-field"><label>หมู่ที่</label><input type="text" name="addresses[registered][moo]" class="pn-input" value="{{ old('addresses.registered.moo', $regAddr->moo ?? '') }}"></div>
                        <div class="pn-field"><label>หมู่บ้าน</label><input type="text" name="addresses[registered][village]" class="pn-input" value="{{ old('addresses.registered.village', $regAddr->village ?? '') }}"></div>
                        <div class="pn-field"><label>ซอย</label><input type="text" name="addresses[registered][soi]" class="pn-input" value="{{ old('addresses.registered.soi', $regAddr->soi ?? '') }}"></div>
                        <div class="pn-field"><label>อาคาร/ชั้น</label><input type="text" name="addresses[registered][building_floor]" class="pn-input" value="{{ old('addresses.registered.building_floor', $regAddr->building_floor ?? '') }}"></div>
                        <div class="pn-field"><label>ถนน</label><input type="text" name="addresses[registered][road]" class="pn-input" value="{{ old('addresses.registered.road', $regAddr->road ?? '') }}"></div>
                        <div class="pn-field"><label>จังหวัด</label><input type="text" name="addresses[registered][province]" class="pn-input" value="{{ old('addresses.registered.province', $regAddr->province ?? '') }}"></div>
                        <div class="pn-field"><label>เขต/อำเภอ</label><input type="text" name="addresses[registered][district]" class="pn-input" value="{{ old('addresses.registered.district', $regAddr->district ?? '') }}"></div>
                        <div class="pn-field"><label>แขวง/ตำบล</label><input type="text" name="addresses[registered][sub_district]" class="pn-input" value="{{ old('addresses.registered.sub_district', $regAddr->sub_district ?? '') }}"></div>
                        <div class="pn-field"><label>รหัสไปรษณีย์</label><input type="text" name="addresses[registered][postal_code]" class="pn-input" maxlength="5" value="{{ old('addresses.registered.postal_code', $regAddr->postal_code ?? '') }}"></div>
                    </div>

                    {{-- เพิ่มปุ่มบันทึกท้ายการ์ดทะเบียนบ้าน --}}
                    <div class="pn-save-wrap mt-4" style="text-align: right;">
                        <button type="submit" class="pn-btn-save"><i class="bi bi-check-lg"></i> บันทึกข้อมูลที่อยู่ทะเบียนบ้าน</button>
                    </div>
                </div>
            </div>

            {{-- 3. ที่อยู่ที่ติดต่อได้ --}}
            @php $curAddr = isset($personnel) ? $personnel->addresses->where('address_type', 'current')->first() : null; @endphp
            <div class="pn-card">
                <div class="pn-card-header"><i class="bi bi-geo-alt"></i> ที่อยู่ที่ติดต่อได้</div>
                <div class="pn-card-body">
                    <input type="hidden" name="addresses[current][address_type]" value="current">
                    <div class="pn-grid-3">
                        <div class="pn-field"><label>บ้านเลขที่</label><input type="text" name="addresses[current][house_no]" class="pn-input" value="{{ old('addresses.current.house_no', $curAddr->house_no ?? '') }}"></div>
                        <div class="pn-field"><label>หมู่ที่</label><input type="text" name="addresses[current][moo]" class="pn-input" value="{{ old('addresses.current.moo', $curAddr->moo ?? '') }}"></div>
                        <div class="pn-field"><label>หมู่บ้าน</label><input type="text" name="addresses[current][village]" class="pn-input" value="{{ old('addresses.current.village', $curAddr->village ?? '') }}"></div>
                        <div class="pn-field"><label>ซอย</label><input type="text" name="addresses[current][soi]" class="pn-input" value="{{ old('addresses.current.soi', $curAddr->soi ?? '') }}"></div>
                        <div class="pn-field"><label>อาคาร/ชั้น</label><input type="text" name="addresses[current][building_floor]" class="pn-input" value="{{ old('addresses.current.building_floor', $curAddr->building_floor ?? '') }}"></div>
                        <div class="pn-field"><label>ถนน</label><input type="text" name="addresses[current][road]" class="pn-input" value="{{ old('addresses.current.road', $curAddr->road ?? '') }}"></div>
                        <div class="pn-field"><label>จังหวัด</label><input type="text" name="addresses[current][province]" class="pn-input" value="{{ old('addresses.current.province', $curAddr->province ?? '') }}"></div>
                        <div class="pn-field"><label>เขต/อำเภอ</label><input type="text" name="addresses[current][district]" class="pn-input" value="{{ old('addresses.current.district', $curAddr->district ?? '') }}"></div>
                        <div class="pn-field"><label>แขวง/ตำบล</label><input type="text" name="addresses[current][sub_district]" class="pn-input" value="{{ old('addresses.current.sub_district', $curAddr->sub_district ?? '') }}"></div>
                        <div class="pn-field"><label>รหัสไปรษณีย์</label><input type="text" name="addresses[current][postal_code]" class="pn-input" maxlength="5" value="{{ old('addresses.current.postal_code', $curAddr->postal_code ?? '') }}"></div>
                    </div>

                    {{-- ย้ายปุ่มบันทึกมารวมไว้ในนี้แทน --}}
                    <div class="pn-save-wrap mt-4" style="text-align: right;">
                        <button type="submit" class="pn-btn-save"><i class="bi bi-check-lg"></i> บันทึกข้อมูลที่อยู่ติดต่อได้</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ╔══════════════════════════════════════════════════╗
       ║  TAB 2 — การศึกษา / เกียรติคุณ / อบรม / TOEIC    ║
       ╚══════════════════════════════════════════════════╝ --}}
    <div x-show="activeTab === 'education'" x-cloak>
        @if(isset($personnel))
            @include('personnel.partials.tab_education', ['personnel' => $personnel])
        @endif
    </div>

    {{-- ╔══════════════════════════════════════╗
       ║  TAB 3 — ตำแหน่งงาน / ข้อมูลการสอน    ║
       ╚══════════════════════════════════════╝ --}}
    <div x-show="activeTab === 'position'" x-cloak>
        @if(isset($personnel))
            @include('personnel.partials.tab_position', ['personnel' => $personnel])
        @endif
    </div>

    {{-- ╔══════════════════════════════════════════════╗
       ║  TAB 4 — ใบอนุญาต / เครื่องราชฯ               ║
       ╚══════════════════════════════════════════════╝ --}}
    <div x-show="activeTab === 'license'" x-cloak>
        @if(isset($personnel))
            @include('personnel.partials.tab_license', ['personnel' => $personnel])
        @endif
    </div>

</div>





@endsection