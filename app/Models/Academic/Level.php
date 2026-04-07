<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $table = 'levels';
    protected $primaryKey = 'level_id';
    protected $fillable = ['name', 'level_group', 'sort_order'];
    public function classSections() { return $this->hasMany(ClassSection::class, 'level_id', 'level_id'); }
}