<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CarDetail\CarController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

// Include auth routes
require __DIR__.'/auth.php';

// Serve CSS from resources folder
Route::get('/css/app.css', function () {
    return Response::make(file_get_contents(resource_path('css/app.css')), 200)
        ->header('Content-Type', 'text/css');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('home');
})->name('home');

// ===== CAR ROUTES =====
// Allow guests to view cars
Route::get('/car', [CarController::class, 'index'])->name('car');

// AJAX routes for checking uniqueness (can be accessed by anyone)
Route::get('/car/check-chassis', [CarController::class, 'checkChassis'])->name('car.check.chassis');
Route::get('/car/check-numberplate', [CarController::class, 'checkNumberPlate'])->name('car.check.numberplate');
// Check engine number uniqueness (AJAX)
Route::get('/car/check-engine', [App\Http\Controllers\CarDetail\CarController::class, 'checkEngineNumber'])
    ->name('car.check.engine');
// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Add form and save - Only for admin and manager (auth required)
Route::middleware(['auth'])->group(function () {
    Route::get('/car/form', [CarController::class, 'form'])->name('car.form');
    Route::post('/car/save', [CarController::class, 'save'])->name('car.save');
});

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});