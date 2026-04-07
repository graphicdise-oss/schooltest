<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'personnels';
    protected $primaryKey = 'personnel_id';

    protected $fillable = [
        'employee_code', 'password', 'thai_firstname', 'thai_lastname',
        'email', 'role', 'department', 'status'
    ];

    protected $hidden = [
        'password',
    ];
}