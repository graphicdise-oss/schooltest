@extends('layouts.sidebar')

@push('styles')
<style>
    body { background:#f4f6f9; }
    .page { padding:24px 28px; }
    .breadcrumb-custom a { color:#00bcd4; text-decoration:none; font-size:.95rem; }
    .breadcrumb-custom i { color:#888; margin:0 8px; font-size:.8rem; }
    .card2 { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); padding:22px; margin-bottom:20px; }
    .card-title { color:#082b75; font-weight:700; font-size:1.05rem; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .filter-row { display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom:16px; }
    .filter-input, .filter-select { border:1px solid #d0d7e5; border-radius:6px; padding:8px 12px; font-size:.88rem; font-family:inherit; outline:none; min-width:180px; }
    table { width:100%; border-collapse:collapse; font-size:.88rem; }
    thead th { background:#f2f6ff; color:#082b75; font-weight:600; padding:10px 12px; text-align:left; white-space:nowrap; }
    tbody td { padding:9px 12px; border-bottom:1px solid #f0f2f7; color:#444; vertical-align:middle; }
    tbody tr:hover { background:#f7faff; }
    .badge2 { padding:3px 10px; border-radius:20px; font-size:.76rem; font-weight:600; white-space:nowrap; }
    .b-avail { background:#dcfce7; color:#16a34a; }
    .b-out { background:#fee2e2; color:#dc2626; }
    .b-overdue { background:#fff1e6; color:#d97706; }
    .btn-add { background:#4caf50; color:#fff; border:none; border-radius:6px; padding:8px 18px; font-size:.86rem; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
    .btn-add:hover { background:#43a047; }
    .btn-sm2 { border:none; border-radius:6px; padding:5px 12px; font-size:.78rem; font-weight:600; cursor:pointer; }
    .btn-loan { background:#2563eb; color:#fff; }
    .btn-loan:hover { background:#1e4fd0; }
    .btn-return { background:#16a34a; color:#fff; }
    .btn-return:hover { background:#12833c; }
    .btn-damage { background:#fff; color:#dc2626; border:1.5px solid #dc2626; }
    .btn-icon-edit { background:none; border:1.5px solid #f59e0b; color:#f59e0b; border-radius:4px; width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:.8rem; }
    .btn-icon-del { background:none; border:none; color:#e53935; font-size:1.05rem; font-weight:700; cursor:pointer; padding:0 6px; }
    .empty { text-align:center; color:#94a3b8; padding:26px 0; }
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:9999; align-items:center; justify-content:center; }
    .modal-overlay.active { display:flex; }
    .modal-box { background:#fff; border-radius:12px; width:460px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden; max-height:90vh; overflow-y:auto; }
    .modal-header { background:#4caf50; color:#fff; padding:16px 20px; font-size:1rem; font-weight:700; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; }
    .modal-header.edit-mode { background:#f59e0b; }
    .modal-header.loan-mode { background:#2563eb; }
    .modal-body { padding:20px; }
    .modal-label { font-size:.85rem; font-weight:600; color:#444; margin-bottom:5px; }
    .modal-input, .modal-select { border:1px solid #d0d7e5; border-radius:6px; padding:9px 12px; font-size:.9rem; width:100%; font-family:inherit; outline:none; box-sizing:border-box; margin-bottom:14px; }
    .modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:0 20px 20px; }
    .btn-modal-save { background:#4caf50; color:#fff; border:none; border-radius:6px; padding:9px 24px; font-size:.9rem; font-weight:700; cursor:pointer; }
    .btn-modal-save.edit-mode { background:#f59e0b; }
    .btn-modal-save.loan-mode { background:#2563eb; }
    .btn-modal-cancel { background:#fff; color:#666; border:1.5px solid #d0d7de; border-radius:6px; padding:9px 20px; font-size:.9rem; font-weight:600; cursor:pointer; }
    .btn-close-x { background:none; border:none; color:#fff; font-size:1.2rem; cursor:pointer; }
    .search-result { border:1px solid #e6ebf5; border-radius:6px; max-height:160px; overflow-y:auto; margin-top:-8px; margin-bottom:14px; }
    .search-item { padding:8px 12px; cursor:pointer; font-size:.86rem; border-bottom:1px solid #f0f2f7; }
    .search-item:hover { background:#f0f6ff; }
    .picked-borrower { background:#eef4ff; border-radius:6px; padding:8px 12px; font-size:.88rem; color:#082b75; margin-bottom:14px; display:none; }
</style>
@endpush

@section('content')
<div class="page">
    <nav class="breadcrumb-custom mb-3">
        <a href="#">ห้องสมุด</a><i class="bi bi-chevron-right"></i>
        <span style="color:#555;">จัดการห้องสมุด</span>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- แคตตาล็อกหนังสือ --}}
    <div class="card2">
        <div class="card-title">
            <span><i class="fas fa-book"></i> รายการหนังสือ</span>
            <button class="btn-add" onclick="openAddModal()"><i class="fas fa-plus"></i> เพิ่มหนังสือ</button>
        </div>

        <form method="GET" action="{{ route('library.books.index') }}" class="filter-row">
            <input type="text" name="search" class="filter-input" value="{{ $search }}" placeholder="ค้นหาชื่อ/ผู้แต่ง/รหัส">
            <select name="category_id" class="filter-select" onchange="this.form.submit()">
                <option value="">ทุกหมวดหมู่</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-add" style="background:#00bcd4;"><i class="fas fa-search"></i> ค้นหา</button>
        </form>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>รหัส</th>
                        <th>ชื่อหนังสือ</th>
                        <th>ผู้แต่ง</th>
                        <th>หมวดหมู่</th>
                        <th style="text-align:center;">คงเหลือ/ทั้งหมด</th>
                        <th style="text-align:center;">สถานะ</th>
                        <th style="text-align:right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books as $book)
                        <tr>
                            <td>{{ $book->code ?: '-' }}</td>
                            <td>{{ $book->title }}</td>
                            <td>{{ $book->author ?: '-' }}</td>
                            <td>{{ $book->category->name ?? '-' }}</td>
                            <td style="text-align:center;">{{ $book->available_copies }}/{{ $book->total_copies }}</td>
                            <td style="text-align:center;">
                                @if($book->available_copies > 0)
                                    <span class="badge2 b-avail">พร้อมให้ยืม</span>
                                @else
                                    <span class="badge2 b-out">ยืมหมด</span>
                                @endif
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                @if($book->available_copies > 0)
                                    <button class="btn-sm2 btn-loan" onclick="openLoanModal({{ $book->id }}, '{{ addslashes($book->title) }}')">
                                        <i class="fas fa-hand-holding"></i> ยืม
                                    </button>
                                @endif
                                <button class="btn-icon-edit" title="แก้ไข" onclick='openEditModal(@json($book))'>
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form action="{{ route('library.books.destroy', $book->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('ยืนยันการลบหนังสือ {{ addslashes($book->title) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-del" title="ลบ">✕</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty"><i class="fas fa-inbox"></i> ยังไม่มีหนังสือในระบบ</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $books->links() }}</div>
    </div>

    {{-- รายการที่ยืมอยู่ตอนนี้ --}}
    <div class="card2">
        <div class="card-title"><i class="fas fa-clock" style="color:#4b7ce3;"></i> รายการที่ยืมอยู่ตอนนี้ ({{ $activeLoans->count() }})</div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>หนังสือ</th>
                        <th>ผู้ยืม</th>
                        <th>รหัส</th>
                        <th>วันที่ยืม</th>
                        <th>กำหนดคืน</th>
                        <th style="text-align:center;">สถานะ</th>
                        <th style="text-align:right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeLoans as $loan)
                        <tr>
                            <td>{{ $loan->book->title ?? '-' }}</td>
                            <td>{{ $loan->borrower_name }}</td>
                            <td>{{ $loan->borrower_code }}</td>
                            <td>{{ $loan->borrowed_at?->format('d/m/Y') }}</td>
                            <td>{{ $loan->due_at?->format('d/m/Y') }}</td>
                            <td style="text-align:center;">
                                @if($loan->isOverdue())
                                    <span class="badge2 b-overdue">เกินกำหนด</span>
                                @else
                                    <span class="badge2 b-avail">ยืมอยู่</span>
                                @endif
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <form action="{{ route('library.loans.return', $loan->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('ยืนยันรับคืนหนังสือเล่มนี้?')">
                                    @csrf
                                    <button type="submit" class="btn-sm2 btn-return"><i class="fas fa-check"></i> รับคืน</button>
                                </form>
                                <button type="button" class="btn-sm2 btn-damage" onclick="openDamageModal({{ $loan->id }}, '{{ addslashes($loan->book->title ?? '') }}')">
                                    <i class="fas fa-triangle-exclamation"></i> ชำรุด/สูญหาย
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty"><i class="fas fa-inbox"></i> ไม่มีหนังสือถูกยืมอยู่ตอนนี้</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal เพิ่มหนังสือ --}}
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <span><i class="fas fa-plus me-2"></i>เพิ่มหนังสือ</span>
            <button class="btn-close-x" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST" action="{{ route('library.books.store') }}">
            @csrf
            <div class="modal-body">
                <div class="modal-label">ชื่อหนังสือ <span style="color:red">*</span></div>
                <input type="text" name="title" class="modal-input" required>
                <div class="modal-label">ผู้แต่ง</div>
                <input type="text" name="author" class="modal-input">
                <div class="modal-label">สำนักพิมพ์</div>
                <input type="text" name="publisher" class="modal-input">
                <div class="modal-label">หมวดหมู่</div>
                <select name="category_id" class="modal-select">
                    <option value="">— ไม่ระบุ —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <div class="modal-label">รหัสหนังสือ</div>
                        <input type="text" name="code" class="modal-input">
                    </div>
                    <div style="flex:1;">
                        <div class="modal-label">จำนวน (เล่ม) <span style="color:red">*</span></div>
                        <input type="number" name="total_copies" class="modal-input" value="1" min="1" required>
                    </div>
                </div>
                <div class="modal-label">ชั้นวาง</div>
                <input type="text" name="shelf_location" class="modal-input" placeholder="เช่น ชั้น A-1">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('addModal')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save"><i class="fas fa-check me-1"></i>บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal แก้ไขหนังสือ --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header edit-mode">
            <span><i class="fas fa-pen me-2"></i>แก้ไขหนังสือ</span>
            <button class="btn-close-x" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="modal-label">ชื่อหนังสือ <span style="color:red">*</span></div>
                <input type="text" name="title" id="e_title" class="modal-input" required>
                <div class="modal-label">ผู้แต่ง</div>
                <input type="text" name="author" id="e_author" class="modal-input">
                <div class="modal-label">สำนักพิมพ์</div>
                <input type="text" name="publisher" id="e_publisher" class="modal-input">
                <div class="modal-label">หมวดหมู่</div>
                <select name="category_id" id="e_category_id" class="modal-select">
                    <option value="">— ไม่ระบุ —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <div class="modal-label">รหัสหนังสือ</div>
                        <input type="text" name="code" id="e_code" class="modal-input">
                    </div>
                    <div style="flex:1;">
                        <div class="modal-label">จำนวน (เล่ม) <span style="color:red">*</span></div>
                        <input type="number" name="total_copies" id="e_total_copies" class="modal-input" min="1" required>
                    </div>
                </div>
                <div class="modal-label">ชั้นวาง</div>
                <input type="text" name="shelf_location" id="e_shelf_location" class="modal-input">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('editModal')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save edit-mode"><i class="fas fa-check me-1"></i>บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal ยืมหนังสือ --}}
<div class="modal-overlay" id="loanModal">
    <div class="modal-box">
        <div class="modal-header loan-mode">
            <span><i class="fas fa-hand-holding me-2"></i>ยืมหนังสือ: <span id="l_bookTitle"></span></span>
            <button class="btn-close-x" onclick="closeModal('loanModal')">✕</button>
        </div>
        <form method="POST" action="{{ route('library.loans.issue') }}" id="loanForm">
            @csrf
            <input type="hidden" name="book_id" id="l_book_id">
            <input type="hidden" name="borrower_type" id="l_borrower_type">
            <input type="hidden" name="borrower_id" id="l_borrower_id">
            <div class="modal-body">
                <div class="modal-label">ค้นหาผู้ยืม (ชื่อ/รหัสนักเรียน/รหัสพนักงาน)</div>
                <input type="text" id="l_search" class="modal-input" placeholder="พิมพ์เพื่อค้นหา..." autocomplete="off">
                <div class="search-result" id="l_results" style="display:none;"></div>
                <div class="picked-borrower" id="l_picked"></div>

                <div class="modal-label">กำหนดคืน <span style="color:red">*</span></div>
                <input type="date" name="due_at" id="l_due_at" class="modal-input" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('loanModal')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save loan-mode" id="l_submit" disabled><i class="fas fa-check me-1"></i>บันทึกการยืม</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal แจ้งชำรุด/สูญหาย --}}
<div class="modal-overlay" id="damageModal">
    <div class="modal-box">
        <div class="modal-header" style="background:#dc2626;">
            <span><i class="fas fa-triangle-exclamation me-2"></i>แจ้งชำรุด/สูญหาย: <span id="d_bookTitle"></span></span>
            <button class="btn-close-x" onclick="closeModal('damageModal')">✕</button>
        </div>
        <form method="POST" id="damageForm">
            @csrf
            <div class="modal-body">
                <div class="modal-label">สถานะ</div>
                <select name="status" class="modal-select">
                    <option value="ชำรุด">ชำรุด</option>
                    <option value="สูญหาย">สูญหาย</option>
                </select>
                <div class="modal-label">รายละเอียด</div>
                <textarea name="description" class="modal-input" rows="3" placeholder="อธิบายลักษณะความเสียหาย"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('damageModal')">ยกเลิก</button>
                <button type="submit" class="btn-modal-save" style="background:#dc2626;"><i class="fas fa-check me-1"></i>บันทึก</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddModal() { document.getElementById('addModal').classList.add('active'); }

    function openEditModal(book) {
        document.getElementById('e_title').value = book.title || '';
        document.getElementById('e_author').value = book.author || '';
        document.getElementById('e_publisher').value = book.publisher || '';
        document.getElementById('e_category_id').value = book.category_id || '';
        document.getElementById('e_code').value = book.code || '';
        document.getElementById('e_total_copies').value = book.total_copies || 1;
        document.getElementById('e_shelf_location').value = book.shelf_location || '';
        document.getElementById('editForm').action = '{{ url('library/books') }}/' + book.id;
        document.getElementById('editModal').classList.add('active');
    }

    function openLoanModal(bookId, title) {
        document.getElementById('l_bookTitle').textContent = title;
        document.getElementById('l_book_id').value = bookId;
        document.getElementById('l_borrower_type').value = '';
        document.getElementById('l_borrower_id').value = '';
        document.getElementById('l_search').value = '';
        document.getElementById('l_picked').style.display = 'none';
        document.getElementById('l_results').style.display = 'none';
        document.getElementById('l_submit').disabled = true;
        const d = new Date(); d.setDate(d.getDate() + 7);
        document.getElementById('l_due_at').value = d.toISOString().slice(0, 10);
        document.getElementById('loanModal').classList.add('active');
    }

    function openDamageModal(loanId, title) {
        document.getElementById('d_bookTitle').textContent = title;
        document.getElementById('damageForm').action = '{{ url('library/loans') }}/' + loanId + '/damage';
        document.getElementById('damageModal').classList.add('active');
    }

    function closeModal(id) { document.getElementById(id).classList.remove('active'); }
    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', function (e) { if (e.target === this) this.classList.remove('active'); });
    });

    // ค้นหาผู้ยืมแบบ debounce
    let l_timer = null;
    document.getElementById('l_search').addEventListener('input', function () {
        clearTimeout(l_timer);
        const q = this.value.trim();
        const box = document.getElementById('l_results');
        if (q.length < 2) { box.style.display = 'none'; return; }
        l_timer = setTimeout(() => {
            fetch('{{ route('library.loans.searchBorrower') }}?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(list => {
                    box.innerHTML = '';
                    if (!list.length) { box.style.display = 'none'; return; }
                    list.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'search-item';
                        div.textContent = (item.type === 'student' ? '🎓 ' : '👤 ') + item.code + ' — ' + item.name;
                        div.onclick = () => {
                            document.getElementById('l_borrower_type').value = item.type;
                            document.getElementById('l_borrower_id').value = item.id;
                            const picked = document.getElementById('l_picked');
                            picked.style.display = 'block';
                            picked.textContent = 'ผู้ยืม: ' + item.name + ' (' + item.code + ')';
                            box.style.display = 'none';
                            document.getElementById('l_search').value = item.name;
                            document.getElementById('l_submit').disabled = false;
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
