<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository{
    public function saveOrUpdate(Order $order){
        $order->save();
        return $order;
    }

    public function getByid($id){
        return Order::where('id',$id)->first();
    }
    
    // public function getAll(){
    //     return Order::all();
    // }
    // public function delete($id){
    //     Order::destroy($id);
    // }

    public function adminGetAll($status){
        $orderQuery = Order::with(['user','orderDetails','orderDetails.product']);
        if(!$status){
            return $orderQuery->paginate(10);
        }                
        
        return $orderQuery->where('status',$status)->paginate(10);
    }


    public function adminGetDetail($id){
        $order = Order::with(['user','orderDetails','orderDetails.product','address','coupon'])
                        ->where('id',$id)->get();
                       
                       
        return $order;
    }


    public function userGetAll($id_user,$status){
        $orderQuery = Order::with(['user','orderDetails','orderDetails.product'])
                        ->where('user_id',$id_user);
        if(!$status){
            return $orderQuery->paginate(10);
        }                
        
        return $orderQuery->where('status',$status)->paginate(10);
    }

    public function userGetDetail($id_user,$id_order){
        $order = Order::with(['user','orderDetails','orderDetails.product','address','coupon'])
                            ->where('id',$id_order)
                            ->where('user_id',$id_user)->get();                          
                       
        return $order;
    }
}