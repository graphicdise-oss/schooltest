<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;



class Subject extends Model
{
    protected $table = 'subjects';
    protected $primaryKey = 'subject_id';
    protected $fillable = ['code', 'name_th', 'name_short', 'code_en', 'name_en', 'subject_group', 'subject_type', 'credits', 'hours_per_week', 'hours_per_year', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function teachingAssigns() { return $this->hasMany(TeachingAssign::class, 'subject_id', 'subject_id'); }
}