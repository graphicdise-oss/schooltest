<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolInfoSetting extends Model
{
    protected $table = 'school_info_settings';

    protected $fillable = ['school_name', 'phone', 'fax', 'website', 'email'];

    public static function getInstance(): self
    {
        return self::first() ?? new self();
    }
}
