<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;


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
    // file management
    Route::get('/projects/{project}/files', [ProjectFileController::class, 'index']);
    Route::get('/projects/{project}/files/read', [ProjectFileController::class, 'show']);
    Route::post('/projects/{project}/files', [ProjectFileController::class, 'store']);
    Route::put('/projects/{project}/files', [ProjectFileController::class, 'update']);
    Route::delete('/projects/{project}/files', [ProjectFileController::class, 'destroy']);
    Route::post('/projects/{project}/files/upload', [ProjectFileController::class, 'upload']);
});
