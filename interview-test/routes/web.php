<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

// Serve CSS from resources folder
Route::get('/css/app.css', function () {
    return Response::make(file_get_contents(resource_path('css/app.css')), 200)
        ->header('Content-Type', 'text/css');
});

// Welcome page 
Route::get('/', function () {
    return view('welcome');
});

// Home page
Route::get('/home', function () {
    return view('home');
});

require base_path('routes/car-details/car.php');
