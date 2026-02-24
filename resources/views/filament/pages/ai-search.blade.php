<x-filament-panels::page>
    <x-loader.basketball wire:target="askAi">
        {{ __('admin.loader.ai_thinking') }}
    </x-loader.basketball>

    <div wire:init="askAi"
         x-data="{
            scrollToBottom() {
                $nextTick(() => {
                    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                });
            }
         }"
         x-init="$watch('$wire.messages', () => scrollToBottom())"
         class="max-w-5xl mx-auto flex flex-col min-h-[60vh] px-4 md:px-0">
        <div class="flex-1 space-y-6 mb-12">
            @if(empty($messages))
                <div class="bg-gradient-to-br from-white to-gray-50/50 dark:from-gray-900 dark:to-gray-950 rounded-3xl shadow-sm p-16 text-center border border-gray-100 dark:border-gray-800 relative overflow-hidden group transition-all duration-500 hover:shadow-xl hover:border-primary/20">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <i class="fa-light fa-sparkles text-8xl text-primary"></i>
                    </div>
                    <div class="w-24 h-24 bg-primary/5 dark:bg-primary/10 rounded-3xl flex items-center justify-center mx-auto mb-8 transform group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                        <i class="fa-light fa-sparkles text-4xl text-primary"></i>
                    </div>
                    <h2 class="text-3xl font-black text-gray-900 dark:text-white mb-4 tracking-tight">Vítejte v AI Vyhledávání</h2>
                    <p class="text-gray-500 dark:text-gray-400 max-w-lg mx-auto text-lg leading-relaxed">
                        Položte dotaz naší umělé inteligenci. Pomůže vám s navigací v administraci, vyhledáním informací nebo s fungováním klubu.
                    </p>

                    <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl mx-auto">
                        @php
                            $tips = [
                                ['icon' => 'fa-palette', 'title' => 'Branding', 'text' => 'Jak změnit logo a barvy?'],
                                ['icon' => 'fa-user-gear', 'title' => 'Členové', 'text' => 'Jak resetovat heslo člena?'],
                                ['icon' => 'fa-calendar-xmark', 'title' => 'Omluvy', 'text' => 'Jak fungují omluvy?'],
                                ['icon' => 'fa-basketball', 'title' => 'Zápasy', 'text' => 'Kdy je další zápas?'],
                            ];
                        @endphp
                        @foreach($tips as $tip)
                            <button @click="$wire.set('query', '{{ $tip['text'] }}'); $wire.askAi()" class="flex items-start gap-4 p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:border-primary/30 hover:bg-primary/5 transition-all text-left group/tip">
                                <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center shrink-0 group-hover/tip:bg-primary group-hover/tip:text-white transition-colors">
                                    <i class="fa-light {{ $tip['icon'] }} text-lg"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-black uppercase tracking-widest text-primary mb-0.5">{{ $tip['title'] }}</div>
                                    <div class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $tip['text'] }}</div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                @foreach($messages as $msg)
                    <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }} animate-in fade-in slide-in-from-bottom-2 duration-400">
                        <div class="min-w-[260px] w-full max-w-[90%] md:max-w-[85%] @if($msg['role'] === 'user') bg-primary text-white rounded-[1.5rem] rounded-tr-md @else bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 rounded-[1.5rem] rounded-tl-md border border-gray-100 dark:border-gray-800 @endif shadow-lg overflow-hidden transition-all hover:shadow-xl">
                            @if($msg['role'] === 'assistant')
                                <div class="px-5 py-1.5 bg-primary/5 border-b border-primary/10 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></div>
                                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Kbelští sokoli AI</span>
                                    </div>
                                    <span class="text-[8px] opacity-40 uppercase font-black tracking-widest">{{ $msg['time'] }}</span>
                                </div>
                            @else
                                <div class="px-5 py-1.5 bg-black/10 flex items-center justify-between gap-4">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-white/90">Vy</span>
                                    <span class="text-[8px] text-white/60 uppercase font-black tracking-widest">{{ $msg['time'] }}</span>
                                </div>
                            @endif
                            <div class="p-4 md:p-5 prose dark:prose-invert prose-base max-w-none {{ $msg['role'] === 'user' ? 'prose-p:text-white/95' : 'prose-p:text-gray-700 dark:prose-p:text-gray-300' }}">
                                <div class="whitespace-pre-wrap font-sans text-[14px] leading-snug md:text-[15px]">
                                    {!! Str::markdown($msg['content']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        @if(!empty($sources) && $sources->isNotEmpty())
            <div class="mb-16 bg-gray-50/50 dark:bg-gray-800/30 rounded-[2rem] p-8 border border-gray-100/50 dark:border-gray-800/50 animate-in fade-in duration-1000">
                <div class="text-[11px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-[0.3em] flex items-center gap-4 mb-6">
                    <span class="w-8 h-px bg-gray-200 dark:bg-gray-700"></span>
                    Zdroje informací
                    <span class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($sources as $doc)
                        <div class="flex items-start gap-4 bg-white dark:bg-gray-900 rounded-2xl p-4 border border-gray-100 dark:border-gray-800 shadow-sm transition-all hover:border-primary/30 hover:shadow-md hover:-translate-y-1 group/source">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 group-hover/source:text-primary group-hover/source:bg-primary/5 flex items-center justify-center shrink-0 transition-colors">
                                <i class="fa-light @if($doc->type === 'admin.navigation') fa-compass @elseif($doc->type === 'admin.view') fa-toolbox @elseif($doc->type === 'docs') fa-book @else fa-file-lines @endif text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-gray-900 dark:text-white truncate group-hover/source:text-primary transition-colors">
                                    {{ $doc->title }}
                                </div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-black tracking-wider mt-0.5">
                                    {{ str_replace(['admin.', '.'], ['', ' '], $doc->type) }}
                                </div>
                                @if($doc->url)
                                    <a href="{{ $doc->url }}" class="mt-2 inline-flex items-center text-[10px] text-primary font-black uppercase tracking-widest hover:underline">
                                        Otevřít <i class="fa-light fa-arrow-up-right-from-square ml-1.5 text-[8px]"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="sticky bottom-6 z-20 px-2 md:px-0">
            <div class="absolute -inset-4 bg-gradient-to-t from-gray-50/80 via-gray-50/40 to-transparent dark:from-gray-950/80 dark:via-gray-950/40 dark:to-transparent -z-10 blur-xl pointer-events-none"></div>

            <form wire:submit="askAi"
                  x-data="{ qLen: @js(mb_strlen($query)) }"
                  x-init="$watch('$wire.query', value => qLen = value ? value.length : 0)"
                  class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-primary/20 to-secondary/20 rounded-[2.5rem] blur opacity-25 group-focus-within:opacity-50 transition-opacity duration-500"></div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="query"
                    x-on:input="qLen = $el.value.length"
                    x-on:keydown.enter.prevent="if (qLen >= 2) $wire.askAi()"
                    placeholder="Zde napište svůj dotaz (např. 'Jak změnit barvy klubu?')"
                    class="relative w-full bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-2 border-white dark:border-gray-800 rounded-[2rem] py-5 pl-8 pr-20 shadow-2xl focus:ring-primary focus:border-primary dark:text-white transition-all text-lg placeholder:text-gray-400 dark:placeholder:text-gray-600 outline-none"
                    @disabled($isProcessing)
                    autofocus
                >
                <div class="absolute right-3 top-3 bottom-3 flex items-center">
                    <button
                        type="submit"
                        class="h-full aspect-square bg-primary text-white rounded-2xl hover:bg-primary-600 hover:scale-105 active:scale-95 transition-all flex items-center justify-center disabled:opacity-50 shadow-lg shadow-primary/20 group/btn"
                        :disabled="$wire.isProcessing || qLen < 2"
                    >
                        <i class="fa-light fa-paper-plane-top text-xl group-hover/btn:translate-x-0.5 group-hover/btn:-translate-y-0.5 transition-transform" wire:loading.remove wire:target="askAi"></i>
                        <i class="fa-light fa-spinner-third fa-spin text-xl" wire:loading wire:target="askAi"></i>
                    </button>
                </div>
            </form>
            <p class="mt-4 text-center text-[10px] text-gray-400 dark:text-gray-500 uppercase font-black tracking-[0.4em] flex items-center justify-center gap-6">
                <span class="w-12 h-px bg-gray-200/50 dark:bg-gray-800/50"></span>
                <span class="opacity-50">Sokoli AI Engine 2.0</span>
                <span class="w-12 h-px bg-gray-200/50 dark:bg-gray-800/50"></span>
            </p>
        </div>
    </div>
</x-filament-panels::page>
