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
        Schema::create('applications_create_new_coops', function (Blueprint $table) {
            $table->id('id_application');
            $table->bigInteger('user_id');
            $table->string('name');
            $table->string('city');
            $table->string('address');
            $table->datetime('date_received');
            $table->bigInteger('number_meter');
            $table->string('status');
            $table->string('id_point');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications_create_new_coops');
    }
};
