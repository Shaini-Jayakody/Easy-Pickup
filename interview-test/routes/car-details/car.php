<?php

use App\Http\Controllers\CarDetail\CarController;

// PUBLIC - Allow guests to view cars
Route::get('/car', [CarController::class, 'index'])->name('car');

// PROTECTED - Admin and Manager only
Route::middleware(['auth', 'check.role:admin,manager'])->group(function () {
    // Forms
    Route::get('/car/form', [CarController::class, 'form'])->name('car.form');
    Route::get('/car/{id}/edit', [CarController::class, 'edit'])->name('car.edit');
    
    // CRUD Operations
    Route::post('/car/save', [CarController::class, 'save'])->name('car.save');
    Route::put('/car/{id}/update', [CarController::class, 'update'])->name('car.update');
    Route::delete('/car/{id}/delete', [CarController::class, 'delete'])->name('car.delete');
    
    // AJAX Uniqueness Checks (also protected)
    Route::get('/car/check-engine', [CarController::class, 'checkEngineNumber'])->name('car.check.engine');
    Route::get('/car/check-chassis', [CarController::class, 'checkChassis'])->name('car.check.chassis');
    Route::get('/car/check-numberplate', [CarController::class, 'checkNumberPlate'])->name('car.check.numberplate');
});