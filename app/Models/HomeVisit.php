<?php

namespace App\Models;

use App\Models\Personne\Personnel;
use Illuminate\Database\Eloquent\Model;

class HomeVisit extends Model
{
    protected $table = 'home_visits';

    protected $fillable = [
        'student_id', 'personnel_id', 'visit_date', 'visit_no',
        'family_status', 'economic_status', 'house_condition',
        'problems_found', 'suggestion', 'need_followup', 'photo', 'created_by',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'need_followup' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id');
    }
}
