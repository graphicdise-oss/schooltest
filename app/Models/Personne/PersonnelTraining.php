<?php
namespace App\Models\Personne;
use Illuminate\Database\Eloquent\Model;

class PersonnelTraining extends Model
{
    protected $table = 'personnel_trainings';
    protected $primaryKey = 'training_id';
    protected $fillable = ['personnel_id', 'training_type', 'project', 'course_name', 'start_date', 'end_date', 'hours', 'location', 'country', 'province', 'expense'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];
    public function personnel() { return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id'); }
}