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
        Schema::create('cooperative_blocks_losses_kw', function (Blueprint $table) {
            $table->id('id_block_losses_kw');
            $table->bigInteger('id_block');
            $table->float('percent_kw');
            $table->datetime('date_indication');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooperative_blocks_losses_kw');
    }
};
