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
    font-size: 15px; color: #222; background: #e0e0e0;
    line-height: 1.0; -webkit-font-smoothing: antialiased;
}

.page {
    width: 210mm; height: 297mm;
    margin: 0 auto 10mm; padding: 3mm 14mm 4mm;
    background: #fff;
    box-shadow: 0 0 8px rgba(0,0,0,0.15);
    page-break-after: always;
    display: flex;
    flex-direction: column;
}
.page:last-child { page-break-after: auto; }

/* ===== HEADER ===== */
.doc-top {
    display: flex; justify-content: center; align-items: center;
    gap: 3px; margin-bottom: 0;
}
.doc-logo { width: 14mm; height: 14mm; flex-shrink: 0; }
.doc-logo img { width: 100%; height: 100%; object-fit: contain; display: block; }
.doc-title-block { line-height: 1.0; }
.doc-title-block h2 {
    font-family: 'TH Sarabun New', 'Sarabun', sans-serif;
    font-size: 20px; font-weight: 700; line-height: 1.0;
    text-align: left; margin-bottom: 0;
}

.doc-meta-row {
    display: flex; align-items: baseline; justify-content: flex-start;
    gap: 8mm; margin: 0; padding: 0; line-height: 1.0;
}
.doc-meta-row .label { font-size: 20px; font-weight: 700; line-height: 1.0; }
.doc-meta-row .field-group { display: flex; align-items: baseline; gap: 3px; font-size: 20px; font-weight: 700; line-height: 1.0; }
.doc-meta-row .field-val {
    font-size: 20px; font-weight: 700;
    border-bottom: 0.3px solid #999;
    min-width: 28mm; text-align: center;
    padding: 0 4px 0; display: inline-block;
    line-height: 1.0;
}

/* ===== INFO SECTION ===== */
.info-section { 
    display: flex; gap: 0; margin: 0; align-items: flex-start;
}
.main-school-info { flex: 1; }
.info-left { flex: 1; }
.info-right { width: 78mm; padding-left: 4mm; }
.info-photo { 
    width: 32mm; flex-shrink: 0; padding-left: 3mm; padding-top: 1mm; 
}
.info-row { display: flex; align-items: baseline; margin-bottom: 2mm; }
.info-row .lbl {
    font-size: 15px; font-weight: 300; white-space: nowrap;
    padding-right: 1mm; flex-shrink: 0; line-height: 1.0;
}
.info-row .val {
    font-size: 15px; line-height: 1.0; flex: 1; min-width: 10mm;
    padding-left: 1mm; position: relative; z-index: 0; border-bottom: none;
}
.info-row .val-fixed {
    font-size: 15px; line-height: 1.0; display: inline-block;
    text-align: center; padding: 0 2px; position: relative; z-index: 0; border-bottom: none;
}
.info-row .val::after, .info-row .val-fixed::after {
    content: ""; position: absolute; left: 0; right: 0; bottom: 3px;
    border-bottom: 0.3px solid #999; z-index: -1;
}

.photo-box {
    width: 28mm; height: 36mm; border: 0.5px solid #888;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; color: #aaa; overflow: hidden;
}
.photo-box img { width: 100%; height: 100%; object-fit: cover; }

.section-title {
    font-size: 17px; font-weight: 400; text-align: center;
    
}

.grades-outer {
    flex: 1; display: flex; flex-direction: column; margin-top: 1mm;
}
.grades-spacer { flex: 0; }

.grades-table {
    width: 100%; height: 100%; border-collapse: collapse;
    font-size: 13px; border: 1px solid #000; 
}
.grades-table th {
    border: 1px solid #000; padding: 2px 3px; vertical-align: middle;
    background: #fff; text-align: center; font-weight: normal;
}
.grades-table td {
    border-left: 1px solid #000; border-right: 1px solid #000;
    border-top: none; border-bottom: none; padding: 2px 4px; vertical-align: top;
}
.grades-table tbody tr.stretch-row td { height: 100%; }

/* ของใหม่: ล็อคความกว้างเป็นหน่วย mm จะแคบลงชัดเจน */
.col-subject { width: 45mm; font-size: 12px; } /* ส่วนชื่อวิชา */
.col-credit  { width: 6mm; text-align: center; padding: 0 !important; } /* ส่วนหน่วยกิต */
.col-grade   { width: 6mm; text-align: center; padding: 0 !important; } /* ส่วนผลการเรียน */

.vert-header {
    writing-mode: vertical-rl; transform: rotate(180deg);
    white-space: nowrap; margin: 0 auto; font-size: 12px;
}


/* ===== PRINT ===== */
.no-print { padding: 12px; text-align: center; background: #f5f5f5; border-bottom: 1px solid #ccc; }
.btn-print {
    background: #5c6bc0; color: #fff; border: none; border-radius: 6px;
    padding: 10px 32px; font-size: 16px; cursor: pointer; font-family: inherit; margin: 0 6px;
}
.btn-close {
    background: #888; color: #fff; border: none; border-radius: 6px;
    padding: 10px 20px; font-size: 16px; cursor: pointer; font-family: inherit; margin: 0 6px;
}

@media print {
    .no-print { display: none !important; }
    body { background: #fff; }
    .page { margin: 0; box-shadow: none; padding: 8mm 12mm 4mm; height: 297mm; min-height: unset; }
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
            @if($logoSrc)<img src="{{ $logoSrc }}" alt="">@endif
        </div>
        <div class="doc-title-block">
            <h2>ระเบียนแสดงผลการเรียนหลักสูตรแกนกลางการศึกษาขั้นพื้นฐาน ระดับมัธยมศึกษาตอนปลาย</h2>
            <div class="doc-meta-row">
                <div class="label">ปพ.1 : พ</div>
                <div class="field-group"><span>ชุดที่</span><span class="field-val">{{ $docNumber->doc_set ?? '' }}</span></div>
                <div class="field-group"><span>เลขที่</span><span class="field-val">{{ $docNumber->doc_number ?? '' }}</span></div>
            </div>
        </div>
    </div>
    <br>

    {{-- ส่วนข้อมูลส่วนบนของนักเรียนทั้งหมด --}}
    <div class="info-section" style="display: flex; width: 100%;">
        
        <div class="main-school-info" style="flex: 1; padding-right: 4mm;">
            <div class="info-row"><span class="lbl">โรงเรียน</span><span class="val">{{ $school['name'] }}</span></div>
            <div class="info-row"><span class="lbl">สังกัด</span><span class="val">{{ $school['affiliation'] }}</span></div>

            <div style="display: flex; gap: 2mm;">
                <div class="info-left" style="flex: 1;">
                    <div class="info-row"><span class="lbl">ตำบล/แขวง</span><span class="val">{{ $school['tambon'] }}</span></div>
                    <div class="info-row"><span class="lbl">อำเภอ/เขต</span><span class="val">{{ $school['amphoe'] }}</span></div>
                    <div class="info-row"><span class="lbl">จังหวัด</span><span class="val">{{ $school['changwat'] }}</span></div>
                    <div class="info-row"><span class="lbl">สำนักงานเขตพื้นที่การศึกษา</span><span class="val">{{ $school['education_area'] }}</span></div>
                    <div class="info-row"><span class="lbl">วันเข้าเรียน</span><span class="val">{{ $enroll ? $enroll->day . ' ' . $thMonths[$enroll->month] . ' ' . ($enroll->year + 543) : '' }}</span></div>
                    <div class="info-row"><span class="lbl">โรงเรียนเดิม</span><span class="val">{{ $edu->previous_school ?? '' }}</span></div>
                    <div class="info-row"><span class="lbl">จังหวัด</span><span class="val">{{ $edu->previous_province ?? '' }}</span></div>
                    <div class="info-row"><span class="lbl">ชั้นเรียนสุดท้าย</span><span class="val">{{ $edu->education_level ?? '' }}</span></div>
                </div>
                
                <div class="info-right" style="width: 78mm;">
                    <div class="info-row"><span class="lbl">ชื่อ</span><span class="val">{{ $student->thai_prefix }}{{ $student->thai_firstname }}</span></div>
                    <div class="info-row"><span class="lbl">นามสกุล</span><span class="val">{{ $student->thai_lastname }}</span></div>
                    <div class="info-row"><span class="lbl">เลขประจำตัวนักเรียน</span><span class="val">{{ $student->student_code }}</span></div>
                    <div class="info-row"><span class="lbl">เลขประจำตัวประชาชน</span><span class="val">{{ $student->id_card_number }}</span></div>
                    <div class="info-row" style="display:flex;flex-wrap:nowrap;gap:0;align-items:baseline;">
                        <span class="lbl">เกิดวันที่</span>
                        <span class="val-fixed" style="min-width:6mm;">{{ $dob ? $dob->day : '' }}</span>
                        <span class="lbl" style="padding-left:2mm;">เดือน</span>
                        <span class="val-fixed" style="min-width:20mm;">{{ $dob ? $thMonths[$dob->month] : '' }}</span>
                        <span class="lbl" style="padding-left:2mm;">พ.ศ.</span>
                        <span class="val" style="min-width:12mm;">{{ $dob ? ($dob->year + 543) : '' }}</span>
                    </div>
                    <div class="info-row" style="display:flex;flex-wrap:nowrap;gap:0;align-items:baseline;">
                        <span class="lbl">เพศ</span>
                        <span class="val-fixed" style="min-width:8mm;">{{ $student->gender === 'M' ? 'ชาย' : ($student->gender === 'F' ? 'หญิง' : '') }}</span>
                        <span class="lbl" style="padding-left:2mm;">สัญชาติ</span>
                        <span class="val-fixed" style="min-width:12mm;">{{ $student->nationality ?? 'ไทย' }}</span>
                        <span class="lbl" style="padding-left:2mm;">ศาสนา</span>
                        <span class="val" style="min-width:12mm;">{{ $student->religion ?? 'พุทธ' }}</span>
                    </div>
                    <div class="info-row"><span class="lbl">ชื่อ-ชื่อสกุลบิดา</span><span class="val">{{ $father ? ($father->prefix_th . $father->first_name_th . ' ' . $father->last_name_th) : '' }}</span></div>
                    <div class="info-row"><span class="lbl">ชื่อ-ชื่อสกุลมารดา</span><span class="val">{{ $mother ? ($mother->prefix_th . $mother->first_name_th . ' ' . $mother->last_name_th) : '' }}</span></div>
                </div>
            </div>
        </div>

        <div class="info-photo" style="width: 32mm; flex-shrink: 0; padding-top: 1mm;">
            <div class="photo-box">
                @if($student->photo)
                    <img src="{{ asset('storage/' . $student->photo) }}" alt="photo">
                @else รูปถ่าย @endif
            </div>
        </div>
    </div>

    {{-- Section title --}}
    <div class="section-title" style="font-size: 18px; font-weight: bold;">ผลการเรียนรายวิชา</div>

    {{-- Grades outer box --}}
    <div class="grades-outer">
        @if(isset($yearGroups) && count($yearGroups) > 0)
        @php
            ksort($yearGroups);
            $cols    = array_values($yearGroups);
            $numCols = 3;
            $colRows = [];
            
            foreach ($cols as $ci => $yg) {
                if ($ci >= $numCols) break;
                $colRows[$ci] = [];
                $colRows[$ci][] = ['type'=>'year','label'=>'ปีการศึกษา '.$yg['year'].' '.($yg['level']??'')];
                
                foreach ([1, 2] as $sn) {
                    $sk = (string)$sn;
                    if (isset($yg['semesters'][$sk]) && count($yg['semesters'][$sk]) > 0) {
                        foreach ($yg['semesters'][$sk] as $g) {
                            $subj = $g->teachingAssign->subject;
                            $colRows[$ci][] = [
                                'type' => 'subject',
                                'code' => $subj->code ?? '',
                                'name' => $subj->name_th ?? '',
                                'credits' => $subj->credits ?? '',
                                'grade' => $g->grade ?? ''
                            ];
                        }
                    }
                }
                if (count($colRows[$ci]) < 3) $colRows[$ci][] = ['type'=>'empty'];
            }
            
            for ($ci = count($cols); $ci < $numCols; $ci++) {
                $colRows[$ci] = [['type'=>'empty']];
            }
            
            $maxRows = max(array_map('count', $colRows));
        @endphp
        <table class="grades-table">
            <thead>
                <tr>
                    @for($c = 0; $c < $numCols; $c++)
                    <th class="col-subject" style="font-weight: 900; font-size: 18px;">รหัส/รายวิชา</th>
                    <th class="col-credit"><div class="vert-header" style="font-weight: bold; font-size: 1.1em;">หน่วยกิต</div></th>
                    <th class="col-grade"><div class="vert-header" style="font-weight: 800; font-size: 110%;">ผลการเรียน</div></th>
                    @endfor
                </tr>
            </thead>
            <tbody>
            @for($r = 0; $r < $maxRows; $r++)
            <tr>
                @for($c = 0; $c < $numCols; $c++)
                @php $row = $colRows[$c][$r] ?? ['type'=>'empty']; @endphp
                @if($row['type'] === 'year' || $row['type'] === 'sem')
                    <td class="col-subject" style="text-align:center;">{{ $row['label'] }}</td>
                    <td class="col-credit"></td>
                    <td class="col-grade"></td>
                @elseif($row['type'] === 'subject')
                    <td class="col-subject">{{ $row['code'] }} {{ $row['name'] }}</td>
                    <td class="col-credit">{{ (float)$row['credits'] == 0 ? '' : number_format((float)$row['credits'], 1) }}</td>
                    <td class="col-grade">{{ $row['grade'] }}</td>
                @else
                    <td class="col-subject">&nbsp;</td>
                    <td class="col-credit"></td>
                    <td class="col-grade"></td>
                @endif
                @endfor
            </tr>
            @endfor
            
            <tr class="stretch-row">
                @for($c = 0; $c < $numCols; $c++)
                <td class="col-subject"></td>
                <td class="col-credit"></td>
                <td class="col-grade"></td>
                @endfor
            </tr>

            <tr style="height: 70px;">
                @for($c = 0; $c < $numCols; $c++)
                    @if($c == $numCols - 1)
                        <td colspan="3" style="border-top: 1px solid #000; vertical-align: bottom; padding: 10px 15px;">
                            <div style="text-align: center; width: 90%; margin: 0 auto;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span>(</span>
                                    <span style="display: inline-block; width: 120px;">{{ $school['registrar_name'] ?? 'นายทะเบียน' }}</span>
                                    <span>)</span>
                                </div>
                                <div>{{ $school['registrar_position'] ?? 'นายทะเบียน' }}</div>
                            </div>
                        </td>
                    @else
                        <td class="col-subject" style="border-top: none;"></td>
                        <td class="col-credit" style="border-top: none;"></td>
                        <td class="col-grade" style="border-top: none;"></td>
                    @endif
                @endfor
            </tr>
            </tbody>
        </table>
        @endif

        <div class="grades-spacer"></div>

    </div>
</div>

{{-- ===== PAGE 2 ===== --}}
<div class="page">
    <div class="subpage" style="height: 100%; display: flex; flex-direction: column;">
        
        {{-- Header ชุดที่ / เลขที่ --}}
        <div style="display: flex; justify-content: center; align-items: baseline; gap: 30px; margin-top: 10px; font-weight: bold; font-size: 16px;">
            <div>ปพ.1 : พ</div>
            <div>ชุดที่ <span style="display: inline-block; width: 60px; text-align: center;">{{ $docNumber->doc_set ?? '' }}</span></div>
            <div>เลขที่ <span style="display: inline-block; width: 80px; text-align: center;">{{ $docNumber->doc_number ?? '' }}</span></div>
        </div>

        <div style="text-align: center; font-weight: bold; font-size: 16px; margin-top: 15px; margin-bottom: 5px;">
            ผลการประเมินกิจกรรมพัฒนาผู้เรียน
        </div>

        {{-- ตารางกิจกรรมพัฒนาผู้เรียน (เส้นยาวต่อเนื่อง, มีเส้นใต้หัวข้อ, จัดความกว้างเป๊ะๆ ให้ตรงกับตารางล่าง) --}}
        <table style="width: 100%; table-layout: fixed; border-collapse: collapse; font-size: 12px; border: 1px solid #000;">
            <colgroup>
                <col style="width: 25%;">
                <col style="width: 4.166667%;">
                <col style="width: 4.166667%;">
                <col style="width: 25%;">
                <col style="width: 4.166667%;">
                <col style="width: 4.166667%;">
                <col style="width: 25%;">
                <col style="width: 4.166667%;">
                <col style="width: 4.166667%;">
            </colgroup>
            <thead>
                <tr>
                    <th style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; text-align: center; font-weight: normal;">กิจกรรม</th>
                    <th style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; font-weight: normal;"><div class="vert-header" style="height: 50px;">เวลา<br>(ชั่วโมง)</div></th>
                    <th style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; font-weight: normal;"><div class="vert-header" style="height: 50px;">ผลการ<br>ประเมิน</div></th>
                    <th style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; text-align: center; font-weight: normal;">กิจกรรม</th>
                    <th style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; font-weight: normal;"><div class="vert-header" style="height: 50px;">เวลา<br>(ชั่วโมง)</div></th>
                    <th style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; font-weight: normal;"><div class="vert-header" style="height: 50px;">ผลการ<br>ประเมิน</div></th>
                    <th style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; text-align: center; font-weight: normal;">กิจกรรม</th>
                    <th style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; font-weight: normal;"><div class="vert-header" style="height: 50px;">เวลา<br>(ชั่วโมง)</div></th>
                    <th style="border-bottom: 1px solid #000; padding: 4px; font-weight: normal;"><div class="vert-header" style="height: 50px;">ผลการ<br>ประเมิน</div></th>
                </tr>
            </thead>
            <tbody>
                <tr style="font-weight: bold;">
                    <td colspan="3" style="text-align: left; padding: 2px 6px; border-right: 1px solid #000;">ปีการศึกษา ...... มัธยมศึกษาปีที่ 4<br>ภาคเรียนที่ 1</td>
                    <td colspan="3" style="text-align: left; padding: 2px 6px; border-right: 1px solid #000;">ปีการศึกษา ...... มัธยมศึกษาปีที่ 5<br>ภาคเรียนที่ 1</td>
                    <td colspan="3" style="text-align: left; padding: 2px 6px;">ปีการศึกษา ...... มัธยมศึกษาปีที่ 6<br>ภาคเรียนที่ 1</td>
                </tr>
                @foreach(['แนะแนว','ชุมนุม','กิจกรรมเพื่อสังคมและสาธารณประโยชน์'] as $act)
                <tr>
                    <td style="padding: 2px 6px; text-align: left; border-right: 1px solid #000;">{{ $act }}</td>
                    <td style="text-align: center; border-right: 1px solid #000;"></td>
                    <td style="text-align: center; border-right: 1px solid #000;"></td>
                    <td style="padding: 2px 6px; text-align: left; border-right: 1px solid #000;">{{ $act }}</td>
                    <td style="text-align: center; border-right: 1px solid #000;"></td>
                    <td style="text-align: center; border-right: 1px solid #000;"></td>
                    <td style="padding: 2px 6px; text-align: left; border-right: 1px solid #000;">{{ $act }}</td>
                    <td style="text-align: center; border-right: 1px solid #000;"></td>
                    <td style="text-align: center;"></td>
                </tr>
                @endforeach
                
                <tr style="font-weight: bold;">
                    <td colspan="3" style="text-align: left; padding: 2px 6px; border-right: 1px solid #000;">ภาคเรียนที่ 2</td>
                    <td colspan="3" style="text-align: left; padding: 2px 6px; border-right: 1px solid #000;">ภาคเรียนที่ 2</td>
                    <td colspan="3" style="text-align: left; padding: 2px 6px;">ภาคเรียนที่ 2</td>
                </tr>
                @foreach(['แนะแนว','ชุมนุม','กิจกรรมเพื่อสังคมและสาธารณประโยชน์'] as $act)
                <tr>
                    <td style="padding: 2px 6px; text-align: left; border-right: 1px solid #000;">{{ $act }}</td>
                    <td style="text-align: center; border-right: 1px solid #000;"></td>
                    <td style="text-align: center; border-right: 1px solid #000;"></td>
                    <td style="padding: 2px 6px; text-align: left; border-right: 1px solid #000;">{{ $act }}</td>
                    <td style="text-align: center; border-right: 1px solid #000;"></td>
                    <td style="text-align: center; border-right: 1px solid #000;"></td>
                    <td style="padding: 2px 6px; text-align: left; border-right: 1px solid #000;">{{ $act }}</td>
                    <td style="text-align: center; border-right: 1px solid #000;"></td>
                    <td style="text-align: center;"></td>
                </tr>
                @endforeach
                {{-- แถวว่างดันท้ายให้ตารางกว้างพอดี --}}
                <tr>
                    <td style="border-right: 1px solid #000; padding: 4px;">&nbsp;</td>
                    <td style="border-right: 1px solid #000;"></td>
                    <td style="border-right: 1px solid #000;"></td>
                    <td style="border-right: 1px solid #000;"></td>
                    <td style="border-right: 1px solid #000;"></td>
                    <td style="border-right: 1px solid #000;"></td>
                    <td style="border-right: 1px solid #000;"></td>
                    <td style="border-right: 1px solid #000;"></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        {{-- ตารางกรอบใหญ่ด้านล่าง (ออกแบบโครงสร้างตารางย่อยเพื่อให้เส้นแนวตั้งตรงกัน 100%) --}}
        <table style="width: 100%; table-layout: fixed; border-collapse: collapse; font-size: 12px; border: 1px solid #000; border-top: none;">
            <colgroup>
                <col style="width: 33.333333%;">
                <col style="width: 33.333333%;">
                <col style="width: 33.333333%;">
            </colgroup>
            <tbody>
                <tr>
                    {{-- ฝั่งซ้าย (กินพื้นที่ 2 คอลัมน์) สรุปผล + ผลการตัดสิน --}}
                    <td colspan="2" style="vertical-align: top; padding: 0; border-right: 1px solid #000;">
                        <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                            <colgroup>
                                <col style="width: 50%;">
                                <col style="width: 50%;">
                            </colgroup>
                            <tbody>
                                {{-- หัวข้อ สรุปผลการประเมิน | ผลการตัดสิน --}}
                                <tr>
                                    <th style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 6px; text-align: center; font-weight: normal;">สรุปผลการประเมิน</th>
                                    <th style="border-bottom: 1px solid #000; padding: 6px; text-align: center; font-weight: normal;">ผลการตัดสิน</th>
                                </tr>
                                {{-- ข้อมูลคะแนน --}}
                                <tr>
                                    <td style="border-right: 1px solid #000; padding: 8px 10px; vertical-align: top; line-height: 1.8;">
                                        <div style="display:flex; justify-content:space-between;"><span>1. จำนวนหน่วยกิตรายวิชาพื้นฐานที่เรียน</span> <div style="display:flex;gap:15px"><span>{{ $totalCredits ?? '41.0' }}</span> <span>ได้</span> <span>{{ $totalCredits ?? '41.0' }}</span></div></div>
                                        <div style="display:flex; justify-content:space-between; padding-left:12px;"><span>จำนวนหน่วยกิตรายวิชาเพิ่มเติมที่เรียน</span> <div style="display:flex;gap:15px"><span>48.0</span> <span>ได้</span> <span>48.0</span></div></div>
                                        <div style="display:flex; justify-content:space-between;"><span>2. ผลการประเมินการอ่าน คิดวิเคราะห์และเขียน</span> <div style="display:flex;gap:15px;"><span>ได้</span><span style="width:40px;text-align:center;">ดีเยี่ยม</span></div></div>
                                        <div style="display:flex; justify-content:space-between;"><span>3. ผลการประเมินคุณลักษณะอันพึงประสงค์</span> <div style="display:flex;gap:15px;"><span>ได้</span><span style="width:40px;text-align:center;">ดีเยี่ยม</span></div></div>
                                        <div style="display:flex; justify-content:space-between;"><span>4. ผลการประเมินกิจกรรมพัฒนาผู้เรียน</span> <div style="display:flex;gap:15px;"><span>ได้</span><span style="width:40px;text-align:center;">ผ่าน</span></div></div>
                                    </td>
                                    <td style="padding: 8px 10px; vertical-align: top; text-align: center; line-height: 1.8;">
                                        <div style="margin-top:2px">ผ่าน</div>
                                        <div>ผ่าน</div>
                                        <div>ผ่าน</div>
                                        <div>ผ่าน</div>
                                        <div>ผ่าน</div>
                                    </td>
                                </tr>
                                {{-- วันที่จบ --}}
                                <tr>
                                    <td colspan="2" style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 6px 10px; line-height: 1.8;">
                                        <div style="display:flex;">
                                            <span style="width:140px;">วันอนุมัติการจบ</span>
                                            <span>{{ $student->approve_date ?? '31 มีนาคม 2569' }}</span>
                                        </div>
                                        <div style="display:flex;">
                                            <span style="width:140px;">วันออกจากโรงเรียน</span>
                                            <span>{{ $student->leave_date ?? '31 มีนาคม 2569' }}</span>
                                        </div>
                                        <div style="display:flex;">
                                            <span style="width:140px;">สาเหตุที่ออกจากโรงเรียน</span>
                                            <span>{{ $student->leave_reason ?? '-' }}</span>
                                        </div>
                                    </td>
                                </tr>
                                {{-- ผลการทดสอบระดับชาติ --}}
                                <tr>
                                    <th colspan="2" style="border-bottom: 1px solid #000; padding: 6px; text-align: center; font-weight: normal;">ผลการทดสอบระดับชาติ</th>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding: 20px; text-align: center; color: #555;">-</td>
                                </tr>
                                {{-- สัดส่วนผลการเรียน... --}}
                                <tr>
                                    <th colspan="2" style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 6px; text-align: center; font-weight: normal;">สัดส่วนผลการเรียนและผลการทดสอบระดับชาติ</th>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding: 8px 12px; line-height: 1.8;">
                                        <div style="display: flex; justify-content: space-between;">
                                            <span>1. ร้อยละ - ของผลการเรียนเฉลี่ยตลอดหลักสูตร</span>
                                            <span>= &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between;">
                                            <span>2. ร้อยละ - ของผลการทดสอบทางการศึกษาระดับชาติขั้นพื้นฐาน</span>
                                            <span>= &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between;">
                                            <span>3. ผลการเรียนเฉลี่ย</span>
                                            <span>= &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -</span>
                                        </div>
                                    </td>
                                </tr>
                                {{-- เกณฑ์การประเมิน... --}}
                                <tr>
                                    <th colspan="2" style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 6px; text-align: center; font-weight: normal;">เกณฑ์การประเมินของสถานศึกษา</th>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding: 8px 10px;">
                                        <div style="font-weight: normal; font-size: 12px; margin-bottom: 2px;">เกณฑ์การจบการศึกษาระดับมัธยมศึกษาตอนปลาย</div>
                                        <div style="font-size: 11.5px; line-height: 1.4; padding-left: 10px; text-indent: -10px;">
                                            1. ผู้เรียนเรียนรายวิชาพื้นฐานและเพิ่มเติมวิชาพื้นฐาน 41 หน่วยกิต และรายวิชาเพิ่มเติมตามที่สถานศึกษากำหนด หน่วยกิต<br>
                                            2. ผู้เรียนต้องได้หน่วยกิตตลอดหลักสูตรไม่น้อยกว่า 77 หน่วยกิต โดยเป็นรายวิชาพื้นฐาน 41 หน่วยกิต และรายวิชาเพิ่มเติมไม่น้อยกว่า 36 หน่วยกิต<br>
                                            3. ผู้เรียนมีผลการประเมินการอ่าน คิดวิเคราะห์ และเขียน ในระดับ ผ่าน ตามเกณฑ์การประเมินของสถานศึกษากำหนด<br>
                                            4. ผู้เรียนมีผลการประเมินคุณลักษณะอันพึงประสงค์ ในระดับ ผ่าน ตามเกณฑ์การประเมินของสถานศึกษากำหนด<br>
                                            5. ผู้เรียนเข้าร่วมกิจกรรมพัฒนาผู้เรียนและมีผลการประเมิน ผ่าน ทุกกิจกรรม ตามเกณฑ์การประเมินของสถานศึกษากำหนด
                                        </div>
                                        
                                        <div style="font-weight: normal; font-size: 12px; margin-top: 8px; margin-bottom: 2px;">คำอธิบายเกณฑ์ ผลการประเมินรายวิชา</div>
                                        <table style="width: 100%; font-size: 11.5px; text-align: center; border-collapse: collapse;">
                                            <tr>
                                                <td style="border: none;">คะแนน</td><td style="border: none;">ระดับผลการเรียน</td><td style="border: none; text-align:left;">ความหมาย</td>
                                                <td style="border: none;">คะแนน</td><td style="border: none;">ระดับผลการเรียน</td><td style="border: none; text-align:left;">ความหมาย</td>
                                            </tr>
                                            <tr><td style="border: none;">80-100</td><td style="border: none;">4</td><td style="border: none; text-align:left;">ดีเยี่ยม</td><td style="border: none;">60-64</td><td style="border: none;">2</td><td style="border: none; text-align:left;">ปานกลาง</td></tr>
                                            <tr><td style="border: none;">75-79</td><td style="border: none;">3.5</td><td style="border: none; text-align:left;">ดีมาก</td><td style="border: none;">55-59</td><td style="border: none;">1.5</td><td style="border: none; text-align:left;">พอใช้</td></tr>
                                            <tr><td style="border: none;">70-74</td><td style="border: none;">3</td><td style="border: none; text-align:left;">ดี</td><td style="border: none;">50-54</td><td style="border: none;">1</td><td style="border: none; text-align:left;">ผ่านเกณฑ์ขั้นต่ำ</td></tr>
                                            <tr><td style="border: none;">65-69</td><td style="border: none;">2.5</td><td style="border: none; text-align:left;">ค่อนข้างดี</td><td style="border: none;">0-49</td><td style="border: none;">0</td><td style="border: none; text-align:left;">ต่ำกว่าเกณฑ์</td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>

                    {{-- ฝั่งขวา (กินพื้นที่ 1 คอลัมน์) กลุ่มสาระฯ และ ลายเซ็น --}}
                    <td colspan="1" style="vertical-align: top; padding: 0;">
                        <table style="width: 100%; table-layout: fixed; border-collapse: collapse; height: 100%;">
                            <colgroup>
                                <col style="width: 75%;">
                                <col style="width: 12.5%;">
                                <col style="width: 12.5%;">
                            </colgroup>
                            <tbody>
                                {{-- หัวข้อ กลุ่มสาระ --}}
                                <tr>
                                    <th style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; text-align: center; font-weight: normal;">กลุ่มสาระการเรียนรู้/<br>การศึกษาค้นคว้าด้วยตนเอง</th>
                                    <th style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; font-weight: normal;"><div class="vert-header" style="height: 60px;">หน่วยกิตรวม</div></th>
                                    <th style="border-bottom: 1px solid #000; padding: 4px; font-weight: normal;"><div class="vert-header" style="height: 60px;">ผลการเรียนเฉลี่ย</div></th>
                                </tr>
                                {{-- รายวิชา --}}
                                @foreach(['ภาษาไทย', 'คณิตศาสตร์', 'วิทยาศาสตร์และเทคโนโลยี', 'สังคมศึกษา ศาสนา และวัฒนธรรม', 'สุขศึกษาและพลศึกษา', 'ศิลปะ', 'การงานอาชีพ', 'ภาษาต่างประเทศ', 'การศึกษาค้นคว้าด้วยตนเอง'] as $group)
                                <tr>
                                    <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px 6px;">{{ $group }}</td>
                                    <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; text-align: center;"></td>
                                    <td style="border-bottom: 1px solid #000; text-align: center;"></td>
                                </tr>
                                @endforeach
                                {{-- ผลการเรียนเฉลี่ยตลอดหลักสูตร --}}
                                <tr>
                                    <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 5px 6px;">ผลการเรียนเฉลี่ยตลอดหลักสูตร</td>
                                    <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; text-align: center;">{{ $totalCredits ?? '89.0' }}</td>
                                    <td style="border-bottom: 1px solid #000; text-align: center;">{{ $gpa ?? '3.58' }}</td>
                                </tr>
                                {{-- ช่องว่างด้านล่างเพื่อดันลายเซ็น --}}
                                <tr>
                                    <td colspan="3" style="border-bottom: none; height: 120px;"></td>
                                </tr>
                                {{-- ลายเซ็น --}}
                                <tr>
                                    <td colspan="3" style="border-bottom: none; padding-bottom: 20px;">
                                        <div style="text-align: center; margin-bottom: 50px;">
                                            <div style="display: flex; justify-content: center; align-items: baseline; gap: 5px;">
                                                <span>(</span>
                                                <span style="border-bottom: 1px dotted #000; width: 140px; display: inline-block;">{{ $school['registrar_name'] ?? 'นายทะเบียน' }}</span>
                                                <span>)</span>
                                            </div>
                                            <div style="margin-top: 3px;">นายทะเบียน</div>
                                        </div>
                                        
                                        <div style="text-align: center;">
                                            <div style="display: flex; justify-content: center; align-items: baseline; gap: 5px;">
                                                <span>(</span>
                                                <span style="border-bottom: 1px dotted #000; width: 140px; display: inline-block;">{{ $school['director_name'] ?? 'ผู้อำนวยการโรงเรียน' }}</span>
                                                <span>)</span>
                                            </div>
                                            <div style="margin-top: 3px;">ผู้อำนวยการโรงเรียน</div>
                                            <div style="margin-top: 15px; text-align: left; padding-left: 20px;">วันที่ ..............................................................</div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>