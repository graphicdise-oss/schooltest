<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;

class CurriculumSubject extends Model
{
    protected $table = 'curriculum_subjects';
    protected $fillable = ['curriculum_id', 'subject_id', 'semester_type', 'is_required', 'personnel_id'];
    protected $casts = ['is_required' => 'boolean'];
    public function curriculum() { return $this->belongsTo(Curriculum::class, 'curriculum_id', 'curriculum_id'); }
    public function subject()    { return $this->belongsTo(Subject::class, 'subject_id', 'subject_id'); }
    public function personnel()  { return $this->belongsTo(\App\Models\Personne\Personnel::class, 'personnel_id', 'personnel_id'); }
}