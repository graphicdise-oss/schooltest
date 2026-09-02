<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPickupNotice extends Model
{
    protected $table = 'student_pickup_notices';

    protected $fillable = [
        'student_id', 'notice_date', 'pickup_time',
        'pickup_person_name', 'relationship', 'phone', 'note',
    ];

    protected $casts = [
        'notice_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
