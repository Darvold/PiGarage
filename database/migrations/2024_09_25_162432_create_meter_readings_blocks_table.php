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
        Schema::create('meter_readings_blocks', function (Blueprint $table) {
            $table->id('id_reading');
            $table->bigInteger('id_block');
            $table->bigInteger('id_coop');
            $table->bigInteger('meter_number_block');
            $table->bigInteger('kw_meter');
            $table->string('img_meter');
            $table->datetime('save_day');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_readings_blocks');
    }
};
