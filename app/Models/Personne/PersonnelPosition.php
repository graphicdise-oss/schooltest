<?php
namespace App\Models\Personne;
use Illuminate\Database\Eloquent\Model;

class PersonnelPosition extends Model
{
    protected $table = 'personnel_positions';
    protected $primaryKey = 'position_id';
    protected $fillable = ['personnel_id', 'work_status', 'level', 'school_start_date', 'appointment_date', 'salary', 'government_start_date', 'position_allowance', 'academic_allowance', 'retirement_date'];
    protected $casts = ['school_start_date' => 'date', 'appointment_date' => 'date', 'government_start_date' => 'date', 'retirement_date' => 'date'];
    public function personnel() { return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id'); }
}