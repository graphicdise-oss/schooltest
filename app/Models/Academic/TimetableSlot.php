<?php
namespace App\Models\Academic;
use Illuminate\Database\Eloquent\Model;

class TimetableSlot extends Model
{
    protected $table = 'timetable_slots';
    protected $primaryKey = 'slot_id';
    protected $fillable = ['assign_id', 'day_of_week', 'start_time', 'end_time', 'room'];
    public function teachingAssign() { return $this->belongsTo(TeachingAssign::class, 'assign_id', 'assign_id'); }
}