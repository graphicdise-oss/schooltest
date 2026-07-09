<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class OnetScore extends Model
{
    protected $table = 'onet_scores';

    protected $fillable = ['student_id', 'year_id', 'level_id', 'subject', 'score'];

    public const SUBJECTS = ['ภาษาไทย', 'คณิตศาสตร์', 'วิทยาศาสตร์', 'ภาษาอังกฤษ'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'year_id', 'year_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'level_id');
    }
}
