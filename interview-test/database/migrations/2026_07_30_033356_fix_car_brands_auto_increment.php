<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Fix tbl_car_brands
        Schema::table('tbl_car_brands', function (Blueprint $table) {
            $table->increments('id')->change();
        });

        // Fix tbl_car_models
        Schema::table('tbl_car_models', function (Blueprint $table) {
            $table->increments('id')->change();
        });
    }

    public function down()
    {
        // Revert changes if needed
        Schema::table('tbl_car_brands', function (Blueprint $table) {
            $table->integer('id')->change();
        });

        Schema::table('tbl_car_models', function (Blueprint $table) {
            $table->integer('id')->change();
        });
    }
};