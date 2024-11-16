<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository{
    public function saveOrUpdate(Product $product){
        $product->save();
        return $product;
    }

    public function getAll(){
        $products= Product::where('is_selling',true)->paginate(10);
         return $products;
    }
    public function adminGetAll(){
        $products= Product::paginate(10);
        return $products;
    }

    
    public function getById($id){
        $product = Product::with(['flavors','categories','characteristics','feedbacks','images'])
                            ->find($id);
        return $product;
    }
    

    public function adminSearch($keyword){
        $product =  Product::where('title',"like","%{$keyword}%")
                                ->orWhere('description',"like","%{$keyword}%")
                                ->get();
        return $product;
    }

    public function searchByText($keyword){
        $product =  Product::where('is_selling',true)
                            ->where(function($query)use($keyword){
                                $query->where('title',"like","%{$keyword}%")
                                ->orWhere('description',"like","%{$keyword}%");
                            })
                            ->orderByRaw("CASE WHEN title LIKE ? THEN 1 ELSE 2 END", ["%{$keyword}%"])
                            ->get();
        return $product;
    }

    
                                
}