<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $table = 'programs';
    protected $primaryKey = 'program_id';
    protected $fillable = ['name', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function curriculums() { return $this->hasMany(Curriculum::class, 'program_id', 'program_id'); }
}
