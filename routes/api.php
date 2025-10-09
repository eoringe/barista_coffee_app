<?php

use App\Http\Controllers\MenuItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Menu Items API Routes
Route::post('/menu-items', [MenuItemController::class, 'store']);
