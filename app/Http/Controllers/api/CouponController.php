<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Repositories\CouponRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    //
    private $couponRepository;
    public function __construct(){
        $this->couponRepository = new CouponRepository();
    }

    public function create(Request $request){
        try{
            $request->validate([
                'quantity' => 'required|integer|min:1',
                'code' => 'required|string|unique:coupons,code|max:255',
                'accept_price' => 'required|integer|min:0',
                'ratio' => 'required|integer|min:0|max:100',
                'max_discount' => 'required|integer|min:0',
                'expiration_date' => 'required|date'
            ]);
    
            $coupon = new Coupon();
            $coupon->quantity = $request->quantity;
            $coupon->code = $request->code;
            $coupon->accept_price = $request->accept_price;
            $coupon->ratio = $request->ratio;
            $coupon->max_discount = $request->max_discount;
            $coupon->expiration_date = $request->expiration_date;
    
            return response()->json($this->couponRepository->saveOrUpdate($coupon),200);
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }

    public function edit(Request $request){
        try{
            $request->validate([
                'quantity' => 'required|integer|min:0',
                'expiration_date' => 'required|date'
            ]);
    
            $coupon = $this->couponRepository->getByid($request->id);
            $coupon->quantity = $request->quantity;
            $coupon->expiration_date = $request->expiration_date;
    
            return response()->json($this->couponRepository->saveOrUpdate($coupon),200);
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }

    public function getAll(){
        try{
            return response()->json($this->couponRepository->getAll(),200);
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }

    public function show(Request $request){
        try{
            return response()->json($this->couponRepository->getByid($request->id),200);
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }

    public function check(Request $request){
        try{
            $coupon = $this->couponRepository->getByCode($request->code);
            if(!$coupon){
                throw new Exception("Coupon not found",400);
            }
            $data=$this->couponRepository->caculate($coupon,$request->carts);
            return response()->json([
                "data" => $data,
                "message" =>"success",
            ],200);
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }
    public function test(){
        return response()->json("ok");
    }
}
