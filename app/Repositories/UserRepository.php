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
    }
}