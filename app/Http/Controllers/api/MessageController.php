<?php

namespace App\Http\Controllers\api;

use App\Events\MessageSend;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Repositories\GroupRepository;
use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    //
    private $groupRepository;
    private $userRepository;
    private $messageRepository;

    public function __construct(){
        $this->groupRepository = new GroupRepository();
        $this->userRepository = new UserRepository();
        $this->messageRepository = new MessageRepository();
    }

    public function send(Request $request){
        
        try{
            $group = $this->groupRepository->getByid($request->group_id);
            if(!$group){
                throw new Exception('Group not found');
            }

            $user = $this->userRepository->getById($request->user_id);

            if(!$user){
                throw new Exception('User not found');
            }

            $message = new Message();
            $message->content = $request->message['content'];
            $message->user_id=$user->id;
            $message->group_id=$group->id;

            $message = $this->messageRepository->saveOrUpdate($message);

            event(new MessageSend($message,$group->name));
            
            return response()->json([
                "message"=>"success",
                "data"=>$message
            ],200);

        }
        catch(Exception $e){
            
            return response()->json([
                "message"=>$e->getMessage(),
            ],401);
        }
    }
}
