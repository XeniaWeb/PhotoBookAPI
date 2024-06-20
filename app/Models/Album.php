<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
        'author_id',
        'preview',
        'description',
    ];

    /**
     * @return HasMany
     */
    public function photos(): HasMany
    {
        return $this->HasMany('App\Models\Photo');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'author_id');
    }
}
