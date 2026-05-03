<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Model;

class LeaveTypeQuota extends Model
{
    protected $table = 'leave_type_quotas';

    protected $fillable = [
        'group_id', 'leave_type_key', 'leave_type_name', 'days_per_year', 'sort_order',
    ];
}
