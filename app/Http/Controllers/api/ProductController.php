<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Message;
use App\Models\Product;
use App\Repositories\CategoryRepository;
use App\Repositories\CharacteristicRepository;
use App\Repositories\FlavorRepository;
use App\Repositories\ImageRepository;
use App\Repositories\ProductRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    //
    private $productRepository;
    private $flavorRepository;
    private $characteristicRepository;
    private $categoryRepository;
    private $imageRepository;
    
    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        $this->flavorRepository = new FlavorRepository();
        $this->characteristicRepository = new CharacteristicRepository();
        $this->categoryRepository = new CategoryRepository();
        $this->imageRepository = new ImageRepository();
    }

    public function create(Request $request){
        DB::beginTransaction();
        try{
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'quantity' => 'required|integer|min:0',
                'selling_price' => 'required|integer|min:0',
                'list_price' => 'required|integer|min:0',
                ''
            ]);

            $product = new Product();
            $product->title = $request->title;
            $product->description = $request->description;
            $product->quantity = $request->quantity;
            $product->selling_price = $request->selling_price;
            $product->list_price = $request->list_price;
            $product->main_image = $request->main_image;
            $this->productRepository->saveOrUpdate($product);
            
            $product->flavors()->sync($request->flavors);
            $product->characteristics()->sync($request->characteristics);
            $product->categories()->sync($request->categories);

            // luu anh chinh vao truoc
            $image= new Image();
            $image->link= $request->main_image;
            $image->product_id= $product->id;
            $this->imageRepository->saveOrUpdate($image);
            // luu cac anh phu vao sau
            foreach($request->images as $imageReq){
                $image= new Image();
                $image->link= $imageReq['link'];
                $image->product_id= $product->id;

                $this->imageRepository->saveOrUpdate($image);
            }
            DB::commit();
            return response()->json([
                'message' => 'Success'

                ],200);

        }
        catch(Exception $e){
            DB::rollback();
            return response()->json([
                'message' =>$e->getMessage()
            ],400);
        }
    }

    public function index(Request $request){

        return $this->productRepository->getAll();
    }

    public function show(Request $request){

        return $this->productRepository->getById($request->id);
    }
}
