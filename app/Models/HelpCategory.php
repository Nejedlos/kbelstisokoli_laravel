<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property array $name
 * @property string $slug
 * @property array|null $description
 * @property string|null $icon
 * @property string|null $color
 * @property array|null $audience_roles
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $is_featured
 * @property bool $is_customized
 * @property string|null $source_hash
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read HelpCategory|null $parent
 * @property-read Collection<int, HelpCategory> $children
 * @property-read Collection<int, HelpArticle> $articles
 */
class HelpCategory extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'audience_roles',
        'sort_order',
        'is_active',
        'is_featured',
        'is_customized',
        'source_hash',
    ];

    public array $translatable = [
        'name',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_customized' => 'boolean',
        'sort_order' => 'integer',
        'audience_roles' => 'json',
    ];

    /**
     * @return BelongsTo<HelpCategory, HelpCategory>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(HelpCategory::class, 'parent_id');
    }

    /**
     * @return HasMany<HelpCategory>
     */
    public function children(): HasMany
    {
        return $this->hasMany(HelpCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<HelpArticle>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(HelpArticle::class, 'category_id')->orderBy('sort_order');
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * @param  Builder  $query
     * @param  string|array  $roles
     * @return Builder
     */
    public function scopeForAudience($query, $roles)
    {
        if (empty($roles)) {
            return $query;
        }

        $roles = (array) $roles;

        return $query->where(function ($q) use ($roles) {
            $q->whereNull('audience_roles');
            foreach ($roles as $role) {
                // FALLBACK: Pro produkční DB bez JSON funkcí (Webglobe) použijeme LIKE.
                // V JSON poli rolí ["role"] je název role v uvozovkách.
                $q->orWhere('audience_roles', 'LIKE', '%"'.(string) $role.'"%');
            }
        });
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getFallbackLocale(): string
    {
        return config('app.fallback_locale', 'cs');
    }
}
