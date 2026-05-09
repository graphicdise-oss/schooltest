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
            padding: 30px 20px;
        }

        /* ---- Action Bar (ไม่แสดงตอนพิมพ์) ---- */
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
            font-family: 'Sarabun', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .btn:hover { opacity: 0.85; }

        .btn-back {
            background: #fff;
            color: #374151;
            border: 1.5px solid #d1d5db;
        }

        .btn-print {
            background: linear-gradient(135deg, #1a56db 0%, #1e429f 100%);
            color: #fff;
        }

        /* ---- เอกสาร ---- */
        .document {
            max-width: 794px;
            margin: 0 auto;
            background: #fff;
            padding: 48px 56px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            border-radius: 8px;
        }

        .doc-title {
            text-align: center;
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 36px;
            line-height: 1.6;
        }

        .section-heading {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 16px;
            margin-top: 28px;
        }

        .field-row {
            display: flex;
            align-items: baseline;
            margin-bottom: 14px;
            gap: 0;
        }

        .field-label {
            font-size: 14px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .field-line {
            flex: 1;
            border-bottom: 1px dotted #555;
            min-width: 60px;
            margin-left: 6px;
            padding-bottom: 2px;
            font-size: 14px;
            color: #111;
        }

        .field-line.filled {
            border-bottom-style: solid;
            border-color: #111;
            font-weight: 600;
        }

        .field-bracket {
            font-size: 14px;
            white-space: nowrap;
            flex-shrink: 0;
            margin-left: 4px;
        }

        /* Priority inline */
        .priority-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .priority-box {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .check-box {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1.5px solid #555;
            text-align: center;
            line-height: 14px;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .check-box.checked {
            border-color: #111;
            color: #111;
        }

        /* ประเภทการดำเนินงาน */
        .op-list {
            margin-top: 6px;
            margin-bottom: 14px;
        }

        .op-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            margin-bottom: 8px;
        }

        /* วัตถุประสงค์ */
        .objective-lines {
            margin-top: 8px;
        }

        .obj-line {
            border-bottom: 1px dashed #888;
            min-height: 24px;
            margin-bottom: 8px;
            font-size: 14px;
            padding-bottom: 2px;
            line-height: 1.6;
            color: #111;
        }

        .obj-text {
            font-size: 14px;
            color: #111;
            line-height: 1.8;
            border-bottom: 1px dashed #888;
            padding-bottom: 4px;
            font-weight: 600;
        }

        .divider {
            border: none;
            border-top: 1px solid #ddd;
            margin: 24px 0;
        }

        /* ---- Print ---- */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .action-bar { display: none !important; }

            .document {
                box-shadow: none;
                border-radius: 0;
                padding: 20mm 20mm;
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

    {{-- Action Bar --}}
    <div class="action-bar">
        <a href="{{ route('change-request.create') }}" class="btn btn-back">
            &#8592; กลับแก้ไขฟอร์ม
        </a>
        <button onclick="window.print()" class="btn btn-print">
            &#128438; พิมพ์ / บันทึก PDF
        </button>
    </div>

    {{-- เอกสาร --}}
    <div class="document">

        <div class="doc-title">
            แบบฟอร์มคำร้องขอปรับปรุงแก้ไขระบบ (System Change Request Form)
        </div>

        {{-- ส่วนที่ 1 --}}
        <div class="section-heading">1. ข้อมูลทั่วไป (General Information)</div>

        <div class="field-row">
            <span class="field-label">ชื่อผู้ขอปรับปรุง:</span>
            <span class="field-line {{ $data['requester_name'] ? 'filled' : '' }}">
                &nbsp;{{ $data['requester_name'] }}
            </span>
        </div>

        <div class="field-row">
            <span class="field-label">หน่วยงาน/แผนก:</span>
            <span class="field-line {{ $data['department'] ? 'filled' : '' }}">
                &nbsp;{{ $data['department'] }}
            </span>
        </div>

        <div class="field-row">
            <span class="field-label">วันที่ยื่นคำร้อง:</span>
            <span class="field-bracket">[</span>
            <span class="field-line {{ $data['request_date'] ? 'filled' : '' }}" style="text-align:center;">
                {{ $data['request_date']
                    ? \Carbon\Carbon::parse($data['request_date'])->format('d/m/Y')
                    : '' }}
            </span>
            <span class="field-bracket">]</span>
        </div>

        @php
            $priorityMap = [
                'normal'   => 'ปกติ',
                'urgent'   => 'ด่วน',
                'critical' => 'ด่วนที่สุด (System Bug/Critical)',
            ];
        @endphp

        <div class="priority-row">
            <span>ระดับความสำคัญ:</span>
            @foreach ($priorityMap as $key => $label)
                <span class="priority-box">
                    <span class="check-box {{ $data['priority'] === $key ? 'checked' : '' }}">
                        {{ $data['priority'] === $key ? '✓' : '&nbsp;' }}
                    </span>
                    {{ $label }}
                </span>
            @endforeach
        </div>

        <hr class="divider">

        {{-- ส่วนที่ 2 --}}
        <div class="section-heading">2. รายละเอียดการแก้ไข (Change Details)</div>

        <div class="field-row">
            <span class="field-label" style="flex-shrink:0;">ชื่อโมดูล/ส่วนงานที่แก้ไข:</span>
            <span class="field-line filled">&nbsp;{{ $data['module_name'] }}</span>
        </div>

        <div style="font-size:14px; margin-bottom:8px;">ประเภทการดำเนินงาน:</div>

        @php
            $opMap = [
                'new_feature'  => 'เพิ่มฟีเจอร์ใหม่ (New Feature)',
                'bug_fix'      => 'แก้ไขข้อผิดพลาด (Bug Fix)',
                'optimization' => 'ปรับปรุงประสิทธิภาพ (Optimization)',
                'ui_ux'        => 'ปรับปรุง UI/UX (Interface Update)',
            ];
        @endphp

        <div class="op-list">
            @foreach ($opMap as $key => $label)
                <div class="op-item">
                    <span class="check-box {{ in_array($key, $data['operation_types']) ? 'checked' : '' }}">
                        {{ in_array($key, $data['operation_types']) ? '✓' : '&nbsp;' }}
                    </span>
                    {{ $label }}
                </div>
            @endforeach
        </div>

        <div style="font-size:14px; margin-bottom:8px;">วัตถุประสงค์และเหตุผลการแก้ไข:</div>

        <div class="objective-lines">
            @php
                $lines = array_filter(explode("\n", $data['objective']));
                $filled = array_values($lines);
                $total  = max(count($filled), 4);
            @endphp
            @for ($i = 0; $i < $total; $i++)
                <div class="obj-line">
                    @if (isset($filled[$i]))
                        <span style="font-weight:600;">{{ $filled[$i] }}</span>
                    @else
                        &nbsp;
                    @endif
                </div>
            @endfor
        </div>

        {{-- ลิ้งที่ต้องการแก้ไข --}}
        <div style="margin-top:20px;">
            <div style="font-size:14px; margin-bottom:6px;">ลิ้งที่ต้องการแก้ไข:</div>
            <div class="field-row">
                <span class="field-line {{ $data['fix_link'] ? 'filled' : '' }}" style="word-break:break-all;">
                    &nbsp;{{ $data['fix_link'] ?: '' }}
                </span>
            </div>
        </div>

    </div>

</body>
</html>
