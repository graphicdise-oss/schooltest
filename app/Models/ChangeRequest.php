<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeRequest extends Model
{
    protected $fillable = [
        'requester_name',
        'department',
        'request_date',
        'priority',
        'module_name',
        'operation_types',
        'objective',
        'fix_link',
    ];

    protected $casts = [
        'operation_types' => 'array',
        'request_date'    => 'date',
    ];
}