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
        Schema::create('applications_garage_to_coop', function (Blueprint $table) {
            $table->id('id_application');
            $table->bigInteger('user_id');
            $table->bigInteger('id_block');
            $table->bigInteger('number_garage');
            $table->bigInteger('number_block');
            $table->bigInteger('number_meter');
            $table->bigInteger('id_coop');
            $table->string('status')->default('pending');
            $table->datetime('date_received')->nullable();
            $table->datetime('date_accepted')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications_garage_to_coop');
    }
};
