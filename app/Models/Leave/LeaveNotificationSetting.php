<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Model;

class LeaveNotificationSetting extends Model
{
    protected $table = 'leave_notification_settings';
    protected $fillable = ['notification_type', 'alert_number', 'threshold_value'];
}