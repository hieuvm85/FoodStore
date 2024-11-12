<?php

namespace App\Repositories;

use App\Models\Address;

class AddressRepository{
    public function saveOrUpdate($detail){
        
        $address = Address::where('detail', $detail)->first();
        if(!$address){
            $address= new Address();
            $address->detail= $detail;
            $address->save();
        }
        return $address;
    }

    public function getByid($id){
        return Address::where('id',$id)->first();
    }
    public function getAll(){
        return Address::all();
    }
    public function delete($id){
        Address::destroy($id);
    }
}