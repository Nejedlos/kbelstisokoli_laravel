@php
    $branding = app(\App\Services\BrandingService::class)->getSettings();
    $title = $data['title'] ?? __('general.quick_facts_title');
    $subtitle = $data['subtitle'] ?? __('general.quick_facts_subtitle');
    $alignment = $data['alignment'] ?? 'center';
@endphp

<div class="section-padding bg-white border-t border-slate-100">
    <div class="container">
        <x-section-heading
            :title="$title"
            :subtitle="$subtitle"
            :align="$alignment"
        />
        <x-quick-facts :branding="$branding ?? []" />
    </div>
</div>
