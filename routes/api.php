<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SellerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [SellerController::class, 'register']);
Route::post('login', [SellerController::class, 'login']);

Route::group(["middleware"=>["auth:api"]],function(){
    Route::get('logout', [SellerController::class, 'logout']);
    Route::get('profile', [ProductController::class, 'profile']);
    Route::get('listProducts', [ProductController::class, 'listProducts']);
    Route::get('singleProductById/{id}', [ProductController::class, 'singleProductById']);
    Route::get('listCommentsById/{id}', [ProductController::class, 'listCommentsById']);
    Route::post('searchProduct', [ProductController::class, 'searchProduct']);
    Route::get('priceProduct/{id}', [ProductController::class, 'priceProduct']);
    Route::post('createProduct', [ProductController::class, 'createProduct']);
    Route::get('listMyProducts', [ProductController::class, 'listMyProducts']);
    Route::get('deleteProductById/{id}', [ProductController::class, 'deleteProductById']);
    Route::post('editProductById', [ProductController::class, 'editProductById']);
    Route::get('likeProductById/{id}', [ProductController::class, 'likeProductById']);
    Route::post('createCommentById', [ProductController::class, 'createCommentById']);
    
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
