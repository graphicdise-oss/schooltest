<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class StudentDocNumber extends Model
{
    protected $table = 'student_doc_numbers';
    protected $fillable = ['student_id', 'semester_id', 'level_id', 'doc_set', 'doc_number'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }
}
