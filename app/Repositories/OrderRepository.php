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
    
    public function getAll(){
        return Order::all();
    }
    public function delete($id){
        Order::destroy($id);
    }
}