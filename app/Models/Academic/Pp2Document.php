<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class Pp2Document extends Model
{
    protected $table = 'pp2_documents';
    protected $fillable = ['student_id', 'section_id', 'doc_number', 'issued_date'];
    protected $casts = ['issued_date' => 'date'];

    public function student() { return $this->belongsTo(Student::class, 'student_id', 'student_id'); }
    public function classSection() { return $this->belongsTo(ClassSection::class, 'section_id', 'section_id'); }
}
