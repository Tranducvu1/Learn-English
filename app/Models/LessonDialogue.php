<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonDialogue extends Model
{
    protected $fillable = ['lesson_id', 'speaker', 'hanzi', 'pinyin', 'vietnamese', 'sort_order'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
