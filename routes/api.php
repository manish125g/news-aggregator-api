<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');
    Route::post('forgot-password', 'forgotPassword')->name('forgot-password');
    Route::get('/password/reset/{token}', function ($token) {
        return response()->json(['message' => 'Reset password page placeholder', 'token' => $token]);
    })->name('password.reset');
    Route::post('reset-password', 'resetPassword')->name('reset-password');
});

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('articles', ArticleController::class)
        ->only(['index']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
