<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'canonical_url',
        'description',
        'image_path',
        'ingredients',
        'steps',
        'nutrition',
        'source_url',
        'prep_time',
        'cook_time',
        'total_time',
        'difficulty',
        'servings',
        'views',
        'likes',
        'dislikes',
        'rating',
        'rating_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ingredients' => 'array',
        'steps' => 'array',
        'nutrition' => 'array',
    ];

    /**
     * Связь с категориями (many-to-many)
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'recipe_category');
    }

    /**
     * Получить первую категорию рецепта
     */
    public function getPrimaryCategoryAttribute()
    {
        return $this->categories()->first();
    }

    /**
     * Связь с постами в Telegram
     */
    public function telegramPosts()
    {
        return $this->hasMany(TelegramPost::class);
    }

    /**
     * Проверка, был ли рецепт опубликован в Telegram
     */
    public function isPublishedToTelegram(): bool
    {
        return $this->telegramPosts()
            ->where('status', 'success')
            ->exists();
    }

    /**
     * Scope для поиска рецептов по различным параметрам
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        $searchTerms = explode(' ', trim($search));
        
        return $query->where(function ($q) use ($searchTerms, $search) {
            // Поиск по полному совпадению (приоритет выше)
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%")
              ->orWhere('meta_description', 'LIKE', "%{$search}%")
              ->orWhere('meta_keywords', 'LIKE', "%{$search}%")
              ->orWhere('difficulty', 'LIKE', "%{$search}%");
            
            // Поиск в JSON-полях (ingredients, steps, nutrition)
            $q->orWhereRaw('JSON_SEARCH(ingredients, "one", ?) IS NOT NULL', ["%{$search}%"])
              ->orWhereRaw('JSON_SEARCH(steps, "one", ?) IS NOT NULL', ["%{$search}%"]);
            
            // Поиск по отдельным словам для лучшего нечеткого совпадения
            foreach ($searchTerms as $term) {
                if (mb_strlen($term) >= 3) { // Игнорируем слишком короткие слова
                    $q->orWhere('title', 'LIKE', "%{$term}%")
                      ->orWhere('description', 'LIKE', "%{$term}%")
                      ->orWhereRaw('JSON_SEARCH(ingredients, "one", ?) IS NOT NULL', ["%{$term}%"]);
                }
            }
        });
    }
}
