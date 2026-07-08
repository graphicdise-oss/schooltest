@extends('layouts.sidebar')

@section('content')
@php
    // แปลงวันที่เป็นรูปแบบไทย (พ.ศ.)
    $thMonths = [1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',
                 7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.'];
    $thDate = function ($d) use ($thMonths) {
        if (!$d) return '—';
        return $d->day . ' ' . $thMonths[$d->month] . ' ' . ($d->year + 543);
    };
@endphp

<div style="padding:24px; max-width:1200px; margin:0 auto;">

    {{-- ===== หัวข้อ ===== --}}
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <div>
            <h2 style="color:#082b75; font-weight:700; font-size:26px; margin:0;">
                <i class="fa-solid fa-gauge-high"></i> แดชบอร์ดภาพรวมระบบ
            </h2>
            <p style="color:#4b6aa5; margin:4px 0 0;">รายงานผู้บริหาร — ข้อมูลสรุป ณ ปัจจุบัน</p>
        </div>
        <div style="background:#fff; border-radius:12px; padding:10px 18px; box-shadow:0 2px 8px rgba(8,43,117,.08); text-align:right;">
            <div style="color:#4b6aa5; font-size:13px;">ปีการศึกษา / ภาคเรียนปัจจุบัน</div>
            <div style="color:#082b75; font-weight:700; font-size:17px;">
                {{ $currentYear->year_name ?? 'ยังไม่ตั้งค่า' }}
                @if($currentSemester) · ภาคเรียน {{ $currentSemester->semester_name }} @endif
            </div>
        </div>
    </div>

    {{-- ===== การ์ดสรุปหลัก 4 ใบ ===== --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-bottom:20px;">

        @php
            $cards = [
                ['icon'=>'fa-user-graduate','bg'=>'#2563eb','label'=>'นักเรียนที่กำลังศึกษา','value'=>number_format($studentsStudying),'unit'=>'คน'],
                ['icon'=>'fa-user-tie','bg'=>'#059669','label'=>'บุคลากรที่กำลังปฏิบัติงาน','value'=>number_format($personnelWorking),'unit'=>'คน'],
                ['icon'=>'fa-door-open','bg'=>'#d97706','label'=>'ห้องเรียน (ภาคเรียนนี้)','value'=>number_format($overview['sections_total']),'unit'=>'ห้อง'],
                ['icon'=>'fa-user-clock','bg'=>'#dc2626','label'=>'นักเรียนที่ยังไม่เปิดเทอม','value'=>number_format($studentsNotOpened),'unit'=>'คน'],
            ];
        @endphp

        @foreach($cards as $c)
        <div style="background:#fff; border-radius:14px; padding:18px 20px; box-shadow:0 2px 10px rgba(8,43,117,.08); display:flex; align-items:center; gap:16px;">
            <div style="width:54px; height:54px; border-radius:12px; background:{{ $c['bg'] }}; color:#fff; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0;">
                <i class="fa-solid {{ $c['icon'] }}"></i>
            </div>
            <div style="min-width:0;">
                <div style="color:#6b7a99; font-size:13px; line-height:1.3;">{{ $c['label'] }}</div>
                <div style="color:#082b75; font-weight:700; font-size:26px; line-height:1.1;">
                    {{ $c['value'] }} <span style="font-size:14px; font-weight:500; color:#94a3b8;">{{ $c['unit'] }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ===== แถวข้อมูลภาคเรียน + ความประพฤติ/วันหยุด ===== --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:16px; margin-bottom:20px;">

        {{-- ข้อมูลภาคเรียนปัจจุบัน --}}
        <div style="background:#fff; border-radius:14px; padding:20px; box-shadow:0 2px 10px rgba(8,43,117,.08);">
            <h3 style="color:#082b75; font-weight:700; font-size:17px; margin:0 0 14px;">
                <i class="fa-solid fa-calendar-days" style="color:#4b7ce3;"></i> ข้อมูลภาคเรียนปัจจุบัน
            </h3>
            @php
                $infoRows = [
                    ['ปีการศึกษา', $currentYear->year_name ?? '—'],
                    ['ภาคการศึกษา', $currentSemester->semester_name ?? '—'],
                    ['วันเริ่มต้นภาคเรียน', $thDate($semStart)],
                    ['วันสิ้นสุดภาคเรียน', $thDate($semEnd)],
                    ['จำนวนวันคงเหลือ', $daysLeft !== null ? number_format($daysLeft).' วัน' : '—'],
                ];
            @endphp
            @foreach($infoRows as $row)
            <div style="display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px dashed #e6ebf5;">
                <span style="color:#6b7a99;">{{ $row[0] }}</span>
                <span style="color:#082b75; font-weight:600;">{{ $row[1] }}</span>
            </div>
            @endforeach
        </div>

        {{-- ความประพฤติ + วันหยุด --}}
        <div style="background:#fff; border-radius:14px; padding:20px; box-shadow:0 2px 10px rgba(8,43,117,.08);">
            <h3 style="color:#082b75; font-weight:700; font-size:17px; margin:0 0 14px;">
                <i class="fa-solid fa-clipboard-check" style="color:#4b7ce3;"></i> เกณฑ์ &amp; วันหยุด
            </h3>
            <div style="display:flex; gap:12px; margin-bottom:14px;">
                <div style="flex:1; background:#eef4ff; border-radius:12px; padding:14px; text-align:center;">
                    <div style="color:#6b7a99; font-size:13px;">คะแนนเต็มความประพฤติ</div>
                    <div style="color:#2563eb; font-weight:700; font-size:24px;">{{ number_format($conductFullScore) }}</div>
                </div>
                <div style="flex:1; background:#fff4ec; border-radius:12px; padding:14px; text-align:center;">
                    <div style="color:#6b7a99; font-size:13px;">วันหยุดทั้งปีการศึกษา</div>
                    <div style="color:#d97706; font-weight:700; font-size:24px;">{{ number_format($holidayDays) }} <span style="font-size:13px; font-weight:500;">วัน</span></div>
                </div>
            </div>
            @if($holidays->isEmpty())
                <p style="color:#94a3b8; font-size:13px; margin:0;">
                    <i class="fa-regular fa-circle-question"></i>
                    ยังไม่มีวันหยุดในปีการศึกษานี้ — เพิ่มได้ที่เมนู
                    <a href="{{ route('holidays.index') }}" style="color:#4b7ce3;">ตั้งค่าวันหยุด</a>
                </p>
            @endif
        </div>
    </div>

    {{-- ===== ปฏิทินวันหยุด ===== --}}
    <div style="background:#fff; border-radius:14px; padding:20px; box-shadow:0 2px 10px rgba(8,43,117,.08); margin-bottom:20px;"
         x-data="{
            holidays: @js($holidayMap),
            view: new Date({{ $calYear }}, {{ $calMonth }} - 1, 1),
            months: ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'],
            dows: ['อา','จ','อ','พ','พฤ','ศ','ส'],
            get y() { return this.view.getFullYear(); },
            get m() { return this.view.getMonth(); },
            get label() { return this.months[this.m] + ' ' + (this.y + 543); },
            get todayKey() {
                let t = new Date();
                return t.getFullYear() + '-' + String(t.getMonth()+1).padStart(2,'0') + '-' + String(t.getDate()).padStart(2,'0');
            },
            get cells() {
                let start = new Date(this.y, this.m, 1).getDay();
                let total = new Date(this.y, this.m + 1, 0).getDate();
                let out = [];
                for (let i = 0; i < start; i++) out.push(null);
                for (let d = 1; d <= total; d++) {
                    let key = this.y + '-' + String(this.m+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
                    out.push({ d: d, key: key, name: this.holidays[key] || null });
                }
                return out;
            },
            get monthHolidays() {
                let pre = this.y + '-' + String(this.m+1).padStart(2,'0');
                return Object.keys(this.holidays).filter(k => k.indexOf(pre) === 0).sort()
                    .map(k => ({ day: parseInt(k.slice(8)), name: this.holidays[k] }));
            },
            prev() { this.view = new Date(this.y, this.m - 1, 1); },
            next() { this.view = new Date(this.y, this.m + 1, 1); }
         }">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
            <h3 style="color:#082b75; font-weight:700; font-size:17px; margin:0;">
                <i class="fa-solid fa-calendar-day" style="color:#4b7ce3;"></i> ปฏิทินวันหยุด
            </h3>
            <div style="display:flex; align-items:center; gap:10px;">
                <button type="button" @click="prev()" style="border:none; background:#eef4ff; color:#2563eb; width:34px; height:34px; border-radius:8px; cursor:pointer;">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <span x-text="label" style="min-width:150px; text-align:center; color:#082b75; font-weight:600;"></span>
                <button type="button" @click="next()" style="border:none; background:#eef4ff; color:#2563eb; width:34px; height:34px; border-radius:8px; cursor:pointer;">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:6px;">
            <template x-for="(dw, i) in dows" :key="'dw'+i">
                <div style="text-align:center; font-size:13px; font-weight:600; color:#94a3b8; padding:4px 0;" x-text="dw"></div>
            </template>
            <template x-for="(c, i) in cells" :key="'c'+i">
                <div>
                    <template x-if="c">
                        <div :title="c.name || ''"
                             :style="'position:relative; text-align:center; padding:9px 0; border-radius:8px; font-size:14px; ' +
                                     (c.name ? 'background:#fff1e6; color:#d97706; font-weight:700;' :
                                      (c.key === todayKey ? 'background:#eef4ff; color:#2563eb; font-weight:700;' : 'color:#334155;'))">
                            <span x-text="c.d"></span>
                            <span x-show="c.name" style="position:absolute; bottom:4px; left:50%; transform:translateX(-50%); width:5px; height:5px; border-radius:50%; background:#d97706;"></span>
                        </div>
                    </template>
                    <template x-if="!c"><div></div></template>
                </div>
            </template>
        </div>

        <div style="margin-top:14px; border-top:1px dashed #e6ebf5; padding-top:12px;">
            <template x-if="monthHolidays.length">
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    <template x-for="(h, i) in monthHolidays" :key="'mh'+i">
                        <span style="background:#fff1e6; color:#b45309; border-radius:20px; padding:4px 12px; font-size:13px;">
                            <span x-text="h.day"></span> <span x-text="label.split(' ')[0]"></span> · <span x-text="h.name"></span>
                        </span>
                    </template>
                </div>
            </template>
            <template x-if="!monthHolidays.length">
                <p style="color:#94a3b8; font-size:13px; margin:0;">ไม่มีวันหยุดในเดือนนี้</p>
            </template>
        </div>
    </div>

    {{-- ===== นักเรียนแยกตามระดับชั้น ===== --}}
    <div style="background:#fff; border-radius:14px; padding:20px; box-shadow:0 2px 10px rgba(8,43,117,.08); margin-bottom:20px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
            <h3 style="color:#082b75; font-weight:700; font-size:17px; margin:0;">
                <i class="fa-solid fa-layer-group" style="color:#4b7ce3;"></i> นักเรียนแยกตามระดับชั้น
            </h3>
            <span style="color:#6b7a99; font-size:14px;">รวม {{ number_format($enrolledTotal) }} คน</span>
        </div>

        @if($studentsByLevel->isEmpty())
            <p style="color:#94a3b8; margin:8px 0;">
                <i class="fa-regular fa-folder-open"></i>
                ยังไม่มีข้อมูลนักเรียนที่จัดเข้าห้องในภาคเรียนปัจจุบัน
            </p>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:520px;">
                    <thead>
                        <tr style="background:#f2f6ff; color:#082b75;">
                            <th style="text-align:left; padding:10px 12px; border-radius:8px 0 0 8px;">ระดับชั้น</th>
                            <th style="text-align:center; padding:10px 12px;">ชาย</th>
                            <th style="text-align:center; padding:10px 12px;">หญิง</th>
                            <th style="text-align:center; padding:10px 12px;">รวม</th>
                            <th style="text-align:left; padding:10px 12px; border-radius:0 8px 8px 0; width:34%;">สัดส่วน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentsByLevel as $lv)
                        @php $pct = $enrolledTotal > 0 ? round($lv->total / $enrolledTotal * 100) : 0; @endphp
                        <tr style="border-bottom:1px solid #eef1f7;">
                            <td style="padding:10px 12px; color:#082b75; font-weight:600;">{{ $lv->level_name }}</td>
                            <td style="padding:10px 12px; text-align:center; color:#2563eb;">{{ number_format($lv->male) }}</td>
                            <td style="padding:10px 12px; text-align:center; color:#db2777;">{{ number_format($lv->female) }}</td>
                            <td style="padding:10px 12px; text-align:center; font-weight:700; color:#082b75;">{{ number_format($lv->total) }}</td>
                            <td style="padding:10px 12px;">
                                <div style="background:#eef1f7; border-radius:6px; height:14px; width:100%; overflow:hidden;">
                                    <div style="background:linear-gradient(90deg,#4b7ce3,#2563eb); height:100%; width:{{ $pct }}%;"></div>
                                </div>
                                <span style="font-size:12px; color:#94a3b8;">{{ $pct }}%</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ===== ภาพรวมระบบ + สรุปเช็คชื่อ/ตัดขาด ===== --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:16px;">

        {{-- ภาพรวมระบบ --}}
        <div style="background:#fff; border-radius:14px; padding:20px; box-shadow:0 2px 10px rgba(8,43,117,.08);">
            <h3 style="color:#082b75; font-weight:700; font-size:17px; margin:0 0 14px;">
                <i class="fa-solid fa-diagram-project" style="color:#4b7ce3;"></i> ภาพรวมระบบ
            </h3>
            @php
                $ov = [
                    ['fa-users','นักเรียนทั้งหมด (ทุกสถานะ)', number_format($overview['students_total']).' คน'],
                    ['fa-user-tie','บุคลากรทั้งหมด', number_format($overview['personnel_total']).' คน'],
                    ['fa-door-open','ห้องเรียนภาคเรียนนี้', number_format($overview['sections_total']).' ห้อง'],
                    ['fa-layer-group','ระดับชั้นทั้งหมด', number_format($overview['levels_total']).' ระดับ'],
                ];
            @endphp
            @foreach($ov as $item)
            <div style="display:flex; align-items:center; gap:12px; padding:9px 0; border-bottom:1px dashed #e6ebf5;">
                <i class="fa-solid {{ $item[0] }}" style="color:#4b7ce3; width:20px; text-align:center;"></i>
                <span style="color:#6b7a99; flex:1;">{{ $item[1] }}</span>
                <span style="color:#082b75; font-weight:600;">{{ $item[2] }}</span>
            </div>
            @endforeach
        </div>

        {{-- สรุปไม่เช็คชื่อ / ตัดขาด --}}
        <div style="background:#fff; border-radius:14px; padding:20px; box-shadow:0 2px 10px rgba(8,43,117,.08);">
            <h3 style="color:#082b75; font-weight:700; font-size:17px; margin:0 0 14px;">
                <i class="fa-solid fa-clipboard-user" style="color:#4b7ce3;"></i> สรุปการเช็คชื่อ / ตัดขาด
            </h3>
            <div style="background:#fff8e6; border:1px solid #ffe2a8; border-radius:12px; padding:16px; color:#8a6d1e;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                ยังไม่ได้เชื่อมระบบเช็คชื่อ/ตัดขาด
                <div style="font-size:13px; margin-top:6px; color:#a08240;">
                    เมื่อมีระบบบันทึกการเช็คชื่อในฐานข้อมูลแล้ว ส่วนนี้จะแสดงจำนวนนักเรียนที่ยังไม่ถูกเช็คชื่อ
                    และนักเรียนที่ถูกตัดขาด (ขาดเรียนเกินเกณฑ์) โดยอัตโนมัติ
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
