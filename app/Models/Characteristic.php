<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Characteristic extends Model
{
    use HasFactory;

    public function products()
    {
        return $this->belongsToMany(Product::class, 'characteristic_product');
    }
}
