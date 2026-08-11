<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;

class BehaviorItem extends Model
{
    protected $table = 'behavior_items';
    protected $primaryKey = 'behavior_item_id';

    protected $fillable = ['type', 'name_th', 'name_en', 'points', 'sort_order'];

    protected $casts = ['points' => 'decimal:2'];
}
