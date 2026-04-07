<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentEducation extends Model
{
    // 📌 บังคับให้ใช้ตารางชื่อ student_education
    protected $table = 'student_education'; 

    // เปลี่ยน Primary Key ตาม Database ของพี่
    protected $primaryKey = 'std_add_id'; 

    protected $guarded = [];
}