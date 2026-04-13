<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\HousingController;
use App\Http\Controllers\OutcomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ResidentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->group(function () {
        Route::post('penghuni/upload-ktp', [ResidentController::class, 'uploadKtp']);
        Route::apiResource('penghuni', ResidentController::class)
            ->parameters(['penghuni' => 'resident']);
        Route::apiResource('rumah', HouseController::class)
            ->parameters(['rumah' => 'house']);
        Route::apiResource('hunian', HousingController::class)
            ->parameters(['hunian' => 'housing']);
        Route::apiResource('iuran', FeeController::class);
        Route::apiResource('category', CategoryController::class);
    });

    Route::middleware('role:admin,penghuni')->group(function () {
        Route::apiResource('payment', PaymentController::class);
        Route::post('payment/generate', [PaymentController::class, 'generate']);
        Route::apiResource('outcome', OutcomeController::class);
    });
});
