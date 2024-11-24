<?php

namespace App\Repositories;

use App\Models\PaymentHistory;

class PaymentHistoryRepository{
    public function saveOrUpdate(PaymentHistory $paymentHistory){
        $paymentHistory->save();
        return $paymentHistory;
    }

    public function getImageByid($id){
        return PaymentHistory::where('id',$id)->first();
    }

    public function getAll(){
        return PaymentHistory::all();
    }
    
}