<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatSetting extends BaseModel
{
    protected $fillable = [
        'provider',
        'endpoint',
        'model',
        'api_key',
        'system_prompt',
        'is_active',
        'max_context_messages',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'is_active' => 'boolean',
            'max_context_messages' => 'integer',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'provider' => 'openai',
            'endpoint' => 'https://api.openai.com/v1/responses',
            'model' => 'gpt-5',
            'is_active' => false,
            'max_context_messages' => 12,
        ]);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function hasApiKey(): bool
    {
        return filled($this->api_key);
    }
}
