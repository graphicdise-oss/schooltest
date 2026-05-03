<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Model;
use App\Models\Personne\Personnel;

class Leave extends Model
{
    protected $table = 'leaves';

    protected $fillable = [
        'personnel_id', 'leave_type_key', 'leave_type_name',
        'start_date', 'end_date', 'days_count', 'reason',
        'submitted_by', 'approved_by', 'approved_at', 'status', 'fiscal_year',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'approved_at' => 'datetime',
        'days_count'  => 'decimal:1',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id', 'personnel_id');
    }

    public function submitter()
    {
        return $this->belongsTo(Personnel::class, 'submitted_by', 'personnel_id');
    }

    public function approver()
    {
        return $this->belongsTo(Personnel::class, 'approved_by', 'personnel_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
