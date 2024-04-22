<?php

use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserDepositController;
use App\Http\Controllers\UserPurchaseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('login', [UserAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('logout', [UserAuthController::class, 'logout']);
        Route::get('/me', [UserAuthController::class, 'me']);

        Route::prefix('deposits')->group(function () {
            Route::get('/', [UserDepositController::class, 'index'])->middleware('can:viewAny,App\Models\UserDeposit');
            Route::post('/', [UserDepositController::class, 'store'])->middleware('can:viewAny,App\Models\UserDeposit');
            Route::get('/{userDeposit}', [UserDepositController::class, 'show'])->middleware('can:view,userDeposit');
            Route::get('/{userDeposit}/check', [UserDepositController::class, 'check'])->middleware('can:view,userDeposit');
            Route::post('/{userDeposit}/approve', [UserDepositController::class, 'approve'])->middleware('can:approve,userDeposit');
            Route::post('/{userDeposit}/reject', [UserDepositController::class, 'reject'])->middleware('can:reject,userDeposit');
        });

        Route::prefix('purchases')->group(function () {
            Route::get('/', [UserPurchaseController::class, 'index'])->middleware('can:viewAny,App\Models\UserPurchase');
            Route::post('/', [UserPurchaseController::class, 'store'])->middleware('can:viewAny,App\Models\UserPurchase');
            Route::get('/{userPurchase}', [UserPurchaseController::class, 'show'])->middleware('can:view,userPurchase');
        });
    });
});
