<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_cars', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_cars', 'number_plate')) {
                $table->string('number_plate', 20)->nullable()->after('chassis_number');
            }
            if (!Schema::hasColumn('tbl_cars', 'rent_price_per_hour')) {
                $table->decimal('rent_price_per_hour', 10, 2)->nullable()->after('number_plate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tbl_cars', function (Blueprint $table) {
            $table->dropColumn(['number_plate', 'rent_price_per_hour']);
        });
    }
};