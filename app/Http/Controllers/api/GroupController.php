<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Repositories\GroupRepository;
use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
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


    public function connect(Request $request){
        DB::beginTransaction();
        try{
            $phone = $request->query('phone');
            $user = $this->userRepository->getUserByEmailOrPhone($phone);
            if(!$user){
                $user = $this->userRepository->createTemporaryUser($phone);
            }

            $group= $this->groupRepository->getByName($phone);
            if(!$group){
                $group= new Group();
                $group->name=$phone;
                $group = $this->groupRepository->saveOrUpdate($group);
                $group->users()->attach($user->id);
            }

            $messages = $group->messages();

            DB::commit();
            return response()->json([
                "message"=>"success",
                "channel_name"=>$phone,
                "messages"=>$messages,
                "groups"=>$group,
                "user_id"=>$user->id
            ],200);
        }
        catch(Exception $e){
            DB::rollBack();
            return response()->json([
                "message"=>$e->getMessage(),
            ],401);
        }
    }
}
