<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UniversityController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'store'])->name('admin.login');
});

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.universities.index'))->name('dashboard');
    Route::resource('universities', UniversityController::class)->except('show');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});
