<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Academic\AcademicYear;

class Holiday extends Model
{
    protected $table = 'holidays';

    protected $fillable = ['year_id', 'title', 'start_date', 'end_date', 'note'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'year_id', 'year_id');
    }

    // จำนวนวันของวันหยุดนี้ (นับรวมวันเริ่มและวันสิ้นสุด)
    public function getDayCountAttribute(): int
    {
        if (!$this->start_date) return 0;
        $end = $this->end_date ?? $this->start_date;
        return $this->start_date->diffInDays($end) + 1;
    }
}
