<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DictionaryEntry extends Model
{
    protected $fillable = ['hanzi', 'pinyin', 'vietnamese', 'hsk', 'pos', 'examples'];

    protected function casts(): array
    {
        return ['examples' => 'array'];
    }
}
