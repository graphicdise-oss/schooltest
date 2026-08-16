<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementRecipient extends Model
{
    protected $table = 'announcement_recipients';
    protected $fillable = ['announcement_id', 'student_id', 'read_at', 'acknowledged_at'];
    protected $casts = ['read_at' => 'datetime', 'acknowledged_at' => 'datetime'];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class, 'announcement_id', 'announcement_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
