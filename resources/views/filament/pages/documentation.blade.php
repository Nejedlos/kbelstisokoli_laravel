<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 h-[calc(100vh-14rem)] overflow-hidden">
        <!-- Sidebar s navigací -->
        <div class="md:col-span-3 overflow-y-auto pr-2 border-r border-gray-100 dark:border-gray-800">
            <div class="mb-4 sticky top-0 bg-white dark:bg-gray-900 pt-1 pb-4 z-10">
                <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                    <x-filament::input
                        type="search"
                        placeholder="Hledat v dokumentaci..."
                        wire:model.live.debounce.500ms="searchQuery"
                    />
                </x-filament::input.wrapper>
            </div>

            @if($searchQuery)
                <div class="space-y-2">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-2 mb-3 flex items-center gap-2">
                        <i class="fa-light fa-search opacity-50"></i> Výsledky vyhledávání
                    </h3>
                    @forelse($this->getSearchResults() as $result)
                        <button
                            wire:click="$set('currentFile', '{{ $result['path'] }}'); $set('searchQuery', '')"
                            class="w-full text-left p-3 rounded-xl hover:bg-primary-50 dark:hover:bg-primary-950/30 transition-all group border border-transparent hover:border-primary-100 dark:hover:border-primary-900/50"
                        >
                            <div class="text-sm font-semibold group-hover:text-primary-600 dark:group-hover:text-primary-400 mb-1 flex items-center gap-2">
                                <i class="fa-light fa-file-lines opacity-40"></i>
                                {{ $result['title'] }}
                            </div>
                            <div class="text-[11px] text-gray-500 line-clamp-2 leading-relaxed">
                                {!! str_replace($searchQuery, '<mark class="bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 rounded px-0.5">' . $searchQuery . '</mark>', e($result['excerpt'])) !!}
                            </div>
                        </button>
                    @empty
                        <div class="text-xs text-gray-500 italic p-4 text-center bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                            <i class="fa-light fa-face-frown mb-2 text-xl block"></i>
                            Nic nenalezeno...
                        </div>
                    @endforelse
                </div>
            @else
                <nav class="space-y-1 pr-2">
                    @foreach($this->getTree() as $item)
                        @if($item['type'] === 'directory')
                            <div x-data="{ open: true }" class="mb-4">
                                <button
                                    @click="open = !open"
                                    class="flex items-center w-full px-2 py-1.5 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest hover:text-primary-600 transition-colors group"
                                >
                                    <x-filament::icon
                                        icon="heroicon-m-chevron-right"
                                        class="w-3 h-3 mr-2 transition group-hover:text-primary-500"
                                        :class="open ? 'rotate-90' : ''"
                                    />
                                    {{ $item['name'] }}
                                </button>
                                <div x-show="open" x-collapse class="pl-3 space-y-1 mt-1 border-l border-gray-100 dark:border-gray-800 ml-3.5">
                                    @foreach($item['children'] as $child)
                                        @if($child['type'] === 'file')
                                            <button
                                                wire:click="$set('currentFile', '{{ $child['path'] }}')"
                                                @class([
                                                    'w-full text-left px-3 py-2 text-sm rounded-lg transition-all flex items-center gap-2 group',
                                                    'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 font-semibold shadow-sm' => $currentFile === $child['path'],
                                                    'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5' => $currentFile !== $child['path'],
                                                ])
                                            >
                                                <i @class([
                                                    'fa-light fa-file-lines text-xs transition-opacity',
                                                    'opacity-100' => $currentFile === $child['path'],
                                                    'opacity-30 group-hover:opacity-60' => $currentFile !== $child['path'],
                                                ])></i>
                                                {{ $child['name'] }}
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <button
                                wire:click="$set('currentFile', '{{ $item['path'] }}')"
                                @class([
                                    'w-full text-left px-3 py-2.5 text-sm rounded-lg transition-all flex items-center gap-2 group mb-1',
                                    'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 font-semibold shadow-sm' => $currentFile === $item['path'],
                                    'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5' => $currentFile !== $item['path'],
                                ])
                            >
                                <i @class([
                                    'fa-light fa-book-open text-xs transition-opacity',
                                    'opacity-100' => $currentFile === $item['path'],
                                    'opacity-30 group-hover:opacity-60' => $currentFile !== $item['path'],
                                ])></i>
                                {{ $item['name'] }}
                            </button>
                        @endif
                    @endforeach
                </nav>
            @endif
        </div>

        <!-- Hlavní obsah -->
        <div class="md:col-span-9 h-full overflow-y-auto px-8 py-8 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 custom-scrollbar relative">
            @if($file = $this->getFile())
                <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-gray-50 dark:border-gray-800 pb-8">
                    <div>
                        <div class="flex items-center gap-2 text-primary-600 dark:text-primary-500 text-[10px] font-bold uppercase tracking-[0.2em] mb-2">
                            <i class="fa-light fa-book"></i> Interní dokumentace
                        </div>
                        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ $file['title'] }}</h1>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800/50 px-3 py-1.5 rounded-full border border-gray-100 dark:border-gray-700">
                             <i class="fa-light fa-folder-open text-[10px] opacity-50"></i>
                             <span class="text-[10px] font-mono text-gray-500 dark:text-gray-400">{{ $currentFile }}</span>
                        </div>
                    </div>
                </div>

                <article class="documentation-content prose dark:prose-invert max-w-none">
                    {!! $file['content'] !!}
                </article>

                <div class="mt-16 pt-8 border-t border-gray-50 dark:border-gray-800 flex justify-between items-center text-xs text-gray-400">
                    <div class="flex items-center gap-4">
                        <span>&copy; {{ date('Y') }} Kbelští sokoli</span>
                        <span class="opacity-20">|</span>
                        <span>Verze 1.0</span>
                    </div>
                    <button @click="$el.closest('.overflow-y-auto').scrollTo({top: 0, behavior: 'smooth'})" class="hover:text-primary-500 transition-colors flex items-center gap-2 font-bold uppercase tracking-wider">
                        Zpět nahoru <i class="fa-light fa-arrow-up-to-line"></i>
                    </button>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-full text-center py-20">
                    <div class="relative mb-8">
                        <div class="absolute inset-0 bg-primary-500/10 blur-3xl rounded-full"></div>
                        <i class="fa-light fa-book-open-reader text-7xl text-primary-500/30 relative"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Vítejte v dokumentaci</h2>
                    <p class="text-gray-500 dark:text-gray-400 max-w-sm leading-relaxed">
                        Vyberte si téma ze seznamu vlevo nebo použijte vyhledávání pro rychlé nalezení odpovědí.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <style>
        .documentation-content {
            font-size: 1.05rem;
            line-height: 1.7;
        }
        .documentation-content h1 { @apply hidden; } /* Schováme H1 pokud je v MD, máme ho v hlavičce */
        .documentation-content h2 { @apply text-2xl font-extrabold mt-12 mb-6 text-gray-900 dark:text-white flex items-center gap-3; }
        .documentation-content h2::before { content: ''; @apply w-1.5 h-6 bg-primary-500 rounded-full inline-block; }
        .documentation-content h3 { @apply text-xl font-bold mt-8 mb-4 text-gray-800 dark:text-gray-200; }
        .documentation-content p { @apply my-5 text-gray-600 dark:text-gray-400; }
        .documentation-content strong { @apply text-gray-900 dark:text-white font-bold; }
        .documentation-content ul { @apply list-none my-6 space-y-3 pl-1; }
        .documentation-content ul li { @apply relative pl-6 flex items-start; }
        .documentation-content ul li::before { content: "\f111"; font-family: "Font Awesome 7 Pro"; @apply absolute left-0 top-1.5 text-[6px] text-primary-500 font-light opacity-50; }
        .documentation-content ol { @apply list-decimal list-inside my-6 space-y-3 font-medium text-gray-800 dark:text-gray-200; }
        .documentation-content ol li span { @apply font-normal text-gray-600 dark:text-gray-400; }
        .documentation-content a { @apply text-primary-600 dark:text-primary-400 underline underline-offset-4 decoration-primary-500/30 hover:decoration-primary-500 transition-all font-semibold; }
        .documentation-content pre { @apply bg-gray-900 text-gray-100 p-6 rounded-2xl overflow-x-auto my-8 border border-gray-800 shadow-xl font-mono text-sm leading-relaxed; }
        .documentation-content code { @apply font-mono text-[0.9em] bg-primary-50 dark:bg-primary-900/30 px-1.5 py-0.5 rounded-md text-primary-700 dark:text-primary-300 border border-primary-100 dark:border-primary-800/50; }
        .documentation-content blockquote { @apply border-l-4 border-primary-500 bg-primary-50/50 dark:bg-primary-950/20 px-6 py-4 my-8 rounded-r-2xl italic text-gray-700 dark:text-gray-300; }
        .documentation-content table { @apply w-full border-separate border-spacing-0 my-8 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden shadow-sm; }
        .documentation-content th { @apply bg-gray-50/80 dark:bg-gray-800/50 text-left p-4 text-[11px] font-black uppercase tracking-wider text-gray-500 border-b border-gray-100 dark:border-gray-800; }
        .documentation-content td { @apply p-4 text-sm text-gray-600 dark:text-gray-400 border-b border-gray-50 dark:border-gray-800 last:border-0; }
        .documentation-content tr:last-child td { @apply border-b-0; }
        .documentation-content img { @apply rounded-2xl shadow-2xl my-10 mx-auto border border-gray-100 dark:border-gray-800 transition-transform hover:scale-[1.02] duration-500; }
        .documentation-content hr { @apply my-12 border-0 h-px bg-gradient-to-r from-transparent via-gray-200 dark:via-gray-800 to-transparent; }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { @apply bg-transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { @apply bg-gray-200 dark:bg-gray-800 rounded-full; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { @apply bg-gray-300 dark:bg-gray-700; }
    </style>
</x-filament-panels::page>
