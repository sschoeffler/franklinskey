<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

// Public pages
Route::get('/', function () {
    return view('welcome');
});

Route::get('/landing', function () {
    return view('landing');
});

// Auth
Route::get('/login', function () {
    return redirect()->route('auth.google');
})->name('login');
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dev auto-login (remove after Google OAuth is configured)
if (app()->environment('local')) {
    Route::get('/dev-login', function () {
        $user = \App\Models\User::where('email', 'sschoeffler@gmail.com')->first();
        if ($user) {
            \Illuminate\Support\Facades\Auth::login($user, true);
            return redirect('/dashboard');
        }
        return 'No stub user found. Run: php artisan db:seed';
    });
}

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Inventory API
    Route::get('/api/inventory', [InventoryController::class, 'index']);
    Route::post('/api/inventory', [InventoryController::class, 'store']);
    Route::patch('/api/inventory/{item}', [InventoryController::class, 'update']);
    Route::delete('/api/inventory/{item}', [InventoryController::class, 'destroy']);
    Route::post('/api/inventory/scan', [InventoryController::class, 'scan']);
    Route::post('/api/inventory/bulk-add', [InventoryController::class, 'bulkAdd']);

    // Builds API
    Route::get('/api/builds', [BuildController::class, 'index']);
    Route::post('/api/builds', [BuildController::class, 'store']);
    Route::patch('/api/builds/{build:slug}', [BuildController::class, 'update']);
    Route::delete('/api/builds/{build:slug}', [BuildController::class, 'destroy']);
    Route::post('/api/builds/{build:slug}/parts', [BuildController::class, 'addPart']);
    Route::delete('/api/builds/{build:slug}/parts/{part}', [BuildController::class, 'removePart']);

    // Build detail page
    Route::get('/builds/{build:slug}', [BuildController::class, 'show'])->name('build.show');
});

// Circuit assistant (session-based, works with or without auth)
Route::get('/app', [ProjectController::class, 'index']);
Route::post('/api/projects', [ProjectController::class, 'create']);
Route::patch('/api/projects/{project:slug}', [ProjectController::class, 'rename']);
Route::delete('/api/projects/{project:slug}', [ProjectController::class, 'destroy']);

Route::get('/project/{project:slug}', [ChatController::class, 'show']);
Route::post('/api/chat/{project:slug}', [ChatController::class, 'send']);
Route::post('/api/ping', [ChatController::class, 'ping']);
