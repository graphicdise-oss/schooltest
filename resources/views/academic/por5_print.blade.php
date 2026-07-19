<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ปพ.5 — {{ $assign->subject->name_th }} {{ $section->level->name ?? '' }}/{{ $section->section_number }}</title>
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

.doc-top { display:flex; align-items:center; justify-content:center; gap:5mm; position:relative; margin-bottom:6px; }
.doc-logo { width:16mm; height:16mm; flex-shrink:0; }
.doc-logo img { width:100%; height:100%; object-fit:contain; }
.doc-tag { position:absolute; right:0; top:0; font-weight:700; }
.doc-title { text-align:center; margin-bottom:8px; }
.doc-title h2 { font-size:19px; font-weight:700; }
.doc-title p { font-size:15px; }

.meta-table { width:100%; border-collapse:collapse; font-size:15px; margin-bottom:8px; }
.meta-table td { padding:2px 4px; }
.meta-table .lbl { font-weight:700; white-space:nowrap; width:70px; }
.meta-table .val { border-bottom:0.5px solid #999; padding-left:6px; }

.grade-table { width:100%; border-collapse:collapse; font-size:13px; margin-bottom:10px; }
.grade-table th, .grade-table td { border:1px solid #000; padding:4px 3px; text-align:center; }
.grade-table th { font-weight:700; background:#f5f5f5; }

.quality-row { display:flex; gap:6px; margin-bottom:12px; }
.quality-box { flex:1; border:1px solid #000; }
.quality-box .qh { text-align:center; font-weight:700; font-size:13px; padding:5px; border-bottom:1px solid #000; background:#f5f5f5; }
.quality-box table { width:100%; border-collapse:collapse; font-size:12.5px; }
.quality-box th, .quality-box td { border:1px solid #000; padding:3px; text-align:center; }
.quality-box th { font-weight:700; }

.sign-section { margin-top:auto; font-size:14px; }
.sign-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px 20px; }
.sign-item { text-align:center; }
.sign-line { margin:22px 0 4px; }
.sign-approve { display:flex; align-items:center; justify-content:center; gap:16px; margin:8px 0; }

/* ตารางเช็คชื่อ */
.att-title { text-align:center; font-weight:700; font-size:16px; margin-bottom:8px; }
.att-table { width:100%; border-collapse:collapse; font-size:9px; table-layout:fixed; }
.att-table th, .att-table td { border:1px solid #666; text-align:center; padding:2px 1px; }
.att-table th { background:#f5f5f5; font-weight:700; }
.att-table .col-no { width:22px; }
.att-table .col-code { width:38px; }
.att-table .col-name { width:110px; text-align:left !important; padding-left:3px; }
.att-mark { color:#c62828; font-weight:700; }

/* ตารางสถิติ/คะแนน */
.stat-table, .score-table { width:100%; border-collapse:collapse; font-size:11px; }
.stat-table th, .stat-table td, .score-table th, .score-table td { border:1px solid #666; text-align:center; padding:4px 3px; }
.stat-table th, .score-table th { background:#f5f5f5; font-weight:700; }
.stat-table .col-name, .score-table .col-name { text-align:left !important; padding-left:5px; }
</style>
</head>
<body>

@php
    $thMonths = [1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',
                 7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.'];
    $gradeBuckets = ['4','3.5','3','2.5','2','1.5','1','0'];
    $specialBuckets = ['ร','มส','มก','ผ','มผ','อื่นๆ'];
    $qLabels = ['ดีเยี่ยม (3)' => 'ดีเยี่ยม', 'ดี (2)' => 'ดี', 'ผ่าน (1)' => 'ผ่าน'];
    $studentFullName = fn($stu) => trim(($stu->thai_prefix ?? '').($stu->thai_firstname ?? '').' '.($stu->thai_lastname ?? ''));
@endphp

{{-- ===================== หน้าที่ 1: ปก + สรุปผล ===================== --}}
<div class="page">
    <div class="doc-top">
        <div class="doc-logo"><img src="{{ asset(config('school.logo')) }}" alt="logo" onerror="this.style.display='none'"></div>
        <span class="doc-tag">ปพ.5</span>
    </div>
    <div class="doc-title">
        <h2>แบบบันทึกผลการพัฒนาคุณภาพผู้เรียน</h2>
        <p>{{ config('school.name') }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td class="lbl">ปีการศึกษา</td><td class="val">{{ $semester->semester_name }}/{{ $semester->academicYear->year_name ?? '' }}</td>
            <td class="lbl" style="width:60px;">ชั้นปี</td><td class="val">{{ $section->level->name ?? '' }}/{{ $section->section_number }}</td>
        </tr>
        <tr>
            <td class="lbl">วิชา</td><td class="val" colspan="3">{{ $assign->subject->code }} {{ $assign->subject->name_th }}</td>
        </tr>
        <tr>
            <td class="lbl">ครูผู้สอน</td><td class="val" colspan="3">{{ $assign->personnel->thai_prefix ?? '' }}{{ $assign->personnel->thai_firstname ?? '' }} {{ $assign->personnel->thai_lastname ?? '' }}</td>
        </tr>
    </table>

    <table class="grade-table">
        <tr>
            <th rowspan="3" style="width:60px;">จำนวน<br>นักเรียน</th>
            <th colspan="{{ count($gradeBuckets) }}">ระดับผลการเรียน</th>
            <th colspan="{{ count($specialBuckets) }}">ผลการประเมิน</th>
        </tr>
        <tr>
            @foreach($gradeBuckets as $b)<th>{{ $b }}</th>@endforeach
            @foreach($specialBuckets as $b)<th>{{ $b }}</th>@endforeach
        </tr>
        <tr><td colspan="{{ count($gradeBuckets)+count($specialBuckets) }}"></td></tr>
        <tr>
            <td rowspan="2">{{ $totalStudents }}</td>
            @foreach($gradeBuckets as $b)<td>{{ $gradeCount[$b] ?: '-' }}</td>@endforeach
            @foreach($specialBuckets as $b)<td>{{ $specialCount[$b] ?: '-' }}</td>@endforeach
        </tr>
        <tr>
            @foreach($gradeBuckets as $b)<td>{{ $gradeCount[$b] ? number_format($gradePct[$b],2) : '-' }}</td>@endforeach
            @foreach($specialBuckets as $b)<td>-</td>@endforeach
        </tr>
    </table>
    <p style="font-size:11px; text-align:right; margin-top:-6px; margin-bottom:10px;">ร้อยละ</p>

    <div class="quality-row">
        @foreach($qualitySummary as $q)
        <div class="quality-box">
            <div class="qh">ผลการประเมิน<br>{{ $q['label'] }}</div>
            <table>
                <tr><th>ระดับคุณภาพ</th><th>จำนวน</th><th>ร้อยละ</th></tr>
                @foreach($qLabels as $displayLabel => $key)
                <tr>
                    <td style="text-align:left; padding-left:4px;">{{ $displayLabel }}</td>
                    <td>{{ $q['counts'][$key] ?: '-' }}</td>
                    <td>{{ $q['counts'][$key] ? number_format($q['pct'][$key],2) : '-' }}</td>
                </tr>
                @endforeach
            </table>
        </div>
        @endforeach
    </div>

    <div class="sign-section">
        <p style="margin-bottom:6px;">การอนุมัติผลการเรียน</p>
        <div class="sign-grid">
            <div class="sign-item">
                <div class="sign-line">ลงชื่อ ........................................................</div>
                <div>( {{ $assign->personnel->thai_prefix ?? '' }}{{ $assign->personnel->thai_firstname ?? '' }} {{ $assign->personnel->thai_lastname ?? '' }} )</div>
                <div>ครูผู้สอน</div>
            </div>
            <div class="sign-item">
                <div class="sign-line">ลงชื่อ ........................................................</div>
                <div>( ................................................ )</div>
                <div>หัวหน้ากลุ่มสาระการเรียนรู้</div>
            </div>
            <div class="sign-item">
                <div class="sign-approve">
                    <label>☐ อนุมัติ</label><label>☐ ไม่อนุมัติ</label>
                </div>
                <div class="sign-line">ลงชื่อ ........................................................</div>
                <div>( {{ $school['director_name'] ?? '' }} )</div>
                <div>ผู้อำนวยการโรงเรียน</div>
            </div>
            <div class="sign-item">
                <div class="sign-line">วันที่ ............ เดือน .................... พ.ศ. ..............</div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== หน้าเช็คชื่อ + สถิติ (ต่อ 45 คน) ===================== --}}
@foreach($studentChunks as $chunk)
    @php $dateChunks = collect($classDates)->chunk(15)->values(); @endphp

    @foreach($dateChunks as $dchunk)
        @php
            $monthGroups = collect($dchunk)->groupBy(fn($d) => $d->format('Y-m'))
                ->map(fn($g, $k) => ['label' => $thMonths[(int) substr($k, 5, 2)] . ' ' . (substr($k, 0, 4) + 543), 'count' => $g->count()]);
        @endphp
        <div class="page">
            <div class="att-title">บันทึกเวลาเรียน วิชา{{ $assign->subject->name_th }} ปีการศึกษา {{ $semester->academicYear->year_name ?? '' }} ภาคเรียนที่ {{ $semester->semester_name }} ชั้น {{ $section->level->name ?? '' }}/{{ $section->section_number }}</div>
            <table class="att-table">
                <thead>
                    <tr>
                        <th class="col-no" rowspan="3">เลขที่</th>
                        <th class="col-code" rowspan="3">รหัส</th>
                        <th class="col-name" rowspan="3">ชื่อ - สกุล</th>
                        @foreach($monthGroups as $mg)
                            <th colspan="{{ $mg['count'] }}">{{ $mg['label'] }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($dchunk as $d)<th>{{ $d->format('d/m') }}</th>@endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($chunk as $s)
                        <tr>
                            <td>{{ $s->student_number }}</td>
                            <td>{{ $s->student->student_code }}</td>
                            <td class="col-name">{{ $studentFullName($s->student) }}</td>
                            @foreach($dchunk as $d)
                                @php
                                    $rec = ($attendance->get($s->student_id) ?? collect())->get($d->format('Y-m-d'));
                                    $mark = match($rec->status ?? null) {
                                        'มา' => '✓', 'ป่วย' => 'ป', 'ลา' => 'ล', 'ขาด' => 'ข', default => '',
                                    };
                                @endphp
                                <td class="att-mark">{{ $mark }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="page">
        <div class="att-title">สถิติการเข้าเรียน วิชา{{ $assign->subject->name_th }} ชั้น {{ $section->level->name ?? '' }}/{{ $section->section_number }}</div>
        <table class="stat-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width:45px;">เลขที่</th>
                    <th rowspan="2" style="width:70px;">รหัส</th>
                    <th rowspan="2" class="col-name">ชื่อ - สกุล</th>
                    <th colspan="5">สถิติการมาเรียน</th>
                </tr>
                <tr>
                    <th>มาเรียน</th><th>ป่วย</th><th>ลา</th><th>ขาด</th><th>ร้อยละที่มาเรียน</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chunk as $s)
                    @php $st = $attendanceStats->firstWhere('student_number', $s->student_number); @endphp
                    <tr>
                        <td>{{ $s->student_number }}</td>
                        <td>{{ $s->student->student_code }}</td>
                        <td class="col-name">{{ $studentFullName($s->student) }}</td>
                        <td>{{ $st->present ?? 0 }}</td>
                        <td>{{ $st->sick ?? 0 }}</td>
                        <td>{{ $st->leave ?? 0 }}</td>
                        <td>{{ $st->absent ?? 0 }}</td>
                        <td>{{ number_format($st->pct ?? 0, 2) }} %</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach

{{-- ===================== หน้าคะแนนเก็บ (ต่อ 45 คน) ===================== --}}
@if($categories->isNotEmpty())
@foreach($studentChunks as $chunk)
    @php $catChunks = $categories->chunk(9)->values(); @endphp
    @foreach($catChunks as $catChunk)
        <div class="page">
            <div class="att-title">คะแนนเก็บ วิชา{{ $assign->subject->name_th }} ชั้น {{ $section->level->name ?? '' }}/{{ $section->section_number }}</div>
            <table class="score-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:45px;">เลขที่</th>
                        <th rowspan="2" style="width:70px;">รหัส</th>
                        <th rowspan="2" class="col-name">ชื่อ - สกุล</th>
                        @foreach($catChunk as $cat)<th colspan="1">{{ $cat->name }} ({{ (float) $cat->max_score }})</th>@endforeach
                        <th rowspan="2">รวม</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chunk as $s)
                        @php
                            $rowScores = $scores->get($s->student_id) ?? collect();
                            $sum = 0;
                        @endphp
                        <tr>
                            <td>{{ $s->student_number }}</td>
                            <td>{{ $s->student->student_code }}</td>
                            <td class="col-name">{{ $studentFullName($s->student) }}</td>
                            @foreach($catChunk as $cat)
                                @php
                                    $val = $rowScores->get($cat->category_id)?->score;
                                    if ($val !== null) $sum += (float) $val;
                                @endphp
                                <td>{{ $val !== null ? (float) $val : '' }}</td>
                            @endforeach
                            <td>{{ $sum ?: '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
@endforeach
@endif

</body>
</html>
