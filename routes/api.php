<?php

use App\Http\Controllers\v1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'registerUserApi']);
Route::post('/login', [AuthController::class, 'loginUserApi']);
Route::post('/logout', [AuthController::class, 'logoutUserApi'])->middleware('auth:sanctum');

