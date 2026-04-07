<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    protected $table = 'curriculums';
    protected $primaryKey = 'curriculum_id';
    protected $fillable = ['name', 'level_id', 'year_applied', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function level() { return $this->belongsTo(Level::class, 'level_id', 'level_id'); }
    public function curriculumSubjects() { return $this->hasMany(CurriculumSubject::class, 'curriculum_id', 'curriculum_id'); }
}