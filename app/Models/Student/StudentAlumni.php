<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;

class StudentAlumni extends Model
{
    protected $table = 'student_alumni';
    protected $primaryKey = 'alumni_id';

    protected $fillable = [
        'student_code', 'fullname_th', 'class_level',
        'academic_year', 'status', 'leave_date', 'note',
    ];

    protected $casts = ['leave_date' => 'date'];
}
