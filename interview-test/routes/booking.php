<?php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    
  
    // CHECK AVAILABILITY (AJAX) 
    Route::get('/bookings/check-availability', [BookingController::class, 'checkAvailability'])
        ->name('bookings.check-availability');

    Route::get('/bookings/get-car-bookings', [BookingController::class, 'getCarBookings'])
        ->name('bookings.calendar');

    
    // VIEW BOOKINGS
    Route::get('/bookings', [BookingController::class, 'index'])
        ->name('bookings.index');


    // CREATE BOOKING
    Route::get('/bookings/create', [BookingController::class, 'create'])
        ->name('bookings.create');
    
    Route::post('/bookings/store', [BookingController::class, 'store'])
        ->name('bookings.store');

   
    // EDIT BOOKING
    Route::get('/bookings/{id}/edit', [BookingController::class, 'edit'])
        ->name('bookings.edit');
    
    Route::put('/bookings/{id}/update', [BookingController::class, 'update'])
        ->name('bookings.update');

    // CANCEL BOOKING
  Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel'])
    ->name('bookings.cancel');


    // VIEW BOOKING DETAILS
    Route::get('/bookings/{id}', [BookingController::class, 'show'])
        ->name('bookings.show');

    // MANAGE BOOKINGS (Admin/Manager Only)
    Route::middleware(['check.role:admin,manager'])->group(function () {
        Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus'])
            ->name('bookings.update-status');

             Route::put('/bookings/{id}/status-dropdown', [BookingController::class, 'updateStatusFromDropdown'])
            ->name('bookings.update-status-dropdown');
    });

    
});