<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class RoomAttendance extends Model
{
    protected $table = 'room_attendances';

    protected $fillable = ['section_id', 'student_id', 'class_date', 'status'];

    protected $casts = ['class_date' => 'date'];

    public const STATUSES = ['มา', 'ไม่มา'];

    public function classSection()
    {
        return $this->belongsTo(ClassSection::class, 'section_id', 'section_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
