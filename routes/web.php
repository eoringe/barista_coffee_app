<?php

use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

// Main view
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Menu Items Routes
Route::prefix('menu-items')->group(function () {
    Route::get('/', [MenuItemController::class, 'index'])->name('menu-items.index');
    Route::post('/', [MenuItemController::class, 'store'])->name('menu-items.store');
    Route::post('/{menuItem}', [MenuItemController::class, 'update'])->name('menu-items.update');
    Route::delete('/{menuItem}', [MenuItemController::class, 'destroy'])->name('menu-items.destroy');
});

// Categories Routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});
