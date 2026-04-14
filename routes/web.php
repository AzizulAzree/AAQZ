<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseInspectorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? to_route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/entries', [DashboardController::class, 'store'])->name('dashboard.entries.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::middleware('admin')->group(function () {
        Route::get('/admin/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/admin/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/admin/database', [DatabaseInspectorController::class, 'index'])->name('database.index');
        Route::get('/admin/database/{table}', [DatabaseInspectorController::class, 'show'])->name('database.show');
        Route::delete('/admin/database/{table}', [DatabaseInspectorController::class, 'destroy'])->name('database.destroy');
    });
});

require __DIR__.'/auth.php';
