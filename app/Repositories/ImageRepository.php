<?php

namespace App\Repositories;

use App\Models\Image;

class ImageRepository{
    public function saveOrUpdate(Image $image){
        $image->save();
        return $image;
    }

    public function getImageByid($id){
        return Image::where('id',$id)->first();
    }

    public function getAll(){
        return Image::all();
    }
    
}