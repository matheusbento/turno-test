<?php

use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserTransactionController;
use App\Http\Controllers\UserTransactionDepositController;
use App\Http\Controllers\UserTransactionPurchaseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('login', [UserAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('logout', [UserAuthController::class, 'logout']);
        Route::get('/me', [UserAuthController::class, 'me']);

        Route::prefix('transactions')->group(function () {
            Route::get('/', [UserTransactionController::class, 'index'])->middleware('can:viewAny,App\Models\UserTransaction');
        });

        Route::prefix('deposits')->group(function () {
            Route::post('/', [UserTransactionDepositController::class, 'store'])->middleware('can:create,App\Models\UserTransaction');
            Route::get('/{userTransaction}', [UserTransactionDepositController::class, 'show'])->middleware('can:view,userTransaction');
            Route::get('/{userTransaction}/check', [UserTransactionDepositController::class, 'check'])->middleware('can:view,userTransaction');
            Route::post('/{userTransaction}/approve', [UserTransactionDepositController::class, 'approve'])->middleware('can:approve,userTransaction');
            Route::post('/{userTransaction}/reject', [UserTransactionDepositController::class, 'reject'])->middleware('can:reject,userTransaction');
        });

        Route::prefix('purchases')->group(function () {
            Route::post('/', [UserTransactionPurchaseController::class, 'store'])->middleware('can:create,App\Models\UserTransaction');
            Route::get('/{userTransaction}', [UserTransactionPurchaseController::class, 'show'])->middleware('can:view,userTransaction');
        });
    });
});
