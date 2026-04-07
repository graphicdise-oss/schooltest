<?php

namespace App\Models\Personne;

use Illuminate\Database\Eloquent\Model;

class PersonnelType extends Model
{
    protected $table = 'personnel_types';
    protected $primaryKey = 'type_id';

    protected $fillable = ['name', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function permissions()
    {
        return $this->hasMany(PersonnelTypePermission::class, 'type_id', 'type_id');
    }

    // เช็คว่ามีสิทธิ์เข้าเมนูนี้ไหม
    public function canAccess($menuKey)
    {
        return $this->permissions()
            ->where('menu_key', $menuKey)
            ->where('is_allowed', true)
            ->exists();
    }
}