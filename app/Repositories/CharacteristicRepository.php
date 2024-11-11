<?php

namespace App\Repositories;

use App\Models\Characteristic;

class CharacteristicRepository{
    public function saveOrUpdate(Characteristic $characteristic){
        $characteristic->save();
        return $characteristic;
    }

    public function getCharacteristicById($id){
        return Characteristic::where('id', $id)->first();
    }

    public function getAll(){
        return Characteristic::all();
    }
    public function delete($id){
        Characteristic::destroy($id);
    }
}