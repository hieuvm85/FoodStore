<?php

namespace App\Repositories;

use App\Models\Message;

class MessageRepository{
    public function saveOrUpdate(Message $message){
        $message->save();
        return $message;
    }

    public function getByid($id){
        return Message::where('id',$id)->first();
    }
    
    public function getAll(){
        return Message::all();
    }
    public function delete($id){
        Message::destroy($id);
    }
}