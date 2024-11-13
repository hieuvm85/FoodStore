<?php

use App\Http\Controllers\api\CouponController;
use App\Http\Controllers\api\FeedbackController;
use App\Http\Controllers\api\ImageController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\ProductController;
use App\Http\Controllers\api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group(['prefix'=> 'auth'],function(){
    //image
    Route::post('image/upload',[ImageController::class, 'upload']);
    Route::get('image/{filename}', [ImageController::class, 'getImage']);
    //
    Route::post('login',[UserController::class, 'login']);
    Route::post('register',[UserController::class, 'geregistertOTP']);


    Route::post('getOTP',[UserController::class, 'getOTP']);
    Route::post('resetPassword',[UserController::class, 'resetPassword']);
    Route::post('verifyOTP',[UserController::class, 'verifyOTP']);
    //
    
    //
    Route::get('products',[ProductController::class, 'index']);
    Route::get('products/{id}',[ProductController::class, 'show']);
    Route::get('search/text',[ProductController::class, 'searchByText']);
    //ccf
    Route::get('flavor/getAll', [ProductController::class, 'getAllFlavor']);
    Route::get('category/getAll', [ProductController::class, 'getAllCategory']);
    Route::get('characteristic/getAll', [ProductController::class, 'getAllCharacteristic']);
    //feedback
    Route::post('feedback/create',[FeedbackController::class, 'create']);
    //order
    Route::post('order/create',[OrderController::class, 'create']);    
    //coupon
    Route::post('coupon/check',[CouponController::class, 'check']);    
} );

Route::group(['middleware' => 'auth:api', 'prefix' => 'v1'], function () {
    //order
    Route::get('order/getAll', [OrderController::class, 'userGetAll']);
    Route::get('order/get/{id}', [OrderController::class, 'userGetDetail']);
    Route::patch('order/{id}/cancel', [OrderController::class, 'userCancel']);

    Route::get('logout', [UserController::class, 'logout']);
    Route::post('changePassword', [UserController::class, 'changePassword']);
    //feedback
    Route::delete('feedback/{id}', [FeedbackController::class, 'delete']);
    Route::put('feedback', [FeedbackController::class, 'edit']);
    //address
    Route::post('address', [UserController::class, 'setAddress']);

    // route cho admin
    Route::group(['middleware' => 'admin', 'prefix' => 'admin'], function () {
        Route::group(['prefix' => 'product'], function () {
            Route::post('create', [ProductController::class, 'create']);
            Route::put('update', [ProductController::class, 'update']);
            Route::get('getAll', [ProductController::class, 'adminGetAll']);

            Route::get('search', [ProductController::class, 'adminSearch']);
        });

        Route::group(['prefix' => 'ccf'], function () {
            Route::post('flavor/create', [ProductController::class, 'createFlavor']);            
            Route::delete('flavor/{id}', [ProductController::class, 'deleteFlavor']);

            Route::post('category/create', [ProductController::class, 'createCategory']);            
            Route::delete('category/{id}', [ProductController::class, 'deleteCategory']);

            Route::post('characteristic/create', [ProductController::class, 'createCharacteristic']);            
            Route::delete('characteristic/{id}', [ProductController::class, 'deleteCharacteristic']);
        });

        Route::group(['prefix' => 'coupon'], function () {
            Route::post('create', [CouponController::class, 'create']);  
            Route::put('edit', [CouponController::class, 'edit']); 
            Route::get('getAll', [CouponController::class, 'getAll']);
            Route::get('get/{id}', [CouponController::class, 'show']);
        });

        Route::group(['prefix' => 'user'], function () {
            Route::get('getAll', [UserController::class, 'getAll']);
            Route::get('get/{id}', [UserController::class, 'getDetail']);
            Route::put('edit', [UserController::class, 'adminEdit']);

            Route::get('search', [UserController::class, 'adminSearch']);
        });

        Route::group(['prefix' => 'order'], function () {
            Route::get('getAll', [OrderController::class, 'adminGetAll']);
            Route::get('get/{id}', [OrderController::class, 'adminGetDetail']);
            Route::put('{id}/edit/status', [OrderController::class, 'adminEditStatus']);
            // Route::get('search', [OrderController::class, 'adminSearch']);
        });
    });
} );