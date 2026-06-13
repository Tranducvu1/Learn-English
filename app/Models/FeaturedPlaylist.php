<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedPlaylist extends Model
{
    protected $fillable = ['playlist_id', 'title', 'embed_url', 'url'];
}
