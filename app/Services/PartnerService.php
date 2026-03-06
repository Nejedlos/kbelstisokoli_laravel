<?php

namespace App\Services;

use App\Models\Partner;
use Illuminate\Support\Collection;

class PartnerService
{
    protected BrandingService $branding;

    public function __construct(BrandingService $branding)
    {
        $this->branding = $branding;
    }

    /**
     * Získá partnery pro strip pod Hero na homepage.
     */
    public function getHomepagePartners(): Collection
    {
        $settings = $this->branding->getSettings();

        if (!$settings['partners']['enabled'] || !$settings['partners']['homepage_strip_enabled']) {
            return collect();
        }

        return Partner::where('is_active', true)
            ->where('show_on_homepage', true)
            ->where('show_below_hero', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Získá partnery pro patičku.
     */
    public function getFooterPartners(): Collection
    {
        $settings = $this->branding->getSettings();

        if (!$settings['partners']['enabled'] || !$settings['partners']['footer_enabled']) {
            return collect();
        }

        return Partner::where('is_active', true)
            ->where('show_in_footer', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Získá partnery pro zápasy.
     */
    public function getMatchPartners(): Collection
    {
        $settings = $this->branding->getSettings();

        if (!$settings['partners']['enabled'] || !$settings['partners']['match_badge_enabled']) {
            return collect();
        }

        return Partner::where('is_active', true)
            ->where('show_on_match_pages', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Získá partnery pro stránku Kontakt.
     */
    public function getContactPartners(): Collection
    {
        $settings = $this->branding->getSettings();

        if (!$settings['partners']['enabled'] || !$settings['partners']['contact_enabled']) {
            return collect();
        }

        return Partner::where('is_active', true)
            ->where('show_on_contact_page', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Získá partnery pro stránku Nábor.
     */
    public function getRecruitmentPartners(): Collection
    {
        $settings = $this->branding->getSettings();

        if (!$settings['partners']['enabled'] || !$settings['partners']['recruitment_enabled']) {
            return collect();
        }

        return Partner::where('is_active', true)
            ->where('show_on_recruitment_page', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order', 'asc')
            ->get();
    }
}
