<?php
// === PersonnelAddress.php ===
namespace App\Models\Personne;

use Illuminate\Database\Eloquent\Model;

class PersonnelAddress extends Model
{
    protected $table = 'personnel_addresses';
    protected $primaryKey = 'address_id';
    protected $fillable = [
        'personnel_id', 'address_type', 'house_no', 'moo', 'village',
        'soi', 'building_floor', 'road', 'province', 'district', 'sub_district', 'postal_code',
    ];

    public function personnel() { return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id'); }
}