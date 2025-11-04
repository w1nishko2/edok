<?php

namespace App\Observers;

use App\Models\Recipe;
use App\Services\SitemapService;
use Illuminate\Support\Facades\Log;

class RecipeObserver
{
    protected SitemapService $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    /**
     * Handle the Recipe "created" event.
     * Автоматически обновляем sitemap при добавлении нового рецепта
     */
    public function created(Recipe $recipe): void
    {
        Log::info("Новый рецепт создан: {$recipe->title}. Обновляем sitemap...");
        
        // Генерируем slug если его нет
        if (empty($recipe->slug)) {
            $recipe->slug = $this->generateUniqueSlug($recipe->title);
            $recipe->saveQuietly(); // Сохраняем без повторного вызова observer
        }

        // Обновляем статический sitemap
        $this->sitemapService->generateStaticSitemap();
        
        Log::info("Sitemap обновлен для рецепта: {$recipe->title}");
    }

    /**
     * Handle the Recipe "updated" event.
     * Обновляем sitemap при изменении рецепта
     */
    public function updated(Recipe $recipe): void
    {
        Log::info("Рецепт обновлен: {$recipe->title}. Обновляем sitemap...");
        
        // Обновляем статический sitemap
        $this->sitemapService->generateStaticSitemap();
    }

    /**
     * Handle the Recipe "deleted" event.
     * Удаляем из sitemap при удалении рецепта
     */
    public function deleted(Recipe $recipe): void
    {
        Log::info("Рецепт удален: {$recipe->title}. Обновляем sitemap...");
        
        // Обновляем статический sitemap
        $this->sitemapService->generateStaticSitemap();
    }

    /**
     * Handle the Recipe "restored" event.
     */
    public function restored(Recipe $recipe): void
    {
        Log::info("Рецепт восстановлен: {$recipe->title}. Обновляем sitemap...");
        
        // Обновляем статический sitemap
        $this->sitemapService->generateStaticSitemap();
    }

    /**
     * Handle the Recipe "force deleted" event.
     */
    public function forceDeleted(Recipe $recipe): void
    {
        Log::info("Рецепт окончательно удален: {$recipe->title}. Обновляем sitemap...");
        
        // Обновляем статический sitemap
        $this->sitemapService->generateStaticSitemap();
    }

    /**
     * Генерация уникального slug
     */
    protected function generateUniqueSlug(string $title): string
    {
        $slug = \Illuminate\Support\Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        // Проверяем уникальность
        while (Recipe::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
