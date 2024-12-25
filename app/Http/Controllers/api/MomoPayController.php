<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\PaymentHistory;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentHistoryRepository;
use Exception;
use Illuminate\Http\Request;

class MomoPayController extends Controller
{
    //
    private $orderRepository;
    private $paymentHistoryRepository;
    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->paymentHistoryRepository = new PaymentHistoryRepository();
    }
    public function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }
    public function atm_momo_payment($amountRe,$order_id)//$amountRe,$order_id
    {
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";


        $partnerCode = 'MOMOBKUN20180529';
        $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

        $orderInfo = $order_id;// cai nay la noi dung cmt 
        $amount = $amountRe;
        // $orderId = time() . "";
        $orderId = time() . "_" . $order_id;
        // $orderInfo = "212";// cai nay la noi dung cmt 
        // $amount = 10000;
        // $orderId = time() . "";

        $redirectUrl = "https://ecommerce-fe-psi.vercel.app";// redirect ve trang order
        $ipnUrl = "http://13.54.3.150/api/auth/payment/momo/updateSatusOrder";// callback
        $extraData = "";


        $requestId = time() . "";
        $requestType = "payWithATM";//captureWallet,payWithATM
        // $extraData = ($_POST["extraData"] ? $_POST["extraData"] : "");
        //before sign HMAC SHA256 signature
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        $data = array(
            'partnerCode' => $partnerCode,
            'partnerName' => "Test",
            "storeId" => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );
        $result = $this->execPostRequest($endpoint, json_encode($data));

        $jsonResult = json_decode($result, true);  // decode json

        // return response()->json([
        //     // 'link'=>$jsonResult['payUrl']
        //     'data'=>$jsonResult
        // ]) ;
        return $jsonResult;
        //Just a example, please check more in there

        // header('Location: ' . $jsonResult['payUrl']);
        
    }
    public function qr_momo_payment($amount,$order_id)
    {
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";


        $partnerCode = 'MOMOBKUN20180529';
        $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

        $orderInfo = $order_id;// cai nay la noi dung cmt 
        $amount = $amount;
        // $orderId = time() . "";
        $orderId = time() . "_" . $order_id;
        $redirectUrl = "https://ecommerce-fe-psi.vercel.app";// redirect ve trang order
        $ipnUrl = "http://13.54.3.150/api/auth/payment/momo/updateSatusOrder";// callback
        $extraData = "";


        $requestId = time() . "";
        $requestType = "captureWallet";//captureWallet,payWithATM
        // $extraData = ($_POST["extraData"] ? $_POST["extraData"] : "");
        //before sign HMAC SHA256 signature
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        $data = array(
            'partnerCode' => $partnerCode,
            'partnerName' => "Test",
            "storeId" => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );
        $result = $this->execPostRequest($endpoint, json_encode($data));

        $jsonResult = json_decode($result, true);  // decode json

        // return response()->json([
        //     // 'link'=>$jsonResult['payUrl']
        //     'data'=>$jsonResult
        // ]) ;
        return $jsonResult;
        //Just a example, please check more in there

        // header('Location: ' . $jsonResult['payUrl']);
        
    }
    
    // public function redirect()
    // {
    //     return response()->json([
    //         'status'=>"ok"
    //     ]);
    // }
    public function updateSatusOrder(Request $request)
    {
        try{
            $paymentHistory = new PaymentHistory();
            $paymentHistory->order_id = $request->orderInfo;
            $paymentHistory->amount = $request->amount;
            $paymentHistory->message = $request->message;
            $paymentHistory->result_code = $request->resultCode;
    
            $paymentHistory = $this->paymentHistoryRepository->saveOrUpdate($paymentHistory);
            $order = $this->orderRepository->getByid($request->orderInfo);
            if(!$order){
                return response()->json([
                    "message" =>"order not found",
                ],400);
            }

            if($request->resultCode == 0){
                $order->status_pay= "Paid";
            }
            else{
                $order->status_pay= "Payment failed";
                $order->status = "CANCEL";
            }
            $this->orderRepository->saveOrUpdate($order);

            return response()->json([
                'status'=>"update order ".$request->id
            ]);
        }
        catch (Exception $e){
           
            return response()->json([
                "message" =>$e->getMessage()
            ],400);
        }
    }
}
