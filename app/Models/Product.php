<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'products';

    protected $fillable = [
        'sku', 
        'name', 
        'category', 
        'purchase_price', 
        'sale_price', 
        'stock_plaza_norte', 
        'stock_la_muela', 
        'stock_av_54'
    ];
}