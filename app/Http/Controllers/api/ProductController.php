<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Jobs\TrainingJob;
use App\Models\Category;
use App\Models\Characteristic;
use App\Models\Flavor;
use App\Models\Product;
use App\Repositories\CategoryRepository;
use App\Repositories\CharacteristicRepository;
use App\Repositories\FlavorRepository;
use App\Repositories\ImageRepository;
use App\Repositories\ProductRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
        $product->is_selling = $request->is_selling ;

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

    // search 
    public function searchByText(Request $request){
        $page = $request->query('page');
        $data= $this->productRepository->searchByText($request->query("keyword"),$page);
        return response()->json($data,200);
    }
    public function searchByImage(Request $request){
        try{
            $url = "https://foodstoresbibe-production.up.railway.app/search";
            $file = $request->file('image');

            $response = Http::attach(
                'file', // Tên của field file trong FormData
                file_get_contents($file->getRealPath()), // Nội dung file
                $file->getClientOriginalName() // Tên file
            )->post($url, [
                'other_field' => 'value', // Các trường khác (nếu có)
            ]);

            
            if ($response->successful()) {
                $data = $response->json();
                $data = $this->productRepository->getProductByImage($data['images']);

                return response()->json($data);
            } else {
                return response()->json(['message' => 'Lỗi khi gửi file', 'error' => $response->body()], 500);
            }

        }
         catch(Exception $e){
            return response()->json([
                "message"=>$e->getMessage(),
            ],401);
        }
    }
    public function adminSearch(Request $request){
        $page = $request->query('page');
        $data= $this->productRepository->adminSearch($request->query("keyword"),$page);
        return response()->json($data,200);
    }
    public function adminGetAll(Request $request){
        $page = $request->query('page');
        $data= $this->productRepository->adminGetAll($page);
        return response()->json($data,200);
    }
    public function training(){
        try{
            // $url = "http://127.0.0.1:5000/train";
            // $response=Http::get($url);
            TrainingJob::dispatch();
            return response()->json([
                "products"=>"Success! training will be gone in a few minutes",     
                // "data"=>$response->json()
            ]);

        }
         catch(Exception $e){
            return response()->json([
                "message"=>$e->getMessage(),
            ],401);
        }
    }

    //update sau
    public function index(Request $request)
    {
        $page = $request->query('page');
        $view_products = $request->view_products;
        if(!$view_products){
            return $this->productRepository->getAll_1($page);
        }
        return $this->productRepository->getAll_2($view_products,$page);
    }

    public function getALL(Request $request)
    {
        $page = $request->query('page');
        return $this->productRepository->getAll_1($page);
    }


}
