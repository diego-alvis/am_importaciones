<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Sale extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sales';
    protected $fillable = [
        'vendedor_id', 
        'vendedor_name', 
        'sede', 
        'stand', 
        'metodo_pago', 
        'total', 
        'productos', 
        'fecha'
    ];
}