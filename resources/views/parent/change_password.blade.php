@extends('parent.layout')
@section('title', 'เปลี่ยนรหัสผ่าน')

@section('content')
<div class="pp-card" style="max-width:420px;">
    <div class="pp-title">เปลี่ยนรหัสผ่าน</div>

    <form method="POST" action="{{ route('parent.change-password.submit') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">รหัสผ่านเดิม</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">รหัสผ่านใหม่</label>
            <input type="password" name="new_password" class="form-control" required minlength="6">
        </div>
        <div class="mb-3">
            <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
            <input type="password" name="new_password_confirmation" class="form-control" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary">บันทึกรหัสผ่านใหม่</button>
    </form>
</div>
@endsection
