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
}