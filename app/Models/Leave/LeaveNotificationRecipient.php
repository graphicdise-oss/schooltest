<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Model;

class LeaveNotificationRecipient extends Model
{
    protected $table = 'leave_notification_recipients';
    protected $fillable = ['position_name', 'personnel_name', 'personnel_id'];
}
