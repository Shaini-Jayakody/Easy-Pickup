<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CarDetail\CarController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;


// INCLUDE ROUTES
require __DIR__.'/auth.php';
require __DIR__.'/car-details/car.php';
require __DIR__.'/booking.php';
require __DIR__.'/invoice.php';
require __DIR__.'/car-details/brand-model.php';

// CSS ROUTE
Route::get('/css/app.css', function () {
    return Response::make(file_get_contents(resource_path('css/app.css')), 200)
        ->header('Content-Type', 'text/css');
});


// PUBLIC ROUTES
Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('home');
})->name('home');


// DASHBOARD
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');


// PROFILE ROUTEs
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});