<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Repositories\AddressRepository;
use App\Repositories\CouponRepository;
use App\Repositories\FeedbackRepository;
use App\Repositories\OrderDetailRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    //
    private $orderRepository;
    private $orderDetailRepository;
    private $userRepository;
    private $productRepository;
    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        $this->userRepository = new UserRepository();
        $this->orderDetailRepository = new OrderDetailRepository(); 
        $this->orderRepository = new OrderRepository();

    }

    public function topProduct(Request $request){
        try{
            $start_date = $request->query('start_date');
            $end_date = $request->query('end_date');
            $num = $request->query('num') ?? 10;
            return response()->json($this->orderDetailRepository->topProduct($num,$start_date,$end_date));
            
        }
        catch(Exception $e){
            return response()->json([
                "message"=>$e->getMessage(),
            ],401);
        }
       

    }

    public function topUser(Request $request){
        try{
            $start_date = $request->query('start_date');
            $end_date = $request->query('end_date');
            $num = $request->query('num') ?? 10;
            return response()->json($this->orderDetailRepository->topUser($num,$start_date,$end_date));
            
        }
        catch(Exception $e){
            return response()->json([
                "message"=>$e->getMessage(),
            ],401);
        }
       

    }


    public function revenue(Request $request){
        try{
            $start_date = $request->query('start_date');
            $end_date = $request->query('end_date');
            $status = $request->query('status') ?? "DELEVERED";
            return response()->json($this->orderDetailRepository->revenue($status,$start_date,$end_date));
            
        }
        catch(Exception $e){
            return response()->json([
                "message"=>$e->getMessage(),
            ],401);
        }
       

    }

}
