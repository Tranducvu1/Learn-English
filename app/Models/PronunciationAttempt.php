<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PronunciationAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'word_id',
        'target_text',
        'transcript',
        'score',
        'feedback',
        'audio_path',
    ];

    protected function casts(): array
    {
        return [
            'feedback' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }
}
