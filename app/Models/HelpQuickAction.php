<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $help_article_id
 * @property array $label
 * @property string $url
 * @property string|null $icon
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read HelpArticle $article
 */
class HelpQuickAction extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'help_article_id',
        'label',
        'url',
        'icon',
        'sort_order',
    ];

    public array $translatable = [
        'label',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * @return BelongsTo<HelpArticle, HelpQuickAction>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(HelpArticle::class, 'help_article_id');
    }
}
