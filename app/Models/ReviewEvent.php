<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewEvent extends Model
{
    protected $fillable = [
        'event_type',
        'source',
        'payload',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
