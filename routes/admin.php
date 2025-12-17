<?php 

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanSubscripeController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\SubscripeController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawPaymentController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware([AdminMiddleware::class])->prefix('admin')->group(function () {
        //get All Users Change Role 

        

         Route::get('users', [AdminController::class, 'index']);
         Route::delete('users/{id}', [AdminController::class, 'destroy']);
         Route::post('users/{id}/change-role', [AdminController::class, 'changeRole']);

         
        Route::post('/wallet/deposit/{id}', [WalletController::class, 'deposit']);
        Route::post('/wallet/withdraw/{id}', [WalletController::class, 'withdraw']);
        
         //Plan Subscription
         Route::apiResource('plan-subscripes', PlanSubscripeController::class);
   
         Route::get('payments', [PaymentController::class, 'index']);
         Route::put('payments/{id}/status', [PaymentController::class, 'updateStatus']);

         //Subscription
            Route::get('subscriptions', [SubscripeController::class, 'getByAdmin']);
            Route::get('subscriptions/count', [SubscripeController::class, 'count']);
            Route::get('subscriptions/revenue', [SubscripeController::class, 'getRevenue']);

            Route::get('contact', [ContactController::class, 'index']); 

            Route::get('withdraw-payments', [WithdrawPaymentController::class, 'getByAdmin']);
            Route::patch('withdraw-payments/{withdrawPayment}/status', [WithdrawPaymentController::class, 'update']);

            
                        Route::get('/restaurants', [RestaurantController::class, 'getByAdmin']);
            
                        Route::get('/restaurants/{restaurant}/orders', [RestaurantController::class, 'getOrdersRestaurant']);
            

    });
});