<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;
use App\Models\Personne\Personnel;

class ClassSection extends Model
{
    protected $table = 'class_sections';
    protected $primaryKey = 'section_id';
    protected $fillable = ['semester_id', 'level_id', 'section_number', 'homeroom_teacher_id', 'max_students', 'curriculum_id'];
    public function semester() { return $this->belongsTo(Semester::class, 'semester_id', 'semester_id'); }
    public function level() { return $this->belongsTo(Level::class, 'level_id', 'level_id'); }
    public function homeroomTeacher() { return $this->belongsTo(Personnel::class, 'homeroom_teacher_id', 'personnel_id'); }
    public function studentSections() { return $this->hasMany(StudentSection::class, 'section_id', 'section_id'); }
    public function teachingAssigns() { return $this->hasMany(TeachingAssign::class, 'section_id', 'section_id'); }
    public function curriculum() { return $this->belongsTo(Curriculum::class, 'curriculum_id', 'curriculum_id'); }
    public function getFullNameAttribute() { return ($this->level->name ?? '') . '/' . $this->section_number; }
}