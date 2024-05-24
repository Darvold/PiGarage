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
        Schema::create('meters_readings', function (Blueprint $table) {
            $table->id('id_reading');
            $table->bigInteger('id_garage');
            $table->bigInteger('id_block');
            $table->bigInteger('id_coop');
            $table->bigInteger('kw_meter');
            $table->string('status');
            $table->string('img_meter');
            $table->datetime('send_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meters_readings');
    }
};
