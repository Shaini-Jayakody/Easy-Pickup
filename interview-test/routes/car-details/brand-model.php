<?php

use App\Http\Controllers\CarDetail\CarBrandController;
use App\Http\Controllers\CarDetail\CarModelController;

Route::middleware(['auth', 'check.role:admin,manager'])->group(function () {
    
    // ============================================
    // BRAND ROUTES
    // ============================================
    Route::get('/car/brands', [CarBrandController::class, 'index'])->name('car.brands.index');
    Route::get('/car/brands/create', [CarBrandController::class, 'create'])->name('car.brands.create');
    Route::post('/car/brands/store', [CarBrandController::class, 'store'])->name('car.brands.store');
    Route::get('/car/brands/{id}/edit', [CarBrandController::class, 'edit'])->name('car.brands.edit');
    Route::post('/car/brands/{id}/update', [CarBrandController::class, 'update'])->name('car.brands.update');
    // ✅ CHANGE: Use DELETE instead of POST
    Route::delete('/car/brands/{id}/delete', [CarBrandController::class, 'destroy'])->name('car.brands.delete');

    // ============================================
    // MODEL ROUTES
    // ============================================
    Route::get('/car/models', [CarModelController::class, 'index'])->name('car.models.index');
    Route::get('/car/models/create', [CarModelController::class, 'create'])->name('car.models.create');
    Route::post('/car/models/store', [CarModelController::class, 'store'])->name('car.models.store');
    Route::get('/car/models/{id}/edit', [CarModelController::class, 'edit'])->name('car.models.edit');
    Route::post('/car/models/{id}/update', [CarModelController::class, 'update'])->name('car.models.update');
    // ✅ CHANGE: Use DELETE instead of POST
    Route::delete('/car/models/{id}/delete', [CarModelController::class, 'destroy'])->name('car.models.delete');
    
    // AJAX: Get models by brand
    Route::get('/car/models/by-brand', [CarModelController::class, 'getByBrand'])->name('car.models.by-brand');
});