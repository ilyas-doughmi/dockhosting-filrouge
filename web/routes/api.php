<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});


Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
   Route::get('/auth/me', [AuthController::class, 'me']); 
   
   // projects
   Route::post('/projects', [ProjectController::class, 'store']);
});
