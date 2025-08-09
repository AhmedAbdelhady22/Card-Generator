<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\UserController;

//show home screen
Route::get('/', function () {
    return view('pages.home');
})->name('home');

//show dashboard screen
Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard')->middleware('auth');

// API route for dashboard data
Route::get('/api/dashboard', [UserController::class, 'getDashboardData'])->name('dashboard.api')->middleware('auth');

// User profile routes
Route::get('/api/user/profile', [UserController::class, 'userProfile'])->name('user.profile')->middleware('auth');
Route::put('/api/user/profile', [UserController::class, 'updateProfile'])->name('user.profile.update')->middleware('auth');
Route::post('/api/user/change-password', [UserController::class, 'changePassword'])->name('user.change-password')->middleware('auth');
Route::get('/api/user/cards', [UserController::class, 'getUserCards'])->name('user.cards')->middleware('auth');
Route::delete('/api/user/account', [UserController::class, 'deleteAccount'])->name('user.delete-account')->middleware('auth');

//show al cards
Route::get('/cards', [CardController::class, 'index'])->name('cards.index')->middleware('auth');

//show create card form
Route::get('/cards/create', function () {
    return view('cards.create');
})->name('cards.create')->middleware(['auth', 'permission:create_cards']);

//store new card
Route::post('/cards', [CardController::class, 'store'])->name('cards.store')->middleware(['auth', 'permission:create_cards']);

//show all cards
Route::get('/cards', [CardController::class, 'index'])->name('cards.index')->middleware('auth');

//show card details
Route::get('/cards/{card}', function ($cardId) {
    $card = \App\Models\Card::where('id', $cardId)
        ->where('user_id', Auth::id())
        ->first();
        if (!$card) {
            return redirect()->route('cards.index')->with('error', 'Card not found or unauthorized');
        }
    return view('cards.show', compact('card'));
})->name('cards.show')->middleware('auth');

//show edit card form
Route::get('/cards/{card}/edit', function ($cardId) {
    $card = \App\Models\Card::where('id', $cardId)
        ->where('user_id', Auth::id())
        ->first();
    
    if (!$card) {
        return redirect()->route('cards.index')->with('error', 'Card not found or unauthorized');
    }
    
    return view('cards.edit', compact('card'));
})->name('cards.edit')->middleware(['auth', 'permission:edit_cards']);
Route::put('/cards/{card}', [CardController::class, 'update'])->name('cards.update')->middleware(['auth', 'permission:edit_cards']);

//delete card
Route::delete('/cards/{card}/delete', [CardController::class, 'destroy'])->name('cards.destroy')->middleware(['auth', 'permission:delete_cards']);

// PDF generation routes
Route::get('/cards/{card}/pdf', [CardController::class, 'downloadPdf'])->name('cards.pdf')->middleware(['auth', 'permission:download_pdf']);
Route::get('/cards/{card}/pdf/preview', [CardController::class, 'previewPdf'])->name('cards.pdf.preview')->middleware(['auth', 'permission:download_pdf']);

// Toggle card status
Route::patch('/cards/{card}/toggle-status', [CardController::class, 'toggleStatus'])->name('cards.toggle-status')->middleware('auth');

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function() {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User Management - temporarily remove permission middleware for testing
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create')->middleware('permission:manage_users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store')->middleware('permission:manage_users');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit')->middleware('permission:manage_users');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update')->middleware('permission:manage_users');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete')->middleware('permission:manage_users');
    Route::patch('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status')->middleware('permission:manage_users');
    
    // User Permission Management
    Route::get('/users/{user}/permissions', [AdminController::class, 'userPermissions'])->name('users.permissions')->middleware('permission:manage_permissions');
    Route::put('/users/{user}/permissions', [AdminController::class, 'updateUserPermissions'])->name('users.permissions.update')->middleware('permission:manage_permissions');
    
    // Card Management
    Route::get('/cards', [AdminController::class, 'cards'])->name('cards')->middleware('permission:manage_users');
    Route::delete('/cards/{card}', [AdminController::class, 'deleteCard'])->name('cards.delete')->middleware('permission:manage_users');
    
    // Permission Management
    Route::get('/permissions', [AdminController::class, 'permissions'])->name('permissions')->middleware('permission:manage_permissions');
    Route::put('/roles/{role}/permissions', [AdminController::class, 'updateRolePermissions'])->name('roles.permissions.update')->middleware('permission:manage_permissions');
    
    // Activity Logs
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs')->middleware('permission:view_activity_logs');
    Route::get('/logs/{log}', [AdminController::class, 'showLog'])->name('logs.show')->middleware('permission:view_activity_logs');
    Route::get('/logs-export', [AdminController::class, 'exportLogs'])->name('logs.export')->middleware(['permission:view_activity_logs', 'permission:export_data']);
});

// Authentication Routes

//login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);

//register
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', [AuthController::class, 'register']);
//logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


