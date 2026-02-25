<section class="block-news-listing section-padding">
    <div class="container">
        <x-section-heading :title="($data['title'] ?? 'Aktuality')" :subtitle="($data['subtitle'] ?? null)" align="center" />
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <x-empty-state class="col-span-full" :title="__('general.blocks.no_news')" :subtitle="__('general.blocks.no_news_desc')" />
        </div>
    </div>
</section>
