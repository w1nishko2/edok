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
        Schema::table('recipes', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('title');
            $table->string('meta_title')->nullable()->after('slug');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->string('og_image')->nullable()->after('meta_keywords');
            $table->string('canonical_url')->nullable()->after('og_image');
            $table->integer('prep_time')->nullable()->after('canonical_url')->comment('Время подготовки в минутах');
            $table->integer('cook_time')->nullable()->after('prep_time')->comment('Время приготовления в минутах');
            $table->integer('total_time')->nullable()->after('cook_time')->comment('Общее время в минутах');
            $table->string('difficulty')->nullable()->after('total_time')->comment('Сложность: easy, medium, hard');
            $table->integer('servings')->nullable()->after('difficulty')->comment('Количество порций');
            $table->decimal('rating', 3, 2)->default(0)->after('servings')->comment('Средний рейтинг 0-5');
            $table->integer('rating_count')->default(0)->after('rating')->comment('Количество оценок');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'og_image',
                'canonical_url',
                'prep_time',
                'cook_time',
                'total_time',
                'difficulty',
                'servings',
                'rating',
                'rating_count'
            ]);
        });
    }
};
