<section class="block-matches-listing section-padding bg-slate-50">
    <div class="container">
        <x-section-heading :title="($data['title'] ?? 'Zápasy')" :subtitle="(($data['type'] ?? 'upcoming') === 'upcoming' ? 'Nadcházející utkání' : 'Poslední výsledky')" align="center" />
        <div class="card overflow-hidden">
            <div class="p-8 text-slate-500 text-center italic">Placeholder pro výpis zápasů (typ: {{ $data['type'] ?? 'upcoming' }}, limit: {{ $data['limit'] ?? 5 }})</div>
        </div>
    </div>
</section>
