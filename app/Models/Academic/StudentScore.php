<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class StudentSection extends Model
{
    protected $table = 'student_sections';
    protected $fillable = ['student_id', 'section_id', 'student_number', 'status'];
    public function student() { return $this->belongsTo(Student::class, 'student_id', 'student_id'); }
    public function classSection() { return $this->belongsTo(ClassSection::class, 'section_id', 'section_id'); }
}