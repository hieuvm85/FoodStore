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
    
}