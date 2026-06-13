<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumFeature extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'icon', 'title', 'tagline', 'description', 'highlights', 'sort_order'];

    protected function casts(): array
    {
        return ['highlights' => 'array'];
    }
}
