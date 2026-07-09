<?php

namespace App\Models\Library;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\Personne\Personnel;

class LibraryLoan extends Model
{
    protected $table = 'library_loans';

    protected $fillable = [
        'book_id', 'borrower_type', 'student_id', 'personnel_id',
        'borrowed_at', 'due_at', 'returned_at', 'status', 'note',
    ];

    protected $casts = [
        'borrowed_at' => 'date',
        'due_at'      => 'date',
        'returned_at' => 'date',
    ];

    public function book()
    {
        return $this->belongsTo(LibraryBook::class, 'book_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id');
    }

    public function getBorrowerNameAttribute(): string
    {
        if ($this->borrower_type === 'student' && $this->student) {
            return trim($this->student->thai_prefix . $this->student->thai_firstname . ' ' . $this->student->thai_lastname);
        }
        if ($this->borrower_type === 'personnel' && $this->personnel) {
            return trim($this->personnel->thai_prefix . $this->personnel->thai_firstname . ' ' . $this->personnel->thai_lastname);
        }
        return '-';
    }

    public function getBorrowerCodeAttribute(): string
    {
        if ($this->borrower_type === 'student' && $this->student) {
            return $this->student->student_code ?? '-';
        }
        if ($this->borrower_type === 'personnel' && $this->personnel) {
            return $this->personnel->employee_code ?? '-';
        }
        return '-';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'ยืมอยู่' && $this->due_at && $this->due_at->lt(now()->startOfDay());
    }
}
