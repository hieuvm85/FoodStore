<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Repositories\AddressRepository;
use App\Repositories\CouponRepository;
use App\Repositories\FeedbackRepository;
use App\Repositories\OrderDetailRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //
    private $orderRepository;
    private $orderDetailRepository;
    private $couponRepository;
    private $userRepository;
    private $addressRepository;
    private $productRepository;
    private $feedbackRepository;
    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        $this->userRepository = new UserRepository();
        $this->addressRepository = new AddressRepository();
        $this->couponRepository = new CouponRepository();
        $this->orderDetailRepository = new OrderDetailRepository(); 
        $this->orderRepository = new OrderRepository();
        $this->feedbackRepository = new FeedbackRepository();
    }


    public function create(Request $request){
        DB::beginTransaction();
        try{
            $request->validate([
                'amount' => 'required',
                'payment_option' => 'required|string',
                'discount' => 'required',
            ]);

            $order= new Order();

            // xu ly tien va ma giam gia
            $caculate = [];
            if($request->coupon_id){
                $coupon =$this->couponRepository->getByid($request->coupon_id);
                $caculate= $this->couponRepository->caculate($coupon,$request->carts);
                $order->coupon_id = $request->coupon_id;
            }
            else{
                $caculate= $this->couponRepository->caculate(null,$request->carts);
            }
            $order->amount= $caculate['amount'];
            $order->note= $request->note;
            $order->payment_option= $request->payment_option;
            $order->discount= $caculate['discount'];
            $order->status = "WAIT_CONFIRM";

            //xu li address
            $address = $this->addressRepository->saveOrUpdate($request->address['detail']);
            $order->address_id = $address->id;



            //xu li user
            $user = $this->userRepository->getUserByEmailOrPhone($request->user['phone']);
            $userE = $this->userRepository->getUserByEmailOrPhone($request->user['email']);
                
            if(!$user){   
                // new khong ton tai user nao thi tao moi
                if(!$userE){
                    $user= new User();                  
                    $user->email= $request->user['email'];                 
                }
                // new khong ton tai user ma co user dung Email thi them thong tin vao user do
                else {
                    $user= $userE;
                }
                $user->phone= $request->user['phone'];
                $user->name= $request->user['name'];
                $user->password= "foodstore247";
                $user->address_id=$address->id;
                $user = $this->userRepository->saveOrUpdate($user);
            }
            else{
                // new ton tai user dung phone ma khong co user dung Email thi them thong tin vao user do
                if(!$userE){
                    $user->email= $request->user['email'];
                    $user->name= $request->user['name'];
                    $user->password= "foodstore247";
                    $user->address_id=$address->id;
                }
                // neu ton tai ca 2 user ma 2 user la 2 user kahc nhau phai gop lai
                else{
                    if($user->id != $userE->id && $user->email =="temprorary@gmail.com"){
                        foreach($userE->feedbacks as $feedback){
                            $feedback->user_id=$user->id;
                            $this->feedbackRepository->saveOrUpdate($feedback);
                        }
                        $this->userRepository->delete($userE->id);
                    }
                    // new la 1 user thi khong lam gi ca
                }
            }
            $order->user_id=$user->id;
            $order = $this->orderRepository->saveOrUpdate($order);
            //xu li orderDetails
            foreach ($request->carts as $cart){
                $product = $this->productRepository->getById($cart['product_id']);
                $product->quantity -= $cart['quantity'];
                if($product->quantity<0){
                    throw new Exception("Insufficient product quantity",400);
                }
                
                $this->productRepository->saveOrUpdate($product);

                $orderDetail= new OrderDetail();
                $orderDetail->quantity= $cart['quantity'];
                $orderDetail->price = $product->selling_price;
                $orderDetail->product_id = $product->id;
                $orderDetail->user_id = $user->id;
                $orderDetail->order_id= $order->id;
                

                $this->orderDetailRepository->saveOrUpdate($orderDetail);
            }
            DB::commit();
            return response()->json([
                "message" => "orderSuccess"
            ],200);
        }
        catch (Exception $e){
            DB::rollBack();
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }



    public function adminGetAll(Request $request){
        try{
            $status= $request->query('status');
            // WAIT_CONFIRM, PREPARING, DELIVERING, DELIVERED, RETURN , CANCEL
            if(!($status == null ||$status == '' ||$status == 'WAIT_CONFIRM' || $status == 'PREPARING' || $status == 'DELIVERING'|| $status == 'DELIVERED'|| $status == 'RETURN'|| $status == 'CANCEL')){
                throw new Exception("Invalid status");
            }
           $orders = $this->orderRepository->adminGetAll($status);
           return response()->json($orders,200);
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }





    public function adminGetDetail(Request $request){
        try{
            $orders = $this->orderRepository->adminGetDetail($request->id);
            return response()->json($orders,200);
         }
         catch (Exception $e){
             return response()->json([
                 "message" =>$e->getMessage()
             ],400);
        }
    }
    public function adminEditStatus(Request $request){
        try{
            $status= $request->query('status');
            $order = $this->orderRepository->getByid($request->id);

            // WAIT_CONFIRM, PREPARING, DELIVERING, DELIVERED, RETURN , CANCEL
            if(!($status == 'WAIT_CONFIRM' || $status == 'PREPARING' || $status == 'DELIVERING'|| $status == 'DELIVERED'|| $status == 'RETURN'|| $status == 'CANCEL')){
                throw new Exception("Invalid status");
            }

            $order->status = $status;
            $this->orderRepository->saveOrUpdate($order);

            return response()->json([
                "message" =>"Success",
                "order" =>$order
            ]);

        }
        
        catch (Exception $e){
 
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }
    
    public function userCancel(Request $request){
        try{
            $order = $this->orderRepository->getByid($request->id);
            if($order->user_id != Auth::user()->id ){
                return response()->json([
                    "message" =>"You are not allowed"
                ],309); 
            }
            if($order->status == "WAIT_CONFIRM" || $order->status == "PREPARING" ){
                $order->status = "CANCEL";
                $this->orderRepository->saveOrUpdate($order);
                return response()->json([
                    "message" =>"Cancel successfully"
                ],200); 
            }

            return response()->json([
                "message" =>"Items in transit cannot be cancelled"
            ],400); 


            return response()->json([
                "message" =>"Cancel Success"
            ]);
        }
        catch (Exception $e){
 
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }


    public function adminSearch(){

    }

    public function userGetAll(Request $request){
        try{
            $status= $request->query('status');
            // WAIT_CONFIRM, PREPARING, DELIVERING, DELIVERED, RETURN , CANCEL
            if(!($status == null ||$status == '' ||$status == 'WAIT_CONFIRM' || $status == 'PREPARING' || $status == 'DELIVERING'|| $status == 'DELIVERED'|| $status == 'RETURN'|| $status == 'CANCEL')){
                throw new Exception("Invalid status");
            }
           $orders = $this->orderRepository->userGetAll(Auth::user()->id,$status);
           return response()->json($orders,200);
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }

    
    public function userGetDetail(Request $request){
        try{
           $orders = $this->orderRepository->userGetDetail(Auth::user()->id,$request->id);
           return response()->json($orders,200);
        }
        catch (Exception $e){
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }
}
