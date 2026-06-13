<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'dark_mode',
        'show_pinyin',
        'font_size',
        'tts_engine',
    ];

    protected function casts(): array
    {
        return [
            'dark_mode' => 'boolean',
            'show_pinyin' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
