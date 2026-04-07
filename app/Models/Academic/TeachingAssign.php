<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;
use App\Models\Personne\Personnel;

class TeachingAssign extends Model
{
    protected $table = 'teaching_assigns';
    protected $primaryKey = 'assign_id';
    protected $fillable = ['personnel_id', 'subject_id', 'section_id', 'semester_id'];
    public function personnel() { return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id'); }
    public function subject() { return $this->belongsTo(Subject::class, 'subject_id', 'subject_id'); }
    public function classSection() { return $this->belongsTo(ClassSection::class, 'section_id', 'section_id'); }
    public function semester() { return $this->belongsTo(Semester::class, 'semester_id', 'semester_id'); }
    public function timetableSlots() { return $this->hasMany(TimetableSlot::class, 'assign_id', 'assign_id'); }
    public function scoreCategories() { return $this->hasMany(ScoreCategory::class, 'assign_id', 'assign_id'); }
    public function finalGrades() { return $this->hasMany(FinalGrade::class, 'assign_id', 'assign_id'); }
}