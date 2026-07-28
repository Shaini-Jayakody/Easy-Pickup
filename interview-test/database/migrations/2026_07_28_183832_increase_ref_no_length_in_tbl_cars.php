<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncreaseRefNoLengthInTblCars extends Migration
{
    public function up()
    {
        Schema::table('tbl_cars', function (Blueprint $table) {
            $table->string('ref_no', 50)->change();
        });
    }

    public function down()
    {
        Schema::table('tbl_cars', function (Blueprint $table) {
            $table->string('ref_no', 20)->change();
        });
    }
}