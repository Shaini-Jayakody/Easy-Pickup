<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tbl_invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->string('invoice_ref_no')->unique();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('car_id');
            
            // Rental details
            $table->dateTime('returned_date')->nullable();
            
            // Hours calculation
            $table->decimal('actual_hours', 8, 2)->default(0);
            
            // Pricing
            $table->decimal('extra_cost', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->string('fine_reason')->nullable();
            $table->decimal('total_cost', 10, 2)->default(0);
            
            // Payment
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer'])->nullable();
            $table->dateTime('paid_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('booking_id')->references('booking_id')->on('tbl_bookings')->onDelete('restrict');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict');
            $table->foreign('car_id')->references('id')->on('tbl_cars')->onDelete('restrict');
            
            // Indexes
            $table->index('invoice_ref_no');
            $table->index('booking_id');
            $table->index('user_id');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_invoices');
    }
};