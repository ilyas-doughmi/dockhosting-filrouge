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
   Route::get('/projects', [ProjectController::class, 'index']);
   Route::post('/projects', [ProjectController::class, 'store']);
   Route::post('/projects/{project}/stop', [ProjectController::class, 'stop']); 
   Route::post('/projects/{project}/start', [ProjectController::class, 'start']);
   Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
});
