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
        Schema::create('applications_for_accessions', function (Blueprint $table) {
            $table->id('id_application');
            $table->integer('user_id');
            $table->integer('id_coop');
            $table->string('status')->default('pending');
            $table->datetime('send_date');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications_for_accessions');
    }
};
