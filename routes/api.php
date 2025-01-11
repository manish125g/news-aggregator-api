<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserPreferenceController;

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');
    Route::post('forgot-password', 'forgotPassword')->name('forgot-password');
    Route::get('/password/reset/{token}', function ($token) {
        return response()->json(['message' => 'Reset password page placeholder', 'token' => $token]);
    })->name('password.reset');
    Route::post('reset-password', 'resetPassword')->name('reset-password');
});

Route::middleware(['auth:sanctum','throttle:60,1'])->group(function () {

    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{id}', [ArticleController::class, 'show']);

    Route::get('/preferences', [UserPreferenceController::class, 'show']);
    Route::post('/preferences', [UserPreferenceController::class, 'store']);
    Route::get('/personalized-news', [UserPreferenceController::class, 'personalizedFeed']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
