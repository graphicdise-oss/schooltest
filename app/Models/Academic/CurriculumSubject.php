<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;

class CurriculumSubject extends Model
{
    protected $table = 'curriculum_subjects';
    protected $fillable = [
        'curriculum_id', 'subject_id', 'semester_type', 'is_required', 'personnel_id',
        'credits', 'hours_per_year', 'hours_per_week', 'is_active',
        'teacher_can_edit_score_ratio', 'score_collect_pct', 'score_collect_after_midterm_pct',
        'midterm_pct', 'final_pct', 'pass_threshold_pct',
    ];
    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'teacher_can_edit_score_ratio' => 'boolean',
    ];
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

    // ครู personnel_id นี้ เป็นครูผู้สอนวิชานี้ในแผนหรือไม่ (เช็คทั้งครูคนหลักเดิม และครูทุกคนในตาราง teachers)
    public function isTaughtBy(?int $personnelId): bool
    {
        if (!$personnelId) {
            return false;
        }
        return $this->personnel_id === $personnelId || $this->teachers->contains('personnel_id', $personnelId);
    }
}