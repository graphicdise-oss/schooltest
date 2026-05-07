<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamRoom extends Model
{
    protected $table = 'exam_rooms';
    protected $fillable = ['curriculum_name', 'room_name', 'capacity'];
}
