@extends('layouts.sidebar')

@push('styles')
<style>
    .ls-page { padding: 24px 28px; min-height: 100%; }

    .ls-card {
        background: #fff; border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        padding: 30px 24px 24px; position: relative;
        margin-top: 50px; margin-bottom: 28px; border: none;
    }
    .ls-icon {
        position: absolute; top: -25px; left: 20px;
        width: 70px; height: 70px; border-radius: 4px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .ls-card-header {
        margin-left: 90px; font-size: 1.1rem; color: #555; margin-top: -10px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .ls-section-title {
        font-size: 1rem; font-weight: 700; color: #082b75;
        border-left: 4px solid #082b75; padding-left: 10px;
        margin-bottom: 18px; margin-top: 10px;
    }
    .ls-label { font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: 4px; }
    .ls-input {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 8px 12px;
        font-size: 0.88rem; color: #333; width: 100%;
        font-family: inherit; outline: none; transition: border 0.2s;
    }
    .ls-input:focus { border-color: #4b7ce3; box-shadow: 0 0 0 3px rgba(75,124,227,0.1); }
    .ls-select {
        border: 1px solid #d0d7e5; border-radius: 6px; padding: 8px 12px;
        font-size: 0.88rem; color: #333; width: 100%;
        font-family: inherit; outline: none; background: #fff;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%23666' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center; padding-right: 30px;
    }
    .ls-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .ls-table thead th {
        padding: 12px 14px; font-weight: 700; color: #333;
        border-bottom: 2px solid #e8eaf0; background: #f8f9fc; text-align: left;
    }
    .ls-table tbody tr { border-bottom: 1px solid #f2f4f8; transition: background 0.1s; }
    .ls-table tbody tr:hover { background: #f0f4ff; }
    .ls-table tbody td { padding: 12px 14px; color: #555; vertical-align: middle; }

    .btn-primary {
        background: #082b75; color: #fff; border: none; border-radius: 6px;
        padding: 10px 22px; font-size: 0.88rem; font-weight: 600;
        cursor: pointer; font-family: inherit; transition: background 0.2s;
    }
    .btn-primary:hover { background: #0a3491; }
    .btn-success {
        background: #22c55e; color: #fff; border: none; border-radius: 6px;
        padding: 8px 18px; font-size: 0.82rem; font-weight: 600;
        cursor: pointer; font-family: inherit; transition: background 0.2s;
    }
    .btn-success:hover { background: #16a34a; }
    .btn-danger {
        background: #ef4444; color: #fff; border: none; border-radius: 6px;
        padding: 7px 14px; font-size: 0.8rem; font-weight: 600;
        cursor: pointer; font-family: inherit; transition: background 0.2s;
    }
    .btn-danger:hover { background: #dc2626; }
    .btn-edit {
        background: #f59e0b; color: #fff; border: none; border-radius: 6px;
        padding: 7px 14px; font-size: 0.8rem; font-weight: 600;
        cursor: pointer; font-family: inherit; transition: background 0.2s;
    }
    .btn-edit:hover { background: #d97706; }
    .btn-cancel {
        background: #f3f4f6; color: #555; border: none; border-radius: 6px;
        padding: 9px 20px; font-size: 0.85rem; font-weight: 600;
        cursor: pointer; font-family: inherit;
    }

    .ls-note {
        background: #fffbeb; border: 1px solid #fcd34d; border-radius: 6px;
        padding: 10px 14px; font-size: 0.82rem; color: #92400e; margin-top: 10px;
    }
    .ls-note ol { margin: 0; padding-left: 18px; }
    .ls-note li { margin-bottom: 4px; }

    .ls-row { display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; }
    .ls-col { flex: 1; min-width: 120px; }

    .ls-alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 0.87rem; }
    .ls-alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .ls-alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    .ls-toggle { position: relative; display: inline-block; width: 44px; height: 24px; }
    .ls-toggle input { opacity: 0; width: 0; height: 0; }
    .ls-toggle-slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background: #ccc; border-radius: 24px; transition: 0.3s;
    }
    .ls-toggle-slider::before {
        content: ''; position: absolute; width: 18px; height: 18px;
        left: 3px; bottom: 3px; background: #fff;
        border-radius: 50%; transition: 0.3s;
    }
    input:checked + .ls-toggle-slider { background: #4caf50; }
    input:checked + .ls-toggle-slider::before { transform: translateX(20px); }

    .ls-divider { border: none; border-top: 1px solid #e8eaf0; margin: 24px 0; }

    .quota-group-card {
        border: 1px solid #e0e7ff; border-radius: 8px;
        padding: 18px 20px; margin-bottom: 18px; background: #f8f9ff;
    }
    .quota-group-card.inactive { background: #f9fafb; border-color: #d1d5db; opacity: 0.7; }
    .quota-group-header {
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;
    }
    .quota-group-title { font-weight: 700; color: #082b75; font-size: 0.95rem; }
    .quota-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
    .quota-table th { padding: 8px 10px; text-align: left; color: #555; font-weight: 600; border-bottom: 1px solid #e0e7ff; }
    .quota-table td { padding: 7px 10px; vertical-align: middle; }
    .quota-input {
        border: 1px solid #cbd5e1; border-radius: 5px; padding: 5px 8px;
        font-size: 0.82rem; width: 80px; text-align: center; font-family: inherit;
    }

    .ls-modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.4);
        display: flex; align-items: center; justify-content: center;
        z-index: 9999; padding: 16px;
    }
    .ls-modal {
        background: #fff; border-radius: 10px; padding: 28px;
        width: 100%; max-width: 540px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    .ls-modal-title { font-size: 1rem; font-weight: 700; color: #082b75; margin-bottom: 20px; }
    .ls-modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
</style>
@endpush

@section('content')
<div class="ls-page">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="ls-alert ls-alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="ls-alert ls-alert-error">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Section 1: ผู้อนุมัติการลา ===== --}}
    <div class="ls-card">
        <div class="ls-icon" style="background: #4b7ce3;">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="ls-card-header">
            <strong>1. ตั้งค่าจำนวนผู้อนุมัติการลา</strong>
        </div>

        <div style="margin-top: 24px;">
            <div class="ls-section-title">1.1 จำนวนผู้ตรวจสอบขั้นต่ำเพื่ออนุมัติ "ผ่าน"</div>
            <form action="{{ route('leave-settings.saveGeneral') }}" method="POST"
                style="display:flex;align-items:flex-end;gap:16px;flex-wrap:wrap;">
                @csrf
                <div>
                    <div class="ls-label">ต้องมีผู้ตรวจสอบอย่างน้อย (คน)</div>
                    <input type="number" name="min_approvers" class="ls-input" style="width:100px;"
                        value="{{ $setting->min_approvers }}" min="1" max="10" required>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> บันทึก
                </button>
            </form>
            <div class="ls-note" style="margin-top:12px;">
                <strong>หมายเหตุ :</strong> คำร้องที่มีผู้ไม่เห็นด้วยเพียง 1 คน จะถือว่าคำร้องนั้นเป็นโมฆะ
            </div>
        </div>

        <hr class="ls-divider">

        <div class="ls-section-title">1.2 รายชื่อผู้อนุมัติการลา</div>
        <div style="margin-bottom:14px;">
            <button class="btn-success" onclick="document.getElementById('modal-add-dept').style.display='flex'">
                <i class="fas fa-plus"></i> เพิ่มแผนก
            </button>
        </div>

        <table class="ls-table">
            <thead>
                <tr>
                    <th style="width:50px;">ลำดับ</th>
                    <th>ชื่อแผนก</th>
                    <th>ผู้อนุมัติ 1 (หัวหน้าแผนก)</th>
                    <th>ผู้อนุมัติ 2</th>
                    <th>ผู้อนุมัติ 3</th>
                    <th style="width:120px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deptApprovers as $dept)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $dept->department_name }}</td>
                    <td>{{ $dept->approver_1 ?: '-' }}</td>
                    <td>{{ $dept->approver_2 ?: '-' }}</td>
                    <td>{{ $dept->approver_3 ?: '-' }}</td>
                    <td>
                        <button class="btn-edit" onclick="openEditDept(
                            {{ $dept->id }},
                            '{{ addslashes($dept->department_name) }}',
                            '{{ addslashes($dept->approver_1) }}',
                            '{{ addslashes($dept->approver_2) }}',
                            '{{ addslashes($dept->approver_3) }}'
                        )"><i class="fas fa-pen"></i></button>
                        <form action="{{ route('leave-settings.destroyDept', $dept->id) }}" method="POST"
                            style="display:inline;"
                            onsubmit="return confirm('ยืนยันการลบแผนก {{ $dept->department_name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:#aaa;padding:24px;">ยังไม่มีข้อมูลแผนก</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

        {{-- ===== Section 2: โควตาวันลา ===== --}}
    <div class="ls-card">
        <div class="ls-icon" style="background: #22c55e;">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="ls-card-header">
            <strong>2. ตั้งค่าจำนวนการลาประจำปี</strong>
        </div>

        <div style="margin-top: 24px;">
            <div class="ls-section-title">2.1 กลุ่มช่วงปีทำงาน</div>
            <div style="margin-bottom:14px;">
                <button class="btn-success" onclick="document.getElementById('modal-add-group').style.display='flex'">
                    <i class="fas fa-plus"></i> เพิ่มกลุ่มช่วงปีทำงาน
                </button>
            </div>

            @forelse($quotaGroups as $group)
            <div class="quota-group-card {{ $group->is_active ? '' : 'inactive' }}">
                <div class="quota-group-header">
                    <div>
                        <span class="quota-group-title">
                            จำนวนปีทำงาน {{ $group->years_from }} ปี
                            @if($group->years_to !== null) ถึง {{ $group->years_to }} ปี @else ขึ้นไป @endif
                        </span>
                        @if(!$group->is_active)
                            <span style="font-size:0.78rem;color:#9ca3af;margin-left:8px;">(ปิดใช้งาน)</span>
                        @endif
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <form action="{{ route('leave-settings.toggleQuotaGroup', $group->id) }}" method="POST" style="display:inline;">
                            @csrf @method('PUT')
                            <label class="ls-toggle" title="{{ $group->is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}">
                                <input type="checkbox" onchange="this.form.submit()" {{ $group->is_active ? 'checked' : '' }}>
                                <span class="ls-toggle-slider"></span>
                            </label>
                        </form>
                        <form action="{{ route('leave-settings.destroyQuotaGroup', $group->id) }}" method="POST"
                            style="display:inline;"
                            onsubmit="return confirm('ยืนยันการลบกลุ่มนี้?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger" style="padding:5px 10px;font-size:0.75rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <form action="{{ route('leave-settings.updateQuotas', $group->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="ls-row" style="margin-bottom:14px;">
                        <div>
                            <label class="ls-label">ปีทำงานเริ่มต้น</label>
                            <input type="number" name="years_from" class="ls-input" style="width:90px;"
                                value="{{ $group->years_from }}" min="0" required>
                        </div>
                        <div>
                            <label class="ls-label">ปีทำงานสิ้นสุด (ว่างคือ ขึ้นไป)</label>
                            <input type="number" name="years_to" class="ls-input" style="width:90px;"
                                value="{{ $group->years_to }}" min="0">
                        </div>
                    </div>
                    <table class="quota-table">
                        <thead>
                            <tr>
                                <th>ลำดับ</th>
                                <th>ประเภทการลา</th>
                                <th style="text-align:center;">จำนวนวันต่อปี</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group->quotas as $quota)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $quota->leave_type_name }}</td>
                                <td style="text-align:center;">
                                    <input type="number" name="quotas[{{ $quota->id }}]"
                                        class="quota-input" value="{{ $quota->days_per_year }}" min="0" required>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div style="text-align:right;margin-top:12px;">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> บันทึกกลุ่มนี้
                        </button>
                    </div>
                </form>
            </div>
            @empty
            <div style="color:#aaa;text-align:center;padding:24px;">ยังไม่มีข้อมูลกลุ่มช่วงปีทำงาน</div>
            @endforelse

            <div class="ls-note">
                <strong>หมายเหตุ :</strong>
                <ol>
                    <li>สิทธิการลาต่างๆจะไม่สามารถสะสมได้ หากใช้ไม่ครบจะถูกตัดออก</li>
                    <li>สิทธิวันลาต่างๆจะคำนวณแบบเฉลี่ยตามสัดส่วนวันทำงานจริง
                        <br>กรณีที่ 1: นาย A เริ่มงานวันที่ 1 พ.ค. 2566 ถึง 30 เม.ย. 2567 จะได้รับสิทธิวันลาครบ (ทำงานครบ 1 ปี)
                        <br>กรณีที่ 2: นาย B เริ่มงานวันที่ 1 ก.ย. 2566 ถึง 30 เม.ย. 2567 จะได้รับสิทธิวันลา 4 วัน (ทำงาน 8 เดือน)
                    </li>
                    <li>การคำนวณทศนิยม: 0.1–0.4 = 0 วัน, 0.5–0.9 = ครึ่งวัน, 1.0 = 1 วัน</li>
                </ol>
            </div>
        </div>
    </div>

        {{-- ===== Section 3: วันตัดรอบ ===== --}}
    <div class="ls-card">
        <div class="ls-icon" style="background: #f59e0b;">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="ls-card-header">
            <strong>3. ตั้งค่าวันตัดรอบปีการทำงาน</strong>
        </div>

        <div style="margin-top: 24px;">
            <div class="ls-section-title">3.1 วันตัดรอบการทำงาน</div>
            <form action="{{ route('leave-settings.saveCutoff') }}" method="POST">
                @csrf
                <div class="ls-row">
                    <div class="ls-col" style="max-width:140px;">
                        <div class="ls-label">วันที่ (1-31)</div>
                        <input type="number" name="cutoff_day" class="ls-input"
                            value="{{ $setting->cutoff_day }}" min="1" max="31" required>
                    </div>
                    <div class="ls-col" style="max-width:180px;">
                        <div class="ls-label">เดือน</div>
                        <select name="cutoff_month" class="ls-select">
                            @foreach(['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'] as $i => $m)
                                <option value="{{ $i + 1 }}" {{ $setting->cutoff_month == $i + 1 ? 'selected' : '' }}>
                                    {{ $m }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> บันทึก
                        </button>
                    </div>
                </div>
            </form>
            <div class="ls-note" style="margin-top:12px;">
                <strong>หมายเหตุ :</strong> วันตัดรอบการทำงานจะใช้งานกับระบบการลาและการแจ้งเตือนมาสายของบุคลากร
            </div>
        </div>
    </div>

    {{-- ===== Section 4: การแจ้งเตือน ===== --}}
    <div class="ls-card">
        <div class="ls-icon" style="background: #ef4444;">
            <i class="fas fa-bell"></i>
        </div>
        <div class="ls-card-header">
            <strong>4. ตั้งค่าการแจ้งเตือนข้อมูลบุคลากร</strong>
        </div>

        <div style="margin-top: 24px;">
            <form action="{{ route('leave-settings.saveNotifications') }}" method="POST">
                @csrf

                {{-- 4.1 มาสาย --}}
                <div class="ls-section-title">4.1 แจ้งเตือนจำนวนการมาสาย</div>
                @php $lateAlerts = $notifications->get('late_arrival', collect())->keyBy('alert_number'); @endphp
                <div class="ls-row" style="margin-bottom:16px;gap:20px;">
                    @foreach([1, 2] as $n)
                    <div style="display:flex;align-items:center;gap:10px;background:#f8f9fc;padding:12px 16px;border-radius:8px;border:1px solid #e0e7ff;">
                        <span class="ls-label" style="margin:0;white-space:nowrap;">เตือนครั้งที่ {{ $n }} : สาย</span>
                        <input type="number" name="notifications[late_arrival][{{ $n }}]"
                            class="ls-input" style="width:80px;"
                            value="{{ $lateAlerts->get($n)?->threshold_value ?? ($n == 1 ? 5 : 10) }}" min="1">
                        <span class="ls-label" style="margin:0;">ครั้ง</span>
                    </div>
                    @endforeach
                </div>

                {{-- 4.2 VISA --}}
                <div class="ls-section-title">4.2 การแจ้งเตือนวันหมดอายุ VISA</div>
                @php $visaAlerts = $notifications->get('visa_expiry', collect())->keyBy('alert_number'); @endphp
                <div class="ls-row" style="margin-bottom:16px;gap:20px;">
                    <div style="display:flex;align-items:center;gap:10px;background:#f8f9fc;padding:12px 16px;border-radius:8px;border:1px solid #e0e7ff;">
                        <span class="ls-label" style="margin:0;white-space:nowrap;">เตือนครั้งที่ 1 : ก่อนครบกำหนด</span>
                        <input type="number" name="notifications[visa_expiry][1]"
                            class="ls-input" style="width:80px;"
                            value="{{ $visaAlerts->get(1)?->threshold_value ?? 45 }}" min="1">
                        <span class="ls-label" style="margin:0;">วัน</span>
                    </div>
                </div>

                {{-- 4.3 Work Permit --}}
                <div class="ls-section-title">4.3 การแจ้งเตือนวันหมดอายุ Work Permit</div>
                @php $wpAlerts = $notifications->get('work_permit_expiry', collect())->keyBy('alert_number'); @endphp
                <div class="ls-row" style="margin-bottom:16px;gap:20px;">
                    <div style="display:flex;align-items:center;gap:10px;background:#f8f9fc;padding:12px 16px;border-radius:8px;border:1px solid #e0e7ff;">
                        <span class="ls-label" style="margin:0;white-space:nowrap;">เตือนครั้งที่ 1 : ก่อนครบกำหนด</span>
                        <input type="number" name="notifications[work_permit_expiry][1]"
                            class="ls-input" style="width:80px;"
                            value="{{ $wpAlerts->get(1)?->threshold_value ?? 60 }}" min="1">
                        <span class="ls-label" style="margin:0;">วัน</span>
                    </div>
                </div>

                {{-- 4.4 ใบประกอบวิชาชีพ --}}
                <div class="ls-section-title">4.4 การแจ้งเตือนวันหมดอายุ ใบประกอบวิชาชีพ</div>
                @php $licAlerts = $notifications->get('license_expiry', collect())->keyBy('alert_number'); @endphp
                <div class="ls-row" style="margin-bottom:20px;gap:20px;">
                    <div style="display:flex;align-items:center;gap:10px;background:#f8f9fc;padding:12px 16px;border-radius:8px;border:1px solid #e0e7ff;">
                        <span class="ls-label" style="margin:0;white-space:nowrap;">เตือนครั้งที่ 1 : ก่อนครบกำหนด</span>
                        <input type="number" name="notifications[license_expiry][1]"
                            class="ls-input" style="width:80px;"
                            value="{{ $licAlerts->get(1)?->threshold_value ?? 90 }}" min="1">
                        <span class="ls-label" style="margin:0;">วัน</span>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> บันทึกการตั้งค่าการแจ้งเตือน
                </button>
            </form>

            <hr class="ls-divider">

            {{-- 4.5 ผู้รับการแจ้งเตือน --}}
            <div class="ls-section-title">4.5 ผู้มีสิทธิ์รับการแจ้งเตือน</div>
            <div style="margin-bottom:14px;">
                <button class="btn-success" onclick="document.getElementById('modal-add-recipient').style.display='flex'">
                    <i class="fas fa-plus"></i> เพิ่มผู้รับการแจ้งเตือน
                </button>
            </div>
            <table class="ls-table">
                <thead>
                    <tr>
                        <th style="width:50px;">ลำดับ</th>
                        <th>ตำแหน่ง</th>
                        <th>ชื่อ-สกุล</th>
                        <th style="width:80px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recipients as $r)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $r->position_name ?: '-' }}</td>
                        <td>{{ $r->personnel_name }}</td>
                        <td>
                            <form action="{{ route('leave-settings.destroyRecipient', $r->id) }}" method="POST"
                                onsubmit="return confirm('ยืนยันการลบผู้รับการแจ้งเตือน?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;color:#aaa;padding:24px;">ยังไม่มีข้อมูลผู้รับการแจ้งเตือน</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===== Modals ===== --}}

{{-- Modal: เพิ่มแผนก --}}
<div id="modal-add-dept" class="ls-modal-overlay" style="display:none;">
    <div class="ls-modal">
        <div class="ls-modal-title"><i class="fas fa-building"></i> เพิ่มแผนก</div>
        <form action="{{ route('leave-settings.storeDept') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px;">
                <div class="ls-label">ชื่อแผนก <span style="color:red;">*</span></div>
                <input type="text" name="department_name" class="ls-input" placeholder="เช่น ครูประถมศึกษา" required>
            </div>
            <div style="margin-bottom:14px;">
                <div class="ls-label">ผู้อนุมัติ 1 (หัวหน้าแผนก)</div>
                <select name="approver_1" class="ls-select">
                    <option value="">-- เลือกผู้อนุมัติ --</option>
                    @foreach($personnelList as $p)
                    <option value="{{ $p->thai_firstname }} {{ $p->thai_lastname }}">
                        {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:14px;">
                <div class="ls-label">ผู้อนุมัติ 2</div>
                <select name="approver_2" class="ls-select">
                    <option value="">-- เลือกผู้อนุมัติ --</option>
                    @foreach($personnelList as $p)
                    <option value="{{ $p->thai_firstname }} {{ $p->thai_lastname }}">
                        {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:14px;">
                <div class="ls-label">ผู้อนุมัติ 3</div>
                <select name="approver_3" class="ls-select">
                    <option value="">-- เลือกผู้อนุมัติ --</option>
                    @foreach($personnelList as $p)
                    <option value="{{ $p->thai_firstname }} {{ $p->thai_lastname }}">
                        {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="ls-modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('modal-add-dept').style.display='none'">ยกเลิก</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: แก้ไขแผนก --}}
<div id="modal-edit-dept" class="ls-modal-overlay" style="display:none;">
    <div class="ls-modal">
        <div class="ls-modal-title"><i class="fas fa-pen"></i> แก้ไขแผนก</div>
        <form id="form-edit-dept" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom:14px;">
                <div class="ls-label">ชื่อแผนก <span style="color:red;">*</span></div>
                <input type="text" name="department_name" id="edit-dept-name" class="ls-input" required>
            </div>
            <div style="margin-bottom:14px;">
                <div class="ls-label">ผู้อนุมัติ 1 (หัวหน้าแผนก)</div>
                <select name="approver_1" id="edit-approver-1" class="ls-select">
                    <option value="">-- เลือกผู้อนุมัติ --</option>
                    @foreach($personnelList as $p)
                    <option value="{{ $p->thai_firstname }} {{ $p->thai_lastname }}">
                        {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:14px;">
                <div class="ls-label">ผู้อนุมัติ 2</div>
                <select name="approver_2" id="edit-approver-2" class="ls-select">
                    <option value="">-- เลือกผู้อนุมัติ --</option>
                    @foreach($personnelList as $p)
                    <option value="{{ $p->thai_firstname }} {{ $p->thai_lastname }}">
                        {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:14px;">
                <div class="ls-label">ผู้อนุมัติ 3</div>
                <select name="approver_3" id="edit-approver-3" class="ls-select">
                    <option value="">-- เลือกผู้อนุมัติ --</option>
                    @foreach($personnelList as $p)
                    <option value="{{ $p->thai_firstname }} {{ $p->thai_lastname }}">
                        {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="ls-modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('modal-edit-dept').style.display='none'">ยกเลิก</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: เพิ่มกลุ่มปีทำงาน --}}
<div id="modal-add-group" class="ls-modal-overlay" style="display:none;">
    <div class="ls-modal">
        <div class="ls-modal-title"><i class="fas fa-layer-group"></i> เพิ่มกลุ่มช่วงปีทำงาน</div>
        <form action="{{ route('leave-settings.storeQuotaGroup') }}" method="POST">
            @csrf
            <div class="ls-row" style="margin-bottom:14px;">
                <div class="ls-col">
                    <div class="ls-label">ปีทำงานเริ่มต้น <span style="color:red;">*</span></div>
                    <input type="number" name="years_from" class="ls-input" value="0" min="0" required>
                </div>
                <div class="ls-col">
                    <div class="ls-label">ปีทำงานสิ้นสุด (ว่างคือ ขึ้นไป)</div>
                    <input type="number" name="years_to" class="ls-input" min="0">
                </div>
            </div>
            <p style="font-size:0.82rem;color:#6b7280;">
                ระบบจะสร้างโควตาวันลาเริ่มต้นทุกประเภทให้อัตโนมัติ สามารถแก้ไขได้ภายหลัง
            </p>
            <div class="ls-modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('modal-add-group').style.display='none'">ยกเลิก</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: เพิ่มผู้รับการแจ้งเตือน --}}
<div id="modal-add-recipient" class="ls-modal-overlay" style="display:none;">
    <div class="ls-modal">
        <div class="ls-modal-title"><i class="fas fa-user-plus"></i> เพิ่มผู้รับการแจ้งเตือน</div>
        <form action="{{ route('leave-settings.storeRecipient') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px;">
                <div class="ls-label">เลือกจากรายชื่อบุคลากร</div>
                <select name="personnel_id" class="ls-select" onchange="fillRecipientName(this)">
                    <option value="">-- เลือกบุคลากร --</option>
                    @foreach($personnelList as $p)
                    <option value="{{ $p->personnel_id }}"
                        data-name="{{ $p->thai_firstname }} {{ $p->thai_lastname }}"
                        data-position="{{ $p->department }}">
                        {{ $p->thai_prefix }}{{ $p->thai_firstname }} {{ $p->thai_lastname }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:14px;">
                <div class="ls-label">ตำแหน่ง</div>
                <input type="text" name="position_name" id="recip-position" class="ls-input" placeholder="เช่น รองผู้อำนวยการ">
            </div>
            <div style="margin-bottom:14px;">
                <div class="ls-label">ชื่อ-สกุล <span style="color:red;">*</span></div>
                <input type="text" name="personnel_name" id="recip-name" class="ls-input" placeholder="ชื่อ-สกุล" required>
            </div>
            <div class="ls-modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('modal-add-recipient').style.display='none'">ยกเลิก</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> บันทึก</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditDept(id, deptName, app1, app2, app3) {
    document.getElementById('form-edit-dept').action = '/leave-settings/dept/' + id;
    document.getElementById('edit-dept-name').value = deptName;
    setSelectValue('edit-approver-1', app1);
    setSelectValue('edit-approver-2', app2);
    setSelectValue('edit-approver-3', app3);
    document.getElementById('modal-edit-dept').style.display = 'flex';
}

function setSelectValue(id, val) {
    const sel = document.getElementById(id);
    for (let opt of sel.options) {
        if (opt.value === val) { sel.value = val; return; }
    }
    sel.value = '';
}

function fillRecipientName(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt.value) {
        document.getElementById('recip-name').value = opt.dataset.name;
        document.getElementById('recip-position').value = opt.dataset.position || '';
    }
}

document.querySelectorAll('.ls-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>
@endpush

@endsection