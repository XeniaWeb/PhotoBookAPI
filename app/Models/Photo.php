<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Photo extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
        'author_id',
        'album_id',
        'photo',
        'is_liked_by_me',
        'description',
        'comment_count',
        'like_count'
    ];

    /**
     *  Get the author that owns the photo.
     *
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'author_id');
    }

    /**
     * Get the album that owns the photo.
     *
     * @return BelongsTo
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo('App\Models\Album');
    }

    /**
     * Get the comments for the photo.
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany('App\Models\Comment');
    }

    /**
     * The socials that belong to the user.
     */
    public function likes(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'likes', 'photo_id', 'author_id')->as('liked_me');
    }
}
