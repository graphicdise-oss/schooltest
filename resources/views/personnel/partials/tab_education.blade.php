    {{-- resources/views/personnel/partials/tab_education.blade.php --}}

    {{-- ===== 1. ข้อมูลการศึกษา ===== --}}
    <div class="pn-card" x-data="{ showForm: false }">
        <div class="pn-card-header">
            <span><i class="bi bi-mortarboard"></i> ข้อมูลการศึกษา</span>
            <button type="button" @click="showForm = !showForm" class="pn-btn-add-sm">
                <i class="bi" :class="showForm ? 'bi-x-lg' : 'bi-plus-lg'"></i>
                <span x-text="showForm ? 'ปิด' : 'เพิ่มข้อมูล'"></span>
            </button>
        </div>
        <div class="pn-card-body">
            {{-- ฟอร์มเพิ่ม --}}
            <div x-show="showForm" x-cloak class="pn-inline-form">
                <form method="POST" action="{{ route('personnels.education.store') }}">
                    @csrf
                    <input type="hidden" name="personnel_id" value="{{ $personnel->personnel_id }}">
                    <div class="pn-grid-3">
                        <div class="pn-field"><label>สถาบันการศึกษา</label><input type="text" name="institution" class="pn-input" required></div>
                        <div class="pn-field"><label>ปีที่เริ่ม</label><input type="text" name="start_year" class="pn-input"></div>
                        <div class="pn-field"><label>ปีที่สำเร็จ</label><input type="text" name="end_year" class="pn-input"></div>
                        <div class="pn-field"><label>ระดับการศึกษา</label><input type="text" name="education_level" class="pn-input"></div>
                        <div class="pn-field"><label>วิชาเอก</label><input type="text" name="major" class="pn-input"></div>
                        <div class="pn-field"><label>วิชาโท</label><input type="text" name="minor" class="pn-input"></div>
                    </div>
                    <div class="pn-form-actions"><button type="submit" class="pn-btn-save-sm"><i class="bi bi-check-lg"></i> บันทึก</button></div>
                </form>
            </div>

            {{-- ตาราง --}}
            <div class="pn-table-wrap">
                <table class="pn-table">
                    <thead><tr><th>#</th><th>สถาบัน</th><th>ปีเริ่ม</th><th>ปีสำเร็จ</th><th>ระดับ</th><th>วิชาเอก</th><th>วิชาโท</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($personnel->educations as $i => $edu)
                        <tr id="row-edu-{{ $edu->education_id }}">
                            <td>{{ $i + 1 }}</td><td>{{ $edu->institution }}</td><td>{{ $edu->start_year }}</td><td>{{ $edu->end_year }}</td>
                            <td>{{ $edu->education_level }}</td><td>{{ $edu->major }}</td><td>{{ $edu->minor }}</td>
                            <td class="pn-td-actions">
                                <button type="button" class="pn-action-btn pn-action-edit" onclick="toggleEdit('edu-{{ $edu->education_id }}')" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('personnels.education.destroy', $edu->education_id) }}" method="POST" onsubmit="return confirm('ลบข้อมูลนี้?')" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button class="pn-action-btn pn-action-delete" title="ลบ"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        {{-- แถวแก้ไข (ซ่อนอยู่) --}}
                        <tr id="edit-edu-{{ $edu->education_id }}" class="pn-edit-row" style="display:none">
                            <td colspan="8">
                                <form method="POST" action="{{ route('personnels.education.update', $edu->education_id) }}">
                                    @csrf @method('PUT')
                                    <div class="pn-grid-3">
                                        <div class="pn-field"><label>สถาบัน</label><input type="text" name="institution" class="pn-input" value="{{ $edu->institution }}"></div>
                                        <div class="pn-field"><label>ปีเริ่ม</label><input type="text" name="start_year" class="pn-input" value="{{ $edu->start_year }}"></div>
                                        <div class="pn-field"><label>ปีสำเร็จ</label><input type="text" name="end_year" class="pn-input" value="{{ $edu->end_year }}"></div>
                                        <div class="pn-field"><label>ระดับ</label><input type="text" name="education_level" class="pn-input" value="{{ $edu->education_level }}"></div>
                                        <div class="pn-field"><label>วิชาเอก</label><input type="text" name="major" class="pn-input" value="{{ $edu->major }}"></div>
                                        <div class="pn-field"><label>วิชาโท</label><input type="text" name="minor" class="pn-input" value="{{ $edu->minor }}"></div>
                                    </div>
                                    <div class="pn-form-actions">
                                        <button type="button" class="pn-btn-cancel-sm" onclick="toggleEdit('edu-{{ $edu->education_id }}')"><i class="bi bi-x-lg"></i> ยกเลิก</button>
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

    {{-- ===== 2. ข้อมูลเกียรติคุณ ===== --}}
    <div class="pn-card" x-data="{ showForm: false }">
        <div class="pn-card-header">
            <span><i class="bi bi-trophy"></i> ข้อมูลเกียรติคุณ</span>
            <button type="button" @click="showForm = !showForm" class="pn-btn-add-sm">
                <i class="bi" :class="showForm ? 'bi-x-lg' : 'bi-plus-lg'"></i>
                <span x-text="showForm ? 'ปิด' : 'เพิ่มข้อมูล'"></span>
            </button>
        </div>
        <div class="pn-card-body">
            <div x-show="showForm" x-cloak class="pn-inline-form">
                <form method="POST" action="{{ route('personnels.honor.store') }}">
                    @csrf
                    <input type="hidden" name="personnel_id" value="{{ $personnel->personnel_id }}">
                    <div class="pn-grid-3">
                        <div class="pn-field"><label>ประเภทเกียรติคุณ</label><input type="text" name="honor_type" class="pn-input" required></div>
                        <div class="pn-field"><label>หน่วยงานที่มอบ</label><input type="text" name="organization" class="pn-input"></div>
                        <div class="pn-field"><label>ปีที่ได้รับ</label><input type="text" name="year_received" class="pn-input"></div>
                    </div>
                    <div class="pn-form-actions"><button type="submit" class="pn-btn-save-sm"><i class="bi bi-check-lg"></i> บันทึก</button></div>
                </form>
            </div>
            <div class="pn-table-wrap">
                <table class="pn-table">
                    <thead><tr><th>#</th><th>ประเภท</th><th>หน่วยงาน</th><th>ปีที่ได้รับ</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($personnel->honors as $i => $h)
                        <tr id="row-honor-{{ $h->honor_id }}">
                            <td>{{ $i + 1 }}</td><td>{{ $h->honor_type }}</td><td>{{ $h->organization }}</td><td>{{ $h->year_received }}</td>
                            <td class="pn-td-actions">
                                <button type="button" class="pn-action-btn pn-action-edit" onclick="toggleEdit('honor-{{ $h->honor_id }}')" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('personnels.honor.destroy', $h->honor_id) }}" method="POST" onsubmit="return confirm('ลบ?')" style="display:inline">@csrf @method('DELETE')<button class="pn-action-btn pn-action-delete" title="ลบ"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                        <tr id="edit-honor-{{ $h->honor_id }}" class="pn-edit-row" style="display:none">
                            <td colspan="5">
                                <form method="POST" action="{{ route('personnels.honor.update', $h->honor_id) }}">
                                    @csrf @method('PUT')
                                    <div class="pn-grid-3">
                                        <div class="pn-field"><label>ประเภท</label><input type="text" name="honor_type" class="pn-input" value="{{ $h->honor_type }}"></div>
                                        <div class="pn-field"><label>หน่วยงาน</label><input type="text" name="organization" class="pn-input" value="{{ $h->organization }}"></div>
                                        <div class="pn-field"><label>ปีที่ได้รับ</label><input type="text" name="year_received" class="pn-input" value="{{ $h->year_received }}"></div>
                                    </div>
                                    <div class="pn-form-actions">
                                        <button type="button" class="pn-btn-cancel-sm" onclick="toggleEdit('honor-{{ $h->honor_id }}')"><i class="bi bi-x-lg"></i> ยกเลิก</button>
                                        <button type="submit" class="pn-btn-save-sm"><i class="bi bi-check-lg"></i> บันทึก</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="pn-empty">ไม่มีข้อมูล</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== 3. ประวัติการอบรม/ศึกษา/ดูงาน ===== --}}
    <div class="pn-card" x-data="{ showForm: false }">
        <div class="pn-card-header">
            <span><i class="bi bi-journal-bookmark"></i> ประวัติการอบรม/ศึกษา/ดูงาน</span>
            <button type="button" @click="showForm = !showForm" class="pn-btn-add-sm">
                <i class="bi" :class="showForm ? 'bi-x-lg' : 'bi-plus-lg'"></i>
                <span x-text="showForm ? 'ปิด' : 'เพิ่มข้อมูล'"></span>
            </button>
        </div>
        <div class="pn-card-body">
            <div x-show="showForm" x-cloak class="pn-inline-form">
                <form method="POST" action="{{ route('personnels.training.store') }}">
                    @csrf
                    <input type="hidden" name="personnel_id" value="{{ $personnel->personnel_id }}">
                    <div class="pn-grid-3">
                        <div class="pn-field"><label>ประเภท</label><select name="training_type" class="pn-select"><option value="">-- เลือก --</option><option value="อบรม">อบรม</option><option value="ศึกษา">ศึกษา</option><option value="ดูงาน">ดูงาน</option></select></div>
                        <div class="pn-field"><label>โครงการ</label><input type="text" name="project" class="pn-input"></div>
                        <div class="pn-field"><label>ชื่อหลักสูตร</label><input type="text" name="course_name" class="pn-input" required></div>
                        <div class="pn-field"><label>วันที่เริ่ม</label><input type="date" name="start_date" class="pn-input"></div>
                        <div class="pn-field"><label>วันที่สิ้นสุด</label><input type="date" name="end_date" class="pn-input"></div>
                        <div class="pn-field"><label>จำนวนชั่วโมง</label><input type="number" name="hours" class="pn-input" step="0.5"></div>
                        <div class="pn-field"><label>สถานที่</label><input type="text" name="location" class="pn-input"></div>
                        <div class="pn-field"><label>ประเทศ</label><input type="text" name="country" class="pn-input"></div>
                        <div class="pn-field"><label>จังหวัด</label><input type="text" name="province" class="pn-input"></div>
                        <div class="pn-field"><label>ค่าใช้จ่าย</label><input type="number" name="expense" class="pn-input" step="0.01"></div>
                    </div>
                    <div class="pn-form-actions"><button type="submit" class="pn-btn-save-sm"><i class="bi bi-check-lg"></i> บันทึก</button></div>
                </form>
            </div>
            <div class="pn-table-wrap">
                <table class="pn-table">
                    <thead><tr><th>#</th><th>ประเภท</th><th>หลักสูตร</th><th>วันเริ่ม</th><th>วันสิ้นสุด</th><th>ชม.</th><th>สถานที่</th><th>ค่าใช้จ่าย</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($personnel->trainings as $i => $t)
                        <tr id="row-train-{{ $t->training_id }}">
                            <td>{{ $i+1 }}</td><td>{{ $t->training_type }}</td><td>{{ $t->course_name }}</td>
                            <td>{{ optional($t->start_date)->format('d/m/Y') }}</td><td>{{ optional($t->end_date)->format('d/m/Y') }}</td>
                            <td>{{ $t->hours }}</td><td>{{ $t->location }}</td><td>{{ number_format($t->expense, 2) }}</td>
                            <td class="pn-td-actions">
                                <button type="button" class="pn-action-btn pn-action-edit" onclick="toggleEdit('train-{{ $t->training_id }}')" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('personnels.training.destroy', $t->training_id) }}" method="POST" onsubmit="return confirm('ลบ?')" style="display:inline">@csrf @method('DELETE')<button class="pn-action-btn pn-action-delete" title="ลบ"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                        <tr id="edit-train-{{ $t->training_id }}" class="pn-edit-row" style="display:none">
                            <td colspan="9">
                                <form method="POST" action="{{ route('personnels.training.update', $t->training_id) }}">
                                    @csrf @method('PUT')
                                    <div class="pn-grid-3">
                                        <div class="pn-field"><label>ประเภท</label><select name="training_type" class="pn-select"><option value="อบรม" {{ $t->training_type=='อบรม'?'selected':'' }}>อบรม</option><option value="ศึกษา" {{ $t->training_type=='ศึกษา'?'selected':'' }}>ศึกษา</option><option value="ดูงาน" {{ $t->training_type=='ดูงาน'?'selected':'' }}>ดูงาน</option></select></div>
                                        <div class="pn-field"><label>โครงการ</label><input type="text" name="project" class="pn-input" value="{{ $t->project }}"></div>
                                        <div class="pn-field"><label>หลักสูตร</label><input type="text" name="course_name" class="pn-input" value="{{ $t->course_name }}"></div>
                                        <div class="pn-field"><label>วันเริ่ม</label><input type="date" name="start_date" class="pn-input" value="{{ optional($t->start_date)->format('Y-m-d') }}"></div>
                                        <div class="pn-field"><label>วันสิ้นสุด</label><input type="date" name="end_date" class="pn-input" value="{{ optional($t->end_date)->format('Y-m-d') }}"></div>
                                        <div class="pn-field"><label>ชม.</label><input type="number" name="hours" class="pn-input" step="0.5" value="{{ $t->hours }}"></div>
                                        <div class="pn-field"><label>สถานที่</label><input type="text" name="location" class="pn-input" value="{{ $t->location }}"></div>
                                        <div class="pn-field"><label>ประเทศ</label><input type="text" name="country" class="pn-input" value="{{ $t->country }}"></div>
                                        <div class="pn-field"><label>จังหวัด</label><input type="text" name="province" class="pn-input" value="{{ $t->province }}"></div>
                                        <div class="pn-field"><label>ค่าใช้จ่าย</label><input type="number" name="expense" class="pn-input" step="0.01" value="{{ $t->expense }}"></div>
                                    </div>
                                    <div class="pn-form-actions">
                                        <button type="button" class="pn-btn-cancel-sm" onclick="toggleEdit('train-{{ $t->training_id }}')"><i class="bi bi-x-lg"></i> ยกเลิก</button>
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

    {{-- ===== 4. TOEIC ===== --}}
    <div class="pn-card" x-data="{ showForm: false }">
        <div class="pn-card-header">
            <span><i class="bi bi-translate"></i> TOEIC</span>
            <button type="button" @click="showForm = !showForm" class="pn-btn-add-sm">
                <i class="bi" :class="showForm ? 'bi-x-lg' : 'bi-plus-lg'"></i>
                <span x-text="showForm ? 'ปิด' : 'เพิ่มข้อมูล'"></span>
            </button>
        </div>
        <div class="pn-card-body">
            <div x-show="showForm" x-cloak class="pn-inline-form">
                <form method="POST" action="{{ route('personnels.toeic.store') }}">
                    @csrf
                    <input type="hidden" name="personnel_id" value="{{ $personnel->personnel_id }}">
                    <div class="pn-grid-3">
                        <div class="pn-field"><label>คะแนน TOEIC</label><input type="number" name="score" class="pn-input" required></div>
                        <div class="pn-field"><label>สถาบัน</label><input type="text" name="institution" class="pn-input"></div>
                        <div class="pn-field"><label>วันหมดอายุ</label><input type="date" name="expiry_date" class="pn-input"></div>
                    </div>
                    <div class="pn-form-actions"><button type="submit" class="pn-btn-save-sm"><i class="bi bi-check-lg"></i> บันทึก</button></div>
                </form>
            </div>
            <div class="pn-table-wrap">
                <table class="pn-table">
                    <thead><tr><th>#</th><th>คะแนน</th><th>สถาบัน</th><th>วันหมดอายุ</th><th>จัดการ</th></tr></thead>
                    <tbody>
                        @forelse($personnel->toeics as $i => $tk)
                        <tr id="row-toeic-{{ $tk->toeic_id }}">
                            <td>{{ $i+1 }}</td><td>{{ $tk->score }}</td><td>{{ $tk->institution }}</td><td>{{ optional($tk->expiry_date)->format('d/m/Y') }}</td>
                            <td class="pn-td-actions">
                                <button type="button" class="pn-action-btn pn-action-edit" onclick="toggleEdit('toeic-{{ $tk->toeic_id }}')" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('personnels.toeic.destroy', $tk->toeic_id) }}" method="POST" onsubmit="return confirm('ลบ?')" style="display:inline">@csrf @method('DELETE')<button class="pn-action-btn pn-action-delete" title="ลบ"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                        <tr id="edit-toeic-{{ $tk->toeic_id }}" class="pn-edit-row" style="display:none">
                            <td colspan="5">
                                <form method="POST" action="{{ route('personnels.toeic.update', $tk->toeic_id) }}">
                                    @csrf @method('PUT')
                                    <div class="pn-grid-3">
                                        <div class="pn-field"><label>คะแนน</label><input type="number" name="score" class="pn-input" value="{{ $tk->score }}"></div>
                                        <div class="pn-field"><label>สถาบัน</label><input type="text" name="institution" class="pn-input" value="{{ $tk->institution }}"></div>
                                        <div class="pn-field"><label>วันหมดอายุ</label><input type="date" name="expiry_date" class="pn-input" value="{{ optional($tk->expiry_date)->format('Y-m-d') }}"></div>
                                    </div>
                                    <div class="pn-form-actions">
                                        <button type="button" class="pn-btn-cancel-sm" onclick="toggleEdit('toeic-{{ $tk->toeic_id }}')"><i class="bi bi-x-lg"></i> ยกเลิก</button>
                                        <button type="submit" class="pn-btn-save-sm"><i class="bi bi-check-lg"></i> บันทึก</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="pn-empty">ไม่มีข้อมูล</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Script สำหรับ toggle แถวแก้ไข --}}
    <script>
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
    </script>