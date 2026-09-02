@extends('parent.layout')
@section('title', 'แจ้งรับ-ส่งนักเรียน')

@section('content')
<div class="pp-card">
    <div class="pp-title">แจ้งการรับ-ส่งนักเรียน</div>
    <p class="text-muted" style="margin-top:-8px;">ถ้าวันไหนมีคนอื่นมารับนักเรียนแทนพ่อแม่ (ญาติ คนขับรถ ฯลฯ) แจ้งล่วงหน้าได้ที่นี่ ให้โรงเรียนรับทราบก่อน</p>

    <form method="POST" action="{{ route('parent.pickup-notices.store') }}" style="margin-top:16px;">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">วันที่ <span class="text-danger">*</span></label>
                <input type="date" name="notice_date" class="form-control" value="{{ old('notice_date', now()->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">เวลา (ถ้ามี)</label>
                <input type="time" name="pickup_time" class="form-control" value="{{ old('pickup_time') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">ใครจะมารับ</label>
                <select class="form-select" id="pickupPersonSelect" onchange="fillPickupPerson(this)">
                    <option value="">-- เลือกจากรายชื่อ หรือพิมพ์เองด้านล่าง --</option>
                    @foreach($families as $f)
                        <option value="{{ trim(($f->prefix_th ?? '') . ($f->first_name_th ?? '') . ' ' . ($f->last_name_th ?? '')) }}"
                            data-relationship="{{ $f->relationship }}" data-phone="{{ $f->phone_mobile ?? $f->phone_home }}">
                            {{ $f->relationship ?? $f->guardian_type }} — {{ $f->first_name_th }} {{ $f->last_name_th }}
                        </option>
                    @endforeach
                    <option value="__other__">อื่นๆ (พิมพ์เอง)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">ชื่อผู้มารับ <span class="text-danger">*</span></label>
                <input type="text" name="pickup_person_name" id="pickupPersonName" class="form-control" value="{{ old('pickup_person_name') }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">ความเกี่ยวข้อง</label>
                <input type="text" name="relationship" id="pickupRelationship" class="form-control" placeholder="เช่น ญาติ, คนขับรถ" value="{{ old('relationship') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">เบอร์โทร</label>
                <input type="text" name="phone" id="pickupPhone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">หมายเหตุ</label>
                <input type="text" name="note" class="form-control" value="{{ old('note') }}">
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> แจ้งการรับ-ส่ง</button>
        </div>
    </form>
</div>

<div class="pp-card">
    <div class="pp-title">รายการที่เคยแจ้งไว้</div>

    @if($notices->isEmpty())
        <div class="text-muted">ยังไม่เคยแจ้งการรับ-ส่งไว้</div>
    @else
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>เวลา</th>
                        <th>ผู้มารับ</th>
                        <th>ความเกี่ยวข้อง</th>
                        <th>เบอร์โทร</th>
                        <th>หมายเหตุ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notices as $n)
                    <tr>
                        <td>{{ $n->notice_date->format('d/m/Y') }}</td>
                        <td>{{ $n->pickup_time ? \Carbon\Carbon::parse($n->pickup_time)->format('H:i') : '-' }}</td>
                        <td>{{ $n->pickup_person_name }}</td>
                        <td>{{ $n->relationship ?? '-' }}</td>
                        <td>{{ $n->phone ?? '-' }}</td>
                        <td>{{ $n->note ?? '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('parent.pickup-notices.destroy', $n->id) }}" onsubmit="return confirm('ยกเลิกการแจ้งนี้?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
function fillPickupPerson(sel) {
    const opt = sel.options[sel.selectedIndex];
    const nameField = document.getElementById('pickupPersonName');
    const relField = document.getElementById('pickupRelationship');
    const phoneField = document.getElementById('pickupPhone');

    if (sel.value === '__other__') {
        nameField.value = '';
        relField.value = '';
        phoneField.value = '';
        nameField.focus();
        return;
    }
    if (!sel.value) return;

    nameField.value = sel.value;
    relField.value = opt.dataset.relationship || '';
    phoneField.value = opt.dataset.phone || '';
}
</script>
@endsection
