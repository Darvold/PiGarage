<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meter_numbers_garages', function (Blueprint $table) {
            $table->id('id_meter_number');
            $table->bigInteger('id_garage');
            $table->bigInteger('meter_number');
            $table->bigInteger('initially_kw');
            $table->bigInteger('active');
            $table->date('creation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_numbers');
    }
};
