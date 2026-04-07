<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class Promotion extends Model
{
    protected $table = 'promotions';
    protected $primaryKey = 'promo_id';
    protected $fillable = ['student_id', 'from_section_id', 'to_section_id', 'promo_type', 'promo_date', 'remark', 'created_by'];
    protected $casts = ['promo_date' => 'date'];
    public function student() { return $this->belongsTo(Student::class, 'student_id', 'student_id'); }
    public function fromSection() { return $this->belongsTo(ClassSection::class, 'from_section_id', 'section_id'); }
    public function toSection() { return $this->belongsTo(ClassSection::class, 'to_section_id', 'section_id'); }
}