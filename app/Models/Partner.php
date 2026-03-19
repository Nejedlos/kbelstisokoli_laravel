<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Partner extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'website_url',
        'logo_path_png',
        'logo_path_webp',
        'is_active',
        'is_featured',
        'sort_order',
        'show_on_homepage',
        'show_below_hero',
        'show_in_footer',
        'show_on_match_pages',
        'show_on_contact_page',
        'show_on_recruitment_page',
        'label',
        'description',
        'opened_in_new_tab',
    ];

    public $translatable = ['name', 'slug', 'website_url', 'label', 'description'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'show_on_homepage' => 'boolean',
        'show_below_hero' => 'boolean',
        'show_in_footer' => 'boolean',
        'show_on_match_pages' => 'boolean',
        'show_on_contact_page' => 'boolean',
        'show_on_recruitment_page' => 'boolean',
        'opened_in_new_tab' => 'boolean',
        'sort_order' => 'integer',
    ];
}
