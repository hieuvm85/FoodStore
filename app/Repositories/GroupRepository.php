<?php

namespace App\Repositories;

use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        return Group::with('messages')
                ->where('name',$name)->first();
    }


    public function adminGetGroup($adminId){
        $groups = DB::table('groups')
            ->join('messages', 'groups.id', '=', 'messages.group_id')
            ->leftJoin('group_user', function ($join) use ($adminId) {
                $join->on('groups.id', '=', 'group_user.group_id')
                    ->where('group_user.user_id', '=', $adminId);
            })
            ->select(
                'groups.id as group_id',
                'groups.name',
                'group_user.left_at',
                DB::raw('MAX(messages.id) as last_message_id')
            )
            ->groupBy('groups.id', 'groups.name', 'group_user.left_at')
            ->havingRaw('COUNT(messages.id) > 0')
            ->get();

        // Lấy thông tin tin nhắn cuối cùng và kiểm tra trạng thái đã đọc
        $groupsWithLastMessage = $groups->map(function ($group) use ($adminId) {
            $lastMessage = DB::table('messages')
                ->where('id', $group->last_message_id)
                ->first();

            // Kiểm tra trạng thái `unread` dựa trên `left_at` và `created_at` của tin nhắn cuối cùng
            $unread = false;
            if (is_null($group->left_at)) {
                // Người dùng chưa rời nhóm, mặc định là chưa đọc
                $unread = true;
            } elseif ($group->left_at < $lastMessage->created_at) {
                // Người dùng rời nhóm trước khi tin nhắn cuối cùng được tạo, coi như chưa đọc
                $unread = true;
            }

            return [
                'group_id' => $group->group_id,
                'name' => $group->name,
                'last_message' => $lastMessage,
                'unread' => $unread,
            ];
        });

        // Sắp xếp các nhóm theo thời gian tạo của tin nhắn cuối cùng (giảm dần)
        $sortedGroups = $groupsWithLastMessage->sortByDesc(function ($group) {
            return $group['last_message']->created_at ?? null;
        })->values();

        return $sortedGroups;

    }

    public function left_at($adminId,$groupId){
        DB::table('group_user')
        ->where('user_id', $adminId)
        ->where('group_id', $groupId)
        ->update(['left_at' => Carbon::now()]);

        return true;
    }


    
}