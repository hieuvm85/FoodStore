<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository{
    public function saveOrUpdate(Product $product){
        $product->save();
        return $product;
    }

    public function getAll(){
         $products= Product::all();
         return $products;
    }

    public function getById($id){
        $product = Product::with(['flavors','categories','characteristics','feedbacks','images'])
                            ->find($id);
        return $product;
    }
    
}