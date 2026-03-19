<x-filament-widgets::widget>
    <div class="fi-section fi-section-border rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
        <div class="fi-section-header flex items-center gap-3 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-white/5">
            <div class="flex flex-1 items-center gap-2">
                <i class="fa-light fa-ranking-star text-primary-600 dark:text-primary-400"></i>
                <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    {{ __('admin.resources.club_competition.leaderboard.heading') }}
                </h3>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse ($entries as $entry)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 transition duration-150">
                        <div class="flex items-center gap-4">
                            <div @class([
                                'flex items-center justify-center w-10 h-10 rounded-full font-black text-sm shadow-sm border-2',
                                'bg-gradient-to-br from-amber-400 to-amber-600 text-white border-amber-300' => $entry->competition_rank == 1,
                                'bg-gradient-to-br from-slate-300 to-slate-500 text-white border-slate-200' => $entry->competition_rank == 2,
                                'bg-gradient-to-br from-orange-500 to-orange-700 text-white border-orange-400' => $entry->competition_rank == 3,
                                'bg-white dark:bg-gray-800 text-gray-950 dark:text-white border-gray-200 dark:border-gray-700' => $entry->competition_rank > 3,
                            ])>
                                {{ $entry->competition_rank }}.
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-950 dark:text-white">
                                    {{ $entry->user_name ?? $entry->label }}
                                </span>
                                @if($entry->player_id)
                                    <span class="text-[10px] uppercase tracking-wider font-semibold text-primary-600 dark:text-primary-400">
                                        {{ __('admin.resources.club_competition.leaderboard.is_member') }}
                                    </span>
                                @else
                                    <span class="text-[10px] uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400">
                                        {{ __('admin.resources.club_competition.leaderboard.is_external') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($entry->competition_rank == 1)
                                <i class="fa-light fa-trophy text-amber-500 text-lg"></i>
                            @elseif($entry->competition_rank == 2)
                                <i class="fa-light fa-trophy text-slate-400 text-lg"></i>
                            @elseif($entry->competition_rank == 3)
                                <i class="fa-light fa-trophy text-orange-600 text-lg"></i>
                            @endif

                            <div class="flex flex-col items-end">
                                <span class="text-lg font-black text-gray-950 dark:text-white leading-none">
                                    {{ number_format($entry->total_value, 0, ',', ' ') }}
                                </span>
                                <span class="text-[10px] uppercase font-bold text-gray-500 dark:text-gray-400">
                                    {{ trans_choice('admin.resources.club_competition.leaderboard.points_count', (float)$entry->total_value) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center">
                        <i class="fa-light fa-users-slash text-4xl text-gray-300 dark:text-gray-700 mb-3 block"></i>
                        <p class="text-gray-500 dark:text-gray-400 italic">
                            {{ __('admin.resources.club_competition.leaderboard.no_entries') }}
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
