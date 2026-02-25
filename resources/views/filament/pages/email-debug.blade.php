<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-filament::section>
            <x-slot name="heading">
                Aktuální konfigurace SMTP
            </x-slot>

            <x-slot name="description">
                Tyto hodnoty jsou načteny z <code>config('mail')</code>, který reflektuje <code>.env</code> a případnou cache.
            </x-slot>

            <div class="space-y-4">
                @foreach($this->getMailConfig() as $label => $value)
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2 dark:border-white/5">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}:</span>
                        <code class="text-sm font-mono bg-gray-50 px-2 py-1 rounded dark:bg-white/5">{{ $value }}</code>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Diagnostika
            </x-slot>

            <div class="prose dark:prose-invert text-sm">
                <p>Pokud e-maily nechodí, zkontrolujte:</p>
                <ul>
                    <li>Zda je v <code>.env</code> správně <code>MAIL_MAILER=smtp</code>.</li>
                    <li>Zda jsou u portu 465 použity uvozovky u hesla a <code>MAIL_ENCRYPTION=ssl</code>.</li>
                    <li>Zda hostitel <code>mail.webglobe.cz</code> odpovídá vaší variantě hostingu.</li>
                </ul>
                <p class="mt-4 text-warning-600 font-bold">
                    <i class="fa-light fa-triangle-exclamation mr-1"></i>
                    Důležité: Po každé změně v .env na produkci musíte spustit <code>php artisan config:cache</code>.
                </p>
            </div>
        </x-filament::section>
    </div>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Nedávné e-mailové záznamy v logu
        </x-slot>

        <div class="bg-gray-950 p-4 rounded-xl overflow-x-auto">
            @php $logs = $this->getRecentLogs(); @endphp
            @if(count($logs) > 0)
                <pre class="text-xs text-gray-300 font-mono leading-relaxed">@foreach($logs as $log)
{{ $log }}
@endforeach</pre>
            @else
                <p class="text-gray-500 text-sm italic">V posledních řádcích logu nebyly nalezeny žádné záznamy o e-mailech nebo chybách.</p>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
