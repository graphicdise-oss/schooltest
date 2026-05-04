<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Personne\Personnel;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = ['name', 'head_id', 'is_active'];

    public function head()
    {
        return $this->belongsTo(Personnel::class, 'head_id', 'personnel_id');
    }
}