<?php

namespace App\Services;

use App\Models\Recipe;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\InputMedia\InputMediaPhoto;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramService
{
    protected BotApi $bot;
    protected ?string $channelId;

    public function __construct()
    {
        $botToken = config('services.telegram.bot_token');
        $this->channelId = config('services.telegram.channel_id');

        if (!$botToken) {
            throw new \Exception('Telegram bot token is not configured. Please set TELEGRAM_BOT_TOKEN in .env file.');
        }

        if (!$this->channelId) {
            throw new \Exception('Telegram channel ID is not configured. Please set TELEGRAM_CHANNEL_ID in .env file.');
        }

        $this->bot = new BotApi($botToken);
    }

    /**
     * Публикация рецепта в Telegram канал
     */
    public function publishRecipe(Recipe $recipe, bool $withButton = false): bool
    {
        try {
            $message = $this->formatRecipeMessage($recipe, !$withButton);
            
            // Если withButton = false, не добавляем клавиатуру (для совместимости с Дзеном)
            $keyboard = null;
            if ($withButton) {
                $recipeUrl = route('recipe.show', $recipe->slug);
                $keyboard = new InlineKeyboardMarkup([
                    [
                        ['text' => '👨‍🍳 Смотреть рецепт', 'url' => $recipeUrl]
                    ]
                ]);
            }

            // Если есть изображение - отправляем с фото
            if ($recipe->image_path && file_exists(storage_path('app/public/' . $recipe->image_path))) {
                $photoPath = storage_path('app/public/' . $recipe->image_path);
                
                $this->bot->sendPhoto(
                    $this->channelId,
                    new \CURLFile($photoPath),
                    $message,
                    null,
                    $keyboard,
                    false,
                    'HTML'
                );
            } else {
                // Отправляем только текст, если нет фото
                $this->bot->sendMessage(
                    $this->channelId,
                    $message,
                    'HTML',
                    false,
                    null,
                    $keyboard
                );
            }

            Log::info('Recipe published to Telegram', [
                'recipe_id' => $recipe->id,
                'recipe_title' => $recipe->title,
                'with_button' => $withButton
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to publish recipe to Telegram', [
                'recipe_id' => $recipe->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Форматирование сообщения для Telegram
     */
    protected function formatRecipeMessage(Recipe $recipe, bool $includeLinkInText = false): string
    {
        $message = "";
        
        // Заголовок с эмодзи
        $emoji = $this->getCategoryEmoji($recipe->primary_category);
        $message .= "{$emoji} <b>" . htmlspecialchars($recipe->title) . "</b>\n\n";

        // Описание (обрезаем если слишком длинное)
        if ($recipe->description) {
            $description = Str::limit(strip_tags($recipe->description), 300);
            $message .= "📝 " . htmlspecialchars($description) . "\n\n";
        }

        // Информация о времени и порциях
        $info = [];
        
        if ($recipe->prep_time) {
            $info[] = "⏱ Подготовка: {$recipe->prep_time} мин";
        }
        
        if ($recipe->cook_time) {
            $info[] = "🔥 Приготовление: {$recipe->cook_time} мин";
        }
        
        if ($recipe->total_time) {
            $info[] = "⏰ Всего: {$recipe->total_time} мин";
        }
        
        if ($recipe->servings) {
            $info[] = "🍽 Порций: {$recipe->servings}";
        }

        if (!empty($info)) {
            $message .= implode("\n", $info) . "\n\n";
        }

        // Категория
        if ($recipe->primary_category) {
            $message .= "📂 Категория: " . htmlspecialchars($recipe->primary_category->name) . "\n";
        }

        // Калорийность
        if ($recipe->calories) {
            $message .= "🔥 Калорийность: {$recipe->calories} ккал\n";
        }

        $message .= "\n";
        
        // Ссылки (если не будет кнопки - добавляем ссылку в текст)
        if ($includeLinkInText) {
            $recipeUrl = route('recipe.show', $recipe->slug);
            $message .= "🌐 Полный рецепт: {$recipeUrl}\n";
        }
        
        $message .= "📢 Наш канал: https://t.me/imedokru\n\n";
        
        // Хештеги
        $message .= $this->generateHashtags($recipe);

        return $message;
    }

    /**
     * Получить эмодзи для категории
     */
    protected function getCategoryEmoji(?object $category): string
    {
        if (!$category || !$category->name) {
            return '🍴';
        }

        $emojiMap = [
            'супы' => '🍲',
            'салаты' => '🥗',
            'закуски' => '🥙',
            'горячие блюда' => '🍛',
            'мясные блюда' => '🥩',
            'рыбные блюда' => '🐟',
            'десерты' => '🍰',
            'торты' => '🎂',
            'выпечка' => '🥐',
            'пироги' => '🥧',
            'напитки' => '🍹',
            'коктейли' => '🍸',
            'паста' => '🍝',
            'пицца' => '🍕',
            'суши' => '🍣',
            'завтраки' => '🍳',
            'каши' => '🥣',
            'соусы' => '🥫',
            'консервация' => '🫙',
        ];

        $categoryName = mb_strtolower($category->name);
        
        foreach ($emojiMap as $key => $emoji) {
            if (Str::contains($categoryName, $key)) {
                return $emoji;
            }
        }

        return '🍴';
    }

    /**
     * Генерация хештегов для рецепта
     */
    protected function generateHashtags(Recipe $recipe): string
    {
        $hashtags = ['#рецепт', '#кулинария', '#яедок'];

        // Хештег категории
        if ($recipe->primary_category) {
            $categoryTag = Str::slug($recipe->primary_category->name, '');
            $hashtags[] = '#' . $categoryTag;
        }

        // Хештеги из названия (первые 2-3 значимых слова)
        $titleWords = explode(' ', $recipe->title);
        $meaningfulWords = array_filter($titleWords, function($word) {
            return mb_strlen($word) > 3 && !in_array(mb_strtolower($word), ['блюда', 'рецепт', 'вкусный']);
        });
        
        $count = 0;
        foreach (array_slice($meaningfulWords, 0, 2) as $word) {
            if ($count >= 2) break;
            $tag = Str::slug($word, '');
            if (!empty($tag)) {
                $hashtags[] = '#' . $tag;
                $count++;
            }
        }

        return implode(' ', $hashtags);
    }

    /**
     * Публикация подборки из 5 случайных рецептов
     */
    public function publishRecipeCollection(?string $categoryName = null): bool
    {
        try {
            $query = \App\Models\Recipe::query();
            $originalCategory = $categoryName;
            
            // Если указана категория, пытаемся фильтровать по ней
            if ($categoryName) {
                $categoryQuery = clone $query;
                $categoryQuery->whereHas('categories', function($q) use ($categoryName) {
                    $q->where('name', 'LIKE', "%{$categoryName}%");
                });
                
                $recipesInCategory = $categoryQuery->count();
                
                // Если в категории меньше 5 рецептов - берем смешанную подборку
                if ($recipesInCategory < 5) {
                    Log::warning('Not enough recipes in category, switching to mixed collection', [
                        'category' => $categoryName,
                        'found' => $recipesInCategory,
                        'required' => 5
                    ]);
                    $categoryName = null; // Переключаемся на смешанную подборку
                } else {
                    $query = $categoryQuery; // Используем фильтрованный запрос
                }
            }
            
            // Получаем 5 случайных рецептов
            $recipes = $query->inRandomOrder()->limit(5)->get();
            
            if ($recipes->count() < 5) {
                Log::error('Not enough recipes in database for collection', [
                    'found' => $recipes->count(),
                    'required' => 5
                ]);
                return false;
            }
            
            $message = $this->formatCollectionMessage($recipes, $categoryName);
            
            // Берем фото первого рецепта
            $firstRecipe = $recipes->first();
            $hasPhoto = $firstRecipe && $firstRecipe->image_path && file_exists(storage_path('app/public/' . $firstRecipe->image_path));
            
            // Отправляем сообщение с фото или без
            if ($hasPhoto) {
                $photoPath = storage_path('app/public/' . $firstRecipe->image_path);
                
                $this->bot->sendPhoto(
                    $this->channelId,
                    new \CURLFile($photoPath),
                    $message,
                    null,
                    null, // без кнопки для совместимости с Дзеном
                    false,
                    'HTML'
                );
            } else {
                // Если нет фото - отправляем только текст
                $this->bot->sendMessage(
                    $this->channelId,
                    $message,
                    'HTML',
                    true, // disable_web_page_preview
                    null,
                    null
                );
            }
            
            Log::info('Recipe collection published to Telegram', [
                'original_category' => $originalCategory,
                'actual_category' => $categoryName ?? 'mixed',
                'recipes_count' => $recipes->count(),
                'with_photo' => $hasPhoto
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to publish recipe collection to Telegram', [
                'category' => $categoryName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
    
    /**
     * Форматирование сообщения для подборки рецептов
     */
    protected function formatCollectionMessage($recipes, ?string $categoryName = null): string
    {
        $message = "";
        
        // Заголовок подборки
        $emoji = $this->getCollectionEmoji();
        if ($categoryName) {
            $message .= "{$emoji} <b>Подборка рецептов: {$categoryName}</b>\n\n";
        } else {
            $message .= "{$emoji} <b>Топ-5 рецептов дня</b>\n\n";
        }
        
        $message .= "Мы подобрали для вас 5 отличных рецептов:\n\n";
        
        // Список рецептов
        foreach ($recipes as $index => $recipe) {
            $number = $index + 1;
            $recipeEmoji = $this->getCategoryEmoji($recipe->primary_category);
            $recipeUrl = route('recipe.show', $recipe->slug);
            
            $message .= "{$number}. {$recipeEmoji} <a href=\"{$recipeUrl}\">" . htmlspecialchars($recipe->title) . "</a>\n";
            
            // Добавляем краткое описание (если есть)
            if ($recipe->description) {
                $shortDescription = Str::limit(strip_tags($recipe->description), 80);
                $message .= "   " . htmlspecialchars($shortDescription) . "\n";
            }
            
            // Добавляем краткую информацию
            $info = [];
            if ($recipe->total_time) {
                $info[] = "⏰ {$recipe->total_time} мин";
            }
            if ($recipe->calories) {
                $info[] = "🔥 {$recipe->calories} ккал";
            }
            
            if (!empty($info)) {
                $message .= "   " . implode(" · ", $info) . "\n";
            }
            
            $message .= "\n";
        }
        
        // Призыв к действию
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "👨‍🍳 Готовьте с удовольствием!\n";
        $message .= "📢 Наш канал: https://t.me/imedokru\n";
        $message .= "🌐 Сайт: " . url('/') . "\n\n";
        
        // Хештеги
        $hashtags = ['#подборка', '#рецепты', '#яедок'];
        if ($categoryName) {
            $categoryTag = Str::slug($categoryName, '');
            $hashtags[] = '#' . $categoryTag;
        }
        $message .= implode(' ', $hashtags);
        
        return $message;
    }
    
    /**
     * Получить эмодзи для подборки
     */
    protected function getCollectionEmoji(): string
    {
        $emojis = ['📚', '🎯', '⭐', '💎', '🏆', '✨', '🎁'];
        return $emojis[array_rand($emojis)];
    }

    /**
     * Тест соединения с ботом
     */
    public function testConnection(): bool
    {
        try {
            $me = $this->bot->getMe();
            Log::info('Telegram bot connected successfully', [
                'bot_username' => $me->getUsername(),
                'bot_name' => $me->getFirstName()
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Telegram bot connection failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
