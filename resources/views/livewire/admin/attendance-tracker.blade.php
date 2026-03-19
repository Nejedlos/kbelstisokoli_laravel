<div class="space-y-6">
    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 text-success-700 dark:text-success-400 flex items-center gap-3">
            <i class="fa-light fa-circle-check"></i>
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fa-light fa-user-plus text-primary-500"></i>
            {{ __('admin.real_attendance.tracker.add_member') }}
        </h3>

        <div class="relative z-20">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-light fa-magnifying-glass text-gray-400"></i>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('admin.real_attendance.tracker.search_placeholder') }}"
                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                >
            </div>

            @if(!empty($search) && count($this->users) > 0)
                <div class="absolute z-30 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-200 dark:border-gray-700">
                    @foreach($this->users as $user)
                        <button
                            wire:click="selectUser({{ $user->id }})"
                            class="w-full text-left px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors"
                        >
                            <img src="{{ $user->getFilamentAvatarUrl() }}" class="w-8 h-8 rounded-full border border-gray-200 dark:border-gray-700">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                            </div>
                        </button>
                    @endforeach
                </div>
            @elseif(!empty($search) && strlen($search) >= 2)
                <div class="absolute z-30 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg rounded-md py-4 text-center text-sm text-gray-500 border border-gray-200 dark:border-gray-700">
                    {{ __('admin.real_attendance.tracker.no_member_found') }}
                </div>
            @endif
        </div>
    </div>

    @if(count($this->attendances) > 0)
        <div class="flex flex-col md:flex-row items-center justify-center gap-4 relative z-10 my-4 md:mt-2 md:mb-10 px-4 w-full">
            <div class="w-full md:w-auto bg-primary-600 dark:bg-primary-500 px-6 py-3 rounded-full shadow-lg shadow-primary-500/40 flex items-center justify-center gap-4 border-2 border-white dark:border-gray-900 transform transition-transform hover:scale-105 shrink-0">
                <div class="text-sm font-black text-white uppercase tracking-tighter flex items-center gap-1">
                    <span>{{ __('admin.real_attendance.tracker.submitted_count') }}</span>
                    <span class="text-xl px-2 py-0.5 bg-white/20 rounded-lg leading-none">{{ count($this->attendances) }}</span>
                    <span>{{ trans_choice('admin.real_attendance.tracker.players_count', count($this->attendances)) }}</span>
                </div>
                <div class="hidden sm:block">
                    <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                </div>
            </div>

            <x-filament::modal id="finalize-attendance-modal" width="lg">
                <x-slot name="trigger">
                    <button
                        type="button"
                        class="w-full md:w-auto px-8 py-3 bg-primary-600 hover:bg-primary-500 text-white font-bold rounded-full shadow-lg shadow-primary-500/20 flex items-center justify-center gap-3 transition-all hover:scale-105 active:scale-95 shrink-0 border-2 border-white dark:border-gray-900 whitespace-normal text-center"
                    >
                        <i class="fa-light fa-file-invoice-dollar text-xl"></i>
                        {{ __('admin.real_attendance.tracker.finalize_button') }}
                    </button>
                </x-slot>

                <x-slot name="heading">
                    <div class="flex items-center gap-3 text-primary-600">
                        <i class="fa-light fa-file-invoice-dollar text-2xl"></i>
                        <span>{{ __('admin.real_attendance.tracker.finalize_modal_title') }}</span>
                    </div>
                </x-slot>

                <div class="py-2 space-y-4 text-left">
                    <div class="p-4 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-xl text-warning-700 dark:text-warning-400 flex items-start gap-3">
                        <i class="fa-light fa-triangle-exclamation text-xl mt-0.5"></i>
                        <div class="text-sm">
                            <p class="font-bold mb-1">{{ __('admin.real_attendance.tracker.finalize_warning_title') }}</p>
                            <p>{{ __('admin.real_attendance.tracker.finalize_warning_desc') }}</p>
                        </div>
                    </div>

                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __('admin.real_attendance.tracker.finalize_confirmation_text') }}
                    </p>
                </div>

                <x-slot name="footerActions">
                    <x-filament::button
                        color="gray"
                        x-on:click="close"
                        class="font-bold rounded-xl"
                    >
                        {{ __('admin.real_attendance.tracker.cancel') }}
                    </x-filament::button>

                    <x-filament::button
                        color="primary"
                        wire:click="finalize"
                        x-on:click="close"
                        class="font-bold rounded-xl shadow-lg shadow-primary-500/20"
                    >
                        <div class="flex items-center gap-2">
                            <i class="fa-light fa-check"></i>
                            {{ __('admin.real_attendance.tracker.confirm_finalize') }}
                        </div>
                    </x-filament::button>
                </x-slot>
            </x-filament::modal>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($this->attendances as $attendance)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3 shadow-sm flex items-center justify-between group animate-in fade-in zoom-in duration-300">
                <div class="flex items-center gap-3">
                    <img src="{{ $attendance->user->getFilamentAvatarUrl() }}" class="w-10 h-10 rounded-full border-2 border-primary-100 dark:border-primary-900">
                    <div>
                        <div class="font-bold text-gray-900 dark:text-gray-100 line-clamp-1">{{ $attendance->user->name }}</div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">{{ __('admin.real_attendance.tracker.present') }}</div>
                    </div>
                </div>
                <button
                    wire:click="removeAttendance({{ $attendance->id }})"
                    class="p-2 text-gray-400 hover:text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg transition-all"
                    title="{{ __('admin.real_attendance.tracker.remove_from_attendance') }}"
                >
                    <i class="fa-light fa-trash-can"></i>
                </button>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-gray-50 dark:bg-gray-800/50 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800">
                <div class="text-gray-400 mb-2 text-4xl">
                    <i class="fa-light fa-users-slash"></i>
                </div>
                <div class="text-gray-500 font-medium">{{ __('admin.real_attendance.tracker.no_attendance_yet') }}</div>
                <div class="text-sm text-gray-400 mt-1">{{ __('admin.real_attendance.tracker.use_search_to_add') }}</div>
            </div>
        @endforelse
    </div>

</div>
