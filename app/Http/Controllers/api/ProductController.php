<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Characteristic;
use App\Models\Flavor;
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

            $product = $this->convertProduct($request, $product);



            $images[] = ['link' => $request->main_image]; 
            // luu cac anh phu vao sau
            foreach($request->images as $imageReq){
                $images[] = ['link' =>$imageReq['link']];
            }

            $product->images()->createMany($images);

            DB::commit();
            return response()->json([
                'product' => $product

            ],200);

        }
        catch(Exception $e){
            DB::rollback();
            return response()->json([
                'message' =>$e->getMessage()
            ],400);
        }
    }
    
    //update sau
    public function index(){

        return $this->productRepository->getAll();
    }

    public function show(Request $request){
        return $this->productRepository->getById($request->id);
    }

    

    public function update(Request $request){
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

            $product = $this->productRepository->getById($request->id);

            $product = $this->convertProduct($request, $product);

            

            DB::commit();
            return response()->json([
                'product' => $product
            ],200);

        }
        catch(Exception $e){
            DB::rollback();
            return response()->json([
                'message' =>$e->getMessage()
            ],400);
        }
    }
    // ham phuc vu cho create va update
    public function convertProduct($request,$product){

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

        return $product;
    }



    // Flavor Management
    public function getAllFlavor(){
        return $this->flavorRepository->getAll();
    }
    public function createFlavor(Request $request){
        $flavor = new Flavor();
        $flavor->title =   $request->title;
        return response()->json(
            $this->flavorRepository->saveOrUpdate($flavor),200);
         
    }
    public function deleteFlavor(Request $request){
        $this->flavorRepository->delete($request->id);
        return response()->json([
            'message' => "Success"  

        ],200);
    }

    // Category Management
    public function getAllCategory(){
        return $this->categoryRepository->getAll();
    }
    public function createCategory(Request $request){
        $category = new Category();
        $category->title =   $request->title;
        return response()->json(
            $this->categoryRepository->saveOrUpdate($category),200);
         
    }
    public function deleteCategory(Request $request){
        $this->categoryRepository->delete($request->id);
        return response()->json([
            'message' => "Success"  

        ],200);
    }

    // Characteristic Management
    public function getAllCharacteristic(){
        return $this->characteristicRepository->getAll();
    }
    public function createCharacteristic(Request $request){
        $characteristic = new Characteristic();
        $characteristic->title =   $request->title;
        return response()->json(
            $this->characteristicRepository->saveOrUpdate($characteristic),200);
         
    }
    public function deleteCharacteristic(Request $request){
        $this->characteristicRepository->delete($request->id);
        return response()->json([
            'message' => "Success"  

        ],200);
    }
}