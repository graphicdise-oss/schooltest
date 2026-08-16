@extends('parent.layout')
@section('title', 'ประกาศ/ข่าวสาร')

@section('content')
<div class="pp-card">
    <div class="pp-title">ประกาศ/ข่าวสาร</div>

    @forelse($recipients as $r)
    @php($a = $r->announcement)
    <a href="{{ route('parent.announcements.show', $a->announcement_id) }}" class="d-block text-decoration-none"
       style="display:flex; align-items:center; gap:14px; padding:14px 12px; border-radius:12px; margin-bottom:8px; background:{{ $r->read_at ? '#fff' : '#eff6ff' }}; border:1px solid {{ $r->read_at ? '#f1f5f9' : '#bfdbfe' }};">
        <div style="width:42px; height:42px; border-radius:50%; background:{{ $r->read_at ? '#f1f5f9' : '#dbeafe' }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="bi bi-megaphone" style="color:{{ $r->read_at ? '#94a3b8' : '#2563eb' }};"></i>
        </div>
        <div style="flex:1; min-width:0;">
            <div style="color:#082b75; font-weight:{{ $r->read_at ? '500' : '700' }};">{{ $a->title }}</div>
            <div style="color:#94a3b8; font-size:.82rem; margin-top:2px;">{{ $a->message_type ?? '' }} · {{ $a->created_at->format('d/m/Y H:i') }}</div>
        </div>
        <div style="flex-shrink:0; display:flex; gap:6px; align-items:center;">
            @if(!$r->read_at)
            <span style="background:#2563eb; color:#fff; font-size:.72rem; font-weight:700; border-radius:999px; padding:3px 10px;">ยังไม่ได้อ่าน</span>
            @endif
            @if($a->requires_ack && !$r->acknowledged_at)
            <span style="background:#f97316; color:#fff; font-size:.72rem; font-weight:700; border-radius:999px; padding:3px 10px;">ต้องรับทราบ</span>
            @elseif($a->requires_ack && $r->acknowledged_at)
            <span style="background:#16a34a; color:#fff; font-size:.72rem; font-weight:700; border-radius:999px; padding:3px 10px;">รับทราบแล้ว</span>
            @endif
        </div>
    </a>
    @empty
    <div style="text-align:center; padding:40px; color:#94a3b8;">
        <i class="bi bi-inbox" style="font-size:2.2rem; display:block; margin-bottom:8px;"></i>
        ยังไม่มีประกาศ
    </div>
    @endforelse
</div>
@endsection
