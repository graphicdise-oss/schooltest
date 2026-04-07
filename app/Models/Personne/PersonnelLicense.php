<?php
namespace App\Models\Personne;
use Illuminate\Database\Eloquent\Model;

class PersonnelLicense extends Model
{
    protected $table = 'personnel_licenses';
    protected $primaryKey = 'license_id';
    protected $fillable = ['personnel_id', 'license_type', 'license_number', 'license_name', 'issue_date', 'expiry_date', 'issuing_organization'];
    protected $casts = ['issue_date' => 'date', 'expiry_date' => 'date'];
    public function personnel() { return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id'); }
}