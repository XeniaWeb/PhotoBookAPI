<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'author_id',
        'photo_id',
        'comment_text',
    ];

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'author_id');
    }

    /**
     * @return BelongsTo
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo('App\Models\Photo');
    }
}
