<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Model;

class LeaveDeptApprover extends Model
{
    protected $table = 'leave_dept_approvers';
    protected $fillable = [
        'department_name',
        'approver_1',
        'approver_2',
        'approver_3',
        'sort_order',
    ];
}
