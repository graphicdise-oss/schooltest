<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ปพ.1 — {{ $student->thai_firstname }} {{ $student->thai_lastname }}</title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'TH Sarabun New', 'Sarabun', 'Tahoma', sans-serif;
    font-size: 15px; color: #000; background: #e0e0e0;
    line-height: 1.0;
}

.page {
    width: 210mm; min-height: 297mm;
    margin: 0 auto 10mm; padding: 3mm 14mm 4mm;
    background: #fff;
    box-shadow: 0 0 8px rgba(0,0,0,0.15);
    page-break-after: always;
}
.page:last-child { page-break-after: auto; }

/* ===== HEADER ===== */
.doc-top {
    display: flex; align-items: center; gap: 4px;
    margin-bottom: 0;
}
.doc-logo {
    width: 18mm; height: 18mm; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.doc-logo img { width: 100%; height: 100%; object-fit: contain; display: block; }
.doc-title-block { flex: 1; line-height: 1.0; }
.doc-title-block h2 {
    font-family: 'TH Sarabun New', 'Sarabun', sans-serif;
    font-size: 20px; font-weight: 400; line-height: 1.0;
    text-align: center; margin-bottom: 0;
}

/* ปพ.1 meta row */
.doc-meta-row {
    display: flex; align-items: baseline; justify-content: center;
    gap: 8mm; margin: 0; padding: 0; line-height: 1.0;
}
.doc-meta-row .label { font-size: 20px; font-weight: 400; line-height: 1.0; }
.doc-meta-row .field-group { display: flex; align-items: baseline; gap: 3px; font-size: 20px; font-weight: 400; line-height: 1.0; }
.doc-meta-row .field-val {
    font-size: 20px; font-weight: 400;
    border-bottom: 0.3px solid #999;
    min-width: 28mm; text-align: center;
    padding: 0 4px 0; display: inline-block;
    line-height: 1.0;
}

/* ===== INFO SECTION ===== */
.info-section {
    display: flex; gap: 0; margin: 0;
}
.info-left { flex: 1; }
.info-right { width: 78mm; padding-left: 4mm; }
.info-photo { width: 28mm; flex-shrink: 0; }

.info-row {
    display: flex; align-items: flex-end;
    margin-bottom: 0.3mm;
}
.info-row .lbl {
    font-size: 15px; font-weight: 400;
    white-space: nowrap; padding-right: 2mm;
    flex-shrink: 0; line-height: 1.5;
}
.info-row .val {
    font-size: 15px;
    border-bottom: 0.3px solid #bbb;
    flex: 1; padding-bottom: 0; min-width: 10mm;
    padding-left: 1mm; line-height: 1.4;
}
.info-row .val-fixed {
    font-size: 15px;
    border-bottom: 0.3px solid #bbb;
    padding-bottom: 0; padding-left: 1mm; line-height: 1.4;
}

.photo-box {
    width: 28mm; height: 36mm;
    border: 1.5px solid #555;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; color: #aaa; overflow: hidden;
}
.photo-box img { width: 100%; height: 100%; object-fit: cover; }

/* inline split row */
.info-row-split { display: flex; gap: 4mm; margin-bottom: 1.5mm; }
.info-row-split .chunk { display: flex; align-items: flex-end; }
.info-row-split .chunk .lbl { font-size: 15px; font-weight: 400; white-space: nowrap; padding-right: 1mm; flex-shrink: 0; }
.info-row-split .chunk .val-fixed { font-size: 15px; border-bottom: 1px solid #555; padding-bottom: 1px; padding-left: 1mm; min-width: 12mm; }

/* ===== SECTION TITLE ===== */
.section-title {
    font-size: 17px; font-weight: 400; text-align: center;
    margin: 1mm 0 1mm;
    border-top: 1px solid #000;
    padding-top: 1mm;
}

/* ===== GRADES TABLE ===== */
.grades-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.grades-table th, .grades-table td {
    border: 1px solid #000; padding: 1px 3px; vertical-align: top;
}
.grades-table th { background: #f0f0f0; text-align: center; }
.year-header { text-align: center; font-weight: 400; font-size: 13px; background: #e8e8e8; }
.sem-header { font-weight: 400; font-size: 12px; background: #f8f8f8; }
.col-subject { width: 37%; font-size: 12px; }
.col-credit  { width: 8%;  text-align: center; }
.col-grade   { width: 8%;  text-align: center; }

/* ===== PAGE 2 ===== */
.p2-title { font-size: 17px; font-weight: 400; text-align: center; margin: 2mm 0 1.5mm; }
.p2-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 3mm; }
.p2-table th, .p2-table td { border: 1px solid #000; padding: 2px 4px; }
.p2-table th { background: #f0f0f0; text-align: center; }

.sig-row { display: flex; justify-content: space-between; margin-top: 10mm; }
.sig-box { text-align: center; width: 45%; }
.sig-line { border-top: 1px solid #000; margin: 14mm 8mm 3px; }

/* ===== PRINT ===== */
.no-print { padding: 12px; text-align: center; background: #f5f5f5; border-bottom: 1px solid #ccc; }
.btn-print {
    background: #5c6bc0; color: #fff; border: none; border-radius: 6px;
    padding: 10px 32px; font-size: 16px; cursor: pointer;
    font-family: inherit; margin: 0 6px;
}
.btn-close {
    background: #888; color: #fff; border: none; border-radius: 6px;
    padding: 10px 20px; font-size: 16px; cursor: pointer;
    font-family: inherit; margin: 0 6px;
}

@media print {
    .no-print { display: none !important; }
    body { background: #fff; }
    .page { margin: 0; box-shadow: none; padding: 8mm 12mm; }
    @page { size: A4; margin: 0; }
}
</style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 พิมพ์ / บันทึก PDF</button>
    <button class="btn-close" onclick="window.close()">ปิด</button>
</div>

@php
    $edu    = $student->education ?? null;
    $dob    = $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth) : null;
    $enroll = $student->enroll_date   ? \Carbon\Carbon::parse($student->enroll_date)   : null;
    $school = config('school');
    $thMonths = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                 'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
@endphp

{{-- ===== PAGE 1 ===== --}}
<div class="page">

    {{-- Header --}}
    @php
        $logoSrc = null;
        $logoExts = ['png','jpg','jpeg','gif','PNG','JPG','JPEG'];
        foreach ($logoExts as $ext) {
            $logoFile = public_path('img/pp_1/logo.' . $ext);
            if (file_exists($logoFile)) {
                $mime    = in_array(strtolower($ext),['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
                $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoFile));
                break;
            }
        }
    @endphp
    <div class="doc-top">
        <div class="doc-logo">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="">
            @endif
        </div>
        <div class="doc-title-block">
            <h2>ระเบียนแสดงผลการเรียนหลักสูตรแกนกลางการศึกษาขั้นพื้นฐาน ระดับมัธยมศึกษาตอนปลาย</h2>
            <div class="doc-meta-row">
                <div class="label">ปพ.1 : พ</div>
                <div class="field-group">
                    <span>ชุดที่</span>
                    <span class="field-val">{{ $docNumber->doc_set ?? '' }}</span>
                </div>
                <div class="field-group">
                    <span>เลขที่</span>
                    <span class="field-val">{{ $docNumber->doc_number ?? '' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Info --}}
    <div class="info-section">
        {{-- Left column --}}
        <div class="info-left">
            <div class="info-row">
                <span class="lbl">โรงเรียน</span>
                <span class="val">{{ $school['name'] }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">สังกัด</span>
                <span class="val">{{ $school['affiliation'] }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">ตำบล/แขวง</span>
                <span class="val">{{ $school['tambon'] }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">อำเภอ/เขต</span>
                <span class="val">{{ $school['amphoe'] }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">จังหวัด</span>
                <span class="val">{{ $school['changwat'] }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">สำนักงานเขตพื้นที่การศึกษา</span>
                <span class="val">{{ $school['education_area'] }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">วันเข้าเรียน</span>
                <span class="val">{{ $enroll ? $enroll->day . ' ' . $thMonths[$enroll->month] . ' ' . ($enroll->year + 543) : '' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">โรงเรียนเดิม</span>
                <span class="val">{{ $edu->previous_school ?? '' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">จังหวัด</span>
                <span class="val">{{ $edu->previous_province ?? '' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">ชั้นเรียนสุดท้าย</span>
                <span class="val">{{ $edu->education_level ?? '' }}</span>
            </div>
        </div>

        {{-- Right column --}}
        <div class="info-right">
            <div class="info-row">
                <span class="lbl">ชื่อ</span>
                <span class="val">{{ $student->thai_prefix }}{{ $student->thai_firstname }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">ชื่อสกุล</span>
                <span class="val">{{ $student->thai_lastname }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">เลขประจำตัวนักเรียน</span>
                <span class="val">{{ $student->student_code }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">เลขประจำตัวประชาชน</span>
                <span class="val">{{ $student->id_card_number }}</span>
            </div>
            <div class="info-row" style="flex-wrap:wrap;gap:4mm">
                <span class="lbl">เกิดวันที่</span>
                <span class="val-fixed" style="min-width:6mm">{{ $dob ? $dob->day : '' }}</span>
                <span class="lbl">เดือน</span>
                <span class="val-fixed" style="min-width:20mm">{{ $dob ? $thMonths[$dob->month] : '' }}</span>
                <span class="lbl">พ.ศ.</span>
                <span class="val-fixed" style="min-width:10mm">{{ $dob ? ($dob->year + 543) : '' }}</span>
            </div>
            <div class="info-row" style="flex-wrap:wrap;gap:4mm">
                <span class="lbl">เพศ</span>
                <span class="val-fixed" style="min-width:8mm">{{ $student->gender === 'M' ? 'ชาย' : ($student->gender === 'F' ? 'หญิง' : '') }}</span>
                <span class="lbl">สัญชาติ</span>
                <span class="val-fixed" style="min-width:10mm">{{ $student->nationality ?? 'ไทย' }}</span>
                <span class="lbl">ศาสนา</span>
                <span class="val-fixed" style="min-width:10mm">{{ $student->religion ?? 'พุทธ' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">ชื่อ-ชื่อสกุลบิดา</span>
                <span class="val">{{ $father ? ($father->prefix_th . $father->first_name_th . ' ' . $father->last_name_th) : '' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">ชื่อ-ชื่อสกุลมารดา</span>
                <span class="val">{{ $mother ? ($mother->prefix_th . $mother->first_name_th . ' ' . $mother->last_name_th) : '' }}</span>
            </div>
        </div>

        {{-- Photo --}}
        <div class="info-photo">
            <div class="photo-box">
                @if($student->photo)
                    <img src="{{ asset('storage/' . $student->photo) }}" alt="photo">
                @else
                    รูปถ่าย
                @endif
            </div>
        </div>
    </div>

    {{-- Subject results --}}
    <div class="section-title">ผลการเรียนรายวิชา</div>

    @if(count($yearGroups) > 0)
    @php
        $cols    = array_values($yearGroups);
        $numCols = max(3, count($cols));
        $colRows = [];
        foreach ($cols as $ci => $yg) {
            $colRows[$ci] = [];
            foreach ([1, 2] as $sn) {
                $sk = (string)$sn;
                $colRows[$ci][] = ['type' => 'sem', 'label' => 'ภาคเรียนที่ ' . $sn];
                foreach ($yg['semesters'][$sk] ?? [] as $g) {
                    $subj = $g->teachingAssign->subject;
                    $colRows[$ci][] = [
                        'type'    => 'subject',
                        'code'    => $subj->code ?? '',
                        'name'    => $subj->name_th ?? '',
                        'credits' => $subj->credits ?? '',
                        'grade'   => $g->grade ?? '',
                    ];
                }
            }
            if (count($colRows[$ci]) < 3) {
                $colRows[$ci][] = ['type' => 'empty'];
            }
        }
        for ($ci = count($cols); $ci < $numCols; $ci++) {
            $colRows[$ci] = [['type'=>'empty'],['type'=>'empty'],['type'=>'empty']];
        }
        $maxRows = max(array_map('count', $colRows));
    @endphp
    <table class="grades-table">
        <thead>
            <tr>
                @foreach($cols as $yg)
                <th colspan="3" class="year-header">ปีการศึกษา {{ $yg['year'] }} {{ $yg['level'] }}</th>
                @endforeach
                @for($i = count($cols); $i < $numCols; $i++)
                <th colspan="3" class="year-header">&nbsp;</th>
                @endfor
            </tr>
            <tr>
                @for($c = 0; $c < $numCols; $c++)
                <th class="col-subject">รหัส/รายวิชา</th>
                <th class="col-credit">หน่วยกิต</th>
                <th class="col-grade">ผลการเรียน</th>
                @endfor
            </tr>
        </thead>
        <tbody>
        @for($r = 0; $r < $maxRows; $r++)
        <tr>
            @for($c = 0; $c < $numCols; $c++)
            @php $row = $colRows[$c][$r] ?? ['type'=>'empty']; @endphp
            @if($row['type'] === 'sem')
                <td colspan="3" class="sem-header">{{ $row['label'] }}</td>
            @elseif($row['type'] === 'subject')
                <td class="col-subject">{{ $row['code'] }} : {{ $row['name'] }}</td>
                <td class="col-credit">{{ $row['credits'] }}</td>
                <td class="col-grade">{{ $row['grade'] }}</td>
            @else
                <td class="col-subject">&nbsp;</td><td class="col-credit"></td><td class="col-grade"></td>
            @endif
            @endfor
        </tr>
        @endfor
        </tbody>
    </table>
    @else
    <p style="text-align:center;color:#999;padding:20px">ยังไม่มีผลการเรียน</p>
    @endif

    <div class="sig-row" style="margin-top:6mm">
        <div class="sig-box" style="margin-left:auto">
            <div class="sig-line"></div>
            <div>({{ $school['registrar_name'] }})</div>
            <div>{{ $school['registrar_position'] }}</div>
        </div>
    </div>
</div>

{{-- ===== PAGE 2 ===== --}}
<div class="page">
    <div class="doc-meta-row" style="margin-bottom:3mm">
        <div class="label">ปพ.1 : พ</div>
        <div class="field-group"><span>ชุดที่</span><span class="field-val">{{ $docNumber->doc_set ?? '' }}</span></div>
        <div class="field-group"><span>เลขที่</span><span class="field-val">{{ $docNumber->doc_number ?? '' }}</span></div>
    </div>

    <div class="p2-title">ผลการประเมินกิจกรรมพัฒนาผู้เรียน</div>
    <table class="p2-table">
        <thead>
            <tr>
                <th rowspan="2" style="width:30%">กิจกรรม</th>
                <th colspan="2">ภาคเรียนที่ 1</th>
                <th rowspan="2" style="width:30%">กิจกรรม</th>
                <th colspan="2">ภาคเรียนที่ 1</th>
                <th rowspan="2" style="width:30%">กิจกรรม</th>
                <th colspan="2">ภาคเรียนที่ 1</th>
            </tr>
            <tr>
                @foreach(range(1,3) as $c)
                <th style="width:5%">เวลา<br>(ชม.)</th><th style="width:5%">ผล</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach(['แนะแนว','ชุมนุม','กิจกรรมเพื่อสังคมและสาธารณประโยชน์'] as $act)
            <tr><td>{{ $act }}</td><td></td><td></td><td>{{ $act }}</td><td></td><td></td><td>{{ $act }}</td><td></td><td></td></tr>
            @endforeach
            <tr><td colspan="9" style="font-size:12px;background:#f8f8f8;font-weight:600">ภาคเรียนที่ 2</td></tr>
            @foreach(['แนะแนว','ชุมนุม','กิจกรรมเพื่อสังคมและสาธารณประโยชน์'] as $act)
            <tr><td>{{ $act }}</td><td></td><td></td><td>{{ $act }}</td><td></td><td></td><td>{{ $act }}</td><td></td><td></td></tr>
            @endforeach
        </tbody>
    </table>

    <table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:3mm">
        <tr>
            <td style="width:50%;border:1px solid #000;padding:4px 6px;vertical-align:top">
                <strong>สรุปผลการประเมิน</strong><br>
                1. จำนวนหน่วยกิตรายวิชาพื้นฐานที่เรียน .............. ได้ ..............<br>
                &nbsp;&nbsp;&nbsp;จำนวนหน่วยกิตรายวิชาเพิ่มเติมที่เรียน ............. ได้ ..............<br>
                2. ผลการประเมินการอ่าน คิดวิเคราะห์และเขียน ได้ .................<br>
                3. ผลการประเมินคุณลักษณะอันพึงประสงค์ ได้ ...................<br>
                4. ผลการประเมินกิจกรรมพัฒนาผู้เรียน ได้ .....................
            </td>
            <td style="width:50%;border:1px solid #000;padding:4px 6px;vertical-align:top">
                <strong>ผลการตัดสิน</strong>
                <table style="width:100%;font-size:13px;margin-top:4px">
                    <tr><td>1. ผลการเรียนเฉลี่ยตลอดหลักสูตร</td><td style="text-align:right;border-bottom:1px solid #555;width:20mm"></td><td style="width:14mm;text-align:center">..........ผ่าน</td></tr>
                    <tr><td>2. ผลการทดสอบทางการศึกษาระดับชั้นพื้นฐาน</td><td style="text-align:right;border-bottom:1px solid #555"></td><td style="text-align:center">..........ผ่าน</td></tr>
                    <tr><td>3. ผลการเรียนเฉลี่ย</td><td style="text-align:right;border-bottom:1px solid #555"></td><td style="text-align:center">..........ผ่าน</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="p2-title" style="font-size:15px">ผลการทดสอบระดับชาติ</div>
    <table class="p2-table" style="margin-bottom:3mm">
        <tr><th style="width:35%">รายการ</th><th style="width:15%">คะแนน</th><th style="width:35%">รายการ</th><th style="width:15%">คะแนน</th></tr>
        <tr><td>&nbsp;</td><td></td><td>&nbsp;</td><td></td></tr>
        <tr><td>&nbsp;</td><td></td><td>&nbsp;</td><td></td></tr>
        <tr><td>&nbsp;</td><td></td><td>&nbsp;</td><td></td></tr>
    </table>

    <div class="p2-title" style="font-size:15px">เกณฑ์การประเมินของสถานศึกษา</div>
    <table class="p2-table" style="font-size:13px">
        <tr>
            <th>คะแนน</th><th>ระดับผลการเรียน</th><th>ความหมาย</th>
            <th>คะแนน</th><th>ระดับผลการเรียน</th><th>ความหมาย</th>
        </tr>
        <tr><td>80-100</td><td style="text-align:center">4</td><td>ดีเยี่ยม</td><td>60-64</td><td style="text-align:center">2</td><td>ปานกลาง</td></tr>
        <tr><td>75-79</td><td style="text-align:center">3.5</td><td>ดีมาก</td><td>55-59</td><td style="text-align:center">1.5</td><td>พอใช้</td></tr>
        <tr><td>70-74</td><td style="text-align:center">3</td><td>ดี</td><td>50-54</td><td style="text-align:center">1</td><td>ผ่านเกณฑ์ขั้นต่ำ</td></tr>
        <tr><td>65-69</td><td style="text-align:center">2.5</td><td>ค่อนข้างดี</td><td>0-49</td><td style="text-align:center">0</td><td>ต่ำกว่าเกณฑ์</td></tr>
    </table>

    <div class="sig-row">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div>({{ $school['registrar_name'] }})</div>
            <div>{{ $school['registrar_position'] }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <div>({{ $school['director_name'] }})</div>
            <div>{{ $school['director_position'] }}</div>
        </div>
    </div>
</div>

</body>
</html>
