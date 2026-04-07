<?php
namespace App\Models\Personne;
use Illuminate\Database\Eloquent\Model;

class PersonnelEducation extends Model
{
    protected $table = 'personnel_educations';
    protected $primaryKey = 'education_id';
    protected $fillable = ['personnel_id', 'institution', 'start_year', 'end_year', 'education_level', 'major', 'minor'];
    public function personnel() { return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id'); }
}