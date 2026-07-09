<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class SubjectAssessment extends Model
{
    protected $table = 'subject_assessments';

    protected $fillable = ['assign_id', 'student_id', 'desired_char', 'reading_thinking', 'competency'];

    public function teachingAssign()
    {
        return $this->belongsTo(TeachingAssign::class, 'assign_id', 'assign_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
