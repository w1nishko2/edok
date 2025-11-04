<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Services\SitemapService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class SitemapController extends Controller
{
    protected SitemapService $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    /**
     * Отдача sitemap (сначала пробуем статический, затем генерируем динамически)
     */
    public function index()
    {
        // Проверяем наличие статического sitemap
        if ($this->sitemapService->sitemapExists()) {
            // Отдаем статический файл (быстрее)
            $content = File::get(public_path('sitemap.xml'));
            return response($content, 200)
                ->header('Content-Type', 'application/xml')
                ->header('Cache-Control', 'public, max-age=3600'); // Кэш на 1 час
        }

        // Если статического нет - генерируем динамически
        $recipes = Recipe::latest()->get();
        $sitemap = $this->buildDynamicSitemap($recipes);

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Динамическая генерация sitemap (fallback)
     */
    protected function buildDynamicSitemap($recipes): string
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $sitemap .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        $sitemap .= ' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';

        // Главная страница
        $sitemap .= '<url>';
        $sitemap .= '<loc>' . route('home') . '</loc>';
        $sitemap .= '<lastmod>' . now()->toAtomString() . '</lastmod>';
        $sitemap .= '<changefreq>daily</changefreq>';
        $sitemap .= '<priority>1.0</priority>';
        $sitemap .= '</url>';

        // Рецепты
        foreach ($recipes as $recipe) {
            $sitemap .= '<url>';
            $sitemap .= '<loc>' . route('recipe.show', $recipe->slug) . '</loc>';
            $sitemap .= '<lastmod>' . $recipe->updated_at->toAtomString() . '</lastmod>';
            $sitemap .= '<changefreq>weekly</changefreq>';
            $sitemap .= '<priority>0.8</priority>';
            
            // Добавляем изображение если есть
            if ($recipe->image_path) {
                $sitemap .= '<image:image>';
                $sitemap .= '<image:loc>' . asset('storage/' . $recipe->image_path) . '</image:loc>';
                $sitemap .= '<image:title>' . htmlspecialchars($recipe->title) . '</image:title>';
                if ($recipe->description) {
                    $sitemap .= '<image:caption>' . htmlspecialchars(mb_substr($recipe->description, 0, 200)) . '</image:caption>';
                }
                $sitemap .= '</image:image>';
            }
            
            $sitemap .= '</url>';
        }

        $sitemap .= '</urlset>';

        return $sitemap;
    }

    /**
     * Генерация robots.txt
     */
    public function robots()
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "\n";
        $content .= "# Sitemap\n";
        $content .= "Sitemap: " . route('sitemap') . "\n";
        $content .= "\n";
        $content .= "# Crawl-delay\n";
        $content .= "Crawl-delay: 1\n";

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }
}
