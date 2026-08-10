@extends('layouts.sidebar')

@push('styles')
<style>
    .hv-page { padding: 24px 28px; max-width: 980px; }
    .hv-breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; margin-bottom: 20px; color: #555; }
    .hv-breadcrumb a { color: #5482e7; text-decoration: none; font-weight: 500; }
    .hv-breadcrumb span { color: #5482e7; font-weight: 600; }
    .hv-card {
        background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        padding: 22px; margin-bottom: 20px;
    }
    .hv-card-title { color: #082b75; font-weight: 700; font-size: 1.05rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .search-box { position: relative; }
    .search-input {
        border: 1px solid #d0d7e5; border-radius: 8px; padding: 12px 14px; font-size: 1rem;
        width: 100%; font-family: inherit; outline: none; box-sizing: border-box;
    }
    .search-input:focus { border-color: #4b7ce3; }
    .search-result { border: 1px solid #e6ebf5; border-radius: 8px; margin-top: 8px; max-height: 240px; overflow-y: auto; position: absolute; background: #fff; width: 100%; z-index: 20; box-shadow: 0 4px 14px rgba(0,0,0,0.08); }
    .search-item { padding: 10px 14px; cursor: pointer; font-size: 0.95rem; border-bottom: 1px solid #f0f2f7; }
    .search-item:hover { background: #f0f6ff; }

    .stu-bar { display: flex; align-items: center; gap: 16px; background: #f0f4ff; border-radius: 8px; padding: 14px 18px; }
    .stu-bar-name { font-size: 1.05rem; font-weight: 700; color: #1a237e; }
    .stu-bar-meta { font-size: 0.85rem; color: #666; }

    .hv-form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px 20px; }
    .hv-form-row { display: flex; flex-direction: column; gap: 5px; }
    .hv-form-row.full { grid-column: 1 / -1; }
    .hv-label { font-size: 0.85rem; font-weight: 600; color: #444; }
    .hv-input, .hv-select, .hv-textarea {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 8px 12px; font-size: 0.9rem;
        font-family: inherit; outline: none; box-sizing: border-box; width: 100%;
    }
    .hv-input:focus, .hv-select:focus, .hv-textarea:focus { border-color: #5482e7; }
    .hv-checkbox-row { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: #444; }
    .btn-save {
        background: #4caf50; color: #fff; border: none; border-radius: 6px; padding: 10px 30px;
        font-size: 0.9rem; font-weight: 600; cursor: pointer; font-family: inherit;
        display: inline-flex; align-items: center; gap: 6px; margin-top: 16px;
    }
    .btn-save:hover { background: #388e3c; }

    .hv-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .hv-table thead th { background: #f2f6ff; color: #082b75; font-weight: 600; padding: 10px 12px; text-align: left; }
    .hv-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f0f2f7; color: #444; vertical-align: top; }
    .badge-followup { background: #fff3e0; color: #e65100; border-radius: 20px; padding: 2px 10px; font-size: 0.76rem; font-weight: 700; }
    .btn-del { background: #ffebee; color: #c62828; border: none; border-radius: 6px; padding: 5px 10px; font-size: 0.78rem; cursor: pointer; }
    .hv-empty { text-align: center; color: #94a3b8; padding: 30px 0; }
</style>
@endpush

@section('content')
<div class="hv-page">
    <nav class="hv-breadcrumb">
        <a href="#">กิจการนักเรียน</a>
        <i class="bi bi-chevron-right"></i>
        <span>บันทึกการเยี่ยมบ้านรายบุคคล</span>
    </nav>

    @if(session('success'))
    <div style="background:#e8f5e9;color:#2e7d32;padding:10px 18px;border-radius:6px;margin-bottom:16px;font-size:0.88rem">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="hv-card">
        <div class="hv-card-title"><i class="fas fa-magnifying-glass" style="color:#4b7ce3;"></i> ค้นหานักเรียน</div>
        <div class="search-box">
            <input type="text" id="hv_search" class="search-input" placeholder="พิมพ์ชื่อ หรือ รหัสนักเรียน..." autocomplete="off"
                   value="{{ $student?->thai_firstname }} {{ $student?->thai_lastname }}">
            <div class="search-result" id="hv_results" style="display:none;"></div>
        </div>
    </div>

    @if($student)
    <div class="hv-card">
        <div class="stu-bar">
            <div>
                <div class="stu-bar-name">{{ $student->thai_prefix }}{{ $student->thai_firstname }} {{ $student->thai_lastname }}</div>
                <div class="stu-bar-meta">
                    รหัส {{ $student->student_code ?? '-' }}
                    @if($currentSection)
                        &nbsp;•&nbsp;{{ $currentSection->classSection?->level?->name }}/{{ $currentSection->classSection?->section_number }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="hv-card">
        <div class="hv-card-title"><i class="fas fa-house" style="color:#4b7ce3;"></i> บันทึกการเยี่ยมบ้านครั้งใหม่</div>
        <form method="POST" action="{{ route('home-visits.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="student_id" value="{{ $student->student_id }}">
            <div class="hv-form-grid">
                <div class="hv-form-row">
                    <label class="hv-label">วันที่เยี่ยม *</label>
                    <input type="date" name="visit_date" class="hv-input" required value="{{ old('visit_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="hv-form-row">
                    <label class="hv-label">ครั้งที่</label>
                    <input type="number" name="visit_no" class="hv-input" min="1" value="{{ old('visit_no', 1) }}">
                </div>
                <div class="hv-form-row">
                    <label class="hv-label">อาศัยอยู่กับ</label>
                    <select name="family_status" class="hv-select">
                        <option value="">-- เลือก --</option>
                        <option value="บิดามารดา">บิดามารดา</option>
                        <option value="บิดา/มารดาเลี้ยงเดี่ยว">บิดา/มารดาเลี้ยงเดี่ยว</option>
                        <option value="ญาติ">ญาติ</option>
                        <option value="ผู้ปกครองอื่น">ผู้ปกครองอื่น</option>
                        <option value="อยู่คนเดียว">อยู่คนเดียว</option>
                    </select>
                </div>
                <div class="hv-form-row">
                    <label class="hv-label">สภาพเศรษฐกิจ</label>
                    <select name="economic_status" class="hv-select">
                        <option value="">-- เลือก --</option>
                        <option value="ดี">ดี</option>
                        <option value="ปานกลาง">ปานกลาง</option>
                        <option value="ยากจน">ยากจน</option>
                    </select>
                </div>
                <div class="hv-form-row" style="align-self:end;">
                    <div class="hv-checkbox-row">
                        <input type="checkbox" name="need_followup" id="need_followup" value="1">
                        <label for="need_followup">ต้องติดตามเป็นกรณีพิเศษ</label>
                    </div>
                </div>
                <div class="hv-form-row">
                    <label class="hv-label">รูปภาพ</label>
                    <input type="file" name="photo" accept="image/*" class="hv-input">
                </div>
                <div class="hv-form-row full">
                    <label class="hv-label">สภาพที่อยู่อาศัย</label>
                    <textarea name="house_condition" class="hv-textarea" rows="2">{{ old('house_condition') }}</textarea>
                </div>
                <div class="hv-form-row full">
                    <label class="hv-label">ปัญหาที่พบ</label>
                    <textarea name="problems_found" class="hv-textarea" rows="2">{{ old('problems_found') }}</textarea>
                </div>
                <div class="hv-form-row full">
                    <label class="hv-label">ข้อเสนอแนะ/การช่วยเหลือ</label>
                    <textarea name="suggestion" class="hv-textarea" rows="2">{{ old('suggestion') }}</textarea>
                </div>
            </div>
            <button type="submit" class="btn-save"><i class="fas fa-floppy-disk"></i> บันทึก</button>
        </form>
    </div>

    <div class="hv-card">
        <div class="hv-card-title"><i class="fas fa-clock-rotate-left" style="color:#4b7ce3;"></i> ประวัติการเยี่ยมบ้าน ({{ $visits->count() }} ครั้ง)</div>
        <div style="overflow-x:auto;">
            <table class="hv-table">
                <thead>
                    <tr>
                        <th style="width:100px;">วันที่</th>
                        <th style="width:60px;">ครั้งที่</th>
                        <th>อาศัยอยู่กับ</th>
                        <th>เศรษฐกิจ</th>
                        <th>ปัญหาที่พบ</th>
                        <th style="width:90px;">ติดตาม</th>
                        <th style="width:120px;">ผู้บันทึก</th>
                        <th style="width:50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $v)
                        <tr>
                            <td>{{ $v->visit_date->format('d/m/Y') }}</td>
                            <td>{{ $v->visit_no }}</td>
                            <td>{{ $v->family_status ?? '-' }}</td>
                            <td>{{ $v->economic_status ?? '-' }}</td>
                            <td>{{ $v->problems_found ?? '-' }}</td>
                            <td>@if($v->need_followup)<span class="badge-followup">ต้องติดตาม</span>@else - @endif</td>
                            <td>{{ $v->personnel?->thai_firstname ?? $v->created_by ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('home-visits.destroy', $v->id) }}" onsubmit="return confirm('ลบบันทึกนี้?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-del"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="hv-empty">ยังไม่มีประวัติการเยี่ยมบ้าน</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    let hv_timer = null;
    document.getElementById('hv_search').addEventListener('input', function () {
        clearTimeout(hv_timer);
        const q = this.value.trim();
        const box = document.getElementById('hv_results');
        if (q.length < 2) { box.style.display = 'none'; return; }
        hv_timer = setTimeout(() => {
            fetch('{{ route('home-visits.search') }}?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(list => {
                    box.innerHTML = '';
                    if (!list.length) { box.style.display = 'none'; return; }
                    list.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'search-item';
                        div.textContent = (item.code ?? '-') + ' — ' + item.name;
                        div.onclick = () => {
                            window.location = '{{ route('home-visits.index') }}?student_id=' + item.id;
                        };
                        box.appendChild(div);
                    });
                    box.style.display = 'block';
                });
        }, 300);
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.search-box')) {
            document.getElementById('hv_results').style.display = 'none';
        }
    });
</script>
@endpush
@endsection
