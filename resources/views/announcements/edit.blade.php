@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="{{ route('announcements.index') }}">ประชาสัมพันธ์</a><i class="bi bi-chevron-right"></i><a href="{{ route('announcements.show', $announcement->announcement_id) }}">{{ $announcement->title }}</a><i class="bi bi-chevron-right"></i><span>แก้ไข</span></nav>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-pencil"></i> แก้ไขประกาศ</div>
        <div class="ac-card-body">
            <p style="font-size:.82rem; color:#999; margin-bottom:16px">แก้ไขได้เฉพาะเนื้อหา — เปลี่ยนกลุ่มผู้รับไม่ได้ เพราะจะทำให้ประวัติการอ่าน/รับทราบไม่ตรงกับผู้รับจริง (ผู้รับเดิม: {{ $announcement->target_label }})</p>

            <form method="POST" action="{{ route('announcements.update', $announcement->announcement_id) }}" enctype="multipart/form-data" style="max-width:640px">
                @csrf @method('PUT')

                <label>ชื่อเรื่อง *</label>
                <input type="text" name="title" class="ac-input" required value="{{ old('title', $announcement->title) }}">

                <label style="margin-top:12px;">ประเภทข้อความ</label>
                <select name="message_type" class="ac-select">
                    @foreach(['แจ้งประกาศ/กิจกรรม','ข่าวสารทั่วไป','แจ้งเตือนเร่งด่วน','อื่นๆ'] as $t)
                    <option value="{{ $t }}" {{ old('message_type', $announcement->message_type) === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>

                <label style="margin-top:12px;">รายละเอียด *</label>
                <textarea name="body" class="ac-input" rows="6" required>{{ old('body', $announcement->body) }}</textarea>

                @if($announcement->attachment_path)
                <div style="margin-top:12px">
                    <div style="font-size:.8rem; color:#888; font-weight:600; margin-bottom:6px">ไฟล์แนบปัจจุบัน :</div>
                    <img src="{{ asset('storage/' . $announcement->attachment_path) }}" style="max-width:220px; border-radius:8px; border:1px solid #eee">
                </div>
                @endif
                <label style="margin-top:12px;">เปลี่ยนไฟล์แนบ (เว้นว่างถ้าไม่เปลี่ยน)</label>
                <input type="file" name="attachment" class="ac-input" accept="image/*">

                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-top:14px; background:#fff7ed; padding:10px; border-radius:8px; border:1px solid #fed7aa;">
                    <input type="checkbox" name="requires_ack" value="1" style="width:18px; height:18px; accent-color:#f97316; margin:0;" {{ old('requires_ack', $announcement->requires_ack) ? 'checked' : '' }}>
                    <span style="font-size:0.85rem; font-weight:600; color:#9a3412;">ต้องการให้ผู้ปกครองกด "รับทราบ" ยืนยัน</span>
                </label>

                <div style="display:flex; gap:10px; margin-top:20px">
                    <a href="{{ route('announcements.show', $announcement->announcement_id) }}" class="ac-btn ac-btn-secondary">ยกเลิก</a>
                    <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-check-lg"></i> บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
