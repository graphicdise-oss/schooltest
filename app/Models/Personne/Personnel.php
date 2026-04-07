<?php

namespace App\Models\Personne;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    protected $table = 'personnels';
    protected $primaryKey = 'personnel_id';

    protected $fillable = [
        'personnel_type', 'employee_code', 'position', 'department','password', 'role',
        'gender', 'thai_prefix', 'thai_firstname', 'thai_lastname',
        'eng_firstname', 'eng_lastname', 'id_card_number',
        'passport_number', 'passport_country', 'passport_expiry',
        'visa_number', 'work_permit_number', 'work_permit_expiry',
        'date_of_birth', 'blood_group', 'nationality', 'ethnicity', 'religion',
        'phone', 'email', 'schedule', 'personnel_image', 'status', 'created_by',
        
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'passport_expiry' => 'date',
        'work_permit_expiry' => 'date',
    ];

    public function addresses()       { return $this->hasMany(PersonnelAddress::class, 'personnel_id', 'personnel_id'); }
    public function educations()      { return $this->hasMany(PersonnelEducation::class, 'personnel_id', 'personnel_id'); }
    public function honors()          { return $this->hasMany(PersonnelHonor::class, 'personnel_id', 'personnel_id'); }
    public function trainings()       { return $this->hasMany(PersonnelTraining::class, 'personnel_id', 'personnel_id'); }
    public function toeics()          { return $this->hasMany(PersonnelToeic::class, 'personnel_id', 'personnel_id'); }
    public function positionDetail()  { return $this->hasOne(PersonnelPosition::class, 'personnel_id', 'personnel_id'); }
    public function licenses()        { return $this->hasMany(PersonnelLicense::class, 'personnel_id', 'personnel_id'); }
    public function decorations()     { return $this->hasMany(PersonnelDecoration::class, 'personnel_id', 'personnel_id'); }
}