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
        Schema::create('total_paid_for_electricity', function (Blueprint $table) {
            $table->id('id_total_elec');
            $table->bigInteger('id_coop');
            $table->bigInteger('value');
            $table->datetime('date_indication');
            $table->timestampsTz();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('total_paid_for_electricities');
    }
};
