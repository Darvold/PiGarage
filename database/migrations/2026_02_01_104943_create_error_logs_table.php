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
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();

            // Основная информация
            $table->string('code', 50)->nullable()->index(); // Код ошибки
            $table->text('message'); // Сообщение ошибки
            $table->string('exception', 200)->nullable(); // Исключение (класс)
            $table->string('level', 20)->default('error')->index(); // Уровень: error, warning, info

            // Контекст
            $table->string('controller', 100)->nullable()->index(); // Контроллер
            $table->string('method', 10)->nullable(); // HTTP метод
            $table->string('route', 100)->nullable()->index(); // Маршрут
            $table->string('url', 500)->nullable(); // Полный URL
            $table->unsignedSmallInteger('status_code')->nullable()->index(); // HTTP статус

            // Пользователь и сессия
            $table->integer('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('session_id', 100)->nullable()->index();

            // Данные запроса
            $table->json('request_data')->nullable(); // Данные запроса
            $table->json('request_headers')->nullable(); // Заголовки

            // Трассировка
            $table->string('file', 500)->nullable(); // Файл
            $table->unsignedInteger('line')->nullable(); // Строка
            $table->text('trace')->nullable(); // Полный trace
            $table->json('trace_array')->nullable(); // Trace как массив

            // Для поиска и группировки
            $table->string('fingerprint', 64)->nullable(); // Уникальный отпечаток ошибки
            $table->unsignedInteger('occurrences')->default(1); // Количество повторений

            // Метаданные
            $table->string('environment', 20)->default('production')->index();
            $table->string('app_version', 20)->nullable();

            $table->timestampsTz();
            $table->softDeletes();


            $table->index(['created_at', 'level']);
            $table->index(['controller', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
