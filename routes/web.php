<?php

use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Events\OrderPaidUpdated;
use Illuminate\Support\Facades\Route;

// Main view
Route::get('/', function () {
    return view('welcome_refactored');
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

// Orders (read-only for UI list)
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

// Dev-only: test websocket broadcast
Route::get('/broadcast-test', function () {
    $id = random_int(1000, 9999);
    event(new OrderPaidUpdated($id));
    return ['ok' => true, 'order_id' => $id];
});
