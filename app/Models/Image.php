<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ['link', 'product_id'];
    
    use HasFactory;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
