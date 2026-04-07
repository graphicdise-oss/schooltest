<?php
namespace App\Models\Personne;
use Illuminate\Database\Eloquent\Model;

class PersonnelToeic extends Model
{
    protected $table = 'personnel_toeics';
    protected $primaryKey = 'toeic_id';
    protected $fillable = ['personnel_id', 'score', 'institution', 'expiry_date'];
    protected $casts = ['expiry_date' => 'date'];
    public function personnel() { return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id'); }
}