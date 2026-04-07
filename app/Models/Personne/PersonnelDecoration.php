<?php
namespace App\Models\Personne;
use Illuminate\Database\Eloquent\Model;

class PersonnelDecoration extends Model
{
    protected $table = 'personnel_decorations';
    protected $primaryKey = 'decoration_id';
    protected $fillable = ['personnel_id', 'year_received', 'decoration_class', 'position', 'gazette_volume', 'gazette_section', 'gazette_number', 'gazette_date'];
    protected $casts = ['gazette_date' => 'date'];
    public function personnel() { return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id'); }
}