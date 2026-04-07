<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFamily extends Model
{
    use HasFactory;

    // กรณีถ้าชื่อตารางไม่ได้เติม s หรือไม่ได้ชื่อตาม default ของ laravel ให้ระบุชื่อตาราง
    protected $table = 'student_family';

    // ปิด timestamps ถ้าในตารางมีแค่ updated_at อย่างเดียว ไม่มี created_at 
    // หรือถ้ามีครบทั้งคู่ (created_at, updated_at) ให้ลบบรรทัดนี้ทิ้งได้เลยครับ
    public $timestamps = false; 

    protected $fillable = [
        'student_id', 'guardian_type', 'prefix_th', 'first_name_th', 'last_name_th', 
        'first_name_en', 'last_name_en', 'birth_date', 'id_card_number', 'ethnicity', 
        'nationality', 'religion', 'education_level', 'house_number', 'village_no', 
        'soi', 'road', 'province_id', 'district_id', 'subdistrict_id', 'postal_code', 
        'relationship', 'tuition_subsidy', 'family_status', 'occupation', 'monthly_income', 
        'workplace', 'phone_home', 'phone_mobile', 'phone_work', 'family_type'
    ];
    
    // ตั้งค่า Relation กลับไปหานักเรียน (ทางเลือก)
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    
}