<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 h-[calc(100vh-14rem)] overflow-hidden">
        <!-- Sidebar s navigací -->
        <div class="md:col-span-3 overflow-y-auto pr-2 border-r border-gray-100 dark:border-gray-800">
            <div class="mb-4 sticky top-0 bg-white dark:bg-gray-900 pt-1 pb-4 z-10">
                <x-filament::input.wrapper>
                    <x-slot name="prefix">
                        <i class="fa-light fa-magnifying-glass text-gray-400"></i>
                    </x-slot>
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
                                    <i
                                        class="fa-light fa-chevron-right w-3 h-3 mr-2 transition group-hover:text-primary-500"
                                        :class="open ? 'rotate-90' : ''"
                                    ></i>
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
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 9999px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #1f2937; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #374151; }
    </style>
</x-filament-panels::page>
