<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบฟอร์มคำร้องขอปรับปรุงแก้ไขระบบ</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Sarabun', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 760px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #1a56db 0%, #1e429f 100%);
            color: #fff;
            padding: 32px 40px;
            text-align: center;
        }

        .form-header h1 {
            font-size: 20px;
            font-weight: 700;
            line-height: 1.5;
        }

        .form-header p {
            font-size: 14px;
            opacity: 0.85;
            margin-top: 6px;
        }

        .form-body {
            padding: 36px 40px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a56db;
            border-left: 4px solid #1a56db;
            padding-left: 12px;
            margin-bottom: 20px;
            margin-top: 32px;
        }

        .section-title:first-child { margin-top: 0; }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        label span.req { color: #e02424; }

        input[type="text"],
        input[type="date"],
        input[type="url"],
        textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-family: 'Sarabun', sans-serif;
            font-size: 14px;
            color: #111827;
            transition: border-color 0.2s;
            background: #fafafa;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #1a56db;
            background: #fff;
        }

        textarea {
            resize: vertical;
            min-height: 110px;
        }

        .radio-group {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .radio-item, .check-item {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .radio-item input[type="radio"],
        .check-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #1a56db;
            cursor: pointer;
        }

        .radio-item label, .check-item label {
            font-weight: 400;
            margin-bottom: 0;
            cursor: pointer;
        }

        .check-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .error-list {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 24px;
        }

        .error-list p {
            font-size: 14px;
            color: #b91c1c;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .error-list ul { padding-left: 18px; }
        .error-list li { font-size: 13px; color: #b91c1c; }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #1a56db 0%, #1e429f 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: 'Sarabun', sans-serif;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 28px;
            transition: opacity 0.2s;
        }

        .btn-submit:hover { opacity: 0.9; }

        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 28px 0 0 0;
        }

        @media (max-width: 600px) {
            .form-body { padding: 24px 20px; }
            .form-row { grid-template-columns: 1fr; }
            .check-group { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
            <h1>แบบฟอร์มคำร้องขอปรับปรุงแก้ไขระบบ</h1>
            <p>System Change Request Form</p>
        </div>

        <div class="form-body">
            @if ($errors->any())
                <div class="error-list">
                    <p>กรุณาตรวจสอบข้อมูลที่กรอก</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('change-request.store') }}">
                @csrf

                {{-- ส่วนที่ 1 --}}
                <div class="section-title">1. ข้อมูลทั่วไป (General Information)</div>

                <div class="form-row">
                    <div class="form-group">
                        <label>ชื่อผู้ขอปรับปรุง <span class="req">*</span></label>
                        <input type="text" name="requester_name" value="{{ old('requester_name') }}"
                               placeholder="กรอกชื่อ-นามสกุล">
                    </div>
                    <div class="form-group">
                        <label>หน่วยงาน / แผนก <span class="req">*</span></label>
                        <input type="text" name="department" value="{{ old('department') }}"
                               placeholder="กรอกหน่วยงานหรือแผนก">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>วันที่ยื่นคำร้อง <span class="req">*</span></label>
                        <input type="date" name="request_date"
                               value="{{ old('request_date', date('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label>ระดับความสำคัญ <span class="req">*</span></label>
                        <div class="radio-group" style="margin-top: 10px;">
                            <label class="radio-item">
                                <input type="radio" name="priority" value="normal"
                                    {{ old('priority', 'normal') === 'normal' ? 'checked' : '' }}>
                                <span>ปกติ</span>
                            </label>
                            <label class="radio-item">
                                <input type="radio" name="priority" value="urgent"
                                    {{ old('priority') === 'urgent' ? 'checked' : '' }}>
                                <span>ด่วน</span>
                            </label>
                            <label class="radio-item">
                                <input type="radio" name="priority" value="critical"
                                    {{ old('priority') === 'critical' ? 'checked' : '' }}>
                                <span>ด่วนที่สุด (Critical)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <hr class="divider">

                {{-- ส่วนที่ 2 --}}
                <div class="section-title">2. รายละเอียดการแก้ไข (Change Details)</div>

                <div class="form-group">
                    <label>ชื่อโมดูล / ส่วนงานที่แก้ไข <span class="req">*</span></label>
                    <input type="text" name="module_name" value="{{ old('module_name') }}"
                           placeholder="เช่น ระบบฐานข้อมูลวิทยานิพนธ์, ระบบตรวจสอบเกรดเฉลี่ย">
                </div>

                <div class="form-group">
                    <label>ประเภทการดำเนินงาน</label>
                    <div class="check-group" style="margin-top: 8px;">
                        @php
                            $ops = old('operation_types', []);
                        @endphp
                        <label class="check-item">
                            <input type="checkbox" name="operation_types[]" value="new_feature"
                                {{ in_array('new_feature', $ops) ? 'checked' : '' }}>
                            <span>เพิ่มฟีเจอร์ใหม่ (New Feature)</span>
                        </label>
                        <label class="check-item">
                            <input type="checkbox" name="operation_types[]" value="bug_fix"
                                {{ in_array('bug_fix', $ops) ? 'checked' : '' }}>
                            <span>แก้ไขข้อผิดพลาด (Bug Fix)</span>
                        </label>
                        <label class="check-item">
                            <input type="checkbox" name="operation_types[]" value="optimization"
                                {{ in_array('optimization', $ops) ? 'checked' : '' }}>
                            <span>ปรับปรุงประสิทธิภาพ (Optimization)</span>
                        </label>
                        <label class="check-item">
                            <input type="checkbox" name="operation_types[]" value="ui_ux"
                                {{ in_array('ui_ux', $ops) ? 'checked' : '' }}>
                            <span>ปรับปรุง UI/UX (Interface Update)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>วัตถุประสงค์และเหตุผลการแก้ไข <span class="req">*</span></label>
                    <textarea name="objective" placeholder="อธิบายวัตถุประสงค์และเหตุผลที่ต้องการแก้ไขระบบ">{{ old('objective') }}</textarea>
                </div>

                <div class="form-group">
                    <label>ลิ้งที่ต้องการแก้ไข</label>
                    <input type="text" name="fix_link" value="{{ old('fix_link') }}"
                           placeholder="https://example.com/path/to/page">
                </div>

                <button type="submit" class="btn-submit">ดูตัวอย่างและแสดงผล &rarr;</button>
            </form>
        </div>
    </div>
</body>
</html>
