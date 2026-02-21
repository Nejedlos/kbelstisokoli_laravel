@props(['competition'])

<div class="leaderboard bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
    @if($competition)
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-primary">{{ $competition->name }}</h3>
            <span class="text-sm font-medium px-3 py-1 bg-primary/10 text-primary rounded-full">
                {{ $competition->metric_description }}
            </span>
        </div>

        <div class="space-y-4">
            @forelse($competition->entries as $entry)
                <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 flex items-center justify-center font-bold text-lg rounded-full
                        {{ $loop->first ? 'bg-yellow-400 text-yellow-900' : ($loop->iteration == 2 ? 'bg-gray-300 text-gray-800' : ($loop->iteration == 3 ? 'bg-amber-600 text-amber-100' : 'bg-gray-200 text-gray-500')) }}">
                        {{ $loop->iteration }}
                    </div>

                    <div class="ml-4 flex-grow">
                        <div class="font-bold text-gray-900 dark:text-white">
                            {{ $entry->player?->name ?? $entry->team?->name ?? $entry->label }}
                        </div>
                        @if($entry->source_note)
                            <div class="text-xs text-gray-500 italic">{{ $entry->source_note }}</div>
                        @endif
                    </div>

                    <div class="text-2xl font-black text-primary">
                        {{ number_format($entry->value, 0, ',', ' ') }}
                    </div>
                </div>
            @empty
                <p class="text-center italic text-gray-500 py-8">V této soutěži zatím nejsou žádné záznamy.</p>
            @endforelse
        </div>
    @endif
</div>
