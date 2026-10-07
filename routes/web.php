<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/inventory', [ProductController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('inventory.index');
    Route::get('/inventory/create', [ProductController::class, 'create'])->middleware(['auth', 'verified'])->name('inventory.create');
    Route::post('/inventory', [ProductController::class, 'store'])->middleware(['auth', 'verified'])->name('inventory.store');
    Route::get('/pos', [PosController::class, 'index'])->middleware(['auth', 'verified'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->middleware(['auth', 'verified'])->name('pos.checkout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
    Route::post('/pos/stand', [PosController::class, 'setStand'])->middleware(['auth', 'verified'])->name('pos.setStand');
    Route::get('/history', [\App\Http\Controllers\HistoryController::class, 'index'])->middleware(['auth', 'verified'])->name('history');
}); 

require __DIR__.'/auth.php';
