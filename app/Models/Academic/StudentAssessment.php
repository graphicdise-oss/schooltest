<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class StudentAssessment extends Model
{
    protected $table = 'student_assessments';

    protected $fillable = [
        'student_id', 'semester_id', 'reading_thinking', 'desired_char', 'activity',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }
}
