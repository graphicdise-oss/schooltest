@extends('layouts.sidebar')
@push('styles')<link rel="stylesheet" href="{{ asset('css/academic/academic.css') }}?v={{ time() }}">@endpush

@section('content')
<div class="ac-page">
    <nav class="ac-breadcrumb"><a href="#">วิชาการ</a><i class="bi bi-chevron-right"></i><span>จัดการหลักสูตร</span></nav>

    <div class="ac-card">
        <div class="ac-card-header">
            <span><i class="bi bi-journal-text"></i> หลักสูตรทั้งหมด</span>
            <a href="{{ route('curriculums.create') }}" class="ac-btn ac-btn-success"><i class="bi bi-plus-lg"></i> สร้างหลักสูตร</a>
        </div>
        <div class="ac-card-body">
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead><tr><th>#</th><th>ชื่อหลักสูตร</th><th>ระดับชั้น</th><th>ปีที่ใช้</th><th>จำนวนวิชา</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($curriculums as $i => $c)
                        <tr>
                            <td>{{ $curriculums->firstItem() + $i }}</td>
                            <td style="text-align:left">{{ $c->name }}</td>
                            <td>{{ $c->level->name ?? 'ทุกระดับ' }}</td>
                            <td>{{ $c->year_applied ?? '-' }}</td>
                            <td><span class="ac-badge ac-badge-info">{{ $c->curriculumSubjects->count() ?? 0 }} วิชา</span></td>
                            <td><span class="ac-badge {{ $c->is_active ? 'ac-badge-active' : 'ac-badge-inactive' }}">{{ $c->is_active ? 'ใช้งาน' : 'ปิด' }}</span></td>
                            <td>
                                <a href="{{ route('curriculums.edit', $c->curriculum_id) }}" class="ac-action-btn ac-action-edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('curriculums.destroy', $c->curriculum_id) }}" method="POST" style="display:inline" onsubmit="return confirm('ลบ?')">@csrf @method('DELETE')<button class="ac-action-btn ac-action-delete"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                        @empty<tr><td colspan="7" class="ac-empty">ไม่มีข้อมูล</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            <div class="ac-pagination">{{ $curriculums->links() }}</div>
        </div>
    </div>
</div>
@endsection