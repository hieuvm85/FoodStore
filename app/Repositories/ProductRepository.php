<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductRepository{
    public function saveOrUpdate(Product $product){
        $product->save();
        return $product;
    }

    public function getAll($page)
    {
        $products = Product::where('is_selling', true)
            ->leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->select(
                'products.*',
                DB::raw('COALESCE(AVG(feedback.star), 0) as star'),
                DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold')
            )
            ->groupBy('products.id');


        if(!$page)
            $data = $products->get();
        else
            $data= $products->paginate(10);

        return [
            "total" => $data['total'] ?? count($data),
            "data" => $data
            
        ];
    }
    
    public function adminGetAll($page){
        $products = Product::leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->select(
                'products.*',
                DB::raw('COALESCE(AVG(feedback.star), 0) as star'),
                DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold')
            )
            ->groupBy('products.id');

        if(!$page)
            $data = $products->get();
        else
            $data= $products->paginate(10);

        return [
            "total" => $data['total'] ?? count($data),
            "data" => $data
            
        ];
    }

    
    public function getById($id){
        $product = Product::with(['flavors','categories','characteristics','feedbacks','images'])
                            ->leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
                            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
                            ->select(
                                'products.*',
                                DB::raw('COALESCE(AVG(feedback.star), 0) as star'),
                                DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold')
                            )
                            ->where('products.id', $id) // Thêm điều kiện tìm theo id
                            ->groupBy('products.id')
                            ->first();;
        return $product;
    }
    

    public function adminSearch($keyword,$page){
        $products = Product::leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
        ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
        ->select(
            'products.*',
            DB::raw('COALESCE(AVG(feedback.star), 0) as star'),
            DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold')
        )
        ->where(function($query) use ($keyword) {
            $query->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%");
        })
        ->groupBy('products.id')
        ->orderByRaw("CASE WHEN title LIKE ? THEN 1 ELSE 2 END", ["%{$keyword}%"]);

        if(!$page)
            $data = $products->get();
        else
            $data= $products->paginate(10);

        return [
            "total" => $data['total'] ?? count($data),
            "data" => $data
            
        ];
    }

    public function searchByText($keyword,$page){
        $products = Product::where('is_selling', true)
            ->leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->select(
                'products.*',
                DB::raw('COALESCE(AVG(feedback.star), 0) as star'),
                DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold')
            )
            ->where(function($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->groupBy('products.id')
            ->orderByRaw("CASE WHEN title LIKE ? THEN 1 ELSE 2 END", ["%{$keyword}%"]);

        if(!$page)
            $data = $products->get();
        else
            $data= $products->paginate(10);

        return [
            "total" => $data['total'] ?? count($data),
            "data" => $data
            
        ];
    }

    
                                
}