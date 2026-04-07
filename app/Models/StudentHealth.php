<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHealth extends Model
{
    use HasFactory;

    protected $table = 'student_health';

    protected $fillable = [
        'student_id',
        'blood_group',
        'food_allergy',
        'medicine_allergy',
        'other_allergy',
        'chronic_disease',
        'serious_disease'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}