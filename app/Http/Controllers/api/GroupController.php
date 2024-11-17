<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Repositories\GroupRepository;
use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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


                $admins = $this->userRepository->getAdmins();
                foreach($admins as $admin){
                    $group->users()->attach($admin->id);
                }
            }

            $messages = $group->messages();

            DB::commit();
            return response()->json([
                "message"=>"success",
                "channel_name"=>$phone,
                "event_name"=>"sendMessage",
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


    public function adminGetGroup(Request $request){
        try{
            $admin_id = Auth::user()->id;
            $groups  = $this->groupRepository->adminGetGroup($admin_id);
            return response()->json([
                "groups"=>$groups,
                "channel_name"=>"adminChat",
                "event_name"=>"adminChat"
            ],401); 
        }
        catch(Exception $e){
            DB::rollBack();
            return response()->json([
                "message"=>$e->getMessage(),
            ],401);
        }
    }

    public function left_at(Request $request){
        try{
            $group_id = $request->id;
            $admin_id = Auth::user()->id;
    
            $this->groupRepository->left_at($admin_id,$group_id);
            return response()->json([
                "message"=>"success",
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
