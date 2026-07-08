<?php

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\Academic\Level;
use App\Models\Academic\AcademicYear;

class AdmissionApplication extends Model
{
    protected $table = 'admission_applications';

    protected $fillable = [
        'student_id', 'year_id', 'level_id', 'applicant_phone', 'status', 'note',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'level_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'year_id', 'year_id');
    }
}
