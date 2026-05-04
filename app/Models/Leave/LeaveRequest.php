<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Model;
use App\Models\Personne\Personnel;

class LeaveRequest extends Model
{
    protected $table = 'leave_requests';

    protected $fillable = [
        'leave_type_key', 'request_date', 'start_date', 'end_date',
        'num_days', 'leave_period',
        'requester_id', 'reviewer_id', 'status', 'reason',
        'attachment', 'note',
        'contact_house', 'contact_road', 'contact_subdistrict',
        'contact_district', 'contact_province', 'contact_phone',
        'approver1_id', 'approver1_comment', 'approver1_date',
        'approver2_id', 'approver2_comment', 'approver2_date',
    ];

    protected $casts = [
        'request_date'   => 'datetime',
        'start_date'     => 'date',
        'end_date'       => 'date',
        'approver1_date' => 'date',
        'approver2_date' => 'date',
        'num_days'       => 'float',
    ];

    public function requester()  { return $this->belongsTo(Personnel::class, 'requester_id',  'personnel_id'); }
    public function reviewer()   { return $this->belongsTo(Personnel::class, 'reviewer_id',   'personnel_id'); }
    public function approver1()  { return $this->belongsTo(Personnel::class, 'approver1_id',  'personnel_id'); }
    public function approver2()  { return $this->belongsTo(Personnel::class, 'approver2_id',  'personnel_id'); }
    public function leaveType()  { return $this->belongsTo(LeaveType::class, 'leave_type_key','leave_type_key'); }
}