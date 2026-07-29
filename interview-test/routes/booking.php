<?php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Booking Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // ============================================
    // VIEW BOOKINGS
    // ============================================
    Route::get('/bookings', [BookingController::class, 'index'])
        ->name('bookings.index');

    // ============================================
    // CREATE BOOKING (Customers)
    // ============================================
    Route::get('/bookings/create', [BookingController::class, 'create'])
        ->name('bookings.create');
    
    Route::post('/bookings/store', [BookingController::class, 'store'])
        ->name('bookings.store');

    // ============================================
    // CHECK CAR AVAILABILITY (AJAX)
    // ============================================
    Route::get('/bookings/check-availability', [BookingController::class, 'checkAvailability'])
        ->name('bookings.check-availability');

    Route::get('/bookings/get-car-bookings', [BookingController::class, 'getCarBookings'])
        ->name('bookings.calendar');

    // ============================================
    // VIEW BOOKING DETAILS
    // ============================================
    Route::get('/bookings/{id}', [BookingController::class, 'show'])
        ->name('bookings.show');

    // ============================================
    // MANAGE BOOKINGS (Admin/Manager Only)
    // ============================================
    Route::middleware(['check.role:admin,manager'])->group(function () {
        Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus'])
            ->name('bookings.update-status');
    });
});
