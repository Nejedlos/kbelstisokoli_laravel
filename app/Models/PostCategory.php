<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    /**
     * Get the posts for the category.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'category_id');
    }
}
