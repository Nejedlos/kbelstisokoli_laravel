<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Search Header --}}
        <div class="relative overflow-hidden rounded-3xl bg-white/50 backdrop-blur-sm border border-slate-200 p-8 sm:p-12 shadow-sm">
            <div class="relative z-10 max-w-2xl mx-auto text-center space-y-4">
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    {{ __('admin.navigation.pages.help_subtitle') }}
                </h2>
                <p class="text-slate-500 text-lg">
                    {{ __('admin.navigation.pages.help_description') }}
                </p>

                <div class="mt-8 relative max-w-lg mx-auto">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-light fa-magnifying-glass text-slate-400"></i>
                    </div>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="{{ __('admin.navigation.pages.help_search_placeholder') }}"
                        class="block w-full pl-11 pr-4 py-4 bg-white border-none rounded-2xl shadow-xl shadow-primary/5 focus:ring-2 focus:ring-primary text-slate-900 placeholder-slate-400 transition-all"
                    >
                </div>
            </div>

            {{-- Background decorative elements --}}
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-primary/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 bg-secondary/5 rounded-full blur-3xl"></div>
        </div>

        @if($searchQuery)
            {{-- Search Results --}}
            <div class="space-y-4">
                <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-light fa-list-check text-primary"></i>
                    Výsledky hledání pro "{{ $searchQuery }}"
                </h3>

                <div class="grid gap-4">
                    @forelse($this->getSearchResults() as $result)
                        <button
                            wire:click="$set('currentFile', '{{ $result['path'] }}'); $set('searchQuery', '')"
                            class="text-left p-6 bg-white rounded-2xl border border-slate-100 hover:border-primary/30 hover:shadow-lg transition-all group"
                        >
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-primary/5 flex items-center justify-center shrink-0 group-hover:bg-primary group-hover:text-white transition-colors">
                                    <i class="fa-light fa-file-lines"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 group-hover:text-primary transition-colors">{{ $result['title'] }}</h4>
                                    <p class="text-sm text-slate-500 mt-1">{!! $result['excerpt'] !!}</p>
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="p-12 text-center bg-white rounded-3xl border border-dashed border-slate-300">
                            <i class="fa-light fa-face-frown text-4xl text-slate-300 mb-4 block"></i>
                            <p class="text-slate-500">Nebylo nic nalezeno. Zkuste jiné klíčové slovo.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @elseif(!$currentFile)
            {{-- Categories Landing --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($this->getTree() as $category)
                    <div class="group relative bg-white rounded-3xl border border-slate-100 p-8 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                        <div @class([
                            'w-16 h-16 rounded-2xl flex items-center justify-center text-2xl mb-6 transition-transform group-hover:scale-110',
                            'bg-orange-50 text-orange-500' => $category['color'] === 'orange',
                            'bg-blue-50 text-blue-500' => $category['color'] === 'blue',
                            'bg-green-50 text-green-500' => $category['color'] === 'green',
                            'bg-purple-50 text-purple-500' => $category['color'] === 'purple',
                            'bg-red-50 text-red-500' => $category['color'] === 'red',
                            'bg-slate-50 text-slate-500' => $category['color'] === 'slate',
                        ])>
                            <i class="fa-light {{ $category['icon'] }}"></i>
                        </div>

                        <h3 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">{{ $category['name'] }}</h3>

                        <ul class="space-y-2 mb-8">
                            @foreach($category['children'] as $file)
                                <li>
                                    <button
                                        wire:click="$set('currentFile', '{{ $file['path'] }}')"
                                        class="text-slate-500 hover:text-primary flex items-center gap-2 text-sm transition-colors group/link"
                                    >
                                        <i class="fa-light fa-chevron-right text-[10px] opacity-0 -ml-2 group-hover/link:opacity-100 group-hover/link:ml-0 transition-all"></i>
                                        {{ $file['name'] }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="absolute bottom-6 right-8">
                            <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-primary group-hover:text-white transition-all">
                                <i class="fa-light fa-arrow-right"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Article Detail --}}
            <div class="flex flex-col lg:flex-row gap-8">
                {{-- Sidebar --}}
                <aside class="w-full lg:w-72 shrink-0">
                    <div class="sticky top-24 space-y-6">
                        <button
                            wire:click="$set('currentFile', null)"
                            class="flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-primary transition-colors mb-8"
                        >
                            <i class="fa-light fa-arrow-left-long"></i>
                            Zpět na přehled
                        </button>

                        <nav class="space-y-8">
                            @foreach($this->getTree() as $category)
                                <div>
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4 px-2">{{ $category['name'] }}</h4>
                                    <div class="space-y-1">
                                        @foreach($category['children'] as $file)
                                            <button
                                                wire:click="$set('currentFile', '{{ $file['path'] }}')"
                                                @class([
                                                    'w-full text-left px-3 py-2 rounded-xl text-sm transition-all',
                                                    'bg-primary/10 text-primary font-bold shadow-sm' => $currentFile === $file['path'],
                                                    'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => $currentFile !== $file['path'],
                                                ])
                                            >
                                                {{ $file['name'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </nav>
                    </div>
                </aside>

                {{-- Content --}}
                <main class="flex-1 min-w-0">
                    @php $fileData = $this->getFile() @endphp
                    @if($fileData)
                        <article class="bg-white rounded-3xl border border-slate-100 p-8 sm:p-12 shadow-sm">
                            <div class="prose prose-slate max-w-none prose-headings:font-black prose-headings:tracking-tight prose-a:text-primary prose-img:rounded-3xl">
                                {!! $fileData['content'] !!}
                            </div>

                            <div class="mt-12 pt-8 border-t border-slate-100 flex items-center justify-between text-sm text-slate-400">
                                <span>Poslední aktualizace: {{ now()->format('d.m.Y') }}</span>
                                <button
                                    wire:click="submitContactForm"
                                    class="text-primary hover:underline font-bold"
                                >
                                    Potřebujete pomoc? Napište nám.
                                </button>
                            </div>
                        </article>
                    @endif
                </main>
            </div>
        @endif
    </div>
</x-filament-panels::page>
