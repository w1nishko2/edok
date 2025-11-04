<?php

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Support\Facades\URL;

class SeoService
{
    /**
     * Генерация всех SEO-данных для рецепта
     */
    public function generateRecipeSeo(Recipe $recipe): array
    {
        $url = route('recipe.show', $recipe->slug);
        $image = $recipe->image_path ? asset('storage/' . $recipe->image_path) : null;
        $ogImage = $recipe->og_image ?: $image;

        return [
            'title' => $this->getMetaTitle($recipe),
            'description' => $this->getMetaDescription($recipe),
            'keywords' => $this->getMetaKeywords($recipe),
            'canonical' => $recipe->canonical_url ?: $url,
            'og' => $this->generateOpenGraph($recipe, $url, $ogImage),
            'twitter' => $this->generateTwitterCard($recipe, $url, $ogImage),
            'schema' => $this->generateRecipeSchema($recipe, $url, $image),
            'breadcrumbs' => $this->generateBreadcrumbs($recipe),
        ];
    }

    /**
     * Генерация SEO-данных для главной страницы
     */
    public function generateHomeSeo(int $recipesCount = 0): array
    {
        $url = route('home');
        
        return [
            'title' => 'Рецепты с фото - Лучшие рецепты приготовления блюд',
            'description' => "Коллекция из {$recipesCount} проверенных рецептов с пошаговыми фото. Простые и вкусные рецепты на каждый день: салаты, супы, вторые блюда, десерты и выпечка.",
            'keywords' => 'рецепты, рецепты с фото, кулинария, приготовление, блюда, еда, кулинарные рецепты',
            'canonical' => $url,
            'og' => [
                'title' => 'Рецепты с фото - Лучшие рецепты приготовления',
                'description' => "Коллекция проверенных рецептов с пошаговыми фото. Простые и вкусные рецепты.",
                'url' => $url,
                'type' => 'website',
                'site_name' => config('app.name'),
                'locale' => 'ru_RU',
            ],
            'twitter' => [
                'card' => 'summary',
                'title' => 'Рецепты с фото - Лучшие рецепты',
                'description' => "Коллекция проверенных рецептов с пошаговыми фото",
            ],
            'schema' => $this->generateWebsiteSchema(),
        ];
    }

    /**
     * Получить meta title для рецепта
     */
    protected function getMetaTitle(Recipe $recipe): string
    {
        if ($recipe->meta_title) {
            return $recipe->meta_title;
        }

        // Максимальная длина для Google: 60 символов
        $maxLength = 60;
        $suffix = ' - Рецепт с фото';
        $availableLength = $maxLength - mb_strlen($suffix);
        
        if (mb_strlen($recipe->title) > $availableLength) {
            // Обрезаем по последнему полному слову
            $title = mb_substr($recipe->title, 0, $availableLength - 3);
            $lastSpace = mb_strrpos($title, ' ');
            if ($lastSpace !== false) {
                $title = mb_substr($title, 0, $lastSpace);
            }
            $title .= '...';
        } else {
            $title = $recipe->title;
        }
        
        return $title . $suffix;
    }

    /**
     * Получить meta description для рецепта
     */
    protected function getMetaDescription(Recipe $recipe): string
    {
        if ($recipe->meta_description) {
            return $recipe->meta_description;
        }

        $description = $recipe->description ?: $recipe->title;
        $maxLength = 155;
        $suffix = ' Смотрите рецепт с фото!';
        
        // Если описание короткое, добавляем суффикс
        if (mb_strlen($description) <= $maxLength - mb_strlen($suffix)) {
            return $description . $suffix;
        }
        
        // Обрезаем по последнему полному слову
        $trimmed = mb_substr($description, 0, $maxLength - 3);
        $lastSpace = mb_strrpos($trimmed, ' ');
        if ($lastSpace !== false) {
            $trimmed = mb_substr($trimmed, 0, $lastSpace);
        }
        
        return $trimmed . '...';
    }

    /**
     * Получить meta keywords для рецепта
     */
    protected function getMetaKeywords(Recipe $recipe): string
    {
        if ($recipe->meta_keywords) {
            return $recipe->meta_keywords;
        }

        $keywords = [$recipe->title, 'рецепт', 'приготовление', 'с фото'];
        
        // Добавляем ингредиенты как ключевые слова
        if (!empty($recipe->ingredients)) {
            $ingredientNames = array_slice(
                array_column($recipe->ingredients, 'name'),
                0,
                5
            );
            $keywords = array_merge($keywords, $ingredientNames);
        }

        return implode(', ', array_unique($keywords));
    }

    /**
     * Генерация Open Graph метатегов
     */
    protected function generateOpenGraph(Recipe $recipe, string $url, ?string $image): array
    {
        $og = [
            'title' => $this->getMetaTitle($recipe),
            'description' => $this->getMetaDescription($recipe),
            'url' => $url,
            'type' => 'article',
            'site_name' => config('app.name'),
            'locale' => 'ru_RU',
        ];

        if ($image) {
            $og['image'] = $image;
            $og['image:width'] = 1200;
            $og['image:height'] = 630;
            $og['image:alt'] = $recipe->title;
        }

        // Добавляем теги для статьи
        $og['article:published_time'] = $recipe->created_at->toIso8601String();
        $og['article:modified_time'] = $recipe->updated_at->toIso8601String();
        $og['article:section'] = 'Кулинария';
        $og['article:tag'] = 'Рецепты';

        return $og;
    }

    /**
     * Генерация Twitter Card метатегов
     */
    protected function generateTwitterCard(Recipe $recipe, string $url, ?string $image): array
    {
        $twitter = [
            'card' => $image ? 'summary_large_image' : 'summary',
            'title' => $this->getMetaTitle($recipe),
            'description' => $this->getMetaDescription($recipe),
        ];

        if ($image) {
            $twitter['image'] = $image;
            $twitter['image:alt'] = $recipe->title;
        }

        return $twitter;
    }

    /**
     * Генерация JSON-LD Schema для рецепта (обновлено для 2025)
     */
    protected function generateRecipeSchema(Recipe $recipe, string $url, ?string $image): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Recipe',
            'name' => $recipe->title,
            'description' => $recipe->description ?: $recipe->title,
            'url' => $url,
            'datePublished' => $recipe->created_at->toIso8601String(),
            'dateModified' => $recipe->updated_at->toIso8601String(),
            'cookingMethod' => 'Традиционный',
            'recipeCategory' => 'Основное блюдо',
            'recipeCuisine' => 'Русская',
            'keywords' => $this->getMetaKeywords($recipe),
        ];

        if ($image) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => $image,
                'width' => 1200,
                'height' => 800,
                'caption' => $recipe->title,
            ];
        }

        // Время приготовления
        if ($recipe->prep_time) {
            $schema['prepTime'] = 'PT' . $recipe->prep_time . 'M';
        }
        if ($recipe->cook_time) {
            $schema['cookTime'] = 'PT' . $recipe->cook_time . 'M';
        }
        if ($recipe->total_time) {
            $schema['totalTime'] = 'PT' . $recipe->total_time . 'M';
        }

        // Количество порций
        if ($recipe->servings) {
            $schema['recipeYield'] = [
                '@type' => 'QuantitativeValue',
                'value' => $recipe->servings,
                'unitText' => 'порций',
            ];
        }

        // Рейтинг (улучшенный для Google)
        if ($recipe->rating > 0 && $recipe->rating_count > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($recipe->rating, 1),
                'ratingCount' => $recipe->rating_count,
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        // Ингредиенты
        if (!empty($recipe->ingredients)) {
            $schema['recipeIngredient'] = array_map(function ($ingredient) {
                $quantity = trim($ingredient['quantity'] ?? '');
                $measure = trim($ingredient['measure'] ?? '');
                $name = trim($ingredient['name'] ?? '');
                
                return trim("$quantity $measure $name");
            }, $recipe->ingredients);
        }

        // Шаги приготовления (улучшенные для Google)
        if (!empty($recipe->steps)) {
            $schema['recipeInstructions'] = array_map(function ($step) {
                $instruction = [
                    '@type' => 'HowToStep',
                    'position' => $step['step_number'],
                    'text' => $step['description'],
                    'name' => 'Шаг ' . $step['step_number'],
                ];
                
                if (!empty($step['image'])) {
                    $instruction['image'] = asset('storage/' . $step['image']);
                }
                
                return $instruction;
            }, $recipe->steps);
        }

        // Пищевая ценность (расширенная)
        if (!empty($recipe->nutrition)) {
            $nutrition = $recipe->nutrition;
            $schema['nutrition'] = [
                '@type' => 'NutritionInformation',
            ];

            if (isset($nutrition['calories'])) {
                $schema['nutrition']['calories'] = $nutrition['calories'] . ' калорий';
            }
            if (isset($nutrition['proteins'])) {
                $schema['nutrition']['proteinContent'] = $nutrition['proteins'] . ' г';
            }
            if (isset($nutrition['fats'])) {
                $schema['nutrition']['fatContent'] = $nutrition['fats'] . ' г';
            }
            if (isset($nutrition['carbs'])) {
                $schema['nutrition']['carbohydrateContent'] = $nutrition['carbs'] . ' г';
            }
            if (isset($nutrition['fiber'])) {
                $schema['nutrition']['fiberContent'] = $nutrition['fiber'] . ' г';
            }
            if (isset($nutrition['sugar'])) {
                $schema['nutrition']['sugarContent'] = $nutrition['sugar'] . ' г';
            }
            if (isset($nutrition['sodium'])) {
                $schema['nutrition']['sodiumContent'] = $nutrition['sodium'] . ' мг';
            }
        }

        // Автор (расширенный для Google Discover)
        $schema['author'] = [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => route('home'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('/android-chrome-512x512.png'),
                'width' => 512,
                'height' => 512,
            ],
        ];

        // Издатель (требуется для Google)
        $schema['publisher'] = [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('/android-chrome-512x512.png'),
                'width' => 512,
                'height' => 512,
            ],
        ];

        // Сложность
        if ($recipe->difficulty) {
            $difficultyMap = [
                'easy' => 'Простой',
                'medium' => 'Средний',
                'hard' => 'Сложный',
            ];
            $schema['recipeDifficulty'] = $difficultyMap[$recipe->difficulty] ?? 'Средний';
        }

        // Дополнительные поля для 2025
        $schema['isAccessibleForFree'] = true;
        $schema['inLanguage'] = 'ru';
        
        // Видео (если есть)
        if (!empty($recipe->video_url)) {
            $schema['video'] = [
                '@type' => 'VideoObject',
                'name' => $recipe->title,
                'description' => $recipe->description,
                'thumbnailUrl' => $image,
                'contentUrl' => $recipe->video_url,
                'uploadDate' => $recipe->created_at->toIso8601String(),
            ];
        }

        return $schema;
    }

    /**
     * Генерация хлебных крошек
     */
    protected function generateBreadcrumbs(Recipe $recipe): array
    {
        $breadcrumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Главная',
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $recipe->title,
                    'item' => route('recipe.show', $recipe->slug),
                ],
            ],
        ];

        return $breadcrumbs;
    }

    /**
     * Генерация схемы веб-сайта для главной страницы (обновлено для 2025)
     */
    protected function generateWebsiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name'),
            'alternateName' => 'Яедок - рецепты с фото',
            'url' => route('home'),
            'description' => 'Коллекция проверенных рецептов с пошаговыми фото',
            'inLanguage' => 'ru',
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'url' => route('home'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('/android-chrome-512x512.png'),
                    'width' => 512,
                    'height' => 512,
                ],
                'sameAs' => [
                    'https://dzen.ru/imedok',
                    'https://t.me/imedokru',
                ],
            ],
            'potentialAction' => [
                [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => route('search') . '?q={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ];
    }

    /**
     * Генерация ItemList Schema для списка рецептов
     */
    public function generateItemListSchema($recipes): array
    {
        $items = [];
        $position = 1;

        foreach ($recipes as $recipe) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'url' => route('recipe.show', $recipe->slug),
                'name' => $recipe->title,
                'image' => $recipe->image_path ? 
                    asset('storage/' . $recipe->image_path) : null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $items,
            'numberOfItems' => count($items),
        ];
    }
}
