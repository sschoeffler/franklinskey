<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/landing', function () {
    return view('landing');
});

// App
Route::get('/app', [ProjectController::class, 'index']);
Route::post('/api/projects', [ProjectController::class, 'create']);
Route::patch('/api/projects/{project:slug}', [ProjectController::class, 'rename']);
Route::delete('/api/projects/{project:slug}', [ProjectController::class, 'destroy']);

// Chat
Route::get('/project/{project:slug}', [ChatController::class, 'show']);
Route::post('/api/chat/{project:slug}', [ChatController::class, 'send']);
Route::post('/api/ping', [ChatController::class, 'ping']);
