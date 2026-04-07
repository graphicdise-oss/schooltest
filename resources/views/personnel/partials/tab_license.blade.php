{{-- resources/views/personnel/partials/tab_license.blade.php --}}

{{-- ===== ใบอนุญาตประกอบวิชาชีพ ===== --}}
<div class="pn-card" x-data="{ showForm: false }">
    <div class="pn-card-header">
        <span><i class="bi bi-patch-check"></i> ใบอนุญาตประกอบวิชาชีพ</span>
        <button type="button" @click="showForm = !showForm" class="pn-btn-add-sm">
            <i class="bi" :class="showForm ? 'bi-x-lg' : 'bi-plus-lg'"></i>
            <span x-text="showForm ? 'ปิด' : 'เพิ่มข้อมูล'"></span>
        </button>
    </div>
    <div class="pn-card-body">
        <div x-show="showForm" x-cloak class="pn-inline-form">
            <form method="POST" action="{{ route('personnels.license.store') }}">
                @csrf
                <input type="hidden" name="personnel_id" value="{{ $personnel->personnel_id }}">
                <div class="pn-grid-3">
                    <div class="pn-field"><label>ประเภท</label><input type="text" name="license_type" class="pn-input" required></div>
                    <div class="pn-field"><label>เลขที่</label><input type="text" name="license_number" class="pn-input"></div>
                    <div class="pn-field"><label>ชื่อใบประกอบ</label><input type="text" name="license_name" class="pn-input"></div>
                    <div class="pn-field"><label>วันที่ออก</label><input type="date" name="issue_date" class="pn-input"></div>
                    <div class="pn-field"><label>วันหมดอายุ</label><input type="date" name="expiry_date" class="pn-input"></div>
                    <div class="pn-field"><label>หน่วยงานที่ออก</label><input type="text" name="issuing_organization" class="pn-input"></div>
                </div>
                <div class="pn-form-actions"><button type="submit" class="pn-btn-save-sm"><i class="bi bi-check-lg"></i> บันทึก</button></div>
            </form>
        </div>
        <div class="pn-table-wrap">
            <table class="pn-table">
                <thead><tr><th>#</th><th>ประเภท</th><th>เลขที่</th><th>ชื่อ</th><th>วันที่ออก</th><th>หมดอายุ</th><th>หน่วยงาน</th><th>จัดการ</th></tr></thead>
                <tbody>
                    @forelse($personnel->licenses as $i => $lic)
                    <tr id="row-lic-{{ $lic->license_id }}">
                        <td>{{ $i+1 }}</td><td>{{ $lic->license_type }}</td><td>{{ $lic->license_number }}</td><td>{{ $lic->license_name }}</td>
                        <td>{{ optional($lic->issue_date)->format('d/m/Y') }}</td><td>{{ optional($lic->expiry_date)->format('d/m/Y') }}</td>
                        <td>{{ $lic->issuing_organization }}</td>
                        <td class="pn-td-actions">
                            <button type="button" class="pn-action-btn pn-action-edit" onclick="toggleEdit('lic-{{ $lic->license_id }}')" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                            <form action="{{ route('personnels.license.destroy', $lic->license_id) }}" method="POST" onsubmit="return confirm('ลบ?')" style="display:inline">@csrf @method('DELETE')<button class="pn-action-btn pn-action-delete" title="ลบ"><i class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                    <tr id="edit-lic-{{ $lic->license_id }}" class="pn-edit-row" style="display:none">
                        <td colspan="8">
                            <form method="POST" action="{{ route('personnels.license.update', $lic->license_id) }}">
                                @csrf @method('PUT')
                                <div class="pn-grid-3">
                                    <div class="pn-field"><label>ประเภท</label><input type="text" name="license_type" class="pn-input" value="{{ $lic->license_type }}"></div>
                                    <div class="pn-field"><label>เลขที่</label><input type="text" name="license_number" class="pn-input" value="{{ $lic->license_number }}"></div>
                                    <div class="pn-field"><label>ชื่อ</label><input type="text" name="license_name" class="pn-input" value="{{ $lic->license_name }}"></div>
                                    <div class="pn-field"><label>วันที่ออก</label><input type="date" name="issue_date" class="pn-input" value="{{ optional($lic->issue_date)->format('Y-m-d') }}"></div>
                                    <div class="pn-field"><label>หมดอายุ</label><input type="date" name="expiry_date" class="pn-input" value="{{ optional($lic->expiry_date)->format('Y-m-d') }}"></div>
                                    <div class="pn-field"><label>หน่วยงาน</label><input type="text" name="issuing_organization" class="pn-input" value="{{ $lic->issuing_organization }}"></div>
                                </div>
                                <div class="pn-form-actions">
                                    <button type="button" class="pn-btn-cancel-sm" onclick="toggleEdit('lic-{{ $lic->license_id }}')"><i class="bi bi-x-lg"></i> ยกเลิก</button>
                                    <button type="submit" class="pn-btn-save-sm"><i class="bi bi-check-lg"></i> บันทึก</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="pn-empty">ไม่มีข้อมูล</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===== ประวัติการรับเครื่องราชฯ ===== --}}
<div class="pn-card" x-data="{ showForm: false }">
    <div class="pn-card-header">
        <span><i class="bi bi-star"></i> ประวัติการรับเครื่องราชอิสริยาภรณ์</span>
        <button type="button" @click="showForm = !showForm" class="pn-btn-add-sm">
            <i class="bi" :class="showForm ? 'bi-x-lg' : 'bi-plus-lg'"></i>
            <span x-text="showForm ? 'ปิด' : 'เพิ่มข้อมูล'"></span>
        </button>
    </div>
    <div class="pn-card-body">
        <div x-show="showForm" x-cloak class="pn-inline-form">
            <form method="POST" action="{{ route('personnels.decoration.store') }}">
                @csrf
                <input type="hidden" name="personnel_id" value="{{ $personnel->personnel_id }}">
                <div class="pn-grid-3">
                    <div class="pn-field"><label>ปีที่ได้รับ</label><input type="text" name="year_received" class="pn-input" required></div>
                    <div class="pn-field"><label>ชั้นเครื่องราชฯ</label><input type="text" name="decoration_class" class="pn-input"></div>
                    <div class="pn-field"><label>ตำแหน่ง</label><input type="text" name="position" class="pn-input"></div>
                    <div class="pn-field"><label>ราชกิจเล่มที่</label><input type="text" name="gazette_volume" class="pn-input"></div>
                    <div class="pn-field"><label>ราชกิจตอนที่</label><input type="text" name="gazette_section" class="pn-input"></div>
                    <div class="pn-field"><label>ราชกิจเลขที่</label><input type="text" name="gazette_number" class="pn-input"></div>
                    <div class="pn-field"><label>ลงวันที่</label><input type="date" name="gazette_date" class="pn-input"></div>
                </div>
                <div class="pn-form-actions"><button type="submit" class="pn-btn-save-sm"><i class="bi bi-check-lg"></i> บันทึก</button></div>
            </form>
        </div>
        <div class="pn-table-wrap">
            <table class="pn-table">
                <thead><tr><th>#</th><th>ปีที่ได้รับ</th><th>ชั้นเครื่องราชฯ</th><th>ตำแหน่ง</th><th>เล่มที่</th><th>ตอนที่</th><th>เลขที่</th><th>ลงวันที่</th><th>จัดการ</th></tr></thead>
                <tbody>
                    @forelse($personnel->decorations as $i => $dec)
                    <tr id="row-deco-{{ $dec->decoration_id }}">
                        <td>{{ $i+1 }}</td><td>{{ $dec->year_received }}</td><td>{{ $dec->decoration_class }}</td><td>{{ $dec->position }}</td>
                        <td>{{ $dec->gazette_volume }}</td><td>{{ $dec->gazette_section }}</td><td>{{ $dec->gazette_number }}</td>
                        <td>{{ optional($dec->gazette_date)->format('d/m/Y') }}</td>
                        <td class="pn-td-actions">
                            <button type="button" class="pn-action-btn pn-action-edit" onclick="toggleEdit('deco-{{ $dec->decoration_id }}')" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                            <form action="{{ route('personnels.decoration.destroy', $dec->decoration_id) }}" method="POST" onsubmit="return confirm('ลบ?')" style="display:inline">@csrf @method('DELETE')<button class="pn-action-btn pn-action-delete" title="ลบ"><i class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                    <tr id="edit-deco-{{ $dec->decoration_id }}" class="pn-edit-row" style="display:none">
                        <td colspan="9">
                            <form method="POST" action="{{ route('personnels.decoration.update', $dec->decoration_id) }}">
                                @csrf @method('PUT')
                                <div class="pn-grid-3">
                                    <div class="pn-field"><label>ปีที่ได้รับ</label><input type="text" name="year_received" class="pn-input" value="{{ $dec->year_received }}"></div>
                                    <div class="pn-field"><label>ชั้นเครื่องราชฯ</label><input type="text" name="decoration_class" class="pn-input" value="{{ $dec->decoration_class }}"></div>
                                    <div class="pn-field"><label>ตำแหน่ง</label><input type="text" name="position" class="pn-input" value="{{ $dec->position }}"></div>
                                    <div class="pn-field"><label>เล่มที่</label><input type="text" name="gazette_volume" class="pn-input" value="{{ $dec->gazette_volume }}"></div>
                                    <div class="pn-field"><label>ตอนที่</label><input type="text" name="gazette_section" class="pn-input" value="{{ $dec->gazette_section }}"></div>
                                    <div class="pn-field"><label>เลขที่</label><input type="text" name="gazette_number" class="pn-input" value="{{ $dec->gazette_number }}"></div>
                                    <div class="pn-field"><label>ลงวันที่</label><input type="date" name="gazette_date" class="pn-input" value="{{ optional($dec->gazette_date)->format('Y-m-d') }}"></div>
                                </div>
                                <div class="pn-form-actions">
                                    <button type="button" class="pn-btn-cancel-sm" onclick="toggleEdit('deco-{{ $dec->decoration_id }}')"><i class="bi bi-x-lg"></i> ยกเลิก</button>
                                    <button type="submit" class="pn-btn-save-sm"><i class="bi bi-check-lg"></i> บันทึก</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="pn-empty">ไม่มีข้อมูล</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Script (ถ้ายังไม่มีจาก tab_education) --}}
<script>
if (typeof toggleEdit === 'undefined') {
    function toggleEdit(id) {
        const editRow = document.getElementById('edit-' + id);
        const dataRow = document.getElementById('row-' + id);
        if (editRow.style.display === 'none') {
            editRow.style.display = '';
            dataRow.style.display = 'none';
        } else {
            editRow.style.display = 'none';
            dataRow.style.display = '';
        }
    }
}
</script>