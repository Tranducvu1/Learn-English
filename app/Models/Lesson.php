<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lesson extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'level_id', 'topic_id', 'title', 'duration', 'intro', 'skills'];

    protected function casts(): array
    {
        return ['skills' => 'array'];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function dialogues(): HasMany
    {
        return $this->hasMany(LessonDialogue::class)->orderBy('sort_order');
    }

    public function words(): BelongsToMany
    {
        return $this->belongsToMany(Word::class, 'lesson_word')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }
}
