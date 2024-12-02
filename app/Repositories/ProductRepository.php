<?php

namespace App\Repositories;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductRepository
{
    public function saveOrUpdate(Product $product)
    {
        $product->save();
        return $product;
    }



    public function adminGetAll($page)
    {
        $products = Product::leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->select(
                'products.*',
                DB::raw('COALESCE(AVG(feedback.star), 0) as star'),
                DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold')
            )
            ->groupBy('products.id');

        if (!$page) {
            $data = $products->get();
            return [
                "data" => $data,
                "total" => count($data)
            ];
        } else {
            $data = $products->paginate(20);
            return $data;
        }
    }


    public function getById($id)
    {
        $product = Product::with(['flavors', 'categories', 'characteristics', 'feedbacks','feedbacks.user', 'images'])
            ->leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->select(
                'products.*',
                DB::raw('CAST(COALESCE(AVG(feedback.star), 0) AS FLOAT) as star'),
                DB::raw('CAST(COALESCE(SUM(order_details.quantity), 0) AS FLOAT) as total_sold')
            )
            ->where('products.id', $id) // Thêm điều kiện tìm theo id
            ->groupBy('products.id')
            ->first();;
        return $product;
    }


    public function adminSearch($keyword, $page)
    {
        $products = Product::leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->select(
                'products.*',
                DB::raw('COALESCE(AVG(feedback.star), 0) as star'),
                DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold')
            )
            ->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->groupBy('products.id')
            ->orderByRaw("CASE WHEN title LIKE ? THEN 1 ELSE 2 END", ["%{$keyword}%"]);

        if (!$page) {
            $data = $products->get();
            return [
                "data" => $data,
                "total" => count($data)
            ];
        } else {
            $data = $products->paginate(20);
            return $data;
        }
    }

    public function searchByText($keyword, $page)
    {
        $products = Product::where('is_selling', true)
            ->leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->select(
                'products.*',
                DB::raw('COALESCE(AVG(feedback.star), 0) as star'),
                DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold')
            )
            ->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->groupBy('products.id')
            ->orderByRaw("CASE WHEN title LIKE ? THEN 1 ELSE 2 END", ["%{$keyword}%"]);
            
        if (!$page) {
            $data = $products->get();
            return [
                "data" => $data,
                "total" => count($data)
            ];
        } else {
            $data = $products->paginate(20);
            return $data;
        }
    }

    public function getProductByImage($imageIds)
    {
        $sortedProductIds = [];
        foreach ($imageIds as $imageId) {
            $productId = DB::table('images')->where('id', $imageId)->value('product_id');
            if ($productId && !in_array($productId, $sortedProductIds)) {
                $sortedProductIds[] = $productId;
            }
        }
        $products = [];

        foreach ($sortedProductIds as $productId) {
            if(count($products)==20){
                break;
            }
            $product = Product::leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
                ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
                ->select(
                    'products.*',
                    DB::raw('COALESCE(AVG(feedback.star), 0) as star'),
                    DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold')
                )
                ->where('products.id', $productId) // Thêm điều kiện tìm theo id
                ->where('is_selling', true)
                ->groupBy('products.id')
                ->first();
            $products[] = $product;
        }

        return [
            "total"=>20,
            "data"=>$products
        ];
    }



    public function getAll_1($page)
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

        if (!$page) {
            $data = $products->get();
            return [
                "data" => $data,
                "total" => count($data)
            ];
        } else {
            $data = $products->paginate(20);
            return $data;
        }
    }

    public function getAll_2($userViewedData, $page)
    {
        $productIds = collect($userViewedData)->pluck('product_id');

        // Tạo mảng map sản phẩm với số lần xem (view_num)
        $productViewsMap = collect($userViewedData)->keyBy('product_id')->mapWithKeys(function ($item) {
            return [$item['product_id'] => $item['view_num']];
        });

        // Lấy top 2 categories yêu thích
        $favoriteCategories = DB::table('category_product')
            ->select('category_id', DB::raw('SUM(CASE WHEN category_product.product_id IN (' . implode(',', $productIds->toArray()) . ') THEN ' . implode('+', $productViewsMap->toArray()) . ' END) as total_views'))
            ->groupBy('category_id')
            ->orderByDesc('total_views')
            ->limit(2)
            ->pluck('category_id');

        // Lấy top 2 flavors yêu thích
        $favoriteFlavors = DB::table('flavor_product')
            ->select('flavor_id', DB::raw('SUM(CASE WHEN flavor_product.product_id IN (' . implode(',', $productIds->toArray()) . ') THEN ' . implode('+', $productViewsMap->toArray()) . ' END) as total_views'))
            ->groupBy('flavor_id')
            ->orderByDesc('total_views')
            ->limit(2)
            ->pluck('flavor_id');

        // Lấy top 2 characteristics yêu thích
        $favoriteCharacteristics = DB::table('characteristic_product')
            ->select('characteristic_id', DB::raw('SUM(CASE WHEN characteristic_product.product_id IN (' . implode(',', $productIds->toArray()) . ') THEN ' . implode('+', $productViewsMap->toArray()) . ' END) as total_views'))
            ->groupBy('characteristic_id')
            ->orderByDesc('total_views')
            ->limit(2)
            ->pluck('characteristic_id');


        $products = Product::where('is_selling', true)
            ->leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->select(
                'products.*',
                DB::raw('COALESCE(AVG(feedback.star), 0) as star'),
                DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold')
            )
            ->groupBy('products.id')
            ->withCount([
                'categories as matching_categories' => function ($query) use ($favoriteCategories) {
                    $query->whereIn('categories.id', $favoriteCategories);
                },
                'flavors as matching_flavors' => function ($query) use ($favoriteFlavors) {
                    $query->whereIn('flavors.id', $favoriteFlavors);
                },
                'characteristics as matching_characteristics' => function ($query) use ($favoriteCharacteristics) {
                    $query->whereIn('characteristics.id', $favoriteCharacteristics);
                },
            ])
            ->orderByRaw('matching_categories + matching_flavors + matching_characteristics DESC');
        // ->selectRaw('products.*, 
        //     (matching_categories + matching_flavors + matching_characteristics) as relevance_score, 
        //     view_num as purchase_score') // Tính điểm sự liên quan và điểm lượt xem
        // ->orderByDesc('relevance_score') // Sắp xếp theo độ liên quan
        // ->orderByRaw('purchase_score DESC');


        if (!$page) {
            $data = $products->get();
            return [
                "data" => $data,
                "total" => count($data)
            ];
        } else {
            $data = $products->paginate(20);
            return $data;
        }
    }

    public function filter($filters,$page){
        $products = Product::where('is_selling', true)
        ->leftJoin('feedback', 'products.id', '=', 'feedback.product_id')
        ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
        ->select(
            'products.*',
            DB::raw('COALESCE(AVG(feedback.star), 0) as star'),
            DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_sold')
        )
        ->groupBy('products.id')
        ->filter($filters);

        if (!$page) {
            $data = $products->get();
            return [
                "data" => $data,
                "total" => count($data)
            ];
        } else {
            $data = $products->paginate(20);
            return $data;
        }
    }
}
