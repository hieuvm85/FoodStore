<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function categories()
    {
        return $this->belongsToMany(Category::class,'category_product');
    }

    public function flavors()
    {
        return $this->belongsToMany(Flavor::class,'flavor_product');
    }

    public function characteristics()
    {
        return $this->belongsToMany(Characteristic::class,'characteristic_product');
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



    public function scopeFilter($query,$filters)
    {
        // Lọc theo categories (nhiều-nhiều)
        if (!empty($filters['categories']) && is_array($filters['categories'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->whereIn('categories.id', $filters['categories']);
            });
        }

        // Lọc theo characteristics (nhiều-nhiều)
        if (!empty($filters['characteristics']) && is_array($filters['characteristics'])) {
            $query->whereHas('characteristics', function ($q) use ($filters) {
                $q->whereIn('characteristics.id', $filters['characteristics']);
            });
        }

        // Lọc theo flavors (nhiều-nhiều)
        if (!empty($filters['flavors']) && is_array($filters['flavors'])) {
            $query->whereHas('flavors', function ($q) use ($filters) {
                $q->whereIn('flavors.id', $filters['flavors']);
            });
        }

        return $query;
    }
}
