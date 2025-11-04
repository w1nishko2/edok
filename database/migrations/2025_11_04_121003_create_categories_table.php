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
        // Таблица категорий
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название категории
            $table->string('slug')->unique(); // ЧПУ
            $table->unsignedBigInteger('parent_id')->nullable(); // Родительская категория (для подкатегорий)
            $table->integer('recipe_count')->default(0); // Счетчик рецептов
            $table->timestamps();

            // Внешний ключ для связи с родительской категорией
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');

            // Индексы для быстрого поиска
            $table->index('slug');
            $table->index('parent_id');
        });

        // Связующая таблица many-to-many между рецептами и категориями
        Schema::create('recipe_category', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            // Внешние ключи
            $table->foreign('recipe_id')
                  ->references('id')
                  ->on('recipes')
                  ->onDelete('cascade');

            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');

            // Уникальный индекс для предотвращения дублей
            $table->unique(['recipe_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_category');
        Schema::dropIfExists('categories');
    }
};
