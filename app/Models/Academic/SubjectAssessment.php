<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class SubjectAssessment extends Model
{
    protected $table = 'subject_assessments';

    protected $fillable = [
        'assign_id', 'student_id', 'desired_char', 'reading_thinking', 'competency',
        'char_1', 'char_2', 'char_3', 'char_4', 'char_5', 'char_6', 'char_7', 'char_8',
        'read_1', 'read_2', 'read_3', 'read_4', 'read_5',
    ];

    const CHAR_LABELS = [
        1 => 'รักชาติ ศาสน์ กษัตริย์', 2 => 'ซื่อสัตย์สุจริต', 3 => 'มีวินัย', 4 => 'ใฝ่เรียนรู้',
        5 => 'อยู่อย่างพอเพียง', 6 => 'มุ่งมั่นในการทำงาน', 7 => 'รักความเป็นไทย', 8 => 'มีจิตสาธารณะ',
    ];

    const READ_LABELS = [
        1 => 'อ่านเพื่อการศึกษา ค้นคว้า', 2 => 'อ่านจับประเด็นสำคัญ', 3 => 'วิเคราะห์',
        4 => 'ประเมินค่าจากการอ่าน', 5 => 'เขียนแสดงความคิดเห็น',
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

    public function charItems(): array
    {
        return array_map(fn($i) => $this->{"char_{$i}"}, range(1, 8));
    }

    public function readItems(): array
    {
        return array_map(fn($i) => $this->{"read_{$i}"}, range(1, 5));
    }

    // เกณฑ์การประเมินคุณลักษณะอันพึงประสงค์ 8 ข้อ ตามมาตรฐาน สพฐ. (แบ่ง 4 ระดับ: 3 ดีเยี่ยม / 2 ดี / 1 ผ่าน / 0 ไม่ผ่าน)
    // อ้างอิงตัวเลขตรงตามประกาศ สพฐ. เป๊ะๆ (ไม่ใช่สูตรทั่วไป — ใช้ได้กับ 8 ข้อเท่านั้น)
    // คืนค่า null ถ้ายังกรอกไม่ครบทั้ง 8 ข้อ
    public static function computeDesiredCharLevel(array $items): ?int
    {
        $items = array_slice($items, 0, 8);
        if (count($items) < 8 || in_array(null, $items, true)) return null;

        $min = min($items);
        if ($min === 0) return 0; // ระดับ 0: มีข้อใดข้อหนึ่งได้ "ไม่ผ่าน"

        $excellent = count(array_filter($items, fn($v) => $v === 3)); // จำนวนข้อที่ได้ "ดีเยี่ยม (3)"

        // ระดับ 3: ดีเยี่ยม 5-8 ข้อ และไม่มีข้อใดต่ำกว่า "ดี"
        if ($excellent >= 5 && $min >= 2) return 3;

        // ระดับ 2: (ดีเยี่ยม 1-4 ข้อ และไม่มีข้อใดต่ำกว่า "ดี") หรือ (ดีเยี่ยม 4 ข้อขึ้นไป และไม่มีข้อใดต่ำกว่า "ผ่าน")
        if ($excellent >= 1 && $min >= 2) return 2;
        if ($excellent >= 4 && $min >= 1) return 2;

        // ระดับ 1: ทุกข้อได้อย่างน้อย "ผ่าน" ขึ้นไป (ไม่เข้าเงื่อนไขระดับ 2 ข้างต้น)
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
