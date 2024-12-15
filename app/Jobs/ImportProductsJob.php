<?php

namespace App\Jobs;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $products;
    private $productRepository;
    public function __construct($products)
    {
        //
        $this->products = $products;
        $this->productRepository = new ProductRepository();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        DB::beginTransaction();
        try{
            $chunks = array_chunk($this->products,80);
            foreach($chunks as $chunk){
                
                foreach($chunk as $productReq){
                    
                    $product = new Product();

                    $product->title = $productReq["title"] ;
                    $product->description = $productReq["description"];
                    $product->quantity = $productReq["quantity"];
                    $product->selling_price = $productReq["selling_price"];
                    $product->list_price = $productReq["list_price"];
                    $product->main_image = $productReq["main_image"];
                    $product->is_selling = $productReq["is_selling"];

                    $this->productRepository->saveOrUpdate($product);
                    
                    $product->flavors()->sync($productReq["flavors"]);
                    $product->characteristics()->sync($productReq["characteristics"]);
                    $product->categories()->sync($productReq["categories"]);

                    $images[] = ["link" => $productReq["main_image"]]; 
                    // luu cac anh phu vao sau
                    foreach($productReq["images"] as $imageReq){
                        $images[] = ["link" =>$imageReq["link"]];
                    }

                    $product->images()->createMany($images);
                }
            }
            DB::commit();
        }
        catch(Exception $e){
           DB::rollBack();
           echo "Error: " . $e->getMessage();
        }
    }
}
