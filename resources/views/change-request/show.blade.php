<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบฟอร์มคำร้องขอปรับปรุงแก้ไขระบบ</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'TH Sarabun New', 'Sarabun', sans-serif;
            background: #f0f4f8;
            padding: 30px 20px;
            font-size: 16px;
        }

        .action-bar {
            max-width: 794px;
            margin: 0 auto 20px auto;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .btn:hover {
            opacity: 0.85;
        }

        .btn-back {
            background: #fff;
            color: #374151;
            border: 1.5px solid #d1d5db;
        }

        .btn-print {
            background: linear-gradient(135deg, #1a56db 0%, #1e429f 100%);
            color: #fff;
        }

        .document {
            max-width: 794px;
            margin: 0 auto;
            background: #fff;
            padding: 48px 56px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.10);
            border-radius: 8px;
        }

        .doc-title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 36px;
            line-height: 1.1;
        }

        .section-heading {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
            margin-top: 28px;
        }

        .divider {
            border: none;
            border-top: 1px solid #ddd;
            margin: 24px 0;
        }

        .field-row {
            display: flex;
            align-items: baseline;
            margin-bottom: 16px;
        }

        .field-label {
            font-size: 16px;
            white-space: nowrap;
            flex-shrink: 0;
            line-height: 1.1;
            font-weight: 400;
        }

        .field-bracket {
            font-size: 16px;
            white-space: nowrap;
            flex-shrink: 0;
            font-weight: 400;
        }

        /* dots ชิดตัวอักษร */
        .dot-line {
            flex: 1;
            min-width: 60px;
            margin-left: 4px;
            font-size: 16px;
            font-weight: 400;
            color: #111;
            line-height: 1.1;
            border-bottom: 1px dashed #aaa;
            padding-bottom: 0;
        }

        .dot-line.center {
            text-align: center;
        }

        .priority-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            margin-bottom: 16px;
            flex-wrap: wrap;
            line-height: 1.1;
            font-weight: 400;
        }

        .priority-box {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .check-box {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1.5px solid #333;
            text-align: center;
            line-height: 1.1;
            font-size: 16px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .check-box.checked {
            color: #111;
        }

        .op-list {
            margin: 6px 0 16px 0;
        }

        .op-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            margin-bottom: 8px;
            line-height: 1.1;
            font-weight: 400;
        }

        /* บรรทัดวัตถุประสงค์ */
        .obj-line {
            font-size: 16px;
            font-weight: 400;
            color: #111;
            line-height: 1.1;
            padding-bottom: 0;
            margin-bottom: 18px;
            display: block;
            width: 100%;
            border-bottom: 1px dashed #aaa;
        }

        .link-line {
            font-size: 16px;
            font-weight: 400;
            color: #111;
            line-height: 1.1;
            padding-bottom: 0;
            display: block;
            word-break: break-all;
            width: 100%;
            border-bottom: 1px dashed #aaa;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .action-bar {
                display: none !important;
            }

            .document {
                box-shadow: none;
                border-radius: 0;
                padding: 15mm 18mm;
                max-width: 100%;
            }

            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="action-bar">
        <a href="{{ route('change-request.create') }}" class="btn btn-back">&#8592; กลับแก้ไข</a>

        @if (!empty($isPreview))
            {{-- ยังไม่บันทึก → มีปุ่มบันทึก --}}
            <form method="POST" action="{{ route('change-request.store') }}" style="margin:0;">
                @csrf
                @foreach ($data as $key => $value)
                    @if (is_array($value))
                        @foreach ($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <input type="hidden" name="action" value="save">
                <button type="submit" class="btn btn-print" style="background:#16a34a;">
                    &#10003; บันทึก
                </button>
            </form>
        @else
            <span style="color:#16a34a; font-weight:700; font-size:14px; align-self:center;">&#10003; บันทึกแล้ว</span>
        @endif

        <button onclick="window.print()" class="btn btn-print">&#128438; พิมพ์ / PDF</button>
    </div>

    <div class="document">

        <div class="doc-title">
            แบบฟอร์มคำร้องขอปรับปรุงแก้ไขระบบ (System Change Request Form)
        </div>

        @php
            $thaiMonths = [
                1 => 'มกราคม',
                2 => 'กุมภาพันธ์',
                3 => 'มีนาคม',
                4 => 'เมษายน',
                5 => 'พฤษภาคม',
                6 => 'มิถุนายน',
                7 => 'กรกฎาคม',
                8 => 'สิงหาคม',
                9 => 'กันยายน',
                10 => 'ตุลาคม',
                11 => 'พฤศจิกายน',
                12 => 'ธันวาคม',
            ];
            $dateObj = \Carbon\Carbon::parse($data['request_date'] ?? $data->request_date);
            $thaiDate = $dateObj->day . ' ' . $thaiMonths[$dateObj->month] . ' ' . ($dateObj->year + 543);
            $reqName = $data['requester_name'] ?? $data->requester_name;
            $dept = $data['department'] ?? $data->department;
            $priority = $data['priority'] ?? $data->priority;
            $modName = $data['module_name'] ?? $data->module_name;
            $ops = (array) ($data['operation_types'] ?? $data->operation_types ?? []);
            $objective = $data['objective'] ?? $data->objective;
            $fixLink = $data['fix_link'] ?? $data->fix_link ?? '';
        @endphp

        {{-- ส่วนที่ 1 --}}
        <div class="section-heading">1. ข้อมูลทั่วไป (General Information)</div>

        <div class="field-row">
            <span class="field-label">ชื่อผู้ขอปรับปรุง:&nbsp;</span>
            <span class="dot-line">{{ $reqName }}</span>
        </div>

        <div class="field-row">
            <span class="field-label">หน่วยงาน/แผนก:&nbsp;</span>
            <span class="dot-line">{{ $dept }}</span>
        </div>

        <div class="field-row">
            <span class="field-label">วันที่ยื่นคำร้อง:&nbsp;[&nbsp;</span>
            <span class="dot-line">{{ $thaiDate }}</span>
            <span class="field-bracket">&nbsp;]</span>
        </div>

        @php
            $priorityMap = ['normal' => 'ปกติ', 'urgent' => 'ด่วน', 'critical' => 'ด่วนที่สุด (System Bug/Critical)'];
        @endphp

        <div class="priority-row">
            <span>ระดับความสำคัญ:</span>
            @foreach ($priorityMap as $key => $label)
                <span class="priority-box">
                    <span class="check-box {{ $priority === $key ? 'checked' : '' }}">
                        {!! $priority === $key ? '✓' : '&nbsp;' !!}
                    </span>
                    {{ $label }}
                </span>
            @endforeach
        </div>

        <hr class="divider">

        {{-- ส่วนที่ 2 --}}
        <div class="section-heading">2. รายละเอียดการแก้ไข (Change Details)</div>

        <div class="field-row">
            <span class="field-label">ชื่อโมดูล/ส่วนงานที่แก้ไข:&nbsp;</span>
            <span class="dot-line">{{ $modName }}</span>
        </div>

        <div style="font-size:18px; margin-bottom:10px;">ประเภทการดำเนินงาน:</div>

        @php
            $opMap = [
                'new_feature' => 'เพิ่มฟีเจอร์ใหม่ (New Feature)',
                'bug_fix' => 'แก้ไขข้อผิดพลาด (Bug Fix)',
                'optimization' => 'ปรับปรุงประสิทธิภาพ (Optimization)',
                'ui_ux' => 'ปรับปรุง UI/UX (Interface Update)',
            ];
        @endphp

        <div class="op-list">
            @foreach ($opMap as $key => $label)
                <div class="op-item">
                    <span class="check-box {{ in_array($key, $ops) ? 'checked' : '' }}">
                        {!! in_array($key, $ops) ? '✓' : '&nbsp;' !!}
                    </span>
                    {{ $label }}
                </div>
            @endforeach
        </div>

        <div style="font-size:18px; margin-bottom:10px;">วัตถุประสงค์และเหตุผลการแก้ไข:</div>

        @php
            $lines = array_values(array_filter(explode("\n", $objective)));
            $total = max(count($lines), 4);
        @endphp

        @for ($i = 0; $i < $total; $i++)
            <span class="obj-line">{{ $lines[$i] ?? '' }}</span>
        @endfor

        <div style="margin-top:20px;">
            <div style="font-size:15px; margin-bottom:10px;">ลิ้งที่ต้องการแก้ไข:</div>
            <span class="link-line">{{ $fixLink }}</span>
        </div>

    </div>
</body>

</html>