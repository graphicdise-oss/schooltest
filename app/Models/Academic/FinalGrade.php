<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class FinalGrade extends Model
{
    protected $table = 'final_grades';
    protected $primaryKey = 'grade_id';
    protected $fillable = ['student_id', 'assign_id', 'semester_id', 'total_score', 'grade', 'gpa_point', 'remark'];
    public function student() { return $this->belongsTo(Student::class, 'student_id', 'student_id'); }
    public function teachingAssign() { return $this->belongsTo(TeachingAssign::class, 'assign_id', 'assign_id'); }
    public function semester() { return $this->belongsTo(Semester::class, 'semester_id', 'semester_id'); }
    public static function calculateGrade($score) {
        if ($score >= 80) return ['grade' => '4', 'gpa' => 4.0];
        if ($score >= 75) return ['grade' => '3.5', 'gpa' => 3.5];
        if ($score >= 70) return ['grade' => '3', 'gpa' => 3.0];
        if ($score >= 65) return ['grade' => '2.5', 'gpa' => 2.5];
        if ($score >= 60) return ['grade' => '2', 'gpa' => 2.0];
        if ($score >= 55) return ['grade' => '1.5', 'gpa' => 1.5];
        if ($score >= 50) return ['grade' => '1', 'gpa' => 1.0];
        return ['grade' => '0', 'gpa' => 0.0];
    }
}