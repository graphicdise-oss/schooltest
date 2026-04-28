<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ปพ.1 — {{ $student->thai_firstname }} {{ $student->thai_lastname }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'TH Sarabun New', 'Sarabun', 'Arial', sans-serif; font-size: 13px; color: #000; background: #fff; }

.page {
    width: 210mm; min-height: 297mm;
    margin: 0 auto; padding: 10mm 12mm;
    background: #fff;
    page-break-after: always;
}
.page:last-child { page-break-after: auto; }

/* Header */
.doc-header { text-align: center; margin-bottom: 4px; }
.doc-header h2 { font-size: 15px; font-weight: bold; }
.doc-meta { display: flex; justify-content: center; align-items: center; gap: 30px; margin: 4px 0 8px; font-size: 14px; font-weight: bold; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 3px 0; }
.doc-meta span { font-size: 14px; }
.doc-meta .meta-val { border-bottom: 1.5px solid #000; min-width: 80px; text-align: center; display: inline-block; padding: 0 8px; font-size: 16px; }

/* Info table */
.info-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
.info-table td { padding: 1px 4px; font-size: 13px; vertical-align: top; }
.info-label { white-space: nowrap; font-weight: 600; width: 90px; }
.info-val { border-bottom: 1px dotted #999; min-width: 80px; }
.photo-cell { width: 28mm; text-align: center; vertical-align: top; }
.photo-box {
    width: 26mm; height: 34mm; border: 1px solid #999;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; color: #999; margin: 0 auto;
}
.photo-box img { width: 100%; height: 100%; object-fit: cover; }

/* Subject results table */
.section-title { font-size: 14px; font-weight: bold; text-align: center; margin: 6px 0 4px; }
.grades-table { width: 100%; border-collapse: collapse; font-size: 11px; }
.grades-table th, .grades-table td { border: 1px solid #000; padding: 1px 3px; vertical-align: top; }
.grades-table th { background: #f5f5f5; text-align: center; font-size: 11px; }
.year-header { text-align: center; font-weight: bold; font-size: 11px; background: #eee; }
.sem-header { font-weight: 600; font-size: 11px; background: #fafafa; }
.col-subject { width: 38%; }
.col-credit { width: 8%; text-align: center; }
.col-grade { width: 8%; text-align: center; }

/* Page 2 */
.p2-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 6px; }
.p2-table th, .p2-table td { border: 1px solid #000; padding: 2px 4px; }
.p2-table th { background: #f0f0f0; text-align: center; font-size: 11px; }

.summary-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0; border-top: 1px solid #000; }
.summary-cell { border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 3px 6px; font-size: 11px; }
.summary-cell:last-child { border-right: none; }

.grade-scale { display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px; font-size: 11px; }

.sig-row { display: flex; justify-content: space-between; margin-top: 12px; }
.sig-box { text-align: center; width: 45%; }
.sig-line { border-top: 1px solid #000; margin: 20px 10px 4px; }

.no-print { margin: 20px auto; text-align: center; }
.btn-print { background: #5c6bc0; color: #fff; border: none; border-radius: 6px; padding: 10px 32px; font-size: 15px; cursor: pointer; font-family: inherit; }

@media print {
    .no-print { display: none !important; }
    body { background: #fff; }
    .page { margin: 0; padding: 8mm 10mm; }
}
</style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ พิมพ์ / บันทึก PDF</button>
    &nbsp;&nbsp;
    <button onclick="window.close()" style="background:#888;color:#fff;border:none;border-radius:6px;padding:10px 20px;font-size:15px;cursor:pointer">ปิด</button>
</div>

{{-- ===== หน้า 1 ===== --}}
<div class="page">
    <div class="doc-header">
        <h2>ระเบียนแสดงผลการเรียนหลักสูตรแกนกลางการศึกษาขั้นพื้นฐาน ระดับมัธยมศึกษาตอนปลาย</h2>
    </div>

    <div class="doc-meta">
        <span>ปว.1 : พ</span>
        <span>ชุดที่ &nbsp; <span class="meta-val">{{ $docNumber->doc_set ?? '...........'}}</span></span>
        <span>เลขที่ &nbsp; <span class="meta-val">{{ $docNumber->doc_number ?? '...........' }}</span></span>
    </div>

    {{-- Student info --}}
    @php
        $edu  = $student->education ?? null;
        $addr = $student->addresses->firstWhere('address_type', 'registered') ?? $student->addresses->first();
        $dob  = $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth) : null;
        $enroll = $student->enroll_date ? \Carbon\Carbon::parse($student->enroll_date) : null;
        $school = config('school');
    @endphp

    <table class="info-table">
        <tr>
            <td style="width:72%">
                <table style="width:100%;border-collapse:collapse">
                    <tr>
                        <td class="info-label">โรงเรียน</td>
                        <td class="info-val" colspan="3">{{ $school['name'] }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">สังกัด</td>
                        <td class="info-val" colspan="3">{{ $school['affiliation'] }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">ตำบล/แขวง</td>
                        <td class="info-val" style="width:25%">{{ $school['tambon'] }}</td>
                        <td class="info-label" style="width:18%">ชื่อ</td>
                        <td class="info-val">{{ $student->thai_prefix }}{{ $student->thai_firstname }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">อำเภอ/เขต</td>
                        <td class="info-val">{{ $school['amphoe'] }}</td>
                        <td class="info-label">ชื่อสกุล</td>
                        <td class="info-val">{{ $student->thai_lastname }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">จังหวัด</td>
                        <td class="info-val">{{ $school['changwat'] }}</td>
                        <td class="info-label">เลขประจำตัวนักเรียน</td>
                        <td class="info-val">{{ $student->student_code }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">สำนักงานเขตพื้นที่การศึกษา</td>
                        <td class="info-val" colspan="3">{{ $school['education_area'] }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">วันเข้าเรียน</td>
                        <td class="info-val">{{ $enroll ? $enroll->locale('th')->translatedFormat('j F') . ' ' . ($enroll->year + 543) : '' }}</td>
                        <td class="info-label">เลขประจำตัวประชาชน</td>
                        <td class="info-val">{{ $student->id_card_number }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">โรงเรียนเดิม</td>
                        <td class="info-val" colspan="3">{{ $edu->previous_school ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">จังหวัด</td>
                        <td class="info-val">{{ $edu->previous_province ?? '' }}</td>
                        <td class="info-label">เกิดวันที่</td>
                        <td class="info-val">{{ $dob ? $dob->day . ' เดือน ' . $dob->locale('th')->monthName . ' พ.ศ. ' . ($dob->year + 543) : '' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">ชั้นเรียนสุดท้าย</td>
                        <td class="info-val">{{ $edu->education_level ?? '' }}</td>
                        <td class="info-label">เพศ</td>
                        <td class="info-val">
                            {{ $student->gender === 'M' ? 'ชาย' : ($student->gender === 'F' ? 'หญิง' : '') }}
                            &nbsp; สัญชาติ {{ $student->nationality ?? 'ไทย' }}
                            &nbsp; ศาสนา {{ $student->religion ?? 'พุทธ' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">ชื่อ-ชื่อสกุลบิดา</td>
                        <td class="info-val" colspan="3">
                            {{ $father ? ($father->prefix_th . $father->first_name_th . ' ' . $father->last_name_th) : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">ชื่อ-ชื่อสกุลมารดา</td>
                        <td class="info-val" colspan="3">
                            {{ $mother ? ($mother->prefix_th . $mother->first_name_th . ' ' . $mother->last_name_th) : '' }}
                        </td>
                    </tr>
                </table>
            </td>
            <td class="photo-cell">
                <div class="photo-box">
                    @if($student->photo)
                        <img src="{{ asset('storage/' . $student->photo) }}" alt="photo">
                    @else
                        รูปถ่าย
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Subject results --}}
    <div class="section-title">ผลการเรียนรายวิชา</div>

    @if(count($yearGroups) > 0)
    <table class="grades-table">
        <thead>
            <tr>
                @foreach($yearGroups as $yg)
                <th colspan="3" class="year-header">
                    ปีการศึกษา {{ $yg['year'] }} {{ $yg['level'] }}
                </th>
                @endforeach
                @for($i = count($yearGroups); $i < 3; $i++)
                <th colspan="3" class="year-header">&nbsp;</th>
                @endfor
            </tr>
            <tr>
                @foreach(range(1, max(3, count($yearGroups))) as $c)
                <th class="col-subject">รหัส/รายวิชา</th>
                <th class="col-credit">หน่วยกิต</th>
                <th class="col-grade">ผลการเรียน</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        @php
            // Build column data
            $cols = array_values($yearGroups);
            $numCols = max(3, count($cols));
            // Flatten rows per column: each row is [sem_header or subject]
            $colRows = [];
            foreach ($cols as $ci => $yg) {
                $colRows[$ci] = [];
                foreach ([1, 2] as $semNum) {
                    $semKey = (string)$semNum;
                    $subjects = $yg['semesters'][$semKey] ?? [];
                    if (count($yg['semesters']) > 0) {
                        $colRows[$ci][] = ['type' => 'sem', 'label' => 'ภาคเรียนที่ ' . $semNum];
                        foreach ($subjects as $g) {
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
                }
                for ($fill = count($colRows[$ci]); $fill < 3; $fill++) {
                    $colRows[$ci][] = ['type' => 'empty'];
                }
            }
            for ($ci = count($cols); $ci < $numCols; $ci++) {
                $colRows[$ci] = [['type'=>'empty'],['type'=>'empty'],['type'=>'empty']];
            }
            $maxRows = max(array_map('count', $colRows));
        @endphp

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
                <td class="col-subject">&nbsp;</td>
                <td class="col-credit"></td>
                <td class="col-grade"></td>
            @endif
            @endfor
        </tr>
        @endfor
        </tbody>
    </table>
    @else
    <p style="text-align:center;color:#999;padding:20px">ยังไม่มีผลการเรียน</p>
    @endif

    {{-- Signature --}}
    <div class="sig-row" style="margin-top:8px">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div>({{ $school['registrar_name'] }})</div>
            <div>{{ $school['registrar_position'] }}</div>
        </div>
    </div>
</div>

{{-- ===== หน้า 2 ===== --}}
<div class="page">
    <div class="doc-meta" style="margin-bottom:8px">
        <span>ปว.1 : พ</span>
        <span>ชุดที่ &nbsp; <span class="meta-val">{{ $docNumber->doc_set ?? '...........' }}</span></span>
        <span>เลขที่ &nbsp; <span class="meta-val">{{ $docNumber->doc_number ?? '...........' }}</span></span>
    </div>

    {{-- Activity Assessment --}}
    <div class="section-title" style="font-size:12px">ผลการประเมินกิจกรรมพัฒนาผู้เรียน</div>
    <table class="p2-table">
        <thead>
            <tr>
                <th rowspan="2" style="width:35%">กิจกรรม</th>
                <th colspan="2">ปีการศึกษา</th>
                <th rowspan="2" style="width:35%">กิจกรรม</th>
                <th colspan="2">ปีการศึกษา</th>
                <th rowspan="2" style="width:35%">กิจกรรม</th>
                <th colspan="2">ปีการศึกษา</th>
            </tr>
            <tr>
                @foreach(range(1,3) as $c)
                <th style="width:6%">เวลา<br>(ชั่วโมง)</th>
                <th style="width:6%">ผลการ<br>ประเมิน</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach(['แนะแนว','ชุมนุม','กิจกรรมเพื่อสังคมและสาธารณประโยชน์'] as $act)
            <tr>
                <td>{{ $act }}</td><td></td><td></td>
                <td>{{ $act }}</td><td></td><td></td>
                <td>{{ $act }}</td><td></td><td></td>
            </tr>
            @endforeach
            <tr>
                <td colspan="9" style="font-size:10px;padding:2px 4px">ภาคเรียนที่ 2</td>
            </tr>
            @foreach(['แนะแนว','ชุมนุม','กิจกรรมเพื่อสังคมและสาธารณประโยชน์'] as $act)
            <tr>
                <td>{{ $act }}</td><td></td><td></td>
                <td>{{ $act }}</td><td></td><td></td>
                <td>{{ $act }}</td><td></td><td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summary --}}
    <table style="width:100%;border-collapse:collapse;font-size:11px;margin-bottom:4px">
        <tr>
            <td style="width:50%;border:1px solid #000;padding:3px 6px;vertical-align:top">
                <strong>สรุปผลการประเมิน</strong><br>
                1. จำนวนหน่วยกิตรายวิชาพื้นฐานที่เรียน _____ ได้ _____ <br>
                &nbsp;&nbsp; จำนวนหน่วยกิตรายวิชาเพิ่มเติมที่เรียน _____ ได้ _____<br>
                2. ผลการประเมินการอ่าน คิดวิเคราะห์และเขียน ได้ _________<br>
                3. ผลการประเมินคุณลักษณะอันพึงประสงค์ ได้ _________<br>
                4. ผลการประเมินกิจกรรมพัฒนาผู้เรียน ได้ _________
            </td>
            <td style="width:50%;border:1px solid #000;padding:3px 6px;vertical-align:top">
                <strong>ผลการตัดสิน</strong>
                <table style="width:100%;font-size:11px">
                    <tr><td>1. ผลการเรียนเฉลี่ยตลอดหลักสูตร</td><td style="text-align:right">_____</td><td>_____</td></tr>
                    <tr><td>2. ผลการเรียนทดสอบทางการศึกษาระดับชั้นพื้นฐาน</td><td style="text-align:right">_____</td><td>_____</td></tr>
                    <tr><td>3. ผลการเรียนเฉลี่ย</td><td style="text-align:right">_____</td><td>_____</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- National Test --}}
    <div class="section-title" style="font-size:12px">ผลการทดสอบระดับชาติ</div>
    <table class="p2-table" style="margin-bottom:4px">
        <tr><th style="width:30%">รายการ</th><th>คะแนน</th><th style="width:30%">รายการ</th><th>คะแนน</th></tr>
        <tr><td>&nbsp;</td><td></td><td>&nbsp;</td><td></td></tr>
        <tr><td>&nbsp;</td><td></td><td>&nbsp;</td><td></td></tr>
    </table>

    {{-- Grade scale --}}
    <div class="section-title" style="font-size:12px">เกณฑ์การประเมินของสถานศึกษา</div>
    <table class="p2-table" style="font-size:11px;margin-bottom:4px">
        <tr>
            <th>คะแนน</th><th>ระดับผลการเรียน</th><th>ความหมาย</th>
            <th>คะแนน</th><th>ระดับผลการเรียน</th><th>ความหมาย</th>
        </tr>
        <tr><td>80-100</td><td style="text-align:center">4</td><td>ดีเยี่ยม</td><td>60-64</td><td style="text-align:center">2</td><td>ปานกลาง</td></tr>
        <tr><td>75-79</td><td style="text-align:center">3.5</td><td>ดีมาก</td><td>55-59</td><td style="text-align:center">1.5</td><td>พอใช้</td></tr>
        <tr><td>70-74</td><td style="text-align:center">3</td><td>ดี</td><td>50-54</td><td style="text-align:center">1</td><td>ผ่านเกณฑ์ขั้นต่ำ</td></tr>
        <tr><td>65-69</td><td style="text-align:center">2.5</td><td>ค่อนข้างดี</td><td>0-49</td><td style="text-align:center">0</td><td>ต่ำกว่าเกณฑ์</td></tr>
    </table>

    {{-- Signatures --}}
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
