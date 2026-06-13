<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'playlist_id', 'youtube_id', 'title', 'duration',
        'level_id', 'topic_id', 'free', 'has_subtitle', 'tags',
    ];

    protected function casts(): array
    {
        return [
            'free' => 'boolean',
            'has_subtitle' => 'boolean',
            'tags' => 'array',
        ];
    }

    public function playlist(): BelongsTo
    {
        return $this->belongsTo(VideoPlaylist::class, 'playlist_id');
    }
}
