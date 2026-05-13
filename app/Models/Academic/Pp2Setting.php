<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;

class Pp2Setting extends Model
{
    protected $table = 'pp2_settings';

    protected $fillable = [
        'school_name',
        'province',
        'affiliation',
        'director_name',
        'registrar_name',
        'registrar_personnel_id',
        'director_personnel_id',
    ];

    public static function getInstance(): self
    {
        return self::first() ?? new self();
    }
}