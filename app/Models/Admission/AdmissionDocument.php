<?php

namespace App\Models\Admission;

use Illuminate\Database\Eloquent\Model;
use App\Models\Academic\Level;

class AdmissionDocument extends Model
{
    protected $table = 'admission_documents';

    protected $fillable = ['level_id', 'title', 'file_path'];

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'level_id');
    }
}
