<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders/history', [Api\OrderController::class, 'history']);
    Route::patch('/orders/{id}/progress', [Api\OrderController::class, 'updateOrderProgress']);
    Route::apiResource('orders', Api\OrderController::class);

    Route::patch('inventories/{id}/adjust', [Api\InventoryController::class, 'adjustQuantity']);
    Route::apiResource('inventories', Api\InventoryController::class);
});
