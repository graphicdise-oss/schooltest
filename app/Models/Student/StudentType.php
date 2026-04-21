<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;

class StudentType extends Model
{
    protected $table = 'student_types';
    protected $primaryKey = 'type_id';

    protected $fillable = ['name_th', 'name_en', 'caretaker', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];
}
