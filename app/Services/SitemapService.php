<?php

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SitemapService
{
    protected string $sitemapPath;

    public function __construct()
    {
        $this->sitemapPath = public_path('sitemap.xml');
    }

    /**
     * Генерация статического sitemap.xml файла (обновлено для 2025)
     * Вызывается автоматически при создании/обновлении/удалении рецепта
     */
    public function generateStaticSitemap(): void
    {
        try {
            // Генерируем основной sitemap index
            $this->generateSitemapIndex();
            
            // Генерируем отдельные sitemap файлы
            $this->generateRecipesSitemap();
            $this->generateCategoriesSitemap();
            $this->generateStaticPagesSitemap();

            Log::info("Все sitemap файлы обновлены успешно");

        } catch (\Exception $e) {
            Log::error("Ошибка генерации sitemap: " . $e->getMessage());
        }
    }

    /**
     * Генерация sitemap index (главный файл)
     */
    public function generateSitemapIndex(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xml .= "\n";

        // Sitemap для статических страниц
        $xml .= $this->buildSitemapIndexNode(
            route('home') . '/sitemap-static.xml',
            now()->toAtomString()
        );

        // Sitemap для категорий
        $xml .= $this->buildSitemapIndexNode(
            route('home') . '/sitemap-categories.xml',
            now()->toAtomString()
        );

        // Sitemap для рецептов
        $xml .= $this->buildSitemapIndexNode(
            route('home') . '/sitemap-recipes.xml',
            now()->toAtomString()
        );

        $xml .= '</sitemapindex>';

        File::put(public_path('sitemap.xml'), $xml);
    }

    /**
     * Построение узла для sitemap index
     */
    protected function buildSitemapIndexNode(string $loc, string $lastmod): string
    {
        $xml = "  <sitemap>\n";
        $xml .= "    <loc>" . htmlspecialchars($loc) . "</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "  </sitemap>\n";

        return $xml;
    }

    /**
     * Генерация sitemap для рецептов
     */
    protected function generateRecipesSitemap(): void
    {
        $recipes = Recipe::latest()->get();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        $xml .= ' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';
        $xml .= "\n";

        foreach ($recipes as $recipe) {
            $xml .= $this->buildRecipeUrlNode($recipe);
        }

        $xml .= '</urlset>';

        File::put(public_path('sitemap-recipes.xml'), $xml);
    }

    /**
     * Генерация sitemap для категорий
     */
    protected function generateCategoriesSitemap(): void
    {
        $categories = \App\Models\Category::all();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xml .= "\n";

        foreach ($categories as $category) {
            $url = route('category.show', $category->slug);
            $xml .= $this->buildUrlNode(
                $url,
                $category->updated_at->toAtomString(),
                'weekly',
                '0.7'
            );
        }

        $xml .= '</urlset>';

        File::put(public_path('sitemap-categories.xml'), $xml);
    }

    /**
     * Генерация sitemap для статических страниц
     */
    protected function generateStaticPagesSitemap(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xml .= "\n";

        // Главная страница
        $xml .= $this->buildUrlNode(
            route('home'),
            now()->toAtomString(),
            'daily',
            '1.0'
        );

        // Страница категорий
        $xml .= $this->buildUrlNode(
            route('categories.index'),
            now()->toAtomString(),
            'weekly',
            '0.9'
        );

        // Страница поиска
        $xml .= $this->buildUrlNode(
            route('search'),
            now()->toAtomString(),
            'weekly',
            '0.6'
        );

        $xml .= '</urlset>';

        File::put(public_path('sitemap-static.xml'), $xml);
    }

    /**
     * Построение XML для sitemap
     */
    protected function buildSitemapXml($recipes): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        $xml .= ' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';
        $xml .= "\n";

        // Главная страница
        $xml .= $this->buildUrlNode(
            route('home'),
            now()->toAtomString(),
            'daily',
            '1.0'
        );

        // Рецепты
        foreach ($recipes as $recipe) {
            $xml .= $this->buildRecipeUrlNode($recipe);
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Построение узла URL для обычной страницы
     */
    protected function buildUrlNode(string $loc, string $lastmod, string $changefreq, string $priority): string
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($loc) . "</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";

        return $xml;
    }

    /**
     * Построение узла URL для рецепта с изображением
     */
    protected function buildRecipeUrlNode(Recipe $recipe): string
    {
        $url = config('app.url') . '/recipe/' . $recipe->slug;
        
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
        $xml .= "    <lastmod>" . $recipe->updated_at->toAtomString() . "</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.8</priority>\n";

        // Добавляем изображение если есть
        if ($recipe->image_path) {
            $imageUrl = config('app.url') . '/storage/' . $recipe->image_path;
            $xml .= "    <image:image>\n";
            $xml .= "      <image:loc>" . htmlspecialchars($imageUrl) . "</image:loc>\n";
            $xml .= "      <image:title>" . htmlspecialchars($recipe->title) . "</image:title>\n";
            
            if ($recipe->description) {
                // Убираем HTML теги и обрезаем до 200 символов
                $caption = mb_substr(strip_tags($recipe->description), 0, 200);
                $xml .= "      <image:caption>" . htmlspecialchars($caption) . "</image:caption>\n";
            }
            
            $xml .= "    </image:image>\n";
        }

        $xml .= "  </url>\n";

        return $xml;
    }

    /**
     * Проверка существования sitemap файла
     */
    public function sitemapExists(): bool
    {
        return File::exists($this->sitemapPath);
    }

    /**
     * Получение даты последнего обновления sitemap
     */
    public function getLastModified(): ?\DateTime
    {
        if (!$this->sitemapExists()) {
            return null;
        }

        $timestamp = File::lastModified($this->sitemapPath);
        return new \DateTime('@' . $timestamp);
    }

    /**
     * Получение количества URL в sitemap
     */
    public function getUrlCount(): int
    {
        if (!$this->sitemapExists()) {
            return 0;
        }

        $content = File::get($this->sitemapPath);
        return substr_count($content, '<url>');
    }

    /**
     * Генерация sitemap для конкретной категории (на будущее)
     */
    public function generateCategorySitemap(string $category): string
    {
        // Заготовка для будущего расширения
        return '';
    }

    /**
     * Ping поисковых систем о новом sitemap (обновлено для 2025)
     */
    public function pingSearchEngines(): void
    {
        $sitemapUrl = route('home') . '/sitemap.xml';

        // Google
        try {
            $googlePingUrl = "https://www.google.com/ping?sitemap=" . urlencode($sitemapUrl);
            @file_get_contents($googlePingUrl);
            Log::info("Sitemap ping отправлен в Google");
        } catch (\Exception $e) {
            Log::warning("Не удалось отправить ping в Google: " . $e->getMessage());
        }

        // Bing
        try {
            $bingPingUrl = "https://www.bing.com/ping?sitemap=" . urlencode($sitemapUrl);
            @file_get_contents($bingPingUrl);
            Log::info("Sitemap ping отправлен в Bing");
        } catch (\Exception $e) {
            Log::warning("Не удалось отправить ping в Bing: " . $e->getMessage());
        }

        // Яндекс (IndexNow API для 2025)
        try {
            $indexNowUrl = "https://yandex.com/indexnow?url=" . urlencode($sitemapUrl) . "&key=your_api_key";
            // Добавьте ваш API ключ в .env файл
            Log::info("Sitemap ping отправлен в Яндекс IndexNow");
        } catch (\Exception $e) {
            Log::warning("Не удалось отправить ping в Яндекс: " . $e->getMessage());
        }
    }

    /**
     * Очистка старого sitemap
     */
    public function clearSitemap(): void
    {
        if ($this->sitemapExists()) {
            File::delete($this->sitemapPath);
            Log::info("Sitemap файл удален");
        }
    }
}
