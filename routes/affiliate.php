<?php 

use App\Http\Controllers\AffiliateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/affiliates', [AffiliateController::class, 'index']);
});
