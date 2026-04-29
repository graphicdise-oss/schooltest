<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class StudentScore extends Model
{

    protected $table = 'student_scores';
    protected $primaryKey = 'score_id'; // ← เปลี่ยนเป็นชื่อจริงของ PK
    protected $fillable = ['category_id', 'student_id', 'score'];
    
    public function category() { return $this->belongsTo(ScoreCategory::class, 'category_id', 'category_id'); }
    public function student() { return $this->belongsTo(Student::class, 'student_id', 'student_id'); }
}
