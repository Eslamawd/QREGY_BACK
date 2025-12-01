<?php 

use App\Http\Controllers\RestaurantController;
use App\Http\Middleware\VerifyOrderAccess;
use App\Http\Middleware\VerifyUserMakeRestaurant;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/restaurants/links', [\App\Http\Controllers\RestaurantLinksController::class, 'update']);

    // المسارات العادية بدون شرط
    Route::get('/restaurants', [RestaurantController::class, 'index']);
    Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show']);
    Route::patch('/restaurants/{restaurant}', [RestaurantController::class, 'update']);
    Route::delete('/restaurants/{restaurant}', [RestaurantController::class, 'destroy']);

    // فقط الـ store عليه middleware التحقق
    Route::post('/restaurants', [RestaurantController::class, 'store'])
        ->middleware(VerifyUserMakeRestaurant::class);


         Route::get('/restaurants/{restaurant}/orders', [RestaurantController::class, 'getOrdersRestaurant'])->middleware(VerifyOrderAccess::class);

         Route::get('/restaurants/all/data', [RestaurantController::class,'getResOrdRevCount']);
});
