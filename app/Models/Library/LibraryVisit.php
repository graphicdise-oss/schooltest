<?php

namespace App\Models\Library;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\Personne\Personnel;

class LibraryVisit extends Model
{
    protected $table = 'library_visits';

    protected $fillable = ['visitor_type', 'student_id', 'personnel_id', 'purpose', 'visited_at'];

    protected $casts = ['visited_at' => 'datetime'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id');
    }

    public function getVisitorNameAttribute(): string
    {
        if ($this->visitor_type === 'student' && $this->student) {
            return trim($this->student->thai_prefix . $this->student->thai_firstname . ' ' . $this->student->thai_lastname);
        }
        if ($this->visitor_type === 'personnel' && $this->personnel) {
            return trim($this->personnel->thai_prefix . $this->personnel->thai_firstname . ' ' . $this->personnel->thai_lastname);
        }
        return '-';
    }
}
