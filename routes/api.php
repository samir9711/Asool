<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\AuthController;
use App\Http\Controllers\Mobile\ProductOfferController;
use App\Http\Controllers\Mobile\CategoryController;
use App\Http\Controllers\Mobile\ShopController;
use App\Http\Controllers\Mobile\ProductController;
use App\Http\Controllers\Mobile\SearchController;
use App\Http\Controllers\Mobile\OrderController;
use App\Http\Controllers\Mobile\WorkUserController;
use App\Http\Controllers\Mobile\OrderSittingController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Auth


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
    Route::post('verify', [AuthController::class, 'verify']);
    Route::post('password/email', [AuthController::class, 'requestPasswordReset']);
    Route::post('password/reset', [AuthController::class, 'resetPassword']);
});

Route::get('/offers', [ProductOfferController::class, 'index']);
Route::get('/offers/{id}', [ProductOfferController::class, 'show']);


Route::get('/categories/interested', action: [CategoryController::class, 'getInterestedCategories']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/shops/{shopId}/categories', [CategoryController::class, 'getCategoriesByShop']);
Route::get('/categories/{categoryId}/products', [CategoryController::class, 'getProductsByCategory']);


Route::get('/shops/interested', [ShopController::class, 'getInterestedShops']);
Route::get('/shops/{id}', [ShopController::class, 'show']);
Route::get('/shops', [ShopController::class, 'index']);



Route::get('/products/hot', [ProductController::class, 'getHotProducts']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/shops/{shopId}/categories/{categoryId}/products', [ProductController::class, 'getProductsByCategoryInShop']);
Route::get('/products/similar/{productId}', [ProductController::class, 'showProductWithSimilar']);


Route::get('/search', [SearchController::class, 'search']);



Route::post('/orders/make', [OrderController::class, 'createOrder'])->middleware('auth:sanctum');



//Dashboard
Route::post('/dashboard/login', [AuthController::class, 'loginDashboard']);


Route::post('/dashboard/work-users', [WorkUserController::class, 'createWorkUser'])
    ->middleware(['auth:sanctum']);


Route::get('/orders/filter', [OrderController::class, 'getOrders'])->middleware('auth:sanctum');



Route::middleware('auth:work_user')->group(function () {

    //shop
    Route::post('/shops/create', [ShopController::class, 'store']);
    Route::delete('/shops/destroy/{shop}', [ShopController::class, 'destroy']);
    Route::post('/shops/update/{shop}', [ShopController::class, 'update']);

    //category
    Route::post('/categories/create', [CategoryController::class, 'store']);
    Route::post('/categories/update/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/destroy/{category}', [CategoryController::class, 'destroy']);

    //product
    Route::post('/products/create', [ProductController::class, 'store']);
    Route::post('/products/update/{product}', [ProductController::class, 'update']);
    Route::delete('/products/destroy/{product}', [ProductController::class, 'destroy']);

    //premium
    Route::get('/order-settings/premium-percentage', [OrderSittingController::class, 'showPremiumPercentage']);
    Route::post('/order-settings/premium-percentage/update', [OrderSittingController::class, 'updatePremiumPercentage']);

    //order
    Route::post('/orders/{order}/status/preparing', [OrderController::class, 'setPreparingStatus']);
    Route::post('/orders/{order}/status/rejected', [OrderController::class, 'rejectOrder']);
    Route::post('/orders/{order}/status/done', [OrderController::class, 'setDoneStatus']);

    //analytics
    Route::get('/analytics/top-products', [OrderController::class, 'topProducts']);
    Route::get('/analytics/top-categories', [OrderController::class, 'topCategories']);
    Route::get('/analytics/top-shops', [OrderController::class, 'topShops']);
    Route::get('/analytics/earnings', [OrderController::class, 'getEarnings']);




});











