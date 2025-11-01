<?php 
use App\Http\Controllers\ItemController;
use App\Http\Middleware\VerifyUserMakeItems;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    // عرض الطاولات
    Route::get('/items', [ItemController::class, 'index']);
    Route::get('/items/{item}', [ItemController::class, 'show']);

    // العمليات المحمية
    Route::put('/items/{item}', [ItemController::class, 'update']);
    Route::delete('/items/{item}', [ItemController::class, 'destroy']);
    Route::post('/items', [ItemController::class, 'store'])
        ->middleware(VerifyUserMakeItems::class);
});
