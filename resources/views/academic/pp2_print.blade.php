<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบ ป.พ.2</title>
    <style>
        @font-face {
            font-family: TFArluck;
            src: url('{{ asset("fonts/TF Arluck.ttf") }}') format('truetype');
            font-weight: normal;
        }
        @font-face {
            font-family: TFArluck;
            src: url('{{ asset("fonts/TF Arluck Bol.ttf") }}') format('truetype');
            font-weight: bold;
        }
        @font-face {
            font-family: TFArluckData;
            src: url('{{ asset("fonts/TF Arluck Bol.ttf") }}') format('truetype');
        }

        * { box-sizing: border-box; -moz-box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: TFArluck, serif;
            width: 100%;
            height: 100%;
            background-color: #FAFAFA;
        }

        .page {
            width: 21cm;
            min-height: 14.5cm;
            padding: 0cm 1.6cm 0cm 1.6cm;
            margin: 0.8cm auto;
            background: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            position: relative;
        }

        .page.front {
            background-image: url('{{ asset("img/pp_1/logo.jpg") }}');
            background-repeat: no-repeat;
            background-position: 2pt 4pt;
            background-size: 110pt auto;
        }

        .row { display: flex; flex-wrap: nowrap; width: 100%; }
        .col-12 { flex: 0 0 100%; max-width: 100%; }
        .col-6 { flex: 0 0 50%; max-width: 50%; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        .spn-label { font-family: TFArluck; letter-spacing: -0.8pt; }
        .back .spn-label { letter-spacing: 0.6pt; }

        .input-data {
            white-space: nowrap;
            border-bottom: dotted 1px #999;
            text-align: center;
            display: inline-block;
            height: 20px;
            font-family: TFArluckData;
        }

        .font-tfarluck { font-family: TFArluck; }
        .font-tfarluck-data { font-family: TFArluckData; }

        .font-size-38em { font-size: 3.8em; }
        .font-size-28em { font-size: 2.8em; }
        .font-size-25em-data { font-size: 2.5em; }
        .font-size-24em { font-size: 2.4em; }
        .font-size-20em { font-size: 2.0em; }
        .font-size-20em-data { font-size: 2.0em; }
        .font-size-18em { font-size: 1.8em; }
        .font-size-18em-data { font-size: 1.8em; font-weight: bold; }
        .font-size-17em-data { font-size: 1.7em; }
        .font-size-16em-data { font-size: 1.6em; font-weight: bold; }
        .font-size-14em-data { font-size: 1.4em; }
        .font-size-10em-bold { font-size: 1.0em; font-weight: bold; }
        .font-size-08em { font-size: 0.8em; }
        .font-size-08em-bold { font-size: 0.8em; font-weight: bold; }

        .custom-section {
            display: flex;
            flex-wrap: nowrap;
        }
        .custom-section .col-6 {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .custom-section .col-6 p { white-space: nowrap; }

        .no-print-me {}

        @media print {
            html, body { margin: 0; padding: 0; background: #fff; }
            .page {
                margin: 0;
                border: none;
                box-shadow: none;
                width: 21cm;
                min-height: 14.5cm;
            }
            @page {
                size: 21cm 14.5cm;
                margin: 0;
                padding: 0;
            }
            .no-print-me { display: none !important; }
        }

        .print-btn {
            padding: 8px 16px;
            text-align: center;
            cursor: pointer;
            color: #fff;
            background-color: #2196F3;
            position: fixed;
            top: 20%;
            right: 10px;
            border: 1px solid #1565C0;
            border-radius: 4px;
            font-size: 14pt;
        }
        .print-btn:hover { background-color: #ccc; color: #000; }
    </style>
</head>
<body>

@php
    $student = $studentSection->student;
    $section = $studentSection->classSection;
    $level   = $section?->level;

    $thaiNums  = ['๐','๑','๒','๓','๔','๕','๖','๗','๘','๙'];
    $toThai    = fn($n) => preg_replace_callback('/\d/', fn($m) => $thaiNums[$m[0]], (string) $n);
    $thaiMonths = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                   'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];

    $dob      = $student?->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth) : null;
    $dobDay   = $dob ? $toThai($dob->day)          : '........';
    $dobMonth = $dob ? $thaiMonths[$dob->month]     : '........';
    $dobYear  = $dob ? $toThai($dob->year + 543)    : '........';

    $issued   = $doc?->issued_date ? \Carbon\Carbon::parse($doc->issued_date) : \Carbon\Carbon::now();
    $issDay   = $toThai($issued->day);
    $issMonth = $thaiMonths[$issued->month];
    $issYear  = $toThai($issued->year + 543);

    $docNum   = $doc?->doc_number ? $toThai($doc->doc_number) : '........';

    $levelName = $level?->name ?? '';
    if (str_contains($levelName, 'ป.6')) {
        $certText = 'เป็นผู้สำเร็จการศึกษาขั้นพื้นฐานตามหลักสูตรแกนกลางการศึกษาขั้นพื้นฐาน';
    } elseif (str_contains($levelName, 'ม.3')) {
        $certText = 'เป็นผู้สำเร็จการศึกษาชั้นมัธยมศึกษาตอนต้นตามหลักสูตรแกนกลางการศึกษาขั้นพื้นฐาน';
    } else {
        $certText = 'เป็นผู้สำเร็จการศึกษาชั้นมัธยมศึกษาตอนปลายตามหลักสูตรแกนกลางการศึกษาขั้นพื้นฐาน';
    }

    $fullName = ($student?->thai_prefix ?? '') . ($student?->thai_firstname ?? '') . ' ' . ($student?->thai_lastname ?? '');
@endphp

<div class="no-print-me" style="position:fixed;top:10%;right:10px;">
    <button class="print-btn" onclick="window.print()">🖨️ พิมพ์</button>
</div>

{{-- ===== หน้า 1 (หน้าหลัก) ===== --}}
<div class="page front">
    <div style="height:15pt;">&nbsp;</div>

    <div class="row">
        <div class="col-12 text-right" style="height:18pt;">
            <span class="font-tfarluck font-size-18em spn-label">ปพ.๒ : </span><span class="font-tfarluck font-size-18em spn-label" style="color:#c00;">พ</span>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-right" style="height:23pt;">
            <span class="font-tfarluck font-size-18em spn-label" style="padding-right:6pt; color:#c00;">เลขที่</span>
            <p style="display:inline-block; margin:-2pt 0 2pt 0; width:12%; height:20pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-16em-data input-data" style="width:100%;height:100%;padding-right:10pt; color:#c00;">{{ $docNum }}</span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-center" style="height:35pt;">
            <span class="font-tfarluck font-size-38em spn-label">กระทรวงศึกษาธิการ</span>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-center" style="height:38pt;">
            <span class="font-tfarluck font-size-28em spn-label">ประกาศนียบัตรฉบับนี้ให้ไว้เพื่อแสดงว่า</span>
        </div>
    </div>

    <div class="row" style="margin-bottom:5pt;">
        <div class="col-12 text-center">
            <p style="display:inline-block; margin:-3pt 6pt 2pt 0; width:62%; height:30pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-25em-data input-data" style="width:100%;height:100%;">{{ $fullName }}</span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-center" style="height:26pt; padding-right:22pt;">
            <span class="font-tfarluck font-size-24em spn-label" style="padding-left:3pt;">เกิดวันที่</span>
            <p style="display:inline-block; margin:-2pt 0 2pt 0; width:9%; height:25pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-20em-data input-data" style="width:100%;height:100%;">{{ $dobDay }}</span>
            </p>
            <span class="font-tfarluck font-size-24em spn-label">เดือน</span>
            <p style="display:inline-block; margin:-2pt 0 2pt 0; width:21%; height:25pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-20em-data input-data" style="width:100%;height:100%;padding-right:8pt;">{{ $dobMonth }}</span>
            </p>
            <span class="font-tfarluck font-size-24em spn-label">พ.ศ.</span>
            <p style="display:inline-block; margin:-2pt 0 2pt 0; width:12%; height:25pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-20em-data input-data" style="width:100%;height:100%;padding-right:8pt;">{{ $dobYear }}</span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-center" style="height:25pt;">
            <span class="font-tfarluck font-size-20em spn-label">{{ $certText }}</span>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-center" style="height:26pt;">
            <span class="font-tfarluck font-size-24em spn-label">จาก</span>
            <p style="display:inline-block; margin:-2pt 8pt 2pt -1pt; width:70%; height:25pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-20em-data input-data" style="width:100%;height:100%;text-align:center;">โรงเรียนสาธิตมหาวิทยาลัยราชภัฏวไลยอลงกรณ์ ในพระบรมราชูปถัมภ์</span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-center" style="height:26pt;">
            <span class="font-tfarluck font-size-24em spn-label">จังหวัด</span>
            <p style="display:inline-block; margin:0; width:18%; height:23pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-18em-data input-data" style="width:100%;height:100%;">ปทุมธานี</span>
            </p>
            <span class="font-tfarluck font-size-24em spn-label">สังกัด</span>
            <p style="display:inline-block; margin:0 -60pt 0 0; width:50%; height:23pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-16em-data input-data" style="width:100%;height:100%;padding-left:3pt;">สำนักงานปลัดกระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม</span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-center" style="height:26pt; padding-right:22pt;">
            <span class="font-tfarluck font-size-24em spn-label">เมื่อวันที่</span>
            <p style="display:inline-block; margin:-2pt 0 2pt 0; width:9%; height:25pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-20em-data input-data" style="width:100%;height:100%;">{{ $issDay }}</span>
            </p>
            <span class="font-tfarluck font-size-24em spn-label">เดือน</span>
            <p style="display:inline-block; margin:-2pt 0 2pt 0; width:21%; height:25pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-20em-data input-data" style="width:100%;height:100%;padding-right:8pt;">{{ $issMonth }}</span>
            </p>
            <span class="font-tfarluck font-size-24em spn-label">พ.ศ.</span>
            <p style="display:inline-block; margin:-2pt 0 2pt 0; width:12%; height:25pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-20em-data input-data" style="width:100%;height:100%;padding-right:8pt;">{{ $issYear }}</span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-center">
            <span class="font-tfarluck font-size-24em spn-label">ขอให้มีความสุขสวัสดิ์เจริญเทอญ</span>
        </div>
    </div>

    <div style="height:21pt;">&nbsp;</div>

    <div class="row">
        <div class="col-12 text-center">
            <span class="font-tfarluck-data font-size-17em-data spn-label">(นางสาววรานิษฐ์ ธนชัยวรพันธ์)</span>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-center" style="margin-top:-3pt;">
            <span class="font-tfarluck-data font-size-17em-data spn-label">ผู้อำนวยการ</span>
        </div>
    </div>
</div>

{{-- ===== หน้า 2 (ลงลายมือชื่อ) ===== --}}
<div class="page back" style="page-break-before:always;">
    <div style="height:167pt;">&nbsp;</div>

    <div class="row">
        <div class="col-12 text-center" style="height:41pt;">
            <span class="font-size-10em-bold spn-label">ลงลายมือชื่อ</span>
        </div>
    </div>

    <div class="row custom-section">
        <div class="col-6 text-left" style="height:23pt; justify-content:flex-start;">
            <p style="display:inline-block; margin:-6pt -2pt 2pt 44pt; width:53%; height:17pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-14em-data input-data" style="width:100%;height:100%;text-align:center;"></span>
            </p>
            <span class="font-size-08em spn-label">ผู้เขียน/ผู้พิมพ์</span>
        </div>
        <div class="col-6 text-left">
            <p style="display:inline-block; margin:-6pt -2pt 2pt 0; width:53%; height:17pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-14em-data input-data" style="width:100%;height:100%;text-align:center;"></span>
            </p>
        </div>
    </div>

    <div class="row custom-section">
        <div class="col-6 text-left" style="height:23pt; justify-content:flex-start;">
            <p style="display:inline-block; margin:-6pt -2pt 2pt 44pt; width:53%; height:17pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-14em-data input-data" style="width:100%;height:100%;text-align:center;"></span>
            </p>
            <span class="font-size-08em spn-label">ผู้ทาน</span>
        </div>
        <div class="col-6 text-left">
            <span class="font-size-08em spn-label">(</span>
            <p style="display:inline-block; margin:-6pt 0 2pt 0; width:auto; height:17pt; vertical-align:top; text-align:center; padding:0 10px;">
                <span class="font-tfarluck-data font-size-14em-data input-data" style="text-align:center;height:100%;"></span>
            </p>
            <span class="font-size-08em spn-label">)</span>
        </div>
    </div>

    <div class="row custom-section">
        <div class="col-6 text-left" style="height:46pt; justify-content:flex-start;">
            <p style="display:inline-block; margin:-6pt -2pt 2pt 44pt; width:53%; height:17pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-14em-data input-data" style="width:100%;height:100%;text-align:center;"></span>
            </p>
            <span class="font-size-08em spn-label">ผู้ตรวจ</span>
        </div>
        <div class="col-6 text-left" style="padding-bottom:10pt;">
            <span class="font-size-08em-bold spn-label">นายทะเบียน</span>
        </div>
    </div>

    <div class="row custom-section">
        <div class="col-6" style="height:23pt;"></div>
        <div class="col-6 text-left">
            <p style="display:inline-block; margin:-6pt 0 2pt 0; width:52%; height:17pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-14em-data input-data" style="width:100%;height:100%;text-align:center;"></span>
            </p>
        </div>
    </div>

    <div class="row custom-section">
        <div class="col-6" style="height:23pt;"></div>
        <div class="col-6 text-left">
            <span class="font-size-08em spn-label">(</span>
            <p style="display:inline-block; margin:-6pt 0 2pt 0; width:47%; height:17pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-14em-data input-data" style="width:100%;height:100%;text-align:center;">{{ $fullName }}</span>
            </p>
            <span class="font-size-08em spn-label">)</span>
        </div>
    </div>

    <div class="row custom-section">
        <div class="col-6" style="height:23pt;"></div>
        <div class="col-6 text-left">
            <span class="font-size-08em-bold spn-label">ผู้รับประกาศนียบัตร</span>
        </div>
    </div>

    <div class="row custom-section">
        <div class="col-6" style="height:23pt;"></div>
        <div class="col-6 text-left">
            <span class="font-size-08em spn-label">วันที่</span>
            <p style="display:inline-block; margin:-7pt -3pt 2pt -4pt; width:19%; height:18pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-14em-data input-data" style="width:100%;height:100%;padding-left:2pt;">{{ $issDay }}</span>
            </p>
            <span class="font-size-08em spn-label">เดือน</span>
            <p style="display:inline-block; margin:-7pt -2pt 2pt -4pt; width:31%; height:18pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-14em-data input-data" style="width:100%;height:100%;padding-left:2pt;">{{ $issMonth }}</span>
            </p>
            <span class="font-size-08em spn-label">พ.ศ.</span>
            <p style="display:inline-block; margin:-7pt 0 2pt -4pt; width:27%; height:18pt; vertical-align:top;">
                <span class="font-tfarluck-data font-size-14em-data input-data" style="width:100%;height:100%;padding-left:8pt;">{{ $issYear }}</span>
            </p>
        </div>
    </div>
</div>

</body>
</html>
