<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $table = 'academic_years';
    protected $primaryKey = 'year_id';
    protected $fillable = ['year_name', 'is_current'];
    protected $casts = ['is_current' => 'boolean'];
    public function semesters() { return $this->hasMany(Semester::class, 'year_id', 'year_id'); }
    public static function current() { return static::where('is_current', true)->first(); }
}