<?php 
use App\Http\Controllers\ItemOptionController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/item-options/{itemOption}', [ItemOptionController::class, 'show']);
    Route::post('/item-options', [ItemOptionController::class, 'store']);
    Route::put('/item-options/{itemOption}', [ItemOptionController::class, 'update']);
    Route::delete('/item-options/{itemOption}', [ItemOptionController::class, 'destroy']);
});
