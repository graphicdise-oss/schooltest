<?php

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Model;
use App\Models\Academic\AcademicYear;

class AdmissionSetting extends Model
{
    protected $table = 'admission_settings';

    protected $fillable = [
        'is_open', 'year_id', 'open_date', 'close_date', 'instructions', 'levels_note',
    ];

    protected $casts = [
        'is_open'    => 'boolean',
        'open_date'  => 'date',
        'close_date' => 'date',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'year_id', 'year_id');
    }

    public static function getOrCreate(): self
    {
        return static::first() ?? static::create(['is_open' => false]);
    }

    // เปิดรับสมัครอยู่หรือไม่ (เช็ค toggle + ช่วงวันที่)
    public function isAcceptingNow(): bool
    {
        if (!$this->is_open) return false;
        $today = now()->startOfDay();
        if ($this->open_date && $today->lt($this->open_date)) return false;
        if ($this->close_date && $today->gt($this->close_date)) return false;
        return true;
    }
}
