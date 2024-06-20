<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Social extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'icon',
    ];

    /**
     * The authors that belong to the social.
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'author_social', 'social_id', 'author_id')->withPivot('link');
    }
}
