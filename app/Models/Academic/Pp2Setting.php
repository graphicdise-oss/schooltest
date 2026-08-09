<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;

class Pp2Setting extends Model
{
    protected $table = 'pp2_settings';

    protected $fillable = [
        'school_name',
        'affiliation',
        'tambon',
        'amphoe',
        'province',
        'education_area',
        'director_name',
        'registrar_name',
        'registrar_personnel_id',
        'director_personnel_id',
    ];

    public static function getInstance(): self
    {
        return self::first() ?? new self();
    }

    // รวมค่าที่ตั้งไว้ในหน้า "ตั้งค่าเริ่มต้น" (ถ้ามี) ทับลงบน config('school') เริ่มต้น — ใช้จุดเดียวนี้
    // ทุกหน้าที่ต้องพิมพ์ข้อมูลโรงเรียน (ปพ.1/3/5/6/7, ใบระเบียนผลการเรียน, รายงานจัดอันดับคะแนนรายวิชา ฯลฯ)
    // เพื่อกันไม่ให้แต่ละหน้า merge ไม่ตรงกันเหมือนเดิม (บางหน้าลืม override ที่อยู่, บางหน้าลืม override ชื่อผู้ลงนาม)
    public static function mergedSchoolConfig(): array
    {
        $s = self::getInstance();
        $school = config('school');

        if ($s->school_name)     $school['name']           = $s->school_name;
        if ($s->affiliation)     $school['affiliation']     = $s->affiliation;
        if ($s->tambon)          $school['tambon']          = $s->tambon;
        if ($s->amphoe)          $school['amphoe']          = $s->amphoe;
        if ($s->province)        $school['changwat']        = $s->province;
        if ($s->education_area)  $school['education_area']  = $s->education_area;
        if ($s->registrar_name)  $school['registrar_name']  = $s->registrar_name;
        if ($s->director_name)   $school['director_name']   = $s->director_name;

        return $school;
    }
}
