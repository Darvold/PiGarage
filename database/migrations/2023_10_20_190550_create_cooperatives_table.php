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
        Schema::create('cooperatives', function (Blueprint $table) {
            $table->id('id_coop');
            $table->bigInteger('user_id');
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('status');
            $table->datetime('date_create');
            $table->integer('personal_number');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooperative');
    }
};
