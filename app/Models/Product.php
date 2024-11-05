<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function categories()
    {
        return $this->belongsToMany(Address::class,'category_product');
    }

    public function flavors()
    {
        return $this->belongsToMany(Address::class,'flavor_product');
    }

    public function characteristics()
    {
        return $this->belongsToMany(Address::class,'characteristic_product');
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
