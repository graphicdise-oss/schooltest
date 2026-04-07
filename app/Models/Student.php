<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    // กำหนดชื่อตาราง (ใส่ไว้เพื่อความชัวร์ครับ)
    protected $table = 'students';

    // 🚨 ตัวแก้ปัญหา: บอก Laravel ว่าคอลัมน์หลักของเราชื่อ student_id ไม่ใช่ id
    protected $primaryKey = 'student_id';

    // อนุญาตให้บันทึกข้อมูลได้ทุกช่อง
    protected $guarded = [];

    // ความสัมพันธ์กับตารางที่อยู่
    public function addresses()
    {
        return $this->hasMany(StudentAddress::class, 'student_id', 'student_id');
    }

    // ความสัมพันธ์กับตารางการศึกษา
    public function education()
    {
        return $this->hasOne(StudentEducation::class, 'student_id', 'student_id');
    }

    // เพิ่มฟังก์ชันนี้ไว้ใน Model Student
    public function families()
    {
        return $this->hasMany(StudentFamily::class, 'student_id', 'student_id'); 
        // หมายเหตุ: ตรง 'student_id' อันหลัง คือชื่อ Primary Key ของตาราง students (เปลี่ยนตามที่คุณตั้งไว้ได้เลยครับ)
    }
}