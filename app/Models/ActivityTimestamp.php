<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityTimestamp extends Model
{
    protected $table = 'activity_timestamps';

    protected $fillable = [
        'personnel_id', 'personnel_name', 'employee_code',
        'route_name', 'page_label', 'method', 'url', 'first_recorded_at',
    ];

    protected $casts = ['first_recorded_at' => 'datetime'];
}
