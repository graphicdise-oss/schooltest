<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Academic\ClassSection;

class Pp2SectionSetting extends Model
{
    protected $table = 'pp2_section_settings';

    protected $fillable = ['section_id', 'issued_date'];

    protected $casts = ['issued_date' => 'date'];

    public function classSection()
    {
        return $this->belongsTo(ClassSection::class, 'section_id', 'section_id');
    }
}
