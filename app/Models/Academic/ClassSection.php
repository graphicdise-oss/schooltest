<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;
use App\Models\Personne\Personnel;

class ClassSection extends Model
{
    protected $table = 'class_sections';
    protected $primaryKey = 'section_id';
    protected $fillable = ['semester_id', 'level_id', 'section_number', 'study_plan', 'homeroom_teacher_id', 'max_students', 'curriculum_id', 'lunch_start', 'lunch_end'];

    // เลขห้องปลอม (ไม่ใช่ห้องเรียนจริง) ที่คำสั่ง console บางตัวสร้างไว้เก็บข้อมูลชั่วคราว:
    // 9998 = เก็บผลนำเข้าเกรดจากไฟล์ที่ไม่รู้ห้องจริง (WritesTranscriptGrades)
    // 9999 = ห้องทดสอบระบบ ปพ.1 (SeedPor1DemoGrades)
    // ต้องกรองออกจากดรอปดาวน์ "เลือกห้องเรียน/ชั้นเรียน" ทุกหน้า ไม่งั้นจะโผล่ปนกับห้องจริงจนสับสน
    public const FAKE_SECTION_NUMBERS = [9998, 9999];

    public function scopeReal($query)
    {
        return $query->whereNotIn('section_number', self::FAKE_SECTION_NUMBERS);
    }

    public function semester() { return $this->belongsTo(Semester::class, 'semester_id', 'semester_id'); }
    public function level() { return $this->belongsTo(Level::class, 'level_id', 'level_id'); }
    public function homeroomTeacher() { return $this->belongsTo(Personnel::class, 'homeroom_teacher_id', 'personnel_id'); }
    public function studentSections() { return $this->hasMany(StudentSection::class, 'section_id', 'section_id'); }
    public function teachingAssigns() { return $this->hasMany(TeachingAssign::class, 'section_id', 'section_id'); }
    public function curriculum() { return $this->belongsTo(Curriculum::class, 'curriculum_id', 'curriculum_id'); }
    public function pp2SectionSetting() { return $this->hasOne(\App\Models\Pp2SectionSetting::class, 'section_id', 'section_id'); }
    public function getFullNameAttribute() { return ($this->level?->name ?? '') . '/' . $this->section_number . ($this->study_plan ? ' ' . $this->study_plan : ''); }
}