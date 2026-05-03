<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Model;
use App\Models\Personne\Personnel;

class LeaveRequest extends Model
{
    protected $table = 'leave_requests';

    protected $fillable = [
        'leave_type_key',
        'request_date',
        'start_date',
        'end_date',
        'num_days',
        'requester_id',
        'reviewer_id',
        'status',
        'reason',
        'attachment',
        'note',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'start_date'   => 'date',
        'end_date'     => 'date',
        'num_days'     => 'float',
    ];

    public function requester()
    {
        return $this->belongsTo(Personnel::class, 'requester_id', 'personnel_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Personnel::class, 'reviewer_id', 'personnel_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_key', 'leave_type_key');
    }
}
