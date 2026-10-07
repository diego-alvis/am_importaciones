<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role',     // admin, encargado, vendedor
        'branch',   // plaza_norte, la_muela, av_54
        'stand',    // MI-7, J-14, etc.
        'pin'       // PIN de 4 dígitos para transacciones
    ];

    protected $hidden = [
        'password', 
        'remember_token', 
        'pin',
    ];
}