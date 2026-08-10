<?php

namespace Database\Seeders;

use App\Models\Library\LibraryBook;
use App\Models\Library\LibraryCategory;
use App\Models\Library\LibraryLoan;
use App\Models\Personne\Personnel;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class LibrarySeeder extends Seeder
{
    // ข้อมูลตัวอย่างสำหรับดูหน้าจอห้องสมุด (หมวดหมู่ + หนังสือ + รูปปก + การยืมตัวอย่าง)
    public function run(): void
    {
        $categories = [
            'นวนิยาย', 'การ์ตูน', 'วิทยาศาสตร์', 'ประวัติศาสตร์', 'สารานุกรม', 'ภาษาต่างประเทศ',
        ];

        foreach ($categories as $name) {
            LibraryCategory::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        $books = [
            ['title' => 'ผู้ชนะสิบทิศ', 'author' => 'ยาขอบ', 'category' => 'นวนิยาย', 'copies' => 3, 'cover' => true],
            ['title' => 'สี่แผ่นดิน', 'author' => 'ม.ร.ว.คึกฤทธิ์ ปราโมช', 'category' => 'นวนิยาย', 'copies' => 2, 'cover' => true],
            ['title' => 'คู่กรรม', 'author' => 'ทมยันตี', 'category' => 'นวนิยาย', 'copies' => 4, 'cover' => false],
            ['title' => 'โดเรมอน', 'author' => 'ฟูจิโกะ เอฟ ฟูจิโอะ', 'category' => 'การ์ตูน', 'copies' => 5, 'cover' => true],
            ['title' => 'วันพีซ', 'author' => 'เออิจิโร โอดะ', 'category' => 'การ์ตูน', 'copies' => 6, 'cover' => true],
            ['title' => 'นารูโตะ', 'author' => 'มาซาชิ คิชิโมโตะ', 'category' => 'การ์ตูน', 'copies' => 4, 'cover' => false],
            ['title' => 'ฟิสิกส์เบื้องต้น', 'author' => 'สสวท.', 'category' => 'วิทยาศาสตร์', 'copies' => 3, 'cover' => true],
            ['title' => 'เคมีในชีวิตประจำวัน', 'author' => 'สสวท.', 'category' => 'วิทยาศาสตร์', 'copies' => 2, 'cover' => false],
            ['title' => 'จักรวาลและดวงดาว', 'author' => 'สมาคมดาราศาสตร์ไทย', 'category' => 'วิทยาศาสตร์', 'copies' => 2, 'cover' => true],
            ['title' => 'ประวัติศาสตร์ไทยสมัยรัตนโกสินทร์', 'author' => 'กรมศิลปากร', 'category' => 'ประวัติศาสตร์', 'copies' => 2, 'cover' => false],
            ['title' => 'สงครามโลกครั้งที่สอง', 'author' => 'ชาญวิทย์ เกษตรศิริ', 'category' => 'ประวัติศาสตร์', 'copies' => 2, 'cover' => true],
            ['title' => 'สารานุกรมไทยสำหรับเยาวชน เล่ม 1', 'author' => 'โครงการสารานุกรมไทย', 'category' => 'สารานุกรม', 'copies' => 1, 'cover' => false],
            ['title' => 'สารานุกรมสัตว์โลก', 'author' => 'National Geographic', 'category' => 'สารานุกรม', 'copies' => 2, 'cover' => true],
            ['title' => 'English Grammar in Use', 'author' => 'Raymond Murphy', 'category' => 'ภาษาต่างประเทศ', 'copies' => 3, 'cover' => true],
            ['title' => 'basic Chinese for beginners', 'author' => 'Li Wei', 'category' => 'ภาษาต่างประเทศ', 'copies' => 2, 'cover' => false],
            ['title' => 'สนทนาภาษาญี่ปุ่นพื้นฐาน', 'author' => 'สมาคมนักเรียนไทยในญี่ปุ่น', 'category' => 'ภาษาต่างประเทศ', 'copies' => 2, 'cover' => true],
        ];

        $shelves = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D1'];
        $createdBooks = [];

        foreach ($books as $i => $b) {
            $cover = $b['cover'] ? $this->makeCoverImage($b['title'], $b['category']) : null;

            $createdBooks[] = LibraryBook::firstOrCreate(
                ['title' => $b['title'], 'author' => $b['author']],
                [
                    'category_id'      => LibraryCategory::where('name', $b['category'])->value('id'),
                    'code'             => 'BK-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'publisher'        => 'สำนักพิมพ์ตัวอย่าง',
                    'isbn'             => '978-616-000-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT) . '-0',
                    'total_copies'     => $b['copies'],
                    'available_copies' => $b['copies'],
                    'shelf_location'   => $shelves[$i % count($shelves)],
                    'price'            => rand(120, 450),
                    'cover_image'      => $cover,
                    'is_active'        => true,
                ]
            );
        }

        $this->seedSampleLoans($createdBooks);
    }

    private function seedSampleLoans(array $books): void
    {
        $student = Student::query()->orderBy('student_id')->first();
        $personnel = Personnel::query()->orderBy('personnel_id')->first();

        if (!$student && !$personnel) {
            return; // ไม่มีนักเรียน/บุคลากรในระบบเลย ข้ามการสร้างตัวอย่างการยืม
        }

        $sample = array_slice($books, 0, 3);
        foreach ($sample as $i => $book) {
            if ($book->available_copies < 1) {
                continue;
            }

            $useStudent = $student && ($i % 2 === 0 || !$personnel);

            LibraryLoan::firstOrCreate(
                ['book_id' => $book->id, 'borrower_type' => $useStudent ? 'student' : 'personnel'],
                [
                    'student_id'   => $useStudent ? $student->student_id : null,
                    'personnel_id' => $useStudent ? null : $personnel->personnel_id,
                    'borrowed_at'  => now()->subDays(3 + $i),
                    'due_at'       => now()->addDays(7 - $i),
                    'status'       => 'ยืมอยู่',
                ]
            );

            $book->decrement('available_copies');
        }
    }

    // สร้างรูปปกตัวอย่างง่ายๆ ด้วย GD (พื้นสีไล่เฉด + ชื่อหนังสือ) เพื่อให้เห็นว่าระบบแสดงรูปปกได้จริง
    private function makeCoverImage(string $title, string $category): ?string
    {
        if (!extension_loaded('gd')) {
            return null;
        }

        $colors = [
            'นวนิยาย' => [63, 81, 181], 'การ์ตูน' => [255, 152, 0], 'วิทยาศาสตร์' => [0, 150, 136],
            'ประวัติศาสตร์' => [121, 85, 72], 'สารานุกรม' => [96, 125, 139], 'ภาษาต่างประเทศ' => [233, 30, 99],
        ];
        [$r, $g, $b] = $colors[$category] ?? [90, 90, 90];

        $width = 300;
        $height = 420;
        $img = imagecreatetruecolor($width, $height);
        $base = imagecolorallocate($img, $r, $g, $b);
        $dark = imagecolorallocate($img, (int) ($r * 0.7), (int) ($g * 0.7), (int) ($b * 0.7));
        imagefilledrectangle($img, 0, 0, $width, $height, $base);
        imagefilledrectangle($img, 0, $height - 70, $width, $height, $dark);

        $white = imagecolorallocate($img, 255, 255, 255);
        $font = public_path('fonts/TF Arluck.ttf');

        if (file_exists($font)) {
            $wrapped = $this->wrapThaiText($title, 15);
            $y = 60;
            foreach ($wrapped as $line) {
                imagettftext($img, 16, 0, 20, $y, $white, $font, $line);
                $y += 30;
            }
            imagettftext($img, 12, 0, 20, $height - 30, $white, $font, $category);
        }

        $filename = 'library/covers/' . uniqid('cover_') . '.png';
        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        Storage::disk('public')->put($filename, $data);

        return $filename;
    }

    private function wrapThaiText(string $text, int $maxChars): array
    {
        $lines = [];
        $current = '';
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
            $current .= $ch;
            if (mb_strlen($current) >= $maxChars) {
                $lines[] = $current;
                $current = '';
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }
        return array_slice($lines, 0, 4);
    }
}
