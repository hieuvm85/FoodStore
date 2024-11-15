<?php

namespace App\Repositories;

use App\Models\Group;

class GroupRepository{
    public function saveOrUpdate(Group $group){
        $group->save();
        return $group;
    }

    public function getByid($id){
        return Group::where('id',$id)->first();
    }
    
    public function getAll(){
        return Group::all();
    }
    public function delete($id){
        Group::destroy($id);
    }

    public function getByName($name){
        return Group::where('name',$name)->first();
    }
}