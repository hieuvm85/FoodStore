<?php

namespace App\Repositories;

use App\Models\OrderDetail;

class OrderDetailRepository{
    public function saveOrUpdate(OrderDetail $orderDetail){
        $orderDetail->save();
        return $orderDetail;
    }

    public function getByid($id){
        return OrderDetail::where('id',$id)->first();
    }
    
    public function getAll(){
        return OrderDetail::all();
    }
    public function delete($id){
        OrderDetail::destroy($id);
    }
}