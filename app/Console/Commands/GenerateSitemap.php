<?php

namespace App\Console\Commands;

use App\Services\SitemapService;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {--ping : Отправить ping в поисковые системы}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерация статического sitemap.xml файла для всех рецептов';

    /**
     * Create a new command instance.
     */
    public function __construct(protected SitemapService $sitemapService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Генерация sitemap.xml...');

        // Генерируем sitemap
        $this->sitemapService->generateStaticSitemap();

        // Получаем статистику
        $urlCount = $this->sitemapService->getUrlCount();
        $lastModified = $this->sitemapService->getLastModified();

        $this->info("✅ Sitemap успешно сгенерирован!");
        $this->info("📊 Всего URL: {$urlCount}");
        
        if ($lastModified) {
            $this->info("📅 Дата обновления: " . $lastModified->format('Y-m-d H:i:s'));
        }

        $this->info("📍 Файл: public/sitemap.xml");
        $this->info("🌐 URL: " . route('sitemap'));

        // Отправляем ping в поисковики если указан флаг
        if ($this->option('ping')) {
            $this->info('');
            $this->info('📡 Отправка уведомлений в поисковые системы...');
            $this->sitemapService->pingSearchEngines();
            $this->info('✅ Уведомления отправлены!');
        }

        $this->info('');
        $this->info('💡 Совет: Добавьте sitemap в Google Search Console и Яндекс.Вебмастер');

        return Command::SUCCESS;
    }
}
