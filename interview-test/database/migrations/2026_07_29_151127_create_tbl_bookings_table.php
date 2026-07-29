<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tbl_bookings', function (Blueprint $table) {
            $table->id('booking_id');
            $table->string('booking_ref_no')->unique();
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('car_id')->constrained('tbl_cars', 'id')->onDelete('cascade');
            $table->dateTime('rental_start_date');
            $table->dateTime('rental_end_date');
            $table->enum('status', ['pending', 'confirmed', 'active', 'returned', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_bookings');
    }
};