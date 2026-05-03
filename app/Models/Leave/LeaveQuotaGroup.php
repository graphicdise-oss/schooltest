<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Model;

class LeaveQuotaGroup extends Model
{
    protected $table = 'leave_quota_groups';
    protected $fillable = ['years_from', 'years_to', 'is_active', 'sort_order'];

    public function quotas()
    {
        return $this->hasMany(LeaveTypeQuota::class, 'group_id')->orderBy('sort_order');
    }

    public function getLabelAttribute(): string
    {
        $to = $this->years_to !== null ? $this->years_to : '∞';
        return "{$this->years_from} - {$to} ปี";
    }
}
