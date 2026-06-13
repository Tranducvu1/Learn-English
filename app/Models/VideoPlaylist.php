<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoPlaylist extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'source', 'playlist_id', 'playlist_url',
        'description', 'premium', 'embed_playlist',
    ];

    protected function casts(): array
    {
        return [
            'premium' => 'boolean',
            'embed_playlist' => 'boolean',
        ];
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'playlist_id');
    }
}
