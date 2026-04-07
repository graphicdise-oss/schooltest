<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentAddress extends Model
{
    // 📌 บังคับให้ใช้ตารางชื่อ student_address (ตาม Database ของพี่เป๊ะๆ)
    protected $table = 'student_address'; 

    // เปลี่ยน Primary Key เพราะของพี่ใช้ชื่อ std_add_id (ไม่ได้ใช้ id)
    protected $primaryKey = 'std_add_id';

    protected $guarded = [];
}