<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Jobs\SendOTPJob;
use App\Models\User;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class UserController extends Controller
{
    //
    private $userRepository;
    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }


    public function login(Request $request){
        $request->validate([
            'username' => 'required', // Có thể là email hoặc số điện thoại
            'password' => 'required',
        ]);

        
        $user = $this->userRepository->getUserByEmailOrPhone($request->username);
       
        if ( !$user || !($request->password === $user->password) ) {
            return response()->json([
                'message' => 'Thông tin đăng nhập không đúng.'
            ], 401);
        }



        $token = $user->createToken('UserToken')->accessToken;

        
        return response()->json([
            
            'token' =>$token,
            'role' =>$user->role
        ]);
    }


    public function logout(){
        $user = Auth::user();

        if ($user) {
            /** 
             *@var \App\Models\User $user 
             **/
            $user->token()->revoke();

            return response()->json([
                'message' => 'Đăng xuất thành công.'
            ], 200);
        }
        
        return response()->json([
            'message' => 'Người dùng chưa đăng nhập.'
        ], 401);

    }

    public function getOTP(Request $request){
        try{
            $request->validate([
                "email" => "required|email",
            ]);
            $email = $request->email;
            $otp = Str::random(6);
            SendOTPJob::dispatch($email,$otp);
            
            $cookie= Cookie::make('otp', $otp, now()->addMinutes(1)->timestamp);

            return response()->json(['message' => 'check your mail'])->withCookie($cookie);
        }
        catch(Exception $e){
            return response()->json($e->getMessage());
        }
    }


    public function verifyOTP(Request $request){

        try{
            $request->validate([
                // "email" => "required|email",
                "otp" => "required"
            ]);
            


            if( $request->otp === $request->cookie("otp"))   

                return response()->json(['status' => true]);
            else
                return response()->json(['status' => false]);
        }
        catch(Exception $e){
            return response()->json($e->getMessage());
        }
    }
}
