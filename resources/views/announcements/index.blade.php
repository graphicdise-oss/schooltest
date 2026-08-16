@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page" x-data="{ targetType: '{{ old('target_type', 'all') }}' }">
    <nav class="ac-breadcrumb"><a href="#">บริหารทั่วไป</a><i class="bi bi-chevron-right"></i><span>ประชาสัมพันธ์</span></nav>

    <div class="ac-card">
        <div class="ac-card-header" style="display:flex; align-items:center; justify-content:space-between">
            <span><i class="bi bi-megaphone"></i> ประชาสัมพันธ์ — รายการประกาศทั้งหมด</span>
            <button type="button" class="ac-btn ac-btn-primary ac-btn-sm" onclick="document.getElementById('createAnnouncementOverlay').classList.add('active')"><i class="bi bi-plus-lg"></i> สร้างประกาศใหม่</button>
        </div>
        <div class="ac-card-body">
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>#</th><th>ชื่อเรื่อง</th><th>ประเภท</th><th>ผู้รับ</th><th>วันที่ส่ง</th><th>อ่านแล้ว</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($announcements as $i => $a)
                        <tr>
                            <td>{{ $announcements->firstItem() + $i }}</td>
                            <td style="text-align:left"><a href="{{ route('announcements.show', $a->announcement_id) }}">{{ $a->title }}</a></td>
                            <td>{{ $a->message_type ?? '-' }}</td>
                            <td>{{ $a->target_label }}</td>
                            <td>{{ $a->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="ac-badge ac-badge-active">{{ $a->recipients->count() }}/{{ $a->recipients_count }} คน</span></td>
                            <td>
                                <a href="{{ route('announcements.show', $a->announcement_id) }}" class="ac-action-btn ac-action-view" title="ดูรายละเอียด"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="ac-empty">ยังไม่มีประกาศ</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="ac-pagination">{{ $announcements->links() }}</div>
        </div>
    </div>

    {{-- Modal สร้างประกาศใหม่ --}}
    <div class="ac-overlay {{ $errors->any() ? 'active' : '' }}" id="createAnnouncementOverlay" onclick="if(event.target===this)this.classList.remove('active')">
        <div class="ac-modal" style="max-width:640px" onclick="event.stopPropagation()">
            <div class="ac-modal-header" style="background:#f8fafc; color:#333;"><i class="bi bi-megaphone me-2"></i> สร้างประกาศใหม่</div>
            <form method="POST" action="{{ route('announcements.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="ac-modal-body">
                    @if($errors->any())
                    <div style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; border-radius:8px; padding:10px 14px; margin-bottom:12px; font-size:.85rem">
                        @foreach($errors->all() as $err)<div>⚠ {{ $err }}</div>@endforeach
                    </div>
                    @endif
                    <label>ชื่อเรื่อง *</label>
                    <input type="text" name="title" class="ac-input" required placeholder="เช่น แนวทางการจัดระบบการรักษาความปลอดภัย..." value="{{ old('title') }}">

                    <label style="margin-top:12px;">ประเภทข้อความ</label>
                    <select name="message_type" class="ac-select">
                        <option value="แจ้งประกาศ/กิจกรรม">แจ้งประกาศ/กิจกรรม</option>
                        <option value="ข่าวสารทั่วไป">ข่าวสารทั่วไป</option>
                        <option value="แจ้งเตือนเร่งด่วน">แจ้งเตือนเร่งด่วน</option>
                        <option value="อื่นๆ">อื่นๆ</option>
                    </select>

                    <label style="margin-top:12px;">ผู้รับข้อความ *</label>
                    <select name="target_type" class="ac-select" x-model="targetType">
                        <option value="all">ทั้งหมด (ทุกคนในโรงเรียน)</option>
                        <option value="level">เฉพาะระดับชั้น</option>
                        <option value="section">เฉพาะห้อง</option>
                    </select>

                    <div x-show="targetType==='level'" x-cloak style="margin-top:10px">
                        <select name="target_level_id" class="ac-select">
                            <option value="">-- เลือกระดับชั้น --</option>
                            @foreach($levels as $lv)<option value="{{ $lv->level_id }}">{{ $lv->name }}</option>@endforeach
                        </select>
                    </div>
                    <div x-show="targetType==='section'" x-cloak style="margin-top:10px">
                        <select name="target_section_id" class="ac-select">
                            <option value="">-- เลือกห้อง --</option>
                            @foreach($sections as $sec)<option value="{{ $sec->section_id }}">{{ $sec->full_name }}</option>@endforeach
                        </select>
                    </div>

                    <label style="margin-top:12px;">รายละเอียด *</label>
                    <textarea name="body" class="ac-input" rows="6" required placeholder="เนื้อหาประกาศ...">{{ old('body') }}</textarea>

                    <label style="margin-top:12px;">ไฟล์แนบ (รูปภาพ)</label>
                    <input type="file" name="attachment" class="ac-input" accept="image/*">

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-top:14px; background:#fff7ed; padding:10px; border-radius:8px; border:1px solid #fed7aa;">
                        <input type="checkbox" name="requires_ack" value="1" style="width:18px; height:18px; accent-color:#f97316; margin:0;" {{ old('requires_ack') ? 'checked' : '' }}>
                        <span style="font-size:0.85rem; font-weight:600; color:#9a3412;">ต้องการให้ผู้ปกครองกด "รับทราบ" ยืนยัน</span>
                    </label>

                    <p style="font-size:.78rem;color:#999;margin-top:10px">ส่งแบบ "ส่งทันที" — ระบบจะบันทึกให้ผู้รับทุกคนเห็นในระบบผู้ปกครองทันทีที่กดบันทึก</p>
                </div>
                <div class="ac-modal-footer">
                    <button type="button" class="ac-btn ac-btn-secondary" onclick="document.getElementById('createAnnouncementOverlay').classList.remove('active')">ยกเลิก</button>
                    <button type="submit" class="ac-btn ac-btn-primary"><i class="bi bi-send"></i> ส่งประกาศ</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>Swal.fire({icon:'success',title:'สำเร็จ!',text:'{{ session("success") }}',timer:2000,showConfirmButton:false});</script>
@endif
@endsection
