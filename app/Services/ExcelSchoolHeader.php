<?php

namespace App\Services;

use App\Models\SchoolInfoSetting;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * แถบหัวชื่อโรงเรียน + ตราโรงเรียน (แถว 1-4) ใช้ร่วมกันทุกไฟล์ Excel ที่ระบบสร้าง
 * (ทั้งแบบฟอร์มนำเข้าและไฟล์ export) จะได้หน้าตาเหมือนกันทุกที่ แก้ที่เดียวเปลี่ยนหมด
 */
class ExcelSchoolHeader
{
    /**
     * วาดแถบหัว (แถว 1-3) คืนค่า true ถ้ามีโลโก้ (แถว 1-4 จะถูกรวมเป็นช่องสี่เหลี่ยมให้ตราโรงเรียน)
     */
    public static function apply(Worksheet $sheet, int $totalCols, ?string $titleOverride = null): bool
    {
        $info = SchoolInfoSetting::getInstance();
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);

        $sheet->mergeCells("B1:{$lastCol}1");
        $sheet->setCellValue('B1', $titleOverride ?: ($info->school_name ?: 'โรงเรียน'));
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
        ]);
        $sheet->getStyle('B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);

        $hasLogo = $info->logo_path && Storage::disk('public')->exists($info->logo_path);
        if ($hasLogo) {
            // ขยายคอลัมน์ A + รวมแถว 1-4 ให้เป็นช่องสี่เหลี่ยมจัตุรัสคร่าวๆ ไว้ใส่ตราโรงเรียนตัวใหญ่
            // (แนะนำอัปโหลดรูปสี่เหลี่ยมจัตุรัส เช่น 500x500px จะได้ไม่ถูกบีบ/ไม่ต้องครอบตัดเอง)
            $sheet->getColumnDimension('A')->setWidth(18);
            $sheet->getRowDimension(1)->setRowHeight(30);
            $sheet->getRowDimension(2)->setRowHeight(25);
            $sheet->getRowDimension(3)->setRowHeight(25);
            $sheet->getRowDimension(4)->setRowHeight(25);
            $sheet->mergeCells('A1:A4');
            $sheet->getStyle('A1:A4')->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            ]);

            $drawing = new Drawing();
            $drawing->setName('ตราโรงเรียน');
            $drawing->setPath(Storage::disk('public')->path($info->logo_path));
            $drawing->setHeight(130);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(6);
            $drawing->setOffsetY(6);
            $drawing->setWorksheet($sheet);
        } else {
            $sheet->getRowDimension(1)->setRowHeight(34);
        }

        $contact1 = trim(collect([
            $info->phone ? "โทรศัพท์ : {$info->phone}" : null,
            $info->fax ? "โทรสาร : {$info->fax}" : null,
        ])->filter()->implode('   '));
        $contact2 = trim(collect([
            $info->website,
            $info->email ? "อีเมล์ : {$info->email}" : null,
        ])->filter()->implode('   '));

        if ($contact1 !== '' || $contact2 !== '') {
            $sheet->mergeCells("B2:{$lastCol}2");
            $sheet->setCellValue('B2', $contact1);
            $sheet->mergeCells("B3:{$lastCol}3");
            $sheet->setCellValue('B3', $contact2);
            $sheet->getStyle("B2:{$lastCol}3")->applyFromArray([
                'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
            ]);
        }

        return $hasLogo;
    }

    /**
     * แถบคำแนะนำสีส้ม (แถว 5) — ใช้เฉพาะแบบฟอร์มเปล่าไว้กรอก ไม่ใช้กับไฟล์ export ข้อมูลที่กรอกแล้ว
     */
    public static function applyInstructionRow(Worksheet $sheet, int $totalCols, string $text): void
    {
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);
        $sheet->mergeCells("A5:{$lastCol}5");
        $sheet->setCellValue('A5', $text);
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'B8720A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF4E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(20);
    }

    /**
     * ปรับความกว้างคอลัมน์มาตรฐานให้คอลัมน์ข้อมูล โดยข้ามคอลัมน์ A ถ้ามีโลโก้ (กันไปทับความกว้างที่ตั้งไว้ให้โลโก้)
     */
    public static function setColumnWidth(Worksheet $sheet, string $col, float $width, bool $hasLogo): void
    {
        if ($col === 'A' && $hasLogo) {
            return;
        }
        $sheet->getColumnDimension($col)->setWidth($width);
    }
}
