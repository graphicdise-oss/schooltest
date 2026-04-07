<?php
namespace App\Models\Personne;
use Illuminate\Database\Eloquent\Model;

class PersonnelHonor extends Model
{
    protected $table = 'personnel_honors';
    protected $primaryKey = 'honor_id';
    protected $fillable = ['personnel_id', 'honor_type', 'organization', 'year_received'];
    public function personnel() { return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id'); }
}