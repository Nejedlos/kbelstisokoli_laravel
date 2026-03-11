<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $category_id
 * @property array $title
 * @property string $slug
 * @property array $content
 * @property array|null $excerpt
 * @property array|null $search_keywords
 * @property array|null $audience_roles
 * @property int $sort_order
 * @property bool $is_published
 * @property bool $is_featured
 * @property bool $is_customized
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property string|null $source_hash
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read HelpCategory $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, HelpQuickAction> $quickActions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, HelpFaq> $faqs
 * @property-read \Illuminate\Database\Eloquent\Collection<int, HelpArticle> $relatedArticles
 */
class HelpArticle extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'search_keywords',
        'audience_roles',
        'sort_order',
        'is_published',
        'is_featured',
        'is_customized',
        'published_at',
        'source_hash',
        'metadata',
    ];

    public array $translatable = [
        'title',
        'content',
        'excerpt',
        'search_keywords',
        'metadata',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'is_customized' => 'boolean',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
        'audience_roles' => 'json',
        'title' => 'array',
        'content' => 'array',
        'excerpt' => 'array',
        'search_keywords' => 'array',
        'metadata' => 'array',
    ];

    /**
     * @return BelongsTo<HelpCategory, HelpArticle>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(HelpCategory::class, 'category_id');
    }

    /**
     * @return HasMany<HelpQuickAction>
     */
    public function quickActions(): HasMany
    {
        return $this->hasMany(HelpQuickAction::class, 'help_article_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<HelpFaq>
     */
    public function faqs(): HasMany
    {
        return $this->hasMany(HelpFaq::class, 'help_article_id')->orderBy('sort_order');
    }

    /**
     * @return BelongsToMany<HelpArticle>
     */
    public function relatedArticles(): BelongsToMany
    {
        return $this->belongsToMany(
            HelpArticle::class,
            'help_article_related',
            'article_id',
            'related_article_id'
        );
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * @param Builder $query
     * @param string|array $roles
     * @return Builder
     */
    public function scopeForAudience(Builder $query, $roles): Builder
    {
        if (empty($roles)) {
            return $query;
        }

        $roles = (array) $roles;

        return $query->where(function (Builder $q) use ($roles) {
            $q->whereNull('audience_roles');
            foreach ($roles as $role) {
                $q->orWhereJsonContains('audience_roles', $role);
            }
        });
    }

    /**
     * Helper pro zjištění, zda je článek určen pro konkrétní roli.
     *
     * @param string $role
     * @return bool
     */
    public function hasAudienceRole(string $role): bool
    {
        if (empty($this->audience_roles)) {
            return true;
        }

        return in_array($role, $this->audience_roles);
    }

    /**
     * Vrátí zformátovaný obsah článku.
     *
     * @return string
     */
    public function getParsedContent(): string
    {
        $content = $this->getTranslation('content', app()->getLocale(), false);

        if (empty($content)) {
            return '';
        }

        // Parsování markdownu
        return Str::markdown($content);
    }

    /**
     * Vrátí krátké shrnutí.
     *
     * @return string
     */
    public function getSafeExcerpt(): string
    {
        $excerpt = $this->getTranslation('excerpt', app()->getLocale(), false);
        if (!empty($excerpt)) {
            return $excerpt;
        }

        $metadata = $this->getTranslation('metadata', app()->getLocale(), false);
        if (isset($metadata['short_intro'])) {
            return $metadata['short_intro'];
        }

        return Str::limit(strip_tags($this->getParsedContent()), 160);
    }
    public function getFallbackLocale(): string
    {
        return config('app.fallback_locale', 'cs');
    }
}
