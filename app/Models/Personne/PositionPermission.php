<?php

namespace App\Models\Personne;
use Illuminate\Database\Eloquent\Model;

class PositionPermission extends Model
{
    protected $table = 'position_permissions';
    protected $primaryKey = 'permission_id';

    protected $fillable = ['position_id', 'menu_key', 'menu_label', 'menu_group', 'is_allowed'];

    protected $casts = ['is_allowed' => 'boolean'];

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'position_id');
    }
}
