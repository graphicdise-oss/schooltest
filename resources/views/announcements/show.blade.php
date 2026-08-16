@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="{{ route('announcements.index') }}">ประชาสัมพันธ์</a><i class="bi bi-chevron-right"></i><span>{{ $announcement->title }}</span></nav>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-file-earmark-text"></i> รายละเอียดประกาศ</div>
        <div class="ac-card-body">
            <table style="width:100%; border-collapse:collapse; margin-bottom:18px">
                <tr>
                    <td style="width:50%; vertical-align:top; padding:6px 0">
                        <div style="font-size:.8rem; color:#888; font-weight:600">ชื่อเรื่อง :</div>
                        <div style="font-size:.95rem">{{ $announcement->title }}</div>
                    </td>
                    <td style="width:50%; vertical-align:top; padding:6px 0">
                        <div style="font-size:.8rem; color:#888; font-weight:600">การตอบรับ :</div>
                        <div style="font-size:.95rem">{{ $announcement->requires_ack ? 'ต้องรับทราบ' : 'ไม่บังคับรับทราบ' }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align:top; padding:6px 0">
                        <div style="font-size:.8rem; color:#888; font-weight:600">ประเภทข้อความ :</div>
                        <div style="font-size:.95rem">{{ $announcement->message_type ?? '-' }}</div>
                    </td>
                    <td style="vertical-align:top; padding:6px 0">
                        <div style="font-size:.8rem; color:#888; font-weight:600">ผู้รับข้อความ :</div>
                        <div style="font-size:.95rem">{{ $announcement->target_label }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align:top; padding:6px 0">
                        <div style="font-size:.8rem; color:#888; font-weight:600">ประเภทการส่ง :</div>
                        <div style="font-size:.95rem">{{ $announcement->send_type }}</div>
                    </td>
                    <td style="vertical-align:top; padding:6px 0">
                        <div style="font-size:.8rem; color:#888; font-weight:600">รูปแบบการส่ง :</div>
                        <div style="font-size:.95rem">{{ $announcement->delivery_mode }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align:top; padding:6px 0">
                        <div style="font-size:.8rem; color:#888; font-weight:600">วันที่ส่ง :</div>
                        <div style="font-size:.95rem">{{ $announcement->created_at->format('d/m/Y H:i') }}</div>
                    </td>
                    <td style="vertical-align:top; padding:6px 0">
                        <div style="font-size:.8rem; color:#888; font-weight:600">ผู้ส่ง :</div>
                        <div style="font-size:.95rem">{{ $announcement->created_by ?? '-' }}</div>
                    </td>
                </tr>
            </table>

            <div style="font-size:.8rem; color:#888; font-weight:600; margin-bottom:6px">รายละเอียด :</div>
            <div style="font-size:.9rem; line-height:1.7; background:#f8fafc; border-radius:10px; padding:16px; white-space:pre-line; margin-bottom:18px">{{ $announcement->body }}</div>

            @if($announcement->attachment_path)
            <div style="font-size:.8rem; color:#888; font-weight:600; margin-bottom:6px">ไฟล์แนบ :</div>
            <img src="{{ asset('storage/' . $announcement->attachment_path) }}" style="max-width:100%; max-height:420px; border-radius:10px; border:1px solid #eee; margin-bottom:18px">
            @endif

            <div style="display:flex; gap:10px">
                <a href="{{ route('announcements.index') }}" class="ac-btn ac-btn-secondary"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
                <a href="{{ route('announcements.edit', $announcement->announcement_id) }}" class="ac-btn ac-btn-primary"><i class="bi bi-pencil"></i> แก้ไขข้อมูล</a>
                <form method="POST" action="{{ route('announcements.destroy', $announcement->announcement_id) }}" onsubmit="return confirm('ยืนยันการลบประกาศนี้? รายการอ่าน/รับทราบทั้งหมดจะถูกลบไปด้วย')" style="display:inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="ac-btn ac-btn-danger"><i class="bi bi-trash"></i> ลบข้อมูล</button>
                </form>
            </div>
        </div>
    </div>

    <div class="ac-card">
        <div class="ac-card-header"><i class="bi bi-people"></i> สถานะการอ่าน/รับทราบของผู้รับ ({{ $recipients->count() }} คน)</div>
        <div class="ac-card-body">
            <div class="ac-field" style="max-width:420px; margin-bottom:14px">
                <input type="text" id="recipientSearch" class="ac-input" placeholder="ค้นหา ลำดับ / รหัส / ชื่อ-นามสกุล / ห้อง / สถานะ" onkeyup="filterRecipients()">
            </div>
            <div class="ac-table-wrap">
                <table class="ac-table" id="recipientTable">
                    <thead><tr><th>ลำดับ</th><th>ระดับชั้น/ห้อง</th><th>รหัส</th><th>ชื่อ-นามสกุล</th><th>ประเภท</th><th>สถานะ</th><th>การตอบกลับ</th></tr></thead>
                    <tbody>
                        @forelse($recipients as $i => $r)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $r->section_name }}</td>
                            <td>{{ $r->student_code }}</td>
                            <td style="text-align:left">{{ $r->name }}</td>
                            <td>นักเรียน</td>
                            <td>
                                @if($r->read_at)
                                <span class="ac-badge ac-badge-active">อ่านแล้ว</span>
                                @else
                                <span class="ac-badge ac-badge-inactive">ไม่ได้อ่าน</span>
                                @endif
                            </td>
                            <td>
                                @if($r->acknowledged_at)
                                <span class="ac-badge ac-badge-active">รับทราบ</span>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="ac-empty">ยังไม่มีผู้รับ</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>Swal.fire({icon:'success',title:'สำเร็จ!',text:'{{ session("success") }}',timer:2000,showConfirmButton:false});</script>
@endif

<script>
function filterRecipients() {
    const q = document.getElementById('recipientSearch').value.trim().toLowerCase();
    document.querySelectorAll('#recipientTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>
@endsection
