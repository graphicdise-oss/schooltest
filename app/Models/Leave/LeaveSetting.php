<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Model;

class LeaveSetting extends Model
{
    protected $table = 'leave_settings';
    protected $fillable = ['min_approvers', 'cutoff_day', 'cutoff_month'];

    public static function getOrCreate(): self
    {
        return self::first() ?? self::create([
            'min_approvers' => 2,
            'cutoff_day'    => 1,
            'cutoff_month'  => 10,
        ]);
    }
}