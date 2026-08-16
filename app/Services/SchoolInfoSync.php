<?php

namespace App\Services;

use App\Models\Academic\Pp2Setting;
use App\Models\SchoolInfoSetting;
use Illuminate\Support\Facades\Storage;

// เชื่อมข้อมูล "ชื่อโรงเรียน/ตราโรงเรียน" ระหว่าง 2 ระบบตั้งค่าที่แต่เดิมแยกกันคนละที่ ไม่เชื่อมกัน:
// - Pp2Setting: หน้า "ตั้งค่าเริ่มต้น" ใช้พิมพ์เอกสาร ปพ.1/2/3/5/6/7 ฯลฯ (โลโก้เก็บเป็นไฟล์ static public/img/pp_1/logo.*)
// - SchoolInfoSetting: ใช้โดยหน้าสมัครเรียน, ส่งออก Excel (ExcelSchoolHeader), หัวกระดาษหน้ารายชื่อนักเรียน/บุคลากร
//   (โลโก้เก็บผ่าน Storage disk 'public')
// เดิมตั้งค่าจากฝั่งหนึ่งแล้วอีกฝั่งไม่เห็นเลย เรียกเมธอดในนี้ทุกครั้งที่บันทึกชื่อ/โลโก้จากฝั่งใดฝั่งหนึ่ง
// ให้อีกฝั่งอัปเดตตามไปด้วยเสมอ
class SchoolInfoSync
{
    // เรียกหลังบันทึกชื่อโรงเรียนจากฝั่งไหนก็ได้ อัปเดตทั้งสองโมเดลให้ตรงกัน
    public static function syncName(?string $name): void
    {
        if (!$name) {
            return;
        }

        $pp2 = Pp2Setting::getInstance();
        if (!$pp2->exists) {
            $pp2->school_name = $name;
            $pp2->save();
        } elseif ($pp2->school_name !== $name) {
            $pp2->update(['school_name' => $name]);
        }

        $info = SchoolInfoSetting::getInstance();
        if (!$info->exists) {
            $info->school_name = $name;
            $info->save();
        } elseif ($info->school_name !== $name) {
            $info->update(['school_name' => $name]);
        }
    }

    // เรียกหลังอัปโหลดโลโก้จากฝั่ง "ตั้งค่าเริ่มต้น" (ไฟล์ static) -> เผยแพร่ไปยัง SchoolInfoSetting ด้วย
    public static function propagateLogoFromStatic(string $staticFilePath, string $extension): void
    {
        if (!file_exists($staticFilePath)) {
            return;
        }

        $info = SchoolInfoSetting::getInstance();
        if ($info->logo_path) {
            Storage::disk('public')->delete($info->logo_path);
        }
        $storedPath = 'school/logo.' . strtolower($extension);
        Storage::disk('public')->put($storedPath, file_get_contents($staticFilePath));

        if (!$info->exists) {
            $info->logo_path = $storedPath;
            $info->save();
        } else {
            $info->update(['logo_path' => $storedPath]);
        }
    }

    // เรียกหลังอัปโหลดโลโก้จากฝั่งหน้ารายชื่อนักเรียน (Storage) -> เผยแพร่ไปยังไฟล์ static ที่ระบบเอกสาร ปพ. ใช้ด้วย
    public static function propagateLogoToStatic(string $storagePath): void
    {
        if (!Storage::disk('public')->exists($storagePath)) {
            return;
        }

        $ext = strtolower(pathinfo($storagePath, PATHINFO_EXTENSION)) ?: 'png';
        $dir = public_path('img/pp_1');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        foreach (['png', 'jpg', 'jpeg', 'PNG', 'JPG', 'JPEG'] as $e) {
            $old = $dir . '/logo.' . $e;
            if (file_exists($old)) {
                unlink($old);
            }
        }
        file_put_contents($dir . '/logo.' . $ext, Storage::disk('public')->get($storagePath));
    }
}
