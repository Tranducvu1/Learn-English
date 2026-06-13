<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeaturedPlaylist;
use App\Models\VideoPlaylist;
use Illuminate\Http\JsonResponse;

class VideoController extends Controller
{
    public function index(): JsonResponse
    {
        $featured = FeaturedPlaylist::first();

        $playlists = VideoPlaylist::with('videos')->get()->map(fn (VideoPlaylist $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'source' => $p->source,
            'playlistId' => $p->playlist_id,
            'playlistUrl' => $p->playlist_url,
            'description' => $p->description,
            'premium' => $p->premium,
            'embedPlaylist' => $p->embed_playlist,
            'videos' => $p->videos->map(fn ($v) => [
                'id' => $v->id,
                'youtubeId' => $v->youtube_id,
                'title' => $v->title,
                'duration' => $v->duration,
                'level' => $v->level_id,
                'topic' => $v->topic_id,
                'free' => $v->free,
                'hasSubtitle' => $v->has_subtitle,
                'tags' => $v->tags,
            ]),
        ]);

        return response()->json([
            'featuredPlaylist' => $featured ? [
                'id' => $featured->playlist_id,
                'title' => $featured->title,
                'embedUrl' => $featured->embed_url,
                'url' => $featured->url,
            ] : null,
            'playlists' => $playlists,
        ]);
    }
}
