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

    public function teachingAssign()
    {
        return $this->belongsTo(TeachingAssign::class, 'assign_id', 'assign_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
