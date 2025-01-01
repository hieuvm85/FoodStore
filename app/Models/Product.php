<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
    public function scopeWithAverageStarAndTotalSold(Builder $query)
    {
        $query->leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->select(
                'products.*',
                DB::raw('CAST(COALESCE(AVG(feedback.star), 0) AS FLOAT) as star'),
                DB::raw('CAST(COALESCE(SUM(order_details.quantity), 0) AS FLOAT) as total_sold')
            );
        return $query;
    }
    
}
