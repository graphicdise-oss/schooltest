<?php

namespace App\Models\Student;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BehaviorRecord extends Model
{
    protected $table = 'behavior_records';
    protected $primaryKey = 'behavior_record_id';

    protected $fillable = [
        'student_id', 'behavior_item_id', 'type', 'item_name_th', 'points',
        'signed_points', 'balance_after', 'year_id', 'semester_id', 'note', 'recorded_by',
    ];

    protected $casts = [
        'points' => 'decimal:2',
        'signed_points' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function behaviorItem()
    {
        return $this->belongsTo(BehaviorItem::class, 'behavior_item_id', 'behavior_item_id');
    }

    public function year()
    {
        return $this->belongsTo(AcademicYear::class, 'year_id', 'year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by', 'personnel_id');
    }
}
