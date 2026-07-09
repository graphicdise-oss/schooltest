<?php

namespace App\Models\Library;

use Illuminate\Database\Eloquent\Model;

class LibraryDamageReport extends Model
{
    protected $table = 'library_damage_reports';

    protected $fillable = ['book_id', 'loan_id', 'description', 'status', 'reported_at', 'resolved_at'];

    protected $casts = [
        'reported_at' => 'date',
        'resolved_at' => 'date',
    ];

    public function book()
    {
        return $this->belongsTo(LibraryBook::class, 'book_id');
    }

    public function loan()
    {
        return $this->belongsTo(LibraryLoan::class, 'loan_id');
    }
}
