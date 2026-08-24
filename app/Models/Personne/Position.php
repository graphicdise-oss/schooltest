<?php

namespace App\Models\Personne;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'positions';
    protected $primaryKey = 'position_id';
    protected $fillable = ['name', 'employee_type', 'is_active', 'sort_order'];
    protected $casts = ['is_active' => 'boolean'];

    public function permissions()
    {
        return $this->hasMany(PositionPermission::class, 'position_id', 'position_id');
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
