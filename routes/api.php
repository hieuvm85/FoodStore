<?php

use App\Http\Controllers\api\ImageController;
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
    Route::post('login',[UserController::class, 'login']);
    Route::post('getOTP',[UserController::class, 'getOTP']);
    Route::post('resetPassword',[UserController::class, 'resetPassword']);
    Route::post('verifyOTP',[UserController::class, 'verifyOTP']);
    //
    Route::post('image/upload',[ImageController::class, 'upload']);
    //
    Route::get('products',[ProductController::class, 'index']);
    Route::get('products/{id}',[ProductController::class, 'show']);
} );

Route::group(['middleware' => 'auth:api', 'prefix' => 'v1'], function () {
    Route::get('logout', [UserController::class, 'logout']);
    Route::post('changePassword', [UserController::class, 'changePassword']);



    // route cho admin
    Route::group(['middleware' => 'admin', 'prefix' => 'admin'], function () {
        Route::group(['prefix' => 'product'], function () {
            Route::post('create', [ProductController::class, 'create']);
        });
    });
} );