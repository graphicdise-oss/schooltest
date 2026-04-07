<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;

class ScoreCategory extends Model
{
    protected $table = 'score_categories';
    protected $primaryKey = 'category_id';
    protected $fillable = ['assign_id', 'name', 'max_score', 'weight_pct', 'sort_order'];
    public function teachingAssign() { return $this->belongsTo(TeachingAssign::class, 'assign_id', 'assign_id'); }
    public function studentScores() { return $this->hasMany(StudentScore::class, 'category_id', 'category_id'); }
}