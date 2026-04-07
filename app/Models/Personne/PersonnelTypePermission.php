<?php

namespace App\Models\Personne;
use Illuminate\Database\Eloquent\Model;

class PersonnelTypePermission extends Model
{
    protected $table = 'personnel_type_permissions';
    protected $primaryKey = 'permission_id';

    protected $fillable = ['type_id', 'menu_key', 'menu_label', 'menu_group', 'is_allowed'];

    protected $casts = ['is_allowed' => 'boolean'];

    public function type()
    {
        return $this->belongsTo(PersonnelType::class, 'type_id', 'type_id');
    }
}