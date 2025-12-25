<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id');
            $table->string('fio');
            $table->string('phone');
            $table->string('email')->unique(); // Уникальный Email
            $table->string('password');
            $table->string('region');
            $table->datetime('data_reg')->useCurrent(); // Используйте текущее время
            $table->integer('id_al');
            $table->rememberToken();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
