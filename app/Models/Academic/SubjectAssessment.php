<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class SubjectAssessment extends Model
{
    protected $table = 'subject_assessments';

    protected $fillable = [
        'assign_id', 'student_id', 'desired_char', 'reading_thinking', 'competency',
        'char_units',
        'read_1', 'read_2', 'read_3', 'read_4', 'read_5',
    ];

    protected $casts = [
        'char_units' => 'array',
    ];

    const READ_LABELS = [
        1 => 'อ่านเพื่อการศึกษา ค้นคว้า', 2 => 'อ่านจับประเด็นสำคัญ', 3 => 'วิเคราะห์',
        4 => 'ประเมินค่าจากการอ่าน', 5 => 'เขียนแสดงความคิดเห็น',
    ];

    // เกณฑ์อ้างอิงกว้างๆ ของ "คุณลักษณะอันพึงประสงค์" ตามมาตรฐาน สพฐ. (แสดงเป็นข้อความอ้างอิงท้ายตารางหน่วยที่ ไม่ผูกกับคอลัมน์ใดโดยตรง)
    const CHAR_REFERENCE_LABELS = [
        1 => 'รักชาติ ศาสน์ กษัตริย์', 2 => 'ซื่อสัตย์สุจริต', 3 => 'มีวินัย', 4 => 'ใฝ่เรียนรู้',
        5 => 'อยู่อย่างพอเพียง', 6 => 'มุ่งมั่นในการทำงาน', 7 => 'รักความเป็นไทย', 8 => 'มีจิตสาธารณะ',
    ];

    const LEVEL_LABELS = [3 => 'ดีเยี่ยม', 2 => 'ดี', 1 => 'ผ่าน', 0 => 'ไม่ผ่าน'];

    public function teachingAssign()
    {
        return $this->belongsTo(TeachingAssign::class, 'assign_id', 'assign_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function charItems(int $unitCount): array
    {
        $units = $this->char_units ?? [];
        return array_map(fn($i) => $units[$i - 1] ?? null, range(1, $unitCount));
    }

    public function readItems(): array
    {
        return array_map(fn($i) => $this->{"read_{$i}"}, range(1, 5));
    }

    // เกณฑ์การประเมินคุณลักษณะอันพึงประสงค์ ตามมาตรฐาน สพฐ. (แบ่ง 4 ระดับ: 3 ดีเยี่ยม / 2 ดี / 1 ผ่าน / 0 ไม่ผ่าน)
    // ต้นฉบับของ สพฐ. กำหนดตัวเลขไว้สำหรับ 8 ข้อ (5-8/1-4/4/5-8) — ระบบนี้ประเมินเป็นรายหน่วยการเรียนแทน (จำนวนหน่วยกำหนดได้ต่อวิชา)
    // จึงแปลงเป็นสัดส่วน "เกินครึ่ง" (majority) และ "ครึ่งพอดี" (half) ของจำนวนหน่วยทั้งหมด ซึ่งให้ผลตรงกับเกณฑ์เดิมเป๊ะเมื่อมี 8 หน่วย
    // คืนค่า null ถ้ายังกรอกไม่ครบทุกหน่วย
    public static function computeDesiredCharLevel(array $items): ?int
    {
        $n = count($items);
        if ($n === 0 || in_array(null, $items, true)) return null;

        $min = min($items);
        if ($min === 0) return 0; // ระดับ 0: มีหน่วยใดหน่วยหนึ่งได้ "ไม่ผ่าน"

        $excellent = count(array_filter($items, fn($v) => $v === 3)); // จำนวนหน่วยที่ได้ "ดีเยี่ยม (3)"
        $half = intdiv($n, 2);       // เช่น 8 หน่วย → 4, 10 หน่วย → 5
        $majority = $half + 1;       // เช่น 8 หน่วย → 5, 10 หน่วย → 6

        // ระดับ 3: ดีเยี่ยมเกินครึ่งของหน่วยทั้งหมด และไม่มีหน่วยใดต่ำกว่า "ดี"
        if ($excellent >= $majority && $min >= 2) return 3;

        // ระดับ 2: (ดีเยี่ยมอย่างน้อย 1 หน่วยแต่ไม่ถึงเกณฑ์ระดับ 3 และไม่มีหน่วยใดต่ำกว่า "ดี")
        //          หรือ (ดีเยี่ยมครึ่งพอดี และไม่มีหน่วยใดต่ำกว่า "ผ่าน")
        //          หรือ (ดีเยี่ยมเกินครึ่ง แต่มีบางหน่วยเป็น "ผ่าน" — ไม่เข้าเงื่อนไขระดับ 3)
        if ($excellent >= 1 && $min >= 2) return 2;
        if ($excellent === $half && $min >= 1) return 2;
        if ($excellent >= $majority && $min >= 1) return 2;

        // ระดับ 1: ทุกหน่วยได้อย่างน้อย "ผ่าน" ขึ้นไป (ไม่เข้าเงื่อนไขระดับ 2 ข้างต้น)
        if ($min >= 1) return 1;

        return 0;
    }

    // หมายเหตุ: เกณฑ์ "การอ่านคิดวิเคราะห์และเขียน" ของ สพฐ. เป็นคำบรรยายเชิงคุณภาพ (ดูจากผลงานโดยรวม)
    // ไม่ได้ให้สูตรนับข้อแบบคุณลักษณะ ระบบนี้จึงใช้ "คะแนนต่ำสุดใน 5 ข้อ" เป็นระดับรวม (จุดอ่อนที่สุดเป็นตัวกำหนด)
    // ถ้าโรงเรียนต้องการเกณฑ์อื่น ให้ปรับที่ฟังก์ชันนี้จุดเดียว
    public static function computeReadingThinkingLevel(array $items): ?int
    {
        $items = array_slice($items, 0, 5);
        if (count($items) < 5 || in_array(null, $items, true)) return null;

        return min($items);
    }

    public static function levelLabel(?int $level): string
    {
        if ($level === null) return '';
        return self::LEVEL_LABELS[$level] ?? '';
    }
}
