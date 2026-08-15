<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\Personne\Personnel;

class SubjectGroupHead extends Model
{
    protected $table = 'subject_group_heads';

    protected $fillable = ['subject_group', 'personnel_id', 'head_name'];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id');
    }

    // รายชื่อกลุ่มสาระการเรียนรู้ตายตัวของระบบ (ใช้ร่วมกับหน้าเพิ่ม/แก้ไขรายวิชา)
    public static function groupList(): array
    {
        return ['ภาษาไทย', 'คณิตศาสตร์', 'วิทยาศาสตร์และเทคโนโลยี', 'สังคมศึกษา ศาสนา และวัฒนธรรม',
                'สุขศึกษาและพลศึกษา', 'ศิลปะ', 'การงานอาชีพ', 'ภาษาต่างประเทศ', 'การศึกษาค้นคว้าด้วยตนเอง (IS)'];
    }
}
