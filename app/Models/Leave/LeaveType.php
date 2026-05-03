<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $table = 'leave_types';

    protected $fillable = [
        'leave_type_key',
        'leave_type_name',
        'days_per_year',
        'is_active',
    ];

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type_key', 'leave_type_key');
    }
}
