<?php

namespace App\Repositories;

use App\Models\Coupon;
use App\Models\Product;
use Exception;

class CouponRepository{
    public function saveOrUpdate(Coupon $coupon){
        $coupon->save();
        return $coupon;
    }

    public function getByid($id){
        return Coupon::with(['orders'])->where('id',$id)->first();
    }

    public function getByCode($code){
        return Coupon::where('code',$code)->first();
    }
    
    public function getAll(){
        return Coupon::all();
    }
    public function delete($id){
        Coupon::destroy($id);
    }


    public function caculate($coupon,$carts){
        $total = 0;
        $discount = 0;
        foreach($carts as $cart){
            
            $product = Product::find($cart['product_id']);

            $total+=$cart['quantity']*$product->selling_price;
        }

        if(!$coupon){
            return [
                "discount" =>$discount,
                "amount"=>$total,
                "cpoupon_id"=>null
            ];
        }

        if($coupon->quantity<1){
            throw new Exception("Discount code has expired",400);
        }

        if($coupon->expiration_date < now()){
            throw new Exception("Coupon code has expired",400);
        }
        
        // dd($total);
        if($total < $coupon->accept_price){
            throw new Exception("Not eligible to apply coupon code",400);
        }

        $discount =1000* round($total * $coupon->ratio / 100000);

        if($discount >$coupon->max_discount){
            $discount =$coupon->max_discount;
        }

        return [
            "discount" =>$discount,
            "total" =>$total,
            "amount"=>$total-$discount,
            "cpoupon_id"=>$coupon->id
        ];

    }
}