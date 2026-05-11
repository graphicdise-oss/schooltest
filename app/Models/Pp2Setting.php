<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pp2Setting extends Model
{
    protected $table = 'pp2_settings';

    protected $fillable = [
        'school_name',
        'province',
        'affiliation',
        'director_name',
    ];
}