<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'icon', 'lesson_count'];

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function words(): HasMany
    {
        return $this->hasMany(Word::class);
    }
}
