@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; max-width:820px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:22px; margin-bottom:20px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
    .search-box { position:relative; }
    .search-input { border:1px solid #d0d7e5; border-radius:8px; padding:12px 14px; font-size:1rem; width:100%; font-family:inherit; outline:none; box-sizing:border-box; }
    .search-input:focus { border-color:#4b7ce3; }
    .search-result { border:1px solid #e6ebf5; border-radius:8px; margin-top:8px; max-height:220px; overflow-y:auto; }
    .search-item { padding:10px 14px; cursor:pointer; font-size:.95rem; border-bottom:1px solid #f0f2f7; display:flex; justify-content:space-between; }
    .search-item:hover { background:#f0f6ff; }
    .btn-checkin { background:#2563eb; color:#fff; border:none; border-radius:6px; padding:6px 16px; font-size:.85rem; font-weight:600; cursor:pointer; }
    .btn-checkin:hover { background:#1e4fd0; }
    table { width:100%; border-collapse:collapse; font-size:.9rem; }
    thead th { background:#f2f6ff; color:#082b75; font-weight:600; padding:10px 12px; text-align:left; }
    tbody td { padding:9px 12px; border-bottom:1px solid #f0f2f7; color:#444; }
    .empty { text-align:center; color:#94a3b8; padding:20px 0; }
    .type-tag { font-size:.76rem; padding:2px 10px; border-radius:20px; background:#eef4ff; color:#2563eb; }
</style>
@endpush

@section('content')
<div class="page">
    <nav class="breadcrumb-custom mb-3">
        <a href="#">ห้องสมุด</a><i class="bi bi-chevron-right"></i>
        <span style="color:#555;">ลงชื่อเข้าใช้ห้องสมุด</span>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card2">
        <div class="card-title"><i class="fas fa-magnifying-glass" style="color:#4b7ce3;"></i> ค้นหาเพื่อลงชื่อเข้าใช้</div>
        <div class="search-box">
            <input type="text" id="ci_search" class="search-input" placeholder="พิมพ์ชื่อ หรือ รหัสนักเรียน/รหัสพนักงาน..." autocomplete="off">
            <div class="search-result" id="ci_results" style="display:none;"></div>
        </div>
    </div>

    <div class="card2">
        <div class="card-title"><i class="fas fa-list" style="color:#4b7ce3;"></i> รายชื่อเข้าใช้วันนี้ ({{ $todayVisits->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th style="width:90px;">เวลา</th>
                    <th>ชื่อ - สกุล</th>
                    <th style="width:110px;">ประเภท</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayVisits as $v)
                    <tr>
                        <td>{{ $v->visited_at->format('H:i') }}</td>
                        <td>{{ $v->visitor_name }}</td>
                        <td><span class="type-tag">{{ $v->visitor_type === 'student' ? 'นักเรียน' : 'บุคลากร' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty"><i class="fas fa-inbox"></i> ยังไม่มีผู้เข้าใช้ห้องสมุดวันนี้</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ฟอร์มซ่อนไว้สำหรับส่งลงชื่อ --}}
<form method="POST" action="{{ route('library.checkin.store') }}" id="ci_form" style="display:none;">
    @csrf
    <input type="hidden" name="visitor_type" id="ci_type">
    <input type="hidden" name="visitor_id" id="ci_id">
</form>

@push('scripts')
<script>
    let ci_timer = null;
    document.getElementById('ci_search').addEventListener('input', function () {
        clearTimeout(ci_timer);
        const q = this.value.trim();
        const box = document.getElementById('ci_results');
        if (q.length < 2) { box.style.display = 'none'; return; }
        ci_timer = setTimeout(() => {
            fetch('{{ route('library.checkin.search') }}?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(list => {
                    box.innerHTML = '';
                    if (!list.length) { box.style.display = 'none'; return; }
                    list.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'search-item';
                        div.innerHTML = '<span>' + (item.type === 'student' ? '🎓 ' : '👤 ') + item.code + ' — ' + item.name + '</span>' +
                                        '<button type="button" class="btn-checkin">ลงชื่อเข้าใช้</button>';
                        div.querySelector('button').onclick = () => {
                            document.getElementById('ci_type').value = item.type;
                            document.getElementById('ci_id').value = item.id;
                            document.getElementById('ci_form').submit();
                        };
                        box.appendChild(div);
                    });
                    box.style.display = 'block';
                });
        }, 300);
    });
</script>
@endpush
@endsection
