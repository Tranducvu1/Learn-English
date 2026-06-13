<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HskProgress extends Model
{
    protected $fillable = [
        'user_id',
        'level_id',
        'percent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
