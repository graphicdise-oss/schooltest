<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class ClassAttendance extends Model
{
    protected $table = 'class_attendances';

    protected $fillable = ['assign_id', 'student_id', 'class_date', 'status'];

    protected $casts = ['class_date' => 'date'];

    public const STATUSES = ['มา', 'ป่วย', 'ลา', 'ขาด'];

    // ป้ายกำกับที่ใส่ในช่องวันเสาร์-อาทิตย์/วันหยุดตามปฏิทินในไฟล์ Excel เช็คชื่อ (แทนที่ช่องกรอกสถานะ)
    public const HOLIDAY_LABEL = 'วันหยุด';

    public function teachingAssign()
    {
        return $this->belongsTo(TeachingAssign::class, 'assign_id', 'assign_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
