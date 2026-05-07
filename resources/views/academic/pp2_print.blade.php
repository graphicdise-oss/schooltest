<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ใบ ป.พ.2</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Thai:wght@400;600;700&display=swap"
        rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        @font-face {
            font-family: 'TF Arluck';
            src: url('{{ asset("fonts/TF Arluck.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'TF Arluck';
            src: url('{{ asset("fonts/TF Arluck Bol.ttf") }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        @font-face {
            font-family: 'TF Arluck';
            src: url('{{ asset("fonts/TF Arluck Ita.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: italic;
        }
        @font-face {
            font-family: 'TF Arluck';
            src: url('{{ asset("fonts/TF Arluck Bol Ita.ttf") }}') format('truetype');
            font-weight: bold;
            font-style: italic;
        }

        body {
            font-family: 'TF Arluck', 'TH Sarabun New', serif;
            background: #fff;
            font-size: 16pt;
            line-height: 1.0;
        }

        .page {
            width: 29.7cm;
            min-height: 21cm;
            margin: 0 auto;
            padding: 0.8cm 2cm 0.8cm;
            position: relative;
        }

        .doc-ref {
            position: absolute;
            top: 0.8cm;
            right: 2cm;
            text-align: right;
            font-size: 14pt;
            color: #c00;
            line-height: 1.0;
        }

        .title-main {
            text-align: center;
            font-size: 26pt;
            font-weight: 700;
            margin-bottom: 0;
            line-height: 1.0;
        }

        .title-sub {
            text-align: center;
            font-size: 17pt;
            margin-bottom: 6px;
            line-height: 1.0;
        }

        .student-name {
            text-align: center;
            font-size: 20pt;
            font-weight: 600;
            border-bottom: 1px dotted #333;
            width: 80%;
            margin: 4px auto 6px;
            padding-bottom: 0;
            line-height: 1.0;
        }

        /* แถวข้อมูล */
        .row {
            display: flex;
            align-items: flex-end;
            margin-bottom: 2px;
            font-size: 15pt;
            line-height: 1.0;
        }

        .row .lbl {
            white-space: nowrap;
            margin-right: 6px;
        }

        .row .val {
            border-bottom: 1px dotted #444;
            text-align: center;
            flex: 1;
            padding: 0 4px;
        }

        .row .val.sm {
            flex: 0 0 auto;
            min-width: 2.5cm;
            margin-right: 6px;
        }

        .row .val.md {
            flex: 0 0 auto;
            min-width: 4.5cm;
            margin-right: 6px;
        }

        .row .val.lg {
            flex: 1;
            margin-right: 6px;
        }

        .row-center {
            text-align: center;
            margin-bottom: 2px;
            font-size: 15pt;
            line-height: 1.0;
        }

        /* ลงชื่อ */
        .sig-block {
            text-align: center;
            margin-top: 16px;
        }

        .sig-line {
            border-bottom: 1px solid #333;
            width: 7cm;
            margin: 0 auto 2px;
            height: 24px;
        }

        .sig-name {
            font-size: 14pt;
            line-height: 1.0;
        }

        .sig-role {
            font-size: 13pt;
            color: #555;
            line-height: 1.0;
        }

        /* หน้า 2 */
        .page2-title {
            text-align: center;
            font-size: 20pt;
            font-weight: 700;
            margin: 2cm 0 1.5cm;
        }

        .sig2-item {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-bottom: 28px;
            font-size: 14pt;
        }

        .sig2-label {
            min-width: 3cm;
            white-space: nowrap;
        }

        .sig2-line {
            flex: 1;
            border-bottom: 1px dotted #555;
        }

        .sig2-extra {
            min-width: 2.5cm;
            text-align: center;
        }

        .receiver-block {
            text-align: center;
            margin-top: 2cm;
            font-size: 15pt;
        }

        .receiver-line {
            border-bottom: 1px solid #333;
            display: inline-block;
            min-width: 8cm;
            padding: 0 8px;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                size: A4 landscape;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .page {
                page-break-after: always;
            }
        }
    </style>
</head>

<body>

    @php
        $student = $studentSection->student;
        $section = $studentSection->classSection;
        $level = $section?->level;

        $thaiNums = ['๐', '๑', '๒', '๓', '๔', '๕', '๖', '๗', '๘', '๙'];
        $toThai = fn($n) => preg_replace_callback('/\d/', fn($m) => $thaiNums[$m[0]], (string) $n);

        $thaiMonths = [
            '',
            'มกราคม',
            'กุมภาพันธ์',
            'มีนาคม',
            'เมษายน',
            'พฤษภาคม',
            'มิถุนายน',
            'กรกฎาคม',
            'สิงหาคม',
            'กันยายน',
            'ตุลาคม',
            'พฤศจิกายน',
            'ธันวาคม'
        ];

        $dob = $student?->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth) : null;
        $dobDay = $dob ? $toThai($dob->day) : '........';
        $dobMonth = $dob ? $thaiMonths[$dob->month] : '........';
        $dobYear = $dob ? $toThai($dob->year + 543) : '........';

        $issued = $doc?->issued_date ? \Carbon\Carbon::parse($doc->issued_date) : \Carbon\Carbon::now();
        $issDay = $toThai($issued->day);
        $issMonth = $thaiMonths[$issued->month];
        $issYear = $toThai($issued->year + 543);

        $docNum = $doc?->doc_number ? $toThai($doc->doc_number) : '........';

        $levelName = $level?->name ?? '';
        if (str_contains($levelName, 'ป.6')) {
            $certText = 'ชั้นประถมศึกษาปีที่ ๖ ตามหลักสูตรแกนกลางการศึกษาขั้นพื้นฐาน';
        } elseif (str_contains($levelName, 'ม.3')) {
            $certText = 'ชั้นมัธยมศึกษาตอนต้น ตามหลักสูตรแกนกลางการศึกษาขั้นพื้นฐาน';
        } else {
            $certText = 'ชั้นมัธยมศึกษาตอนปลาย ตามหลักสูตรแกนกลางการศึกษาขั้นพื้นฐาน';
        }

        $fullName = ($student?->thai_prefix ?? '') . ($student?->thai_firstname ?? '') . ' ' . ($student?->thai_lastname ?? '');
    @endphp

    {{-- ===== หน้า 1 ===== --}}
    <div class="page">
        <div class="doc-ref">
            ปว.๒ : พ<br>
            เลขที่ {{ $docNum }}
        </div>

        <div class="title-main">กระทรวงศึกษาธิการ</div>
        <div class="title-sub">ประกาศนียบัตรฉบับนี้ให้ไว้เพื่อแสดงว่า</div>

        <div class="student-name">{{ $fullName }}</div>

        <div class="row" style="justify-content:center; gap:12px;">
            <span class="lbl">เกิดวันที่</span>
            <span class="val sm">{{ $dobDay }}</span>
            <span class="lbl">เดือน</span>
            <span class="val md">{{ $dobMonth }}</span>
            <span class="lbl">พ.ศ.</span>
            <span class="val sm">{{ $dobYear }}</span>
        </div>

        <div class="row-center" style="margin: 2px 0;">
            เป็นผู้สำเร็จการศึกษา{{ $certText }}
        </div>

        <div class="row">
            <span class="lbl">จาก</span>
            <span class="val">โรงเรียนสาธิตมหาวิทยาลัยราชภัฏวไลยอลงกรณ์ ในพระบรมราชูปถัมภ์</span>
        </div>

        <div class="row">
            <span class="lbl">จังหวัด</span>
            <span class="val sm">ปทุมธานี</span>
            <span class="lbl" style="margin: 0 6px;">สังกัด</span>
            <span class="val">สำนักงานปลัดกระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม</span>
        </div>

        <div class="row" style="justify-content:center; gap:12px; margin-top:2px;">
            <span class="lbl">เมื่อวันที่</span>
            <span class="val sm">{{ $issDay }}</span>
            <span class="lbl">เดือน</span>
            <span class="val md">{{ $issMonth }}</span>
            <span class="lbl">พ.ศ.</span>
            <span class="val sm">{{ $issYear }}</span>
        </div>

        <div class="row-center" style="margin-top: 8px;">ขอให้มีความสุขสวัสดิ์เจริญเทอญ</div>

        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-name">(นางสาววรานิษฐ์ ธนชัยวรพันธ์)</div>
            <div class="sig-role">ผู้อำนวยการ</div>
        </div>
    </div>

    {{-- ===== หน้า 2 ===== --}}
    <div class="page">
        <div class="page2-title">ลงลายมือชื่อ</div>

        <div style="padding: 0 3cm;">
            <div class="sig2-item">
                <span class="sig2-label">ผู้เขียนผู้รับมอบ</span>
                <span class="sig2-line"></span>
            </div>
            <div class="sig2-item">
                <span class="sig2-label">ผู้กาน</span>
                <span class="sig2-line"></span>
                <span class="sig2-extra">( &nbsp;&nbsp;&nbsp; )</span>
            </div>
            <div class="sig2-item">
                <span class="sig2-label">ผู้ตรวจ</span>
                <span class="sig2-line"></span>
                <span style="font-weight:700; white-space:nowrap; margin-left:16px;">นายทะเบียน</span>
            </div>
        </div>

        <div class="receiver-block" style="margin-top: 1.5cm;">
            <div>( <span class="receiver-line">{{ $fullName }}</span> )</div>
            <div style="margin-top:6px;">ผู้รับประกาศนียบัตร</div>
            <div class="row" style="justify-content:center; gap:12px; margin-top:12px;">
                <span class="lbl">วันที่</span>
                <span class="val sm">{{ $issDay }}</span>
                <span class="lbl">เดือน</span>
                <span class="val md">{{ $issMonth }}</span>
                <span class="lbl">พุทธ</span>
                <span class="val sm">{{ $issYear }}</span>
            </div>
        </div>
    </div>

    <div class="no-print" style="text-align:center; padding:16px;">
        <button onclick="window.print()"
            style="background:#00bcd4; color:#fff; border:none; border-radius:6px; padding:10px 30px; font-size:1rem; cursor:pointer;">
            🖨️ พิมพ์
        </button>
    </div>

    <script>window.onload = () => window.print();</script>
</body>

</html>