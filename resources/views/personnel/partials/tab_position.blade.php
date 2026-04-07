{{-- resources/views/personnel/partials/tab_position.blade.php --}}
@php $pos = $personnel->positionDetail; @endphp

<div class="pn-card">
    <div class="pn-card-header"><i class="bi bi-briefcase"></i> ข้อมูลตำแหน่งงาน</div>
    <div class="pn-card-body">
        <form method="POST" action="{{ route('personnels.position.store') }}">
            @csrf
            <input type="hidden" name="personnel_id" value="{{ $personnel->personnel_id }}">
            <div class="pn-grid-3">
                <div class="pn-field"><label>สถานภาพการทำงาน</label><input type="text" name="work_status" class="pn-input" value="{{ $pos->work_status ?? '' }}"></div>
                <div class="pn-field"><label>ระดับ</label><input type="text" name="level" class="pn-input" value="{{ $pos->level ?? '' }}"></div>
                <div class="pn-field"><label>วันที่ปฏิบัติงานในสถานศึกษา</label><input type="date" name="school_start_date" class="pn-input" value="{{ optional($pos->school_start_date ?? null)->format('Y-m-d') }}"></div>
                <div class="pn-field"><label>วันสั่งบรรจุ</label><input type="date" name="appointment_date" class="pn-input" value="{{ optional($pos->appointment_date ?? null)->format('Y-m-d') }}"></div>
                <div class="pn-field"><label>เงินเดือน</label><input type="number" name="salary" class="pn-input" step="0.01" value="{{ $pos->salary ?? '' }}"></div>
                <div class="pn-field"><label>วันเริ่มปฏิบัติราชการ</label><input type="date" name="government_start_date" class="pn-input" value="{{ optional($pos->government_start_date ?? null)->format('Y-m-d') }}"></div>
                <div class="pn-field"><label>เงินประจำตำแหน่ง</label><input type="number" name="position_allowance" class="pn-input" step="0.01" value="{{ $pos->position_allowance ?? '' }}"></div>
                <div class="pn-field"><label>จำนวนเงินวิทยฐานะ</label><input type="number" name="academic_allowance" class="pn-input" step="0.01" value="{{ $pos->academic_allowance ?? '' }}"></div>
                <div class="pn-field"><label>วันครบเกษียณอายุ</label><input type="date" name="retirement_date" class="pn-input" value="{{ optional($pos->retirement_date ?? null)->format('Y-m-d') }}"></div>
            </div>

            {{-- คำนวณรายได้สุทธิ + อายุงานเหลือ --}}
            <div class="pn-summary-row">
                <div class="pn-summary-item">
                    <span class="pn-summary-label">รายได้สุทธิรวม</span>
                    <span class="pn-summary-value">
                        {{ number_format(($pos->salary ?? 0) + ($pos->position_allowance ?? 0) + ($pos->academic_allowance ?? 0), 2) }} บาท
                    </span>
                </div>
                <div class="pn-summary-item">
                    <span class="pn-summary-label">อายุงานเหลือ</span>
                    <span class="pn-summary-value">
                        @if($pos && $pos->retirement_date)
                            @php
                                $now = \Carbon\Carbon::now();
                                $ret = \Carbon\Carbon::parse($pos->retirement_date);
                                $diff = $now->diff($ret);
                            @endphp
                            {{ $diff->y }} ปี {{ $diff->m }} เดือน
                        @else
                            -
                        @endif
                    </span>
                </div>
            </div>

            <div class="pn-save-wrap">
                <button type="submit" class="pn-btn-save"><i class="bi bi-check-lg"></i> บันทึกข้อมูลตำแหน่ง</button>
            </div>
        </form>
    </div>
</div>