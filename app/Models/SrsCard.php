<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SrsCard extends Model
{
    protected $fillable = [
        'user_id',
        'word_id',
        'ease',
        'interval_days',
        'repetitions',
        'next_review_at',
        'last_review_at',
    ];

    protected function casts(): array
    {
        return [
            'ease' => 'decimal:2',
            'next_review_at' => 'datetime',
            'last_review_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
