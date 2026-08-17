<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $table = 'contact_messages';

    protected $fillable = ['personnel_id', 'name', 'email', 'subject', 'message', 'email_sent'];

    protected $casts = [
        'email_sent' => 'boolean',
    ];

    public function personnel()
    {
        return $this->belongsTo(\App\Models\Personne\Personnel::class, 'personnel_id', 'personnel_id');
    }
}
