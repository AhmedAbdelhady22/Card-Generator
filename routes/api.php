<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ActivityLogController;



Route::get('/test', function () {
    return response()->json(['message' => 'Routes are working!']);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});




Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        return request()->user();
    });

    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'userProfile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::put('/change-password', [UserController::class, 'changePassword']);
        Route::get('/my-cards', [UserController::class, 'getMyCards']);
        Route::delete('/delete-account', [UserController::class, 'deleteAccount']);
    });


        Route::prefix('cards')->group(function () {
        Route::get('/', [CardController::class, 'index']);          
        Route::post('/', [CardController::class, 'store']);         
        Route::get('/{card}', [CardController::class, 'show']);      
        Route::put('/{card}', [CardController::class, 'update']);
        Route::delete('/{card}', [CardController::class, 'destroy']); 
    });


        Route::prefix('activities')->group(function () {
        Route::get('/my-activities', [ActivityLogController::class, 'myActivities']);
    });


        Route::prefix('admin')->middleware('admin')->group(function () {
        
        // Dashboard & Statistics
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        
        // User Management
        Route::get('/users', [AdminController::class, 'getAllUsers']);
        Route::patch('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus']);
        
        // Card Management
        Route::get('/cards', [AdminController::class, 'getAllCards']);
        
        // Activity Logs
        Route::get('/activities', [ActivityLogController::class, 'allActivities']);
    });

});