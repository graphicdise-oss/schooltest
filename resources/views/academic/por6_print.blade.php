<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ปพ.6 — {{ $section->level->name ?? '' }}/{{ $section->section_number }}</title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'TH Sarabun New','Sarabun','Tahoma',sans-serif; font-size:15px; color:#222; background:#e0e0e0; }
.page {
    width:210mm; min-height:297mm; margin:0 auto 10mm; padding:10mm 12mm;
    background:#fff; box-shadow:0 0 8px rgba(0,0,0,.15);
    page-break-after:always; display:flex; flex-direction:column;
}
.page:last-child { page-break-after:auto; }
@media print { body{background:#fff;} .page{margin:0; box-shadow:none;} }

.doc-top { display:flex; align-items:center; gap:5mm; margin-bottom:4px; }
.doc-logo { width:16mm; height:16mm; flex-shrink:0; }
.doc-logo img { width:100%; height:100%; object-fit:contain; }
.doc-title { flex:1; text-align:center; }
.doc-title h2 { font-size:18px; font-weight:700; }
.doc-title p { font-size:14px; }

.meta-row { display:flex; gap:14px; font-size:14px; margin:8px 0 10px; flex-wrap:wrap; }
.meta-row b { font-weight:700; }

.subj-table { width:100%; border-collapse:collapse; font-size:13px; margin-bottom:10px; }
.subj-table th, .subj-table td { border:1px solid #000; padding:3px 5px; }
.subj-table th { text-align:center; font-weight:700; background:#f5f5f5; }
.subj-table td.c { text-align:center; }
.subj-table td.l { text-align:left; }
.subj-table tbody tr { height:18px; }

.bottom-row { display:flex; gap:14px; margin-top:auto; }
.summary-box { flex:1.1; border:1px solid #000; font-size:13.5px; }
.summary-box .head { text-align:center; font-weight:700; padding:5px; border-bottom:1px solid #000; background:#f5f5f5; }
.summary-box table { width:100%; border-collapse:collapse; }
.summary-box td { padding:4px 8px; border-top:1px solid #ccc; }
.summary-box td.v { text-align:center; width:70px; font-weight:700; }

.sign-box { flex:1; font-size:14px; }
.sign-item { margin-bottom:14px; }
.sign-line { margin-bottom:4px; }
</style>
</head>
<body>

@foreach($reportData as $data)
@php
    $stu = $data->student;
    $ss  = $data->studentSection;
@endphp
<div class="page">
    <div class="doc-top">
        <div class="doc-logo"><img src="{{ asset(config('school.logo')) }}" alt="logo" onerror="this.style.display='none'"></div>
        <div class="doc-title">
            <h2>แบบรายงานผลพัฒนาคุณภาพผู้เรียนรายบุคคล</h2>
            <p>{{ $section->level->name ?? '' }} ภาคเรียนที่ {{ $semester->semester_name }} ปีการศึกษา {{ $semester->academicYear->year_name ?? '' }}</p>
            <p>{{ config('school.education_area') }} จังหวัด {{ config('school.changwat') }}</p>
        </div>
        <div style="width:16mm;"></div>
    </div>

    <div class="meta-row">
        <span><b>รหัสประจำตัวนักเรียน:</b> {{ $stu->student_code }}</span>
        <span><b>ชื่อ-นามสกุล:</b> {{ $stu->thai_prefix }}{{ $stu->thai_firstname }} {{ $stu->thai_lastname }}</span>
        <span><b>ห้อง:</b> {{ $section->level->name ?? '' }}/{{ $section->section_number }}</span>
        <span><b>เลขที่:</b> {{ $ss->student_number }}</span>
    </div>

    <table class="subj-table">
        <thead>
            <tr>
                <th style="width:35px;">ลำดับ</th>
                <th style="width:70px;">รหัสวิชา</th>
                <th>รายวิชา</th>
                <th style="width:70px;">ประเภท</th>
                <th style="width:55px;">น้ำหนัก<br>หน่วยกิต</th>
                <th style="width:60px;">ระดับผล<br>การเรียน</th>
                <th style="width:90px;">หมายเหตุ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data->rows as $i => $r)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td class="c">{{ $r->code }}</td>
                    <td class="l">{{ $r->name }}</td>
                    <td class="c">{{ $r->type }}</td>
                    <td class="c">{{ $r->is_activity ? '-' : number_format($r->credits, 1) }}</td>
                    <td class="c">{{ $r->grade }}</td>
                    <td class="c"></td>
                </tr>
            @endforeach
            @for($i = $data->rows->count(); $i < max(10, $data->rows->count()); $i++)
                <tr><td class="c"></td><td class="c"></td><td class="l"></td><td class="c"></td><td class="c"></td><td class="c"></td><td class="c"></td></tr>
            @endfor
        </tbody>
    </table>

    <div class="bottom-row">
        <div class="summary-box">
            <div class="head">สรุปผลการประเมิน</div>
            <table>
                <tr><td>จำนวนหน่วยกิต/น้ำหนักวิชาพื้นฐาน</td><td class="v">{{ number_format($data->basicCredits, 1) }}</td></tr>
                <tr><td>จำนวนหน่วยกิต/น้ำหนักวิชาเพิ่มเติม</td><td class="v">{{ number_format($data->extraCredits, 1) }}</td></tr>
                <tr><td>รวมหน่วยกิต/น้ำหนัก</td><td class="v">{{ number_format($data->totalCredits, 1) }}</td></tr>
                <tr><td>ระดับผลการเรียนเฉลี่ย</td><td class="v">{{ number_format($data->gpa, 2) }}</td></tr>
            </table>
            <div class="head" style="border-top:1px solid #000;">การประเมินคุณลักษณะ</div>
            <table>
                <tr><td>คุณลักษณะอันพึงประสงค์ของสถานศึกษา</td><td class="v">{{ $data->assessment->desired_char ?? '-' }}</td></tr>
                <tr><td>การอ่าน คิด วิเคราะห์และเขียน</td><td class="v">{{ $data->assessment->reading_thinking ?? '-' }}</td></tr>
                <tr><td>กิจกรรมพัฒนาผู้เรียน</td><td class="v">{{ $data->assessment->activity ?? '-' }}</td></tr>
            </table>
        </div>

        <div class="sign-box">
            <div class="sign-item">
                <div class="sign-line">ลงชื่อ ........................................................</div>
                <div style="text-align:center;">( {{ $section->homeroomTeacher->thai_prefix ?? '' }}{{ $section->homeroomTeacher->thai_firstname ?? '..............................' }} {{ $section->homeroomTeacher->thai_lastname ?? '' }} )</div>
                <div style="text-align:center;">ครูที่ปรึกษา/ครูประจำชั้น</div>
            </div>
            <div class="sign-item">
                <div class="sign-line">ลงชื่อ ........................................................</div>
                <div style="text-align:center;">( {{ $school['director_name'] ?? '' }} )</div>
                <div style="text-align:center;">ผู้อำนวยการ</div>
            </div>
            <div class="sign-item">
                <div class="sign-line">ลงชื่อ ........................................................</div>
                <div style="text-align:center;">( .......................................... )</div>
                <div style="text-align:center;">ผู้ปกครอง</div>
            </div>
        </div>
    </div>
</div>
@endforeach

</body>
</html>
