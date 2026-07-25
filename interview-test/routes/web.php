<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

//include auth routes
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


Route::get('/car', [App\Http\Controllers\CarDetail\CarController::class, 'index'])->name('car');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

