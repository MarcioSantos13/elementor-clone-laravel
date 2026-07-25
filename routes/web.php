<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/page-builder/pages');
    }
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);
    Route::get('/register', [\App\Http\Controllers\Auth\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\AuthController::class, 'register']);
});

Route::post('/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->get('/tutorial', function () {
    return view('tutorial');
})->name('tutorial');
