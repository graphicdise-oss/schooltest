<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semesters';
    protected $primaryKey = 'semester_id';
    protected $fillable = ['year_id', 'semester_name', 'start_date', 'end_date', 'is_current'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'is_current' => 'boolean'];
    public function academicYear() { return $this->belongsTo(AcademicYear::class, 'year_id', 'year_id'); }
    public function classSections() { return $this->hasMany(ClassSection::class, 'semester_id', 'semester_id'); }
    public static function current() { return static::where('is_current', true)->first(); }

    // เรียงตามปีการศึกษาล่าสุดก่อน แล้วค่อยเทอมล่าสุดก่อน (ไม่ใช่เรียงตาม semester_id ที่อาจไม่ตรงลำดับปีจริง)
    public function scopeOrderedByRecency($query)
    {
        return $query->join('academic_years', 'semesters.year_id', '=', 'academic_years.year_id')
            ->orderByDesc('academic_years.year_name')
            ->orderByDesc('semesters.semester_name')
            ->select('semesters.*');
    }
}