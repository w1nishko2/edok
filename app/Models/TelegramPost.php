<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'message_id',
        'channel_id',
        'status',
        'error_message',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Связь с рецептом
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Проверка, был ли рецепт успешно опубликован
     */
    public function isPublished(): bool
    {
        return $this->status === 'success' && $this->published_at !== null;
    }

    /**
     * Отметить как успешно опубликованный
     */
    public function markAsPublished(string $messageId): void
    {
        $this->update([
            'message_id' => $messageId,
            'status' => 'success',
            'published_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Отметить как неудачный
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
