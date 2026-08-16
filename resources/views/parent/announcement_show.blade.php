@extends('parent.layout')
@section('title', $announcement->title)

@section('content')
<a href="{{ route('parent.announcements') }}" style="color:#2563eb; text-decoration:none; font-size:.88rem; display:inline-block; margin-bottom:10px;"><i class="bi bi-arrow-left"></i> กลับไปหน้าประกาศ</a>

<div class="pp-card">
    <div style="color:#94a3b8; font-size:.82rem; margin-bottom:6px;">{{ $announcement->message_type ?? '' }} · {{ $announcement->created_at->format('d/m/Y H:i') }}</div>
    <div class="pp-title" style="border:none; padding:0;">{{ $announcement->title }}</div>

    @if($announcement->attachment_path)
    <img src="{{ asset('storage/' . $announcement->attachment_path) }}" style="width:100%; max-height:520px; object-fit:contain; border-radius:12px; background:#f8fafc; margin:14px 0;">
    @endif

    <div style="font-size:.95rem; line-height:1.8; white-space:pre-line; color:#334155;">{{ $announcement->body }}</div>

    @if($announcement->requires_ack)
    <div style="margin-top:24px; padding-top:18px; border-top:1px solid #f1f5f9;">
        @if($recipient->acknowledged_at)
        <div style="display:flex; align-items:center; gap:8px; color:#16a34a; font-weight:600;">
            <i class="bi bi-check-circle-fill" style="font-size:1.3rem;"></i>
            กดรับทราบแล้ว เมื่อ {{ $recipient->acknowledged_at->format('d/m/Y H:i') }}
        </div>
        @else
        <form method="POST" action="{{ route('parent.announcements.acknowledge', $announcement->announcement_id) }}">
            @csrf
            <button type="submit" class="btn" style="background:#16a34a; color:#fff; border:none; border-radius:10px; padding:10px 28px; font-weight:600;">
                <i class="bi bi-check-lg"></i> รับทราบแล้ว
            </button>
        </form>
        @endif
    </div>
    @endif
</div>
@endsection
