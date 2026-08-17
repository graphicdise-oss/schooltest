@extends('layouts.sidebar')

@push('styles')
<style>
    .ct-page { padding: 24px 28px; max-width: 720px; }
    .ct-card {
        background: #fff; border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        padding: 30px 24px 24px; position: relative;
        margin-top: 50px; margin-bottom: 28px;
    }
    .ct-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px; color: #fff; background: #ef4444;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .ct-card-title { margin-left: 90px; font-size: 1.05rem; color: #555; margin-top: -8px; font-weight: 600; }
    .ct-note { font-size: 0.85rem; color: #777; margin: 16px 0 20px; }

    .ct-label { font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: 5px; display: block; }
    .ct-input, .ct-textarea {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 9px 12px;
        font-size: 0.9rem; color: #333; width: 100%; font-family: inherit;
        outline: none; box-sizing: border-box; margin-bottom: 16px;
    }
    .ct-input:focus, .ct-textarea:focus { border-color: #ef4444; }
    .ct-textarea { resize: vertical; min-height: 140px; }
    .ct-btn-send {
        background: #ef4444; color: #fff; border: none; border-radius: 6px;
        padding: 10px 26px; font-size: 0.9rem; font-weight: 700; cursor: pointer; font-family: inherit;
        display: inline-flex; align-items: center; gap: 8px;
    }
    .ct-btn-send:hover { background: #dc2626; }
    .ct-alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 0.87rem; }
    .ct-alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .ct-alert-warning { background: #fff3e0; color: #b45309; border: 1px solid #fdba74; }
    .ct-alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
</style>
@endpush

@section('content')
<div class="ct-page">

    @if(session('success'))
        <div class="ct-alert ct-alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('saved_only'))
        <div class="ct-alert ct-alert-warning"><i class="fas fa-exclamation-triangle"></i> {{ session('saved_only') }}</div>
    @endif
    @if($errors->any())
        <div class="ct-alert ct-alert-error">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="ct-card">
        <div class="ct-icon"><i class="fas fa-headset"></i></div>
        <div class="ct-card-title">ติดต่อ-แจ้งปัญหา</div>
        <p class="ct-note">
            พบปัญหาการใช้งานระบบ หรือต้องการติดต่อผู้ดูแลระบบ กรอกแบบฟอร์มด้านล่างนี้ได้เลย
            ข้อความจะถูกส่งไปยังผู้ดูแลระบบทันที
        </p>

        <form method="POST" action="{{ route('contact.store') }}">
            @csrf
            <label class="ct-label">ชื่อผู้แจ้ง <span style="color:red;">*</span></label>
            <input type="text" name="name" class="ct-input" value="{{ old('name', $defaultName) }}" required>

            <label class="ct-label">อีเมลติดต่อกลับ (ไม่บังคับ)</label>
            <input type="email" name="email" class="ct-input" value="{{ old('email', $defaultEmail) }}" placeholder="เผื่อผู้ดูแลระบบต้องการติดต่อกลับ">

            <label class="ct-label">หัวข้อ <span style="color:red;">*</span></label>
            <input type="text" name="subject" class="ct-input" value="{{ old('subject') }}" placeholder="เช่น หน้านำเข้าเกรดขึ้น error" required>

            <label class="ct-label">รายละเอียด <span style="color:red;">*</span></label>
            <textarea name="message" class="ct-textarea" placeholder="อธิบายปัญหา หรือข้อความที่ต้องการติดต่อ" required>{{ old('message') }}</textarea>

            <button type="submit" class="ct-btn-send"><i class="fas fa-paper-plane"></i> ส่งข้อความ</button>
        </form>
    </div>

</div>
@endsection
