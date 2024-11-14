<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Jobs\SendMailResetPaswordJob;
use App\Jobs\SendOTPJob;
use App\Mail\SendResetPasswordEmail;
use App\Models\Address;
use App\Models\User;
use App\Repositories\AddressRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    //
    private $userRepository;
    private $addressRepository;
    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->addressRepository = new AddressRepository();
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
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }


    public function verifyOTP(Request $request){

        try{
            $request->validate([
                // "email" => "required|email",
                "otp" => "required"
            ] );          
            if( $request->otp === $request->cookie("otp"))   

                return response()->json(['status' => true]);
            else
                return response()->json(['status' => false]);
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }

    public function resetPassword(Request $request){

        try{
            $request->validate([
                'username' => 'required', // Có thể là email hoặc số điện thoại
                "otp" => "required"
            ]);
            if( !$request->otp === $request->cookie("otp")){
                return response()->json([
                    'status' => 302,
                    'message' => "wrong otp"
                ]);
            }   
            
            $user = $this->userRepository->getUserByEmailOrPhone($request->username);
        
            if ( !$user ) {
                return response()->json([
                    'message' => 'user does not exist',
                ], 401);
            }

            $user->password = "foodstore247";

            $this->userRepository->saveOrUpdate($user);

            SendMailResetPaswordJob::dispatch($user->email, $user->password);

            $token = $user->createToken('UserToken')->accessToken;

            
            return response()->json([
                
                'token' =>$token,
                'role' =>$user->role
            ]);
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }

    
    public function changePassword(Request $request){

        try{
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:6', 
                'password_confirmation'=> 'required'
            ]);

            if (!($request->new_password === $request->password_confirmation)) {
                return response()->json([
                    'message' => 'The new password field confirmation does not match'
                ], 400);
            }

            
            if (!($request->current_password == Auth::user()->password)) {
                return response()->json([
                    'message' => 'Mật khẩu cũ không đúng.'
                ], 400);
            }

            $user = Auth::user();
            $user->password = $request->new_password;
            $this->userRepository->saveOrUpdate($user);

            return response()->json([
                'message' => 'Mật khẩu đã được thay đổi thành công.'
            ]);
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }

    // public function getUser(Request $request){
        
    // }


    public function setAddress(Request $request){
        try{
            $request->validate([
                "detail"=>'required|string'
            ]);

            $address = $this->addressRepository->saveOrUpdate($request->detail);

            $user = $this->userRepository->getById(Auth::user()->id);
            $user->address_id = $address->id;  // Gán address_id cho user

            $this->userRepository->saveOrUpdate($user); // Lưu lại thông tin người dùng

            return response()->json($user); 
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }

    



    public function getAll(){
        try{        
            return response()->json($this->userRepository->getAll(),200);       
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }
    

    public function getDetail(Request $request){
        try{        
            return response()->json($this->userRepository->getById($request->id),200);       
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }

    public function adminEdit(Request $request){
        try{
            $user = $this->userRepository->getById($request->id);
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = $request->password;
            $user->name = $request->name;
            $user->role = $request->role;
            $user->is_ban = $request->is_ban;
            $this->userRepository->saveOrUpdate($user);
            return response()->json([
                "message" =>"success",
                "user" =>$user
            ],200);       
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }

    public function adminSearch(Request $request){
        $data= $this->userRepository->adminSearch($request->query("keyword"));
        return response()->json($data,200);
    }



    public function register(Request $request){
        try{         


            if (!($request->password === $request->password_confirmation)) {
                return response()->json([
                    'message' => 'The password field confirmation does not match'
                ], 400);
            }

            $userF = $this->userRepository->getUserByEmailOrPhone($request->phone);
            $userE = $this->userRepository->getUserByEmailOrPhone($request->email);
            if($userF){
                throw new Exception('This phone number has been already if this is your phone, let click forget password');
            }
            if($userE){
                throw new Exception('This email has been already please enter your new email');
            }

            $user = new User();
            $user->phone= $request->phone;
            $user->name= $request->name;
            $user->password= $request->password;
            $user->email= $request->email;
            $user->is_ban= false;

            $user=$this->userRepository->saveOrUpdate($user);

            $token = $user->createToken('UserToken')->accessToken;

        
            return response()->json([
                'message' =>"success",
                'token' =>$token,
                'role' =>$user->role
            ]);     
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }


    public function getByToken(){
        try{                   
            return response()->json($this->userRepository->getById(Auth::user()->id),200);       
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }
}
