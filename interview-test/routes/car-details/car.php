<?php

use App\Http\Controllers\CarDetail\CarController;

// Allow guests to view cars
Route::get('/car', [CarController::class, 'index'])->name('car');

// Add form and save - Only for admin and manager
Route::middleware(['auth'])->group(function () {
    Route::get('/car/form', [CarController::class, 'form'])
        ->name('car.form')
        ->middleware('check.role:admin,manager');
    
    Route::post('/car/save', [CarController::class, 'save'])
        ->name('car.save')
        ->middleware('check.role:admin,manager');

          // ===== AJAX Uniqueness Check Routes =====
    Route::get('/car/check-engine', [CarController::class, 'checkEngineNumber'])
        ->name('car.check.engine')
        ->middleware('auth');
    
    Route::get('/car/check-chassis', [CarController::class, 'checkChassis'])
        ->name('car.check.chassis')
        ->middleware('auth');
    
    Route::get('/car/check-numberplate', [CarController::class, 'checkNumberPlate'])
        ->name('car.check.numberplate')
        ->middleware('auth');
});