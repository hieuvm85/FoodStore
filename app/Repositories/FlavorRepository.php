<?php

namespace App\Repositories;

use App\Models\Flavor;

class FlavorRepository{
    public function saveOrUpdate(Flavor $flavor){
        $flavor->save();
        return $flavor;
    }

    public function getFlavorByid($id){
        return Flavor::where('id',$id)->first();
    }
    
    public function getAll(){
        return Flavor::all();
    }
    public function delete($id){
        Flavor::destroy($id);
    }
}