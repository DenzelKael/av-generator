<?php

use App\Http\Controllers\MaterialMovementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MaterialMovementController::class, 'index']);
Route::get('/movimientos', [MaterialMovementController::class, 'index'])->name('material-movements.index');
Route::post('/movimientos', [MaterialMovementController::class, 'store'])->name('material-movements.store');
