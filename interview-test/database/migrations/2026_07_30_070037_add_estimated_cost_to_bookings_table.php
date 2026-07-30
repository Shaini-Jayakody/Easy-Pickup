<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_bookings', function (Blueprint $table) {
            $table->decimal('estimated_cost', 10, 2)->nullable()->after('rental_end_date');
        });

        // Update existing bookings with estimated cost
        $bookings = \App\Models\Booking::with('car')->get();
        foreach ($bookings as $booking) {
            $durationInHours = $booking->getDurationInHours();
            $pricePerHour = $booking->car->rent_price_per_hour ?? 0;
            $booking->estimated_cost = $durationInHours * $pricePerHour;
            $booking->saveQuietly();
        }
    }

    public function down(): void
    {
        Schema::table('tbl_bookings', function (Blueprint $table) {
            $table->dropColumn('estimated_cost');
        });
    }
};