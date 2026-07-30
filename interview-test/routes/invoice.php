<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    
    // ============================================
    // INVOICE LISTING (All users)
    // ============================================
    Route::get('/invoices', [InvoiceController::class, 'index'])
        ->name('invoices.index');

    // ============================================
    // CREATE INVOICE (Admin/Manager only)
    // ============================================
    Route::get('/invoices/create', [InvoiceController::class, 'create'])
        ->name('invoices.create')
        ->middleware(['check.role:admin,manager']);
    
    Route::post('/invoices/store', [InvoiceController::class, 'store'])
        ->name('invoices.store')
        ->middleware(['check.role:admin,manager']);

    // ============================================
    // VIEW / PRINT INVOICE (All users with access)
    // ============================================
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])
        ->name('invoices.show');
    
    Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])
        ->name('invoices.print');

    // ============================================
    // UPDATE INVOICE STATUS (Admin/Manager only)
    // ============================================
    Route::put('/invoices/{id}/status', [InvoiceController::class, 'updateStatus'])
        ->name('invoices.update-status')
        ->middleware(['check.role:admin,manager']);

    // ============================================
    // AJAX ENDPOINTS (Admin/Manager only)
    // ============================================
    Route::post('/invoices/preview', [InvoiceController::class, 'previewInvoice'])
        ->name('invoices.preview')
        ->middleware(['check.role:admin,manager']);
    
    Route::get('/invoices/booking-details', [InvoiceController::class, 'getBookingDetails'])
        ->name('invoices.booking-details')
        ->middleware(['check.role:admin,manager']);
});