@extends('layouts.sidebar')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/studentdetail/studentdetail.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
    <div class="container mt-4">



        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
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

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>พบข้อผิดพลาด:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">

                @php
                    $isSaved = isset($student) ? true : false;
                    $regAddr = null;
                    $curAddr = null;
                    $edu = null;
                    $health = null;

                    if ($isSaved) {
                        if (isset($student->addresses)) {
                            $regAddr = $student->addresses->where('address_type', 'Registered')->first();
                            $curAddr = $student->addresses->where('address_type', 'Current')->first();
                        }
                        $edu = $student->education ?? null;
                        $health = $student->health ?? null;
                    }
                @endphp

                <div class="text-center mb-4 position-relative">
                    @php
                        $defaultImage = 'https://via.placeholder.com/150';
                        $imageSrc = (isset($student) && $student->student_image)
                            ? asset('storage/' . $student->student_image)
                            : $defaultImage;
                    @endphp

                    <img src="{{ $imageSrc }}" alt="Profile" class="profile-upload mb-2 rounded-circle object-fit-cover"
                        id="preview-image"
                        style="width: 150px; height: 150px; border: 2px solid #ccc; display: block; margin: 0 auto;">

                    <div>
                        <span class="text-muted small">
                            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                            ข้อแนะนำ : ไม่ควรอัปโหลดรูปเกิน 1MB
                        </span>
                    </div>

                    <input type="file" name="student_image" class="form-control d-none" id="image-upload" accept="image/*"
                        onchange="previewImage(event)">

                    <button type="button" class="btn btn-sm btn-secondary mt-2"
                        onclick="document.getElementById('image-upload').click()">อัปโหลดรูปภาพ</button>
                </div>

                <ul class="nav nav-pills nav-justified border-bottom pb-3 mb-4" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-student" type="button">
                            ข้อมูลนักเรียน
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ !$isSaved ? 'disabled' : '' }}" data-bs-toggle="pill"
                            data-bs-target="#tab-education" type="button">
                            ข้อมูลทางการศึกษา
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ !$isSaved ? 'disabled' : '' }}" data-bs-toggle="pill"
                            data-bs-target="#tab-guardian" type="button">
                            ข้อมูลผู้ปกครอง
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ !$isSaved ? 'disabled' : '' }}" data-bs-toggle="pill"
                            data-bs-target="#tab-health" type="button">
                            ข้อมูลสุขภาพ
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">

                    {{-- ========================================== --}}
                    {{-- TAB 1: ข้อมูลนักเรียน (ประวัติ + ที่อยู่) --}}
                    {{-- ========================================== --}}
                    <div class="tab-pane fade show active" id="tab-student">

                        <form
                            action="{{ $isSaved ? route('students.update', $student->student_id) : route('students.store') }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            @if($isSaved)
                                @method('PUT')
                            @endif

                            @if(!$isSaved)
                                <div class="alert alert-warning text-center">
                                    <i class="bi bi-info-circle-fill me-2"></i> กรุณาบันทึก <strong>"ข้อมูลนักเรียน"</strong>
                                    ก่อน ระบบถึงจะเปิดให้เพิ่มข้อมูลส่วนอื่นๆ ได้
                                </div>
                            @endif

                            <div class="section-header mb-4">
                                <i class="bi bi-person-fill me-1"></i> ประวัติส่วนตัว
                            </div>


                            {{-- ===== ข้อมูลชั้นเรียน ===== --}}
                            <div class="section-header mb-4">
                                <i class="bi bi-building me-1"></i> ข้อมูลชั้นเรียน
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ปีการศึกษา :</label>
                                        <div class="col-sm-8">
                                            <select id="sel-year" class="form-select" onchange="filterSemesters()">
                                                <option value="">-- เลือกปีการศึกษา --</option>
                                                @foreach($academicYears as $y)
                                                    @php
                                                        $selYear = isset($currentSection)
                                                            ? optional($sections->firstWhere('section_id', $currentSection->section_id))?->semester?->year_id
                                                            : null;
                                                    @endphp
                                                    <option value="{{ $y->year_id }}" {{ $selYear == $y->year_id ? 'selected' : '' }}>
                                                        {{ $y->year_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">เทอม :</label>
                                        <div class="col-sm-8">
                                            <select name="semester_id" id="sel-semester" class="form-select"
                                                onchange="filterSections()">
                                                <option value="">-- เลือกเทอม --</option>
                                                @foreach($semesters as $sm)
                                                    <option value="{{ $sm->semester_id }}" data-year="{{ $sm->year_id }}" {{ (isset($currentSection) && $sections->firstWhere('section_id', $currentSection->section_id)?->semester_id == $sm->semester_id) ? 'selected' : '' }}>
                                                        {{ $sm->semester_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ระดับชั้น :</label>
                                        <div class="col-sm-8">
                                            <select id="sel-level" class="form-select" onchange="filterSections()">
                                                <option value="">-- เลือกระดับชั้น --</option>
                                                @foreach($levels as $lv)
                                                    <option value="{{ $lv->level_id }}" {{ (isset($currentSection) && $sections->firstWhere('section_id', $currentSection->section_id)?->level_id == $lv->level_id) ? 'selected' : '' }}>
                                                        {{ $lv->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ห้องเรียน :</label>
                                        <div class="col-sm-8">
                                            <select name="section_id" id="sel-section" class="form-select">
                                                <option value="">-- เลือกห้องเรียน --</option>
                                                @foreach($sections as $sec)
                                                    <option value="{{ $sec->section_id }}"
                                                        data-semester="{{ $sec->semester_id }}"
                                                        data-level="{{ $sec->level_id }}" {{ (isset($currentSection) && $currentSection->section_id == $sec->section_id) ? 'selected' : '' }}>
                                                        {{ $sec->level->name ?? '' }} / {{ $sec->section_number }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">รหัสนักเรียน :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="student_code" class="form-control"
                                                placeholder="รหัสนักเรียน"
                                                value="{{ old('student_code', $student->student_code ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">สถานะ :</label>
                                        <div class="col-sm-8">
                                            <select name="status" class="form-select">
                                                <option value="กำลังศึกษา" {{ old('status', $student->status ?? '') == 'กำลังศึกษา' ? 'selected' : '' }}>กำลังศึกษา</option>
                                                <option value="จำหน่าย" {{ old('status', $student->status ?? '') == 'จำหน่าย' ? 'selected' : '' }}>จำหน่าย</option>
                                                <option value="ลาออก" {{ old('status', $student->status ?? '') == 'ลาออก' ? 'selected' : '' }}>ลาออก</option>
                                                <option value="พ้นสภาพ" {{ old('status', $student->status ?? '') == 'พ้นสภาพ' ? 'selected' : '' }}>พ้นสภาพ</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">เลขที่นักเรียน :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="classroom_number" class="form-control"
                                                value="{{ old('classroom_number', $student->classroom_number ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end text-danger">รหัสบัตรประชาชน*
                                            :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="id_card_number" class="form-control" maxlength="13"
                                                required
                                                value="{{ old('id_card_number', $student->id_card_number ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end text-danger">เพศ* :</label>
                                        <div class="col-sm-8">
                                            <select name="gender" class="form-select" required>
                                                <option value="" disabled {{ old('gender', $student->gender ?? '') == '' ? 'selected' : '' }}>เลือกเพศ</option>
                                                <option value="M" {{ old('gender', $student->gender ?? '') == 'M' ? 'selected' : '' }}>ชาย (M)</option>
                                                <option value="F" {{ old('gender', $student->gender ?? '') == 'F' ? 'selected' : '' }}>หญิง (F)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end text-danger">คำนำหน้า (ไทย)*
                                            :</label>
                                        <div class="col-sm-8">
                                            <select name="thai_prefix" class="form-select" required>
                                                <option value="" disabled {{ old('thai_prefix', $student->thai_prefix ?? '') == '' ? 'selected' : '' }}>เลือกคำนำหน้า</option>
                                                @foreach(\App\Models\Prefix::where('is_active', true)->where(function ($q) {
                                                    $q->where('role', 'student')->orWhere('role', 'all'); })->orderBy('sort_order')->get() as $prefix)
                                                    <option value="{{ $prefix->name_th }}" {{ old('thai_prefix', $student->thai_prefix ?? '') == $prefix->name_th ? 'selected' : '' }}>
                                                        {{ $prefix->name_th }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end text-danger">ชื่อ (ไทย)* :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="thai_firstname" class="form-control" required
                                                value="{{ old('thai_firstname', $student->thai_firstname ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end text-danger">นามสกุล (ไทย)*
                                            :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="thai_lastname" class="form-control" required
                                                value="{{ old('thai_lastname', $student->thai_lastname ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ชื่อ (อังกฤษ) :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="english_firstname" class="form-control"
                                                value="{{ old('english_firstname', $student->english_firstname ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">นามสกุล (อังกฤษ) :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="english_lastname" class="form-control"
                                                value="{{ old('english_lastname', $student->english_lastname ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ชื่อเล่น (ไทย) :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="thai_nickname" class="form-control"
                                                value="{{ old('thai_nickname', $student->thai_nickname ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ชื่อเล่น (อังกฤษ) :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="english_nickname" class="form-control"
                                                value="{{ old('english_nickname', $student->english_nickname ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end text-danger">วันเกิด* :</label>
                                        <div class="col-sm-8">
                                            <input type="date" name="date_of_birth" class="form-control" required
                                                value="{{ old('date_of_birth', $student->date_of_birth ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">คำนำหน้า (EN) :</label>
                                        <div class="col-sm-8">
                                            <select name="english_prefix" class="form-select">
                                                <option value="" disabled {{ old('english_prefix', $student->english_prefix ?? '') == '' ? 'selected' : '' }}>เลือก</option>
                                                <option value="Master" {{ old('english_prefix', $student->english_prefix ?? '') == 'Master' ? 'selected' : '' }}>Master</option>
                                                <option value="Miss" {{ old('english_prefix', $student->english_prefix ?? '') == 'Miss' ? 'selected' : '' }}>Miss</option>
                                                <option value="Mr." {{ old('english_prefix', $student->english_prefix ?? '') == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                                                <option value="Ms." {{ old('english_prefix', $student->english_prefix ?? '') == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end text-danger">เชื้อชาติ* :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="ethnicity" class="form-control" required
                                                value="{{ old('ethnicity', $student->ethnicity ?? 'ไทย') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end text-danger">สัญชาติ* :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="nationality" class="form-control" required
                                                value="{{ old('nationality', $student->nationality ?? 'ไทย') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ศาสนา :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="religion" class="form-control"
                                                value="{{ old('religion', $student->religion ?? 'พุทธ') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">อีเมล :</label>
                                        <div class="col-sm-8">
                                            <input type="email" name="email" class="form-control"
                                                value="{{ old('email', $student->email ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">เลข Passport :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="passport_number" class="form-control"
                                                value="{{ old('passport_number', $student->passport_number ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ประเทศ (Passport) :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="passport_country" class="form-control"
                                                value="{{ old('passport_country', $student->passport_country ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">หมดอายุ Passport :</label>
                                        <div class="col-sm-8">
                                            <input type="date" name="passport_expiration" class="form-control"
                                                value="{{ old('passport_expiration', $student->passport_expiration ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">เบอร์โทร :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="phone_number" class="form-control"
                                                value="{{ old('phone_number', $student->phone_number ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">พี่น้องทั้งหมด :</label>
                                        <div class="col-sm-8">
                                            <input type="number" name="total_siblings" class="form-control"
                                                value="{{ old('total_siblings', $student->total_siblings ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ลำดับการเกิด :</label>
                                        <div class="col-sm-8">
                                            <input type="number" name="sibling_order" class="form-control"
                                                value="{{ old('sibling_order', $student->sibling_order ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">สถานะพิเศษ :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="disadvantage_type" class="form-control"
                                                placeholder="เช่น ยากจน / กำพร้า"
                                                value="{{ old('disadvantage_type', $student->disadvantage_type ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ยอดใช้จ่ายต่อวัน :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="daily_allowance" class="form-control"
                                                value="{{ old('daily_allowance', $student->daily_allowance ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">การเดินทาง :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="transportation_type" class="form-control"
                                                value="{{ old('transportation_type', $student->transportation_type ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ความพิการ :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="disability_type" class="form-control"
                                                value="{{ old('disability_type', $student->disability_type ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4 mb-5 pt-3 border-top">
                                <button type="reset" class="btn btn-warning text-white me-2">ล้างข้อมูล</button>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i>บันทึกประวัติส่วนตัว
                                </button>
                            </div>


                            {{-- ========================================== --}}
                            {{-- ส่วนที่ 1 : ที่อยู่ตามทะเบียนบ้าน (Registered) --}}
                            {{-- ========================================== --}}
                            <div class="section-header mb-4 mt-5">
                                <i class="bi bi-house-fill me-1"></i> ที่อยู่ตามทะเบียนบ้าน
                            </div>

                            <input type="hidden" name="addresses[registered][address_type]" value="Registered">

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">รหัสประจำบ้าน :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[registered][house_code]" class="form-control"
                                                maxlength="20"
                                                value="{{ old('addresses.registered.house_code', $regAddr->house_code ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">บ้านเลขที่ :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[registered][house_number]"
                                                class="form-control" maxlength="20"
                                                value="{{ old('addresses.registered.house_number', $regAddr->house_number ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">หมู่ :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[registered][village_no]" class="form-control"
                                                maxlength="10"
                                                value="{{ old('addresses.registered.village_no', $regAddr->village_no ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ซอย :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[registered][soi]" class="form-control"
                                                maxlength="100"
                                                value="{{ old('addresses.registered.soi', $regAddr->soi ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ถนน :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[registered][road]" class="form-control"
                                                maxlength="100"
                                                value="{{ old('addresses.registered.road', $regAddr->road ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">จังหวัด :</label>
                                        <div class="col-sm-8">
                                            <select name="addresses[registered][province_id]" class="form-select">
                                                <option value="" disabled selected>เลือกจังหวัด</option>
                                                <option value="{{ $regAddr->province_id ?? '' }}" {{ isset($regAddr->province_id) ? 'selected' : '' }}>
                                                    {{ $regAddr->province_id ?? '' }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">อำเภอ :</label>
                                        <div class="col-sm-8">
                                            <select name="addresses[registered][district_id]" class="form-select">
                                                <option value="" disabled selected>เลือกอำเภอ</option>
                                                <option value="{{ $regAddr->district_id ?? '' }}" {{ isset($regAddr->district_id) ? 'selected' : '' }}>
                                                    {{ $regAddr->district_id ?? '' }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ตำบล :</label>
                                        <div class="col-sm-8">
                                            <select name="addresses[registered][subdistrict_id]" class="form-select">
                                                <option value="" disabled selected>เลือกตำบล</option>
                                                <option value="{{ $regAddr->subdistrict_id ?? '' }}" {{ isset($regAddr->subdistrict_id) ? 'selected' : '' }}>
                                                    {{ $regAddr->subdistrict_id ?? '' }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">รหัสไปรษณีย์ :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[registered][postal_code]"
                                                class="form-control" maxlength="10"
                                                value="{{ old('addresses.registered.postal_code', $regAddr->postal_code ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">เบอร์โทรศัพท์บ้าน :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[registered][home_phone]" class="form-control"
                                                maxlength="20"
                                                value="{{ old('addresses.registered.home_phone', $regAddr->home_phone ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">สถานที่เกิดระบุที่เกิด(TH)
                                            :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[registered][birth_hospital_th]"
                                                class="form-control" maxlength="10"
                                                value="{{ old('birth_hospital_th.registered.postal_code', $regAddr->birth_hospital_th ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">สถานที่เกิดระบุที่เกิด(EN)
                                            :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[registered][birth_hospital_en]"
                                                class="form-control" maxlength="20"
                                                value="{{ old('birth_hospital_en.registered.home_phone', $regAddr->birth_hospital_en ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">จังหวัด สถานที่เกิด :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[registered][birth_province_id]"
                                                class="form-control" maxlength="10"
                                                value="{{ old('birth_province_id.registered.postal_code', $regAddr->birth_province_id ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">เขต/อำเภอ สถานที่เกิด :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[registered][birth_district_id]"
                                                class="form-control" maxlength="20"
                                                value="{{ old('birth_district_id.registered.home_phone', $regAddr->birth_district_id ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">แขวง/ตำบล สถานที่เกิด :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[registered][birth_subdistrict_id]"
                                                class="form-control" maxlength="10"
                                                value="{{ old('birth_subdistrict_id.registered.postal_code', $regAddr->birth_subdistrict_id ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4 mb-5 pt-3 border-top">
                                <button type="reset" class="btn btn-warning text-white me-2">ล้างข้อมูล</button>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i>บันทึกที่อยู่ตามทะเบียนบ้าน
                                </button>
                            </div>


                            {{-- ========================================== --}}
                            {{-- ส่วนที่ 2 : ที่อยู่ปัจจุบัน (Current) --}}
                            {{-- ========================================== --}}
                            <div class="section-header mb-4 mt-5 d-flex align-items-center border-top pt-4">
                                <div><i class="bi bi-geo-alt-fill me-1"></i> ที่อยู่ปัจจุบัน (ที่ติดต่อได้)</div>
                                <button type="button" class="btn btn-sm btn-outline-info ms-4" onclick="copyAddressData()">
                                    <i class="bi bi-box-arrow-in-down"></i> ดึงข้อมูลจากที่อยู่ตามทะเบียนบ้าน
                                </button>
                            </div>

                            <input type="hidden" name="addresses[current][address_type]" value="Current">

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">รหัสประจำบ้าน :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[current][house_code]" class="form-control"
                                                maxlength="20"
                                                value="{{ old('addresses.current.house_code', $curAddr->house_code ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">บ้านเลขที่ :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[current][house_number]" class="form-control"
                                                maxlength="20"
                                                value="{{ old('addresses.current.house_number', $curAddr->house_number ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">หมู่ :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[current][village_no]" class="form-control"
                                                maxlength="10"
                                                value="{{ old('addresses.current.village_no', $curAddr->village_no ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ซอย :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[current][soi]" class="form-control"
                                                maxlength="100"
                                                value="{{ old('addresses.current.soi', $curAddr->soi ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ถนน :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[current][road]" class="form-control"
                                                maxlength="100"
                                                value="{{ old('addresses.current.road', $curAddr->road ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">จังหวัด :</label>
                                        <div class="col-sm-8">
                                            <select name="addresses[current][province_id]" class="form-select">
                                                <option value="" disabled selected>เลือกจังหวัด</option>
                                                <option value="{{ $curAddr->province_id ?? '' }}" {{ isset($curAddr->province_id) ? 'selected' : '' }}>
                                                    {{ $curAddr->province_id ?? '' }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">อำเภอ :</label>
                                        <div class="col-sm-8">
                                            <select name="addresses[current][district_id]" class="form-select">
                                                <option value="" disabled selected>เลือกอำเภอ</option>
                                                <option value="{{ $curAddr->district_id ?? '' }}" {{ isset($curAddr->district_id) ? 'selected' : '' }}>
                                                    {{ $curAddr->district_id ?? '' }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ตำบล :</label>
                                        <div class="col-sm-8">
                                            <select name="addresses[current][subdistrict_id]" class="form-select">
                                                <option value="" disabled selected>เลือกตำบล</option>
                                                <option value="{{ $curAddr->subdistrict_id ?? '' }}" {{ isset($curAddr->subdistrict_id) ? 'selected' : '' }}>
                                                    {{ $curAddr->subdistrict_id ?? '' }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">รหัสไปรษณีย์ :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[current][postal_code]" class="form-control"
                                                maxlength="10"
                                                value="{{ old('addresses.current.postal_code', $curAddr->postal_code ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">เบอร์โทรศัพท์บ้าน :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[current][home_phone]" class="form-control"
                                                maxlength="20"
                                                value="{{ old('addresses.current.home_phone', $curAddr->home_phone ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ลักษณะบ้าน :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[current][house_characteristic]"
                                                class="form-control" placeholder="เช่น บ้านเดี่ยว / ทาวน์เฮาส์"
                                                value="{{ old('addresses.current.house_characteristic', $curAddr->house_characteristic ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">ผู้พักอาศัยด้วย :</label>
                                        <div class="col-sm-3 pe-1">
                                            <select name="addresses[current][stay_with_prefix]" class="form-select">
                                                <option value="" disabled {{ old('addresses.current.stay_with_prefix', $curAddr->stay_with_prefix ?? '') == '' ? 'selected' : '' }}>คำนำหน้า
                                                </option>
                                                <option value="นาย" {{ old('addresses.current.stay_with_prefix', $curAddr->stay_with_prefix ?? '') == 'นาย' ? 'selected' : '' }}>นาย
                                                </option>
                                                <option value="นาง" {{ old('addresses.current.stay_with_prefix', $curAddr->stay_with_prefix ?? '') == 'นาง' ? 'selected' : '' }}>นาง
                                                </option>
                                                <option value="นางสาว" {{ old('addresses.current.stay_with_prefix', $curAddr->stay_with_prefix ?? '') == 'นางสาว' ? 'selected' : '' }}>นางสาว
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-sm-5 ps-1">
                                            <input type="text" name="addresses[current][stay_with_first_name]"
                                                class="form-control" placeholder="ชื่อ"
                                                value="{{ old('addresses.current.stay_with_first_name', $curAddr->stay_with_first_name ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">นามสกุลผู้พักอาศัย :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[current][stay_with_last_name]"
                                                class="form-control"
                                                value="{{ old('addresses.current.stay_with_last_name', $curAddr->stay_with_last_name ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">อีเมลผู้พักอาศัย :</label>
                                        <div class="col-sm-8">
                                            <input type="email" name="addresses[current][stay_with_email]"
                                                class="form-control"
                                                value="{{ old('addresses.current.stay_with_email', $curAddr->stay_with_email ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label text-md-end">เบอร์ติดต่อฉุกเฉิน :</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="addresses[current][emergency_contact_phone]"
                                                class="form-control" maxlength="20"
                                                value="{{ old('addresses.current.emergency_contact_phone', $curAddr->emergency_contact_phone ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4 mb-5 pt-3 border-top">
                                <button type="reset" class="btn btn-warning text-white me-2">ล้างข้อมูล</button>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i>บันทึกที่อยู่ปัจจุบัน
                                </button>
                            </div>

                        </form>
                    </div>
                    {{-- /tab-student --}}

                    {{-- ========================================== --}}
                    {{-- TAB 2: ข้อมูลทางการศึกษา --}}
                    {{-- ========================================== --}}
                    <div class="tab-pane fade" id="tab-education">

                        @if(!$isSaved)
                            <div class="alert alert-warning text-center mt-4">
                                <i class="bi bi-info-circle-fill me-2"></i> กรุณาบันทึก <strong>"ข้อมูลนักเรียน"</strong> ก่อน
                                ระบบถึงจะเปิดให้เพิ่มข้อมูลการศึกษาได้
                            </div>
                        @else
                            <form action="{{ route('students.storeEducation') }}" method="POST" class="mt-4">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->student_id ?? '' }}">

                                <div class="section-header mb-4">
                                    <i class="bi bi-book-fill me-1"></i> ข้อมูลทางการศึกษา
                                </div>

                                <div class="row gx-5">
                                    <div class="col-md-6">
                                        <div class="row mb-3 align-items-center">
                                            <label class="col-sm-4 col-form-label text-md-end">ประเภทสถานศึกษา :</label>
                                            <div class="col-sm-8">
                                                <select name="country_type" class="form-select">
                                                    <option value="ในประเทศ" {{ old('country_type', $edu->country_type ?? '') == 'ในประเทศ' ? 'selected' : '' }}>ในประเทศ</option>
                                                    <option value="ต่างประเทศ" {{ old('country_type', $edu->country_type ?? '') == 'ต่างประเทศ' ? 'selected' : '' }}>ต่างประเทศ</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-center">
                                            <label class="col-sm-4 col-form-label text-md-end">สถานศึกษาเดิม :</label>
                                            <div class="col-sm-8">
                                                <input type="text" name="school_name" class="form-control" maxlength="255"
                                                    value="{{ old('school_name', $edu->school_name ?? '') }}">
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-center">
                                            <label class="col-sm-4 col-form-label text-md-end">จังหวัด :</label>
                                            <div class="col-sm-8">
                                                <select name="province_id" class="form-select">
                                                    <option value="" disabled selected>เลือกจังหวัด</option>
                                                    <option value="{{ $edu->province_id ?? '' }}" {{ isset($edu->province_id) ? 'selected' : '' }}>
                                                        {{ $edu->province_id ?? '' }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-center">
                                            <label class="col-sm-4 col-form-label text-md-end">เขต/อำเภอ :</label>
                                            <div class="col-sm-8">
                                                <select name="district_id" class="form-select">
                                                    <option value="" disabled selected>เลือกอำเภอ</option>
                                                    <option value="{{ $edu->district_id ?? '' }}" {{ isset($edu->district_id) ? 'selected' : '' }}>
                                                        {{ $edu->district_id ?? '' }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-center">
                                            <label class="col-sm-4 col-form-label text-md-end">แขวง/ตำบล :</label>
                                            <div class="col-sm-8">
                                                <select name="subdistrict_id" class="form-select">
                                                    <option value="" disabled selected>เลือกตำบล</option>
                                                    <option value="{{ $edu->subdistrict_id ?? '' }}" {{ isset($edu->subdistrict_id) ? 'selected' : '' }}>
                                                        {{ $edu->subdistrict_id ?? '' }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-center">
                                            <label class="col-sm-4 col-form-label text-md-end">ประเทศ/เมือง (กรณีตปท.) :</label>
                                            <div class="col-sm-8">
                                                <input type="text" name="country_city" class="form-control" maxlength="255"
                                                    value="{{ old('country_city', $edu->country_city ?? '') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="row mb-3 align-items-center">
                                            <label class="col-sm-4 col-form-label text-md-end">วุฒิการศึกษา :</label>
                                            <div class="col-sm-8">
                                                <input type="text" name="education_level" class="form-control" maxlength="50"
                                                    placeholder="เช่น ม.3, ป.6"
                                                    value="{{ old('education_level', $edu->education_level ?? '') }}">
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-center">
                                            <label class="col-sm-4 col-form-label text-md-end">เกรดเฉลี่ย (GPA) :</label>
                                            <div class="col-sm-8">
                                                <input type="number" name="gpa" class="form-control" step="0.01" min="0"
                                                    max="4.00" value="{{ old('gpa', $edu->gpa ?? '') }}">
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-center">
                                            <label class="col-sm-4 col-form-label text-md-end">หน่วยกิต :</label>
                                            <div class="col-sm-8">
                                                <input type="number" name="credit" class="form-control" step="0.01" min="0"
                                                    value="{{ old('credit', $edu->credit ?? '') }}">
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-center">
                                            <label class="col-sm-4 col-form-label text-md-end">ปีที่จบ (พ.ศ.) :</label>
                                            <div class="col-sm-8">
                                                <input type="number" name="graduation_year" class="form-control"
                                                    placeholder="เช่น 2566"
                                                    value="{{ old('graduation_year', $edu->graduation_year ?? '') }}">
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-center">
                                            <label class="col-sm-4 col-form-label text-md-end">เหตุผลที่ย้าย :</label>
                                            <div class="col-sm-8">
                                                <textarea name="transfer_reason" class="form-control"
                                                    rows="2">{{ old('transfer_reason', $edu->transfer_reason ?? '') }}</textarea>
                                            </div>
                                        </div>

                                        <input type="hidden" name="education_type" value="ปกติ">
                                    </div>
                                </div>

                                <div class="text-end mt-4 pt-3 border-top mb-5">
                                    <button type="reset" class="btn btn-warning text-white me-2">ล้างข้อมูล</button>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-save me-1"></i>บันทึกข้อมูลการศึกษา
                                    </button>
                                </div>
                            </form>
                        @endif

                    </div>
                    {{-- /tab-education --}}

                    {{-- ========================================== --}}
                    {{-- TAB 3: ข้อมูลครอบครัวและผู้ปกครอง (แบบเรียงลงมา) --}}
                    {{-- ========================================== --}}
                    <div class="tab-pane fade" id="tab-guardian">

                        @if(!$isSaved)
                            <div class="alert alert-warning text-center mt-4">
                                <i class="bi bi-info-circle-fill me-2"></i> กรุณาบันทึก <strong>"ข้อมูลนักเรียน"</strong> ก่อน
                                ระบบถึงจะเปิดให้เพิ่มข้อมูลผู้ปกครองได้
                            </div>
                        @else
                            <div class="section-header mb-4 mt-4">
                                <i class="bi bi-people-fill me-1"></i> ข้อมูลครอบครัวและผู้ปกครอง
                            </div>

                            @php
                                $familyMap = [
                                    'บิดา' => 'father',
                                    'มารดา' => 'mother',
                                    'ผู้ปกครอง' => 'guardian'
                                ];
                                $families = isset($student->families) ? $student->families->keyBy('guardian_type') : collect();
                            @endphp

                            {{-- วนลูปสร้างฟอร์ม 3 ชุด แสดงเรียงต่อกันลงมา --}}
                            @foreach($familyMap as $typeTh => $typeEn)
                                @php
                                    $famData = $families->get($typeTh);
                                    $val = function ($field) use ($famData, $typeTh) {
                                        return old('guardian_type') === $typeTh ? old($field) : ($famData->$field ?? '');
                                    };
                                @endphp

                                <div class="mb-5 pb-3">
                                    {{-- หัวข้อแยกแต่ละฟอร์ม --}}
                                    <h5 class="text-primary border-bottom pb-2 mb-4">
                                        <i class="bi bi-person-badge-fill me-2"></i>ข้อมูล{{ $typeTh }}
                                    </h5>

                                    <form action="{{ route('students.storeFamily') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $student->student_id ?? '' }}">
                                        <input type="hidden" name="guardian_type" value="{{ $typeTh }}">
                                        <input type="hidden" name="family_type" value="ปกติ">

                                        <div class="row gx-5">
                                            <div class="col-md-6">
                                                <h6 class="text-secondary mb-3"><i class="bi bi-person-lines-fill"></i>
                                                    ข้อมูลส่วนตัวและอาชีพ</h6>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">คำนำหน้า (TH) :</label>
                                                    <div class="col-sm-8">
                                                        <select name="prefix_th" class="form-select">
                                                            <option value="" disabled {{ $val('prefix_th') == '' ? 'selected' : '' }}>
                                                                เลือก</option>
                                                            <option value="นาย" {{ $val('prefix_th') == 'นาย' ? 'selected' : '' }}>นาย
                                                            </option>
                                                            <option value="นาง" {{ $val('prefix_th') == 'นาง' ? 'selected' : '' }}>นาง
                                                            </option>
                                                            <option value="นางสาว" {{ $val('prefix_th') == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">ชื่อ - นามสกุล (TH) :</label>
                                                    <div class="col-sm-4 pe-1">
                                                        <input type="text" name="first_name_th" class="form-control"
                                                            placeholder="ชื่อ" value="{{ $val('first_name_th') }}">
                                                    </div>
                                                    <div class="col-sm-4 ps-1">
                                                        <input type="text" name="last_name_th" class="form-control"
                                                            placeholder="นามสกุล" value="{{ $val('last_name_th') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">ชื่อ - นามสกุล (EN) :</label>
                                                    <div class="col-sm-4 pe-1">
                                                        <input type="text" name="first_name_en" class="form-control"
                                                            placeholder="First Name" value="{{ $val('first_name_en') }}">
                                                    </div>
                                                    <div class="col-sm-4 ps-1">
                                                        <input type="text" name="last_name_en" class="form-control"
                                                            placeholder="Last Name" value="{{ $val('last_name_en') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">รหัสบัตรประชาชน :</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="id_card_number" class="form-control" maxlength="13"
                                                            value="{{ $val('id_card_number') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">วันเกิด :</label>
                                                    <div class="col-sm-8">
                                                        <input type="date" name="birth_date" class="form-control"
                                                            value="{{ $val('birth_date') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">สัญชาติ / เชื้อชาติ :</label>
                                                    <div class="col-sm-4 pe-1">
                                                        <input type="text" name="nationality" class="form-control"
                                                            placeholder="สัญชาติ" value="{{ $val('nationality') }}">
                                                    </div>
                                                    <div class="col-sm-4 ps-1">
                                                        <input type="text" name="ethnicity" class="form-control"
                                                            placeholder="เชื้อชาติ" value="{{ $val('ethnicity') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">ศาสนา :</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="religion" class="form-control"
                                                            value="{{ $val('religion') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">วุฒิการศึกษา :</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="education_level" class="form-control"
                                                            value="{{ $val('education_level') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">ความสัมพันธ์ :</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="relationship" class="form-control"
                                                            placeholder="เช่น บิดา, ป้า, น้า" value="{{ $val('relationship') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">อาชีพ :</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="occupation" class="form-control"
                                                            value="{{ $val('occupation') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">รายได้/เดือน :</label>
                                                    <div class="col-sm-8">
                                                        <input type="number" name="monthly_income" class="form-control" step="0.01"
                                                            value="{{ $val('monthly_income') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">สถานที่ทำงาน :</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="workplace" class="form-control"
                                                            value="{{ $val('workplace') }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <h6 class="text-secondary mb-3"><i class="bi bi-geo-alt-fill"></i>
                                                    ข้อมูลที่อยู่และการติดต่อ</h6>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">บ้านเลขที่ / หมู่ :</label>
                                                    <div class="col-sm-4 pe-1">
                                                        <input type="text" name="house_number" class="form-control"
                                                            placeholder="บ้านเลขที่" value="{{ $val('house_number') }}">
                                                    </div>
                                                    <div class="col-sm-4 ps-1">
                                                        <input type="text" name="village_no" class="form-control"
                                                            placeholder="หมู่ที่" value="{{ $val('village_no') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">ซอย / ถนน :</label>
                                                    <div class="col-sm-4 pe-1">
                                                        <input type="text" name="soi" class="form-control" placeholder="ซอย"
                                                            value="{{ $val('soi') }}">
                                                    </div>
                                                    <div class="col-sm-4 ps-1">
                                                        <input type="text" name="road" class="form-control" placeholder="ถนน"
                                                            value="{{ $val('road') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">จังหวัด :</label>
                                                    <div class="col-sm-8">
                                                        <select name="province_id" class="form-select">
                                                            <option value="" disabled selected>เลือกจังหวัด</option>
                                                            <option value="{{ $val('province_id') }}" {{ $val('province_id') ? 'selected' : '' }}>{{ $val('province_id') }}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">เขต/อำเภอ :</label>
                                                    <div class="col-sm-8">
                                                        <select name="district_id" class="form-select">
                                                            <option value="" disabled selected>เลือกอำเภอ</option>
                                                            <option value="{{ $val('district_id') }}" {{ $val('district_id') ? 'selected' : '' }}>{{ $val('district_id') }}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">แขวง/ตำบล :</label>
                                                    <div class="col-sm-8">
                                                        <select name="subdistrict_id" class="form-select">
                                                            <option value="" disabled selected>เลือกตำบล</option>
                                                            <option value="{{ $val('subdistrict_id') }}" {{ $val('subdistrict_id') ? 'selected' : '' }}>{{ $val('subdistrict_id') }}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">รหัสไปรษณีย์ :</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="postal_code" class="form-control"
                                                            value="{{ $val('postal_code') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center mt-4">
                                                    <label class="col-sm-4 col-form-label text-md-end">เบอร์มือถือ :</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="phone_mobile" class="form-control"
                                                            value="{{ $val('phone_mobile') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">เบอร์บ้าน :</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="phone_home" class="form-control"
                                                            value="{{ $val('phone_home') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">เบอร์ที่ทำงาน :</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="phone_work" class="form-control"
                                                            value="{{ $val('phone_work') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">สิทธิ์เบิกค่าเล่าเรียน
                                                        :</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="tuition_subsidy" class="form-control"
                                                            placeholder="เช่น มีสิทธิ์ / ไม่มีสิทธิ์"
                                                            value="{{ $val('tuition_subsidy') }}">
                                                    </div>
                                                </div>

                                                <div class="row mb-3 align-items-center">
                                                    <label class="col-sm-4 col-form-label text-md-end">สถานะครอบครัว :</label>
                                                    <div class="col-sm-8">
                                                        <select name="family_status" class="form-select">
                                                            <option value="" disabled {{ $val('family_status') == '' ? 'selected' : '' }}>เลือก</option>
                                                            <option value="อยู่ร่วมกัน" {{ $val('family_status') == 'อยู่ร่วมกัน' ? 'selected' : '' }}>อยู่ร่วมกัน</option>
                                                            <option value="หย่าร้าง" {{ $val('family_status') == 'หย่าร้าง' ? 'selected' : '' }}>หย่าร้าง</option>
                                                            <option value="แยกกันอยู่" {{ $val('family_status') == 'แยกกันอยู่' ? 'selected' : '' }}>แยกกันอยู่</option>
                                                            <option value="ถึงแก่กรรม" {{ $val('family_status') == 'ถึงแก่กรรม' ? 'selected' : '' }}>ถึงแก่กรรม</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-end mt-4 pt-3 mb-2">
                                            <button type="reset" class="btn btn-warning text-white me-2">ล้างข้อมูล</button>
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="bi bi-save me-1"></i>บันทึกข้อมูล{{ $typeTh }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        @endif

                    </div>
                    {{-- /tab-guardian --}}

                    {{-- ========================================== --}}
                    {{-- TAB 4: ข้อมูลสุขภาพ --}}
                    {{-- ========================================== --}}
                    <div class="tab-pane fade" id="tab-health">

                        @if(!$isSaved)
                            <div class="alert alert-warning text-center mt-4">
                                <i class="bi bi-info-circle-fill me-2"></i> กรุณาบันทึก <strong>"ข้อมูลนักเรียน"</strong> ก่อน
                                ระบบถึงจะเปิดให้เพิ่มข้อมูลสุขภาพได้
                            </div>
                        @else
                            <div class="section-header mb-4 mt-4">
                                <i class="bi bi-heart-pulse-fill me-1"></i> ข้อมูลสุขภาพ
                            </div>

                            <form action="{{ route('students.storeHealth') }}" method="POST">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->student_id ?? '' }}">

                                <div class="row gx-5">
                                    <div class="col-md-8 offset-md-2">

                                        <div class="row mb-3 align-items-center">
                                            <label class="col-sm-3 col-form-label text-md-end">กรุ๊ปเลือด :</label>
                                            <div class="col-sm-9">
                                                <select name="blood_group" class="form-select">
                                                    <option value="" disabled {{ old('blood_group', $health->blood_group ?? '') == '' ? 'selected' : '' }}>เลือกกรุ๊ปเลือด</option>
                                                    <option value="A" {{ old('blood_group', $health->blood_group ?? '') == 'A' ? 'selected' : '' }}>A</option>
                                                    <option value="B" {{ old('blood_group', $health->blood_group ?? '') == 'B' ? 'selected' : '' }}>B</option>
                                                    <option value="AB" {{ old('blood_group', $health->blood_group ?? '') == 'AB' ? 'selected' : '' }}>AB</option>
                                                    <option value="O" {{ old('blood_group', $health->blood_group ?? '') == 'O' ? 'selected' : '' }}>O</option>
                                                    <option value="ไม่ทราบ" {{ old('blood_group', $health->blood_group ?? '') == 'ไม่ทราบ' ? 'selected' : '' }}>ไม่ทราบ</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-start">
                                            <label class="col-sm-3 col-form-label text-md-end mt-1">อาการแพ้อาหาร :</label>
                                            <div class="col-sm-9">
                                                <textarea name="food_allergy" class="form-control" rows="2"
                                                    placeholder="ระบุอาการแพ้อาหาร (หากไม่มีให้เว้นว่าง)">{{ old('food_allergy', $health->food_allergy ?? '') }}</textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-start">
                                            <label class="col-sm-3 col-form-label text-md-end mt-1">อาการแพ้ยา :</label>
                                            <div class="col-sm-9">
                                                <textarea name="medicine_allergy" class="form-control" rows="2"
                                                    placeholder="ระบุอาการแพ้ยา (หากไม่มีให้เว้นว่าง)">{{ old('medicine_allergy', $health->medicine_allergy ?? '') }}</textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-start">
                                            <label class="col-sm-3 col-form-label text-md-end mt-1">อาการแพ้อื่นๆ :</label>
                                            <div class="col-sm-9">
                                                <textarea name="other_allergy" class="form-control" rows="2"
                                                    placeholder="เช่น แพ้ฝุ่น แพ้เกสรดอกไม้ (หากไม่มีให้เว้นว่าง)">{{ old('other_allergy', $health->other_allergy ?? '') }}</textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-start">
                                            <label class="col-sm-3 col-form-label text-md-end mt-1">โรคประจำตัว :</label>
                                            <div class="col-sm-9">
                                                <textarea name="chronic_disease" class="form-control" rows="2"
                                                    placeholder="ระบุโรคประจำตัว (หากไม่มีให้เว้นว่าง)">{{ old('chronic_disease', $health->chronic_disease ?? '') }}</textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-3 align-items-start">
                                            <label class="col-sm-3 col-form-label text-md-end mt-1">โรคร้ายแรง :</label>
                                            <div class="col-sm-9">
                                                <textarea name="serious_disease" class="form-control" rows="2"
                                                    placeholder="ประวัติโรคร้ายแรงที่เคยเป็นหรือกำลังเป็น (หากไม่มีให้เว้นว่าง)">{{ old('serious_disease', $health->serious_disease ?? '') }}</textarea>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="text-center mt-5 mb-5 pt-3 border-top">
                                    <button type="reset" class="btn btn-warning text-white me-2">ล้างข้อมูล</button>
                                    <button type="submit" class="btn btn-primary px-5">
                                        <i class="bi bi-save me-1"></i>บันทึกข้อมูลสุขภาพ
                                    </button>
                                </div>
                            </form>
                        @endif

                    </div>
                    {{-- /tab-health --}}

                </div>
                {{-- /tab-content หลัก --}}

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function copyAddressData() {
            const fieldsToCopy = [
                'house_code', 'house_number', 'village_no', 'soi', 'road',
                'province_id', 'district_id', 'subdistrict_id', 'postal_code',
                'home_phone', 'house_characteristic', 'stay_with_prefix',
                'stay_with_first_name', 'stay_with_last_name', 'stay_with_email',
                'emergency_contact_phone', 'birth_hospital_th', 'birth_hospital_en',
                'birth_province_id', 'birth_district_id', 'birth_subdistrict_id'
            ];

            fieldsToCopy.forEach(field => {
                let sourceField = document.querySelector(`[name="addresses[registered][${field}]"]`);
                let targetField = document.querySelector(`[name="addresses[current][${field}]"]`);

                if (sourceField && targetField) {
                    targetField.value = sourceField.value;
                }
            });

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'คัดลอกสำเร็จ',
                    text: 'ดึงข้อมูลจากที่อยู่ตามทะเบียนบ้านมาแล้วครับ',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert('ดึงข้อมูลจากที่อยู่ตามทะเบียนบ้านเรียบร้อยแล้ว!');
            }
        }

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                const output = document.getElementById('preview-image');
                output.src = reader.result;
            }
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>

    @push('scripts')
        <script>
            function filterSemesters() {
                const yearId = document.getElementById('sel-year').value;
                document.querySelectorAll('#sel-semester option[data-year]').forEach(opt => {
                    opt.style.display = (!yearId || opt.dataset.year === yearId) ? '' : 'none';
                });
                document.getElementById('sel-semester').value = '';
                filterSections();
            }

            function filterSections() {
                const semId = document.getElementById('sel-semester').value;
                const lvlId = document.getElementById('sel-level').value;
                document.querySelectorAll('#sel-section option[data-semester]').forEach(opt => {
                    const okSem = !semId || opt.dataset.semester === semId;
                    const okLvl = !lvlId || opt.dataset.level === lvlId;
                    opt.style.display = (okSem && okLvl) ? '' : 'none';
                });
                document.getElementById('sel-section').value = '';
            }
        </script>
    @endpush

@endsection