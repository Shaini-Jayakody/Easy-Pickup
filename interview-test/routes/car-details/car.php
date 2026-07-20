<?php

use App\Http\Controllers\CarDetail\CarController;

Route::get('/car', [CarController::class, 'index'])->name('car');
Route::GET('/car/form', [CarController::class, 'form'])->name('car.form');
Route::POST('/car/save', [CarController::class, 'save'])->name('car.save');