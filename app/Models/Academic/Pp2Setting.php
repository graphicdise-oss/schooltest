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
    ];

    public static function getInstance(): self
    {
        return self::first() ?? new self();
    }
}
