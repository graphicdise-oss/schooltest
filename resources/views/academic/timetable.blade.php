@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><span>จัดการตารางสอน</span></nav>

    {{-- มอบหมายการสอน --}}
    <div class="ac-card">
        <div class="ac-card-header">
            <span><i class="bi bi-person-workspace"></i> มอบหมายการสอน</span>
            <a href="{{ route('timetable.view', ['semester_id' => $semesterId]) }}" class="ac-btn ac-btn-primary ac-btn-sm"><i class="bi bi-calendar-week"></i> ดูตารางสอน</a>
        </div>
        <div class="ac-card-body">
            {{-- ฟอร์มเพิ่ม --}}
            <form method="POST" action="{{ route('timetable.storeAssign') }}" class="ac-grid-4" style="margin-bottom:20px; padding:16px; background:#f8fafc; border-radius:10px">
                @csrf
                <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                <div class="ac-field"><label>ครูผู้สอน *</label>
                    <select name="personnel_id" class="ac-select" required>
                        <option value="">-- เลือก --</option>
                        @foreach($teachers as $t)<option value="{{ $t->personnel_id }}">{{ $t->thai_firstname }} {{ $t->thai_lastname }}</option>@endforeach
                    </select>
                </div>
                <div class="ac-field"><label>วิชา *</label>
                    <select name="subject_id" class="ac-select" required>
                        <option value="">-- เลือก --</option>
                        @foreach($subjects as $sub)<option value="{{ $sub->subject_id }}">{{ $sub->code }} — {{ $sub->name_th }}</option>@endforeach
                    </select>
                </div>
                <div class="ac-field"><label>ห้อง *</label>
                    <select name="section_id" class="ac-select" required>
                        <option value="">-- เลือก --</option>
                        @foreach($sections as $sec)<option value="{{ $sec->section_id }}">{{ $sec->level->name }}/{{ $sec->section_number }}</option>@endforeach
                    </select>
                </div>
                <div class="ac-field" style="justify-content:flex-end"><button type="submit" class="ac-btn ac-btn-success" style="margin-top:20px"><i class="bi bi-plus"></i> มอบหมาย</button></div>
            </form>

            {{-- ตารางมอบหมาย --}}
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>#</th><th>ครูผู้สอน</th><th>วิชา</th><th>ห้อง</th><th>คาบเรียน</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($assigns as $i => $a)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $a->personnel->thai_firstname }} {{ $a->personnel->thai_lastname }}</td>
                            <td style="text-align:left">{{ $a->subject->code }} — {{ $a->subject->name_th }}</td>
                            <td>{{ $a->classSection->level->name }}/{{ $a->classSection->section_number }}</td>
                            <td>
                                @foreach($a->timetableSlots as $slot)
                                    <span class="ac-badge ac-badge-info" style="margin:2px">{{ $slot->day_of_week }} {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</span>
                                @endforeach
                                <button class="ac-action-btn ac-action-view" title="เพิ่มคาบ"
                                    onclick="openSlotModal({{ $a->assign_id }})"><i class="bi bi-plus"></i></button>
                            </td>
                            <td>
                                <form action="{{ route('timetable.destroyAssign', $a->assign_id) }}" method="POST" style="display:inline" onsubmit="return confirm('ลบการมอบหมายนี้?')">
                                    @csrf @method('DELETE')
                                    <button class="ac-action-btn ac-action-delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="ac-empty">ยังไม่มีการมอบหมายการสอน</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal เพิ่มคาบเรียน --}}
<div class="ac-overlay" id="slotOverlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="ac-modal" onclick="event.stopPropagation()">
        <div class="ac-modal-header"><i class="bi bi-clock me-2"></i> เพิ่มคาบเรียน</div>
        <form method="POST" action="{{ route('timetable.storeSlot') }}">
            @csrf
            <input type="hidden" name="assign_id" id="slotAssignId">
            <div class="ac-modal-body">
                <label>วัน *</label>
                <select name="day_of_week" required>
                    <option value="">-- เลือก --</option>
                    @foreach(['จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์'] as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                    @endforeach
                </select>
                <label>เวลาเริ่ม *</label>
                <input type="time" name="start_time" required value="08:30">
                <label>เวลาจบ *</label>
                <input type="time" name="end_time" required value="09:20">
                <label>ห้องสอน</label>
                <input type="text" name="room" placeholder="เช่น 301, ห้อง Lab">
            </div>
            <div class="ac-modal-footer">
                <button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('slotOverlay').classList.remove('active')">ยกเลิก</button>
                <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSlotModal(assignId) {
    document.getElementById('slotAssignId').value = assignId;
    document.getElementById('slotOverlay').classList.add('active');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.ac-overlay.active').forEach(el => el.classList.remove('active'));
});
</script>

@if(session('success'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>Swal.fire({icon:'success',title:'สำเร็จ!',text:'{{ session("success") }}',timer:2000,showConfirmButton:false});</script>
@endif
@endsection