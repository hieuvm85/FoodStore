<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Repositories\FeedbackRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    //
    private $feedbackRepository;
    private $userRepository;
    private $productRepository;
    public function __construct()
    {
        $this->feedbackRepository = new FeedbackRepository();
        $this->userRepository = new UserRepository();
        $this->productRepository = new ProductRepository();
    }

    public function create(Request $request){
        DB::beginTransaction();
        try{
            $request->validate([
                'star' => 'integer',
                'content' => 'required|string',
                'product_id' => 'required|integer',
                'username' => 'required|string'
                
            ]);
    
            $feedback = new Feedback();
            $feedback->star= $request->star;
            $feedback->content= $request->content;
            $feedback->product_id= $request->product_id;
            $user =  $this->userRepository->getUserByEmailOrPhone($request->username);
            if(!$user){
                $user = $this->userRepository->createTemporaryUser($request->username);
            }
            
            $feedback->user_id= $user->id;
            
            $feedback = $this->feedbackRepository->saveOrUpdate($feedback);
    
            DB::commit();
            return response()->json($feedback,200);
        }
        catch(Exception $e){
            DB::rollback();
            return response()->json([
                "message"=>$e->getMessage(),
            ],401);
        }
        
    }


    public function delete(Request $request){
        $feedback =   $this->feedbackRepository->getByById($request->id);
        $user = Auth::user();
        if( !($user->role=='admin' || $user->id==$feedback->user_id) ){
            return response()->json([
                "message"=>"False: you do not have the right to edit"
            ],200);
        }

        $this->feedbackRepository->delete($request->id);

        return response()->json([
            "message"=>"Success"
        ],200);
    }
    public function edit(Request $request){
        $feedback =   $this->feedbackRepository->getByById($request->id);
        $user = Auth::user();
        if( !($user->role=='admin' || $user->id==$feedback->user_id) ){
            return response()->json([
                "message"=>"False: you do not have the right to edit"
            ],200);
        }

        $feedback->star=$request->star;
        $feedback->content=$request->content;

        

        return response()->json($this->feedbackRepository->saveOrUpdate($feedback),200);
    }
}
