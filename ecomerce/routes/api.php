<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::options('/{any}', function () {
    return response()->json([]);
})->where('any', '.*');


Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('authToken')->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
        'user' => $user
    ]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(ProductController::class)->group(function () {
    Route::get('/list', 'list')->name('product.list');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/list-purchases', [ProductController::class, 'listPurchases'])->name('product.listPurchases');
    Route::post('/buy', [SaleController::class, 'store'])->name('sale.create');
    Route::post('/sale', [SaleController::class, 'detailSale'])->name('sale.detail');
    Route::post('/update-sale', [SaleController::class, 'updateSale'])->name('sale.update');
    Route::delete('/delete/{sale}', [SaleController::class, 'destroy'])->name('sale.delete');
});

