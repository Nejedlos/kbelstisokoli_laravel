<section class="block-news-listing section-padding">
    <div class="container">
        <x-section-heading :title="($data['title'] ?? 'Aktuality')" :subtitle="($data['subtitle'] ?? null)" align="center" />
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <x-empty-state class="col-span-full" title="Zatím žádné novinky" subtitle="Obsah bude doplněn později (limit: {{ $data['limit'] ?? 3 }})." />
        </div>
    </div>
</section>
