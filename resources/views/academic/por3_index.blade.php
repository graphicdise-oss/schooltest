@extends('layouts.sidebar')
@push('styles')
<style>
.p3-page { padding: 24px 28px; }
.p3-card {
    background: #fff; border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 30px 24px 24px; position: relative;
    margin-top: 50px; margin-bottom: 28px;
}
.p3-icon {
    position: absolute; top: -25px; left: 20px;
    width: 70px; height: 70px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.p3-icon-search { background: #00bcd4; }
.p3-icon-list   { background: #43a047; }
.p3-card-title  { margin-left: 90px; font-size: 1.05rem; color: #555; margin-top: -8px; }

.p3-search-grid {
    display: grid; grid-template-columns: 1fr 1fr 1fr;
    gap: 14px 24px; margin-top: 20px; align-items: end;
}
.p3-search-row2 {
    display: flex; justify-content: center; margin-top: 20px;
}
.p3-field label { font-size: 0.82rem; font-weight: 600; color: #444; margin-bottom: 3px; display: block; }
.p3-field select {
    width: 100%; height: 36px; border: none; border-bottom: 1.5px solid #bbb;
    padding: 0 8px; font-size: 0.88rem; font-family: inherit; outline: none;
    background: transparent; box-sizing: border-box;
}
.p3-field select:focus { border-bottom-color: #00bcd4; }
.btn-search {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px;
    padding: 9px 32px; font-size: 0.9rem; font-weight: 600;
    cursor: pointer; font-family: inherit;
    display: inline-flex; align-items: center; gap: 6px; height: 40px;
}

/* Table */
.p3-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-top: 12px; }
.p3-table thead th {
    padding: 11px 12px; border-bottom: 2px solid #eee;
    color: #333; font-weight: 600; text-align: center; font-size: 0.83rem;
}
.p3-table thead th:nth-child(5),
.p3-table thead th:nth-child(6),
.p3-table thead th:nth-child(7) { text-align: left; }
.p3-table tbody tr { border-bottom: 1px solid #f0f0f0; }
.p3-table tbody tr:hover { background: #f9fbff; }
.p3-table tbody td { padding: 10px 12px; color: #555; vertical-align: middle; }
.p3-table tbody td.center { text-align: center; }

/* Print dropdown button */
.btn-print-wrap { position: relative; display: inline-block; }
.btn-print-main {
    background: #00bcd4; color: #fff; border: none; border-radius: 6px 0 0 6px;
    padding: 8px 18px; font-size: 0.88rem; font-weight: 600; cursor: pointer;
    font-family: inherit; display: inline-flex; align-items: center; gap: 6px;
}
.btn-print-caret {
    background: #0097a7; color: #fff; border: none; border-radius: 0 6px 6px 0;
    padding: 8px 10px; font-size: 0.88rem; cursor: pointer;
    border-left: 1px solid rgba(255,255,255,0.3);
}
.btn-print-dropdown {
    display: none; position: absolute; top: 100%; right: 0;
    background: #fff; border-radius: 6px; min-width: 140px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15); z-index: 100; margin-top: 4px;
}
.btn-print-dropdown.open { display: block; }
.btn-print-dropdown a {
    display: block; padding: 10px 16px; font-size: 0.88rem; color: #333;
    text-decoration: none; font-family: inherit;
}
.btn-print-dropdown a:hover { background: #f5f5f5; }
</style>
@endpush

@section('content')
<div class="p3-page">

    @if(session('success'))
    <div style="background:#e8f5e9;color:#2e7d32;padding:10px 16px;border-radius:6px;margin-bottom:16px;">
        {{ session('success') }}
    </div>
    @endif

    {{-- ฟอร์มค้นหา --}}
    <div class="p3-card">
        <div class="p3-icon p3-icon-search"><i class="bi bi-search"></i></div>
        <div class="p3-card-title">ค้นหา</div>

        <form method="GET" action="{{ route('por3.index') }}">
            <div class="p3-search-grid">
                <div class="p3-field">
                    <label>ปีการศึกษา</label>
                    <select name="year_id" onchange="this.form.submit()">
                        @foreach($academicYears as $y)
                        <option value="{{ $y->year_id }}" {{ $yearId == $y->year_id ? 'selected' : '' }}>
                            {{ $y->year_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="p3-field">
                    <label>เทอม</label>
                    <select name="term" onchange="this.form.submit()">
                        <option value="1" {{ $term == '1' ? 'selected' : '' }}>1</option>
                        <option value="2" {{ $term == '2' ? 'selected' : '' }}>2</option>
                    </select>
                </div>
                <div class="p3-field">
                    <label>ระดับชั้น</label>
                    <select name="level_id">
                        <option value="">-- ทุกระดับชั้น --</option>
                        @foreach($levels as $lv)
                        <option value="{{ $lv->level_id }}" {{ $levelId == $lv->level_id ? 'selected' : '' }}>
                            {{ $lv->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="p3-search-row2">
                <button type="submit" class="btn-search"><i class="bi bi-search"></i> ค้นหา</button>
            </div>
        </form>
    </div>

    {{-- รายการนักเรียน --}}
    <div class="p3-card">
        <div class="p3-icon p3-icon-list"><i class="bi bi-award"></i></div>
        <div class="p3-card-title" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <span>ระเบียนรายงานผู้สำเร็จการศึกษา (ปพ.3) ({{ $students->count() }} คน)</span>
            @if($students->count())
            <div class="btn-print-wrap" id="printWrap">
                <button class="btn-print-main" onclick="document.getElementById('printWrap').querySelector('.btn-print-dropdown').classList.toggle('open')">
                    <i class="bi bi-printer"></i> เตรียมการพิมพ์ใบ ปพ.3
                </button>
                <button class="btn-print-caret" onclick="document.getElementById('printWrap').querySelector('.btn-print-dropdown').classList.toggle('open')">
                    <i class="bi bi-caret-down-fill" style="font-size:0.7rem;"></i>
                </button>
                <div class="btn-print-dropdown">
                    <a href="#" onclick="alert('ฟีเจอร์ PDF กำลังพัฒนา'); return false;">
                        <i class="bi bi-file-earmark-pdf" style="color:#e53935;"></i> PDF
                    </a>
                    <a href="#" onclick="alert('ฟีเจอร์ Excel กำลังพัฒนา'); return false;">
                        <i class="bi bi-file-earmark-excel" style="color:#43a047;"></i> Excel
                    </a>
                </div>
            </div>
            @endif
        </div>

        @if($students->count())
        <table class="p3-table">
            <thead>
                <tr>
                    <th style="width:60px;">ลำดับ</th>
                    <th>รหัสนักเรียน</th>
                    <th>บัตรประชาชน</th>
                    <th>คำนำหน้า</th>
                    <th>ชื่อ</th>
                    <th>นามสกุล</th>
                    <th>ระดับชั้น</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students->values() as $i => $row)
                @php $stu = $row['student']; $sec = $row['section']; @endphp
                <tr>
                    <td class="center">{{ $i + 1 }}.</td>
                    <td class="center">{{ $stu->student_code }}</td>
                    <td class="center">{{ $stu->id_card_number ?? '-' }}</td>
                    <td class="center">{{ $stu->thai_prefix ?? '' }}</td>
                    <td>{{ $stu->thai_firstname }}</td>
                    <td>{{ $stu->thai_lastname }}</td>
                    <td>{{ $sec->level->name ?? '' }}{{ $sec ? ' / '.$sec->section_number : '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="text-align:center;padding:40px;color:#aaa;">
            <i class="bi bi-inbox" style="font-size:2rem;"></i>
            <div style="margin-top:8px;">ไม่พบข้อมูลผู้สำเร็จการศึกษา</div>
        </div>
        @endif
    </div>
</div>

<script>
// ปิด dropdown เมื่อคลิกนอก
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('printWrap');
    if (wrap && !wrap.contains(e.target)) {
        wrap.querySelector('.btn-print-dropdown')?.classList.remove('open');
    }
});
</script>
@endsection
