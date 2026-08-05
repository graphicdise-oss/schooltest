<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;

class CurriculumSubject extends Model
{
    protected $table = 'curriculum_subjects';
    protected $fillable = ['curriculum_id', 'subject_id', 'semester_type', 'is_required', 'personnel_id', 'credits', 'hours_per_year', 'hours_per_week'];
    protected $casts = ['is_required' => 'boolean'];
    public function curriculum() { return $this->belongsTo(Curriculum::class, 'curriculum_id', 'curriculum_id'); }
    public function subject()    { return $this->belongsTo(Subject::class, 'subject_id', 'subject_id'); }
    public function personnel()  { return $this->belongsTo(\App\Models\Personne\Personnel::class, 'personnel_id', 'personnel_id'); }

    // ครูผู้สอนทุกคนของวิชานี้ในแผน (personnel_id ด้านบนคือครูคนหลัก/เดิม เก็บไว้ให้เข้ากันได้ของเดิม)
    public function teachers()
    {
        return $this->belongsToMany(
            \App\Models\Personne\Personnel::class,
            'curriculum_subject_teachers',
            'curriculum_subject_id',
            'personnel_id'
        )->withTimestamps();
    }
}