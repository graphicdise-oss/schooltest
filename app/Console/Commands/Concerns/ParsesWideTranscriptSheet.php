<?php

namespace App\Console\Commands\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * อ่านชีตเดียวที่เป็นฟอร์แมต "3 ปีการศึกษาเคียงกัน" (แบบฟอร์มที่ importTranscriptTemplate()/
 * importBulkTemplate() ของ GradeController สร้างให้) — ใช้ร่วมกันทั้ง import:transcript
 * (ไฟล์มีชีตเดียว) และ import:transcript-bulk (ไฟล์เดียวหลายชีต ชีตละ 1 คน วนเรียกทีละชีต)
 */
trait ParsesWideTranscriptSheet
{
    /**
     * คืนค่า [data, activities, warnings] ของชีตเดียว — ถ้าชีตนี้ไม่มีแถว "ปีการศึกษา" เลย (เช่น
     * เป็นชีตสารบัญ/รายชื่อที่ไม่ใช่ชีตข้อมูล) จะคืน [[], [], []] แบบไม่มีคำเตือน ให้ผู้เรียกตัดสินใจเอง
     * ว่าจะถือเป็น error (ไฟล์เดี่ยวมีชีตเดียว) หรือแค่ข้ามชีตนี้ไปเงียบๆ (ไฟล์ทั้งห้องหลายชีต)
     */
    private function parseTranscriptSheet($sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $get = fn (int $col, int $row) => trim((string) ($sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue() ?? ''));
        $startsWithYear = fn (string $text): bool => str_starts_with(trim($text), 'ปีการศึกษา');

        $warnings = [];

        $subjectHeaderRow = $this->findWideHeaderRow($get, $highestRow, $startsWithYear, 1);
        if ($subjectHeaderRow === null) {
            return [[], [], []];
        }

        $activityLabelRow = null;
        for ($row = $subjectHeaderRow + 1; $row <= $highestRow; $row++) {
            if ($get(1, $row) === 'กิจกรรม' || $get(4, $row) === 'กิจกรรม' || $get(7, $row) === 'กิจกรรม') {
                $activityLabelRow = $row;
                break;
            }
        }
        $subjectEndRow = $activityLabelRow ? $activityLabelRow - 1 : $highestRow;

        $subjectGroups = $this->locateWideGroups($get, $subjectHeaderRow, $startsWithYear, $warnings);
        $data = $this->readWideSubjectRows($get, $subjectGroups, $subjectHeaderRow + 1, $subjectEndRow, $warnings);

        $activities = [];
        if ($activityLabelRow !== null) {
            $activityHeaderRow = $this->findWideHeaderRow($get, $highestRow, $startsWithYear, $activityLabelRow + 1);
            if ($activityHeaderRow !== null) {
                $activityGroups = $this->locateWideGroups($get, $activityHeaderRow, $startsWithYear, $warnings);
                $activities = $this->readWideActivityRows($get, $activityGroups, $activityHeaderRow + 1, $highestRow, $warnings);
            }
        }

        return [$data, $activities, $warnings];
    }

    // หาแถวแรก (นับจาก $fromRow) ที่คอลัมน์ 1, 4 หรือ 7 ขึ้นต้นด้วยคำว่า "ปีการศึกษา"
    private function findWideHeaderRow(callable $get, int $highestRow, callable $startsWithYear, int $fromRow): ?int
    {
        for ($row = $fromRow; $row <= min($fromRow + 20, $highestRow); $row++) {
            if ($startsWithYear($get(1, $row)) || $startsWithYear($get(4, $row)) || $startsWithYear($get(7, $row))) {
                return $row;
            }
        }
        return null;
    }

    // อ่านแถวหัว "ปีการศึกษา XXXX ระดับชั้น" ของทั้ง 3 กลุ่ม (คอลัมน์ 1, 4, 7) ที่แถว $headerRow
    // รองรับ 2 รูปแบบ: (1) ข้อความรวมกันในช่องเดียว เช่น "ปีการศึกษา 2567 ระดับชั้น มัธยมศึกษาปีที่ 4" (ไฟล์จริงจากโรงเรียน)
    // (2) แบบฟอร์มที่ระบบสร้างให้ ซึ่งแยกเป็น 2 แถวคนละช่องกรอก: แถว "ปีการศึกษา" (ปีอยู่ช่องถัดไปในแถวเดียวกัน)
    //     ตามด้วยแถว "ระดับชั้น" (ระดับชั้นอยู่ช่องถัดไปในแถวถัดไป) — ตรวจจากการที่แถวหัวไม่มีตัวเลขปีอยู่ในตัวเอง
    private function locateWideGroups(callable $get, int $headerRow, callable $startsWithYear, array &$warnings): array
    {
        $groups = [];
        for ($g = 0; $g < 3; $g++) {
            $col = 1 + $g * 3;
            $header = $get($col, $headerRow);
            if ($header === '' || !$startsWithYear($header)) {
                continue;
            }
            if (!preg_match('/\d{4}/u', $header)) {
                $yearInput = $get($col + 1, $headerRow);
                $levelInput = $get($col + 1, $headerRow + 1);
                $header = trim("ปีการศึกษา {$yearInput} {$levelInput}");
            }
            [$year, $level] = $this->parseYearLevel($header);
            if (!$year || !$level) {
                $warnings[] = "อ่านปีการศึกษา/ระดับชั้นจากข้อความ \"{$header}\" ไม่ได้ — ข้ามกลุ่มนี้ทั้งกลุ่ม";
                continue;
            }
            $groups[] = ['col' => $col, 'year' => $year, 'level' => $level, 'semester' => '1', 'room' => null];
        }
        return $groups;
    }

    private function readWideSubjectRows(callable $get, array $groups, int $fromRow, int $toRow, array &$warnings): array
    {
        $data = [];

        for ($row = $fromRow; $row <= $toRow; $row++) {
            foreach ($groups as $gi => $grp) {
                $col = $grp['col'];
                $c1 = $get($col, $row);
                $c2 = $get($col + 1, $row);
                $c3 = $get($col + 2, $row);

                if ($c1 === '' && $c2 === '' && $c3 === '') {
                    continue;
                }

                // ต้องขึ้นต้นด้วย "ภาคเรียน" เป๊ะๆ (ไม่ใช่แค่มีคำนี้อยู่ตรงไหนก็ได้) กันข้อความหมายเหตุ/บันทึกที่บังเอิญ
                // พูดถึงคำว่า "ภาคเรียน" ปนตัวเลขอยู่ในประโยค ถูกเข้าใจผิดว่าเป็นแถวสลับภาคเรียนแบบเงียบๆ
                if (str_starts_with($c1, 'ภาคเรียน')) {
                    if (preg_match('/(\d+)/u', $c1, $m)) {
                        $groups[$gi]['semester'] = $m[1];
                    }
                    continue;
                }

                if (str_starts_with($c1, 'ระดับชั้น')) {
                    // แถวช่องกรอกระดับชั้นของแบบฟอร์มที่ระบบสร้างให้ (อ่านไปแล้วตอน locateWideGroups) — ไม่ใช่แถววิชา ข้าม
                    continue;
                }

                // แถวระบุห้องเรียนจริง (ไม่บังคับ) — เว้นว่างได้ถ้าไม่ทราบ ระบบจะลองหาห้องที่นักเรียนเคยถูก
                // จัดไว้เองให้ก่อน รูปแบบ: "ห้อง" (แบบฟอร์มที่ระบบสร้างให้ ห้องอยู่ช่องถัดไป) หรือ
                // "ห้อง 2" / "ห้อง: 2 วิทย์-คณิต" (พิมพ์รวมในช่องเดียว ตามด้วยเลขห้อง + แผนการเรียน ไม่บังคับ)
                if (str_starts_with($c1, 'ห้อง')) {
                    $roomText = trim(preg_replace('/^ห้อง(ที่)?\s*:?\s*/u', '', $c1));
                    if ($roomText === '') {
                        $roomText = $c2;
                    }
                    $groups[$gi]['room'] = $roomText !== '' ? $roomText : null;
                    continue;
                }

                if (!preg_match('/^(\S+)\s*:\s*(.+)$/u', $c1, $m)) {
                    // มีแค่คอลัมน์แรก ไม่มีหน่วยกิต/เกรดเลย — เป็นข้อความอื่น (เช่น แถบคำแนะนำที่ทับเข้ามาในช่วงแถว)
                    // ไม่ใช่ความตั้งใจกรอกวิชา จึงข้ามแบบเงียบๆ ไม่ต้องเตือน
                    if ($c2 !== '' || $c3 !== '') {
                        $warnings[] = "แถว {$row} ({$grp['year']} {$grp['level']}): อ่านชื่อวิชาไม่ได้จากค่า \"{$c1}\" — ข้าม";
                    }
                    continue;
                }
                $code = trim($m[1]);
                $name = trim($m[2]);
                $credit = is_numeric($c2) ? (float) $c2 : 0;

                if (!is_numeric($c3)) {
                    $warnings[] = "แถว {$row} วิชา {$code} {$name}: เกรด \"{$c3}\" ไม่ใช่ตัวเลข (0-4) — ข้าม";
                    continue;
                }
                $grade = (float) $c3;
                if ($grade < 0 || $grade > 4) {
                    $warnings[] = "แถว {$row} วิชา {$code} {$name}: เกรด {$grade} ต้องอยู่ระหว่าง 0-4 — ข้าม";
                    continue;
                }

                $sem = $groups[$gi]['semester'];
                $data[$grp['year']]['level'] = $grp['level'];
                $data[$grp['year']]['semesters'][$sem][] = [$code, $name, $credit, $grade];
                if (!empty($groups[$gi]['room'])) {
                    $data[$grp['year']]['rooms'][$sem] = $groups[$gi]['room'];
                }
            }
        }

        return $data;
    }

    private function readWideActivityRows(callable $get, array $groups, int $fromRow, int $toRow, array &$warnings): array
    {
        $activities = [];

        for ($row = $fromRow; $row <= $toRow; $row++) {
            foreach ($groups as $gi => $grp) {
                $col = $grp['col'];
                $c1 = $get($col, $row);
                $c2 = $get($col + 1, $row);
                $c3 = $get($col + 2, $row);

                if ($c1 === '' && $c2 === '' && $c3 === '') {
                    continue;
                }

                if (str_starts_with($c1, 'ภาคเรียน')) {
                    if (preg_match('/(\d+)/u', $c1, $m)) {
                        $groups[$gi]['semester'] = $m[1];
                    }
                    continue;
                }

                if (str_starts_with($c1, 'ระดับชั้น')) {
                    continue;
                }

                $name = $c1;
                if ($name === '') {
                    $warnings[] = "แถว {$row} ({$grp['year']} {$grp['level']}): ไม่มีชื่อกิจกรรม — ข้าม";
                    continue;
                }
                $hours = is_numeric($c2) ? (float) $c2 : 0;

                $resultRaw = trim($c3);
                if ($resultRaw === '') {
                    $warnings[] = "แถว {$row} กิจกรรม {$name}: ยังไม่มีผลการประเมิน — ข้าม";
                    continue;
                }
                if ($resultRaw === 'มผ' || str_contains($resultRaw, 'ไม่ผ่าน')) {
                    $grade = 'ไม่ผ่าน';
                    $remark = 'ไม่ผ่าน';
                } elseif ($resultRaw === 'ผ' || str_contains($resultRaw, 'ผ่าน')) {
                    $grade = 'ผ่าน';
                    $remark = 'ผ่าน';
                } else {
                    $grade = $resultRaw;
                    $remark = $resultRaw;
                }

                $sem = $groups[$gi]['semester'];
                $activities[$grp['year']]['level'] = $grp['level'];
                $activities[$grp['year']]['semesters'][$sem][] = [$name, $hours, $grade, $remark];
            }
        }

        return $activities;
    }
}
