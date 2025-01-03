<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository{
    public function getUserByEmailOrPhone($username){
        $loginField = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        // dd($loginField);
        $user = User::where($loginField, $username)->first();
        return $user;
    }

    public function saveOrUpdate(User $user){
        $user->save();

        return User::with(['address'])->find($user->id);
    }

    public function getAll(){
        return User::all();
    }


    public function createTemporaryUser($username){
        $loginField = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user= new User();
        if($loginField == 'email'){
            $user->email = $username;
            $user->phone = "0000000000";
        }
        else{
            $user->phone=$username;
            $user->email = $username."@gmail.com";
        }
        $user->name="temprorary";
        $user->password="foodstore247";
        $user->save();

        return $user;
    }

    public function getById($id){
        return User::with(['address','orders'])->find($id);
    }

    public function delete($id){
        return User::destroy($id);
    }

    public function getAdmins(){
        return User::where('role', 'admin')->get();
    }

    public function adminSearch($keyword){
        $user =  User::where('name',"like",'%{$keyword}%')
                                ->orWhere('phone',"like","%{$keyword}%")
                                ->orWhere('email',"like","%{$keyword}%")
                                ->get();
        return $user;
    }

}