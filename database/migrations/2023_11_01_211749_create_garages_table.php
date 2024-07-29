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
        Schema::create('garages', function (Blueprint $table) {
            $table->id('id_garage');
            $table->bigInteger('id_application');
            $table->bigInteger('user_id');
            $table->bigInteger('id_block');
            $table->bigInteger('id_coop');
            $table->bigInteger('number_garage');
            $table->bigInteger('number_block');
            $table->bigInteger('number_meter');
            /*$table->string('img_garage')->nullable()->default(null);*/
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garages');
    }
};
