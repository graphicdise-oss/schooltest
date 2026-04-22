<?php

namespace App\Models\Personne;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'positions';
    protected $primaryKey = 'position_id';
    protected $fillable = ['name', 'employee_type', 'is_active', 'sort_order'];
    protected $casts = ['is_active' => 'boolean'];
}
