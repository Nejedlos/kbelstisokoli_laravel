<!-- Bulk Actions Panel (Modern Glassmorphism / Bottom Sheet) -->
<div x-show="selectedEvents.length > 0"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="translate-y-full opacity-0 sm:scale-95"
     x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
     x-transition:leave-end="translate-y-full opacity-0 sm:scale-95"
     class="fixed bottom-0 sm:bottom-8 left-0 sm:left-1/2 sm:-translate-x-1/2 z-[9999] w-full sm:w-[calc(100%-1.5rem)] sm:max-w-[840px] sm:px-4 pointer-events-none"
     x-cloak>
     <div class="pointer-events-auto relative overflow-hidden bg-white/95 backdrop-blur-3xl rounded-t-[2.5rem] sm:rounded-[2.5rem] shadow-[0_-15px_40px_-10px_rgba(0,0,0,0.1),0_30px_60px_-12px_rgba(0,0,0,0.25)] border-t sm:border border-white p-4 sm:p-5">
        <!-- Mobile Drag Handle -->
        <div class="sm:hidden w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-5"></div>

        <!-- Inner glow/shine -->
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
            <!-- Info Badge -->
            <div class="flex items-center gap-3 bg-slate-50/80 px-4 py-3 sm:py-2.5 rounded-2xl border border-slate-200/50 w-full sm:w-auto shrink-0">
                <div class="relative">
                    <div class="absolute inset-0 bg-primary/20 rounded-xl blur-md animate-pulse"></div>
                    <div class="relative w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white shadow-lg shadow-primary/30">
                        <i class="fa-light fa-layer-group text-lg"></i>
                    </div>
                </div>
                <div class="flex flex-col">
                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 leading-none mb-1">
                        {{ __('member.attendance.bulk_actions.title') }}
                    </span>
                    <span class="text-sm font-black text-secondary leading-none">
                        {!! __('member.attendance.bulk_actions.selected', ['count' => '<span x-text="selectedEvents.length" class="text-primary"></span>']) !!}
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 w-full sm:flex-1 sm:justify-end pb-4 sm:pb-0" x-data="{
                openDecline: false,
                async submitBulk(status, reason = null) {
                    $dispatch('loading-start');
                    const formData = new FormData();
                    selectedEvents.forEach(id => formData.append('events[]', id));
                    formData.append('status', status);
                    if (reason) formData.append('excuse_reason', reason);
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        const response = await fetch('{{ route('member.attendance.bulk-store') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            $dispatch('loading-stop');

                            const eventIds = result.events;
                            const animateClass = status === 'confirmed' ? 'animate-pulse-success' : 'animate-pulse-danger';

                            const statusMap = {
                                'confirmed': {
                                    label: '{{ __('member.attendance.status.confirmed') }}',
                                    badgeClass: 'bg-success-100 text-success-700',
                                    stripClass: 'bg-emerald-500',
                                    icon: 'fa-light fa-circle-check text-[10px]'
                                },
                                'declined': {
                                    label: '{{ __('member.attendance.status.declined') }}',
                                    badgeClass: 'bg-danger-100 text-danger-700',
                                    stripClass: 'bg-rose-500',
                                    icon: 'fa-light fa-circle-xmark text-[10px]'
                                }
                            };

                            const newStatus = statusMap[status];

                            // Krátká pauza aby loader zmizel dřív než začne blikání
                            setTimeout(() => {
                                // Animace a aktualizace obsahu karet
                                eventIds.forEach(id => {
                                    const card = document.getElementById('event-card-' + id.replace(':', '-'));
                                    if (card) {
                                        // Okamžitá aktualizace obsahu před reloadem
                                        const strip = card.querySelector('.js-event-status-strip');
                                        const badge = card.querySelector('.js-event-status-badge');
                                        const label = card.querySelector('.js-event-status-label');
                                        const icon = badge ? badge.querySelector('i') : null;

                                        if (strip) {
                                            strip.className = strip.className.replace(/bg-(emerald|rose|slate)-\d+/, '');
                                            strip.classList.add(newStatus.stripClass);
                                        }

                                        if (badge) {
                                            badge.className = badge.className.replace(/bg-(success|danger|slate|warning)-\d+ text-(success|danger|slate|warning)-\d+/, '');
                                            badge.classList.add(...newStatus.badgeClass.split(' '));
                                        }

                                        if (label) {
                                            label.textContent = newStatus.label;
                                        }

                                        if (icon) {
                                            icon.className = newStatus.icon;
                                        }

                                        // Spuštění animace
                                        card.classList.add(animateClass);
                                    }
                                });
                            }, 100);

                            // Vyčistit výběr hned po odeslání, aby panel zmizel
                            selectedEvents = [];
                            this.openDecline = false;
                        } else {
                            // Standardní hláška při erroru
                            $dispatch('loading-stop');
                            alert(result.message || 'Chyba při ukládání docházky.');
                        }
                    } catch (error) {
                        console.error('Bulk attendance error:', error);
                        alert('Došlo k chybě při komunikaci se serverem.');
                        $dispatch('loading-stop');
                    }
                }
            }">
                <button type="button" @click="submitBulk('confirmed')" class="group/btn w-full sm:w-auto h-11 px-3.5 sm:px-5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl text-[10px] sm:text-[11px] font-black uppercase tracking-widest transition-all hover:shadow-xl hover:shadow-emerald-500/30 flex items-center justify-center gap-2 active:scale-95">
                    <i class="fa-light fa-check-circle text-base group-hover/btn:scale-110 transition-transform"></i>
                    <span class="hidden xs:inline">{{ __('member.attendance.bulk_actions.confirm') }}</span>
                    <span class="xs:hidden">{{ __('member.attendance.status.confirmed') }}</span>
                </button>

                <div class="flex-1 sm:flex-none relative">
                    <button @click="openDecline = !openDecline"
                            id="decline-bulk-btn"
                            type="button"
                            class="group/btn w-full sm:w-auto h-11 px-3.5 sm:px-5 bg-rose-500 hover:bg-rose-600 text-white rounded-2xl text-[10px] sm:text-[11px] font-black uppercase tracking-widest transition-all hover:shadow-xl hover:shadow-rose-500/30 flex items-center justify-center gap-2 active:scale-95">
                        <i class="fa-light fa-times-circle text-base group-hover/btn:scale-110 transition-transform"></i>
                        <span class="hidden xs:inline">{{ __('member.attendance.bulk_actions.decline') }}</span>
                        <span class="xs:hidden">{{ __('member.attendance.status.declined') }}</span>
                        <i class="fa-light fa-chevron-up text-[10px] ml-1 transition-transform" :class="openDecline ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- Decline Reasons Dropdown (Teleported for visibility) -->
                    <template x-teleport="body">
                        <div id="bulk-excuse-dropdown"
                             x-show="openDecline"
                             x-floating.top.fixed="'#decline-bulk-btn'"
                             x-effect="if (openDecline) { $nextTick(() => { const el = document.getElementById('bulk-excuse-dropdown'); if (el) el.dispatchEvent(new CustomEvent('reposition')); }) }"
                             @click.away="openDecline = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                             class="w-max bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-[10001]"
                             x-cloak>
                            <div class="p-2 overflow-y-auto ks-scrollbar" style="max-height: inherit;">
                                <div class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 mb-1">
                                    {{ __('member.attendance.excuse_reason') }}
                                </div>
                                @foreach(\App\Enums\ExcuseReason::cases() as $reason)
                                    <button type="button" @click="submitBulk('declined', '{{ $reason->value }}')" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-rose-50 rounded-xl transition-colors group/item text-left">
                                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-500 group-hover/item:bg-rose-100 group-hover/item:text-rose-600 transition-colors">
                                            <i class="{{ $reason->getIcon() }} text-sm"></i>
                                        </div>
                                        <span class="text-xs font-bold text-secondary group-hover/item:text-rose-700 transition-colors">{{ $reason->getLabel() }}</span>
                                    </button>
                                @endforeach
                                <div class="mt-1 pt-1 border-t border-slate-100">
                                    <button type="button" @click="submitBulk('declined')" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 rounded-xl transition-colors group/item text-left">
                                         <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover/item:bg-slate-100 transition-colors">
                                            <i class="fa-light fa-minus text-sm"></i>
                                        </div>
                                        <span class="text-xs font-bold text-slate-500">{{ __('member.attendance.bulk_actions.decline_no_reason') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <button @click="selectedEvents = []" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-slate-100 text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all shrink-0 border border-slate-200/50" title="{{ __('member.attendance.bulk_actions.clear') }}">
                    <i class="fa-light fa-xmark text-lg"></i>
                </button>
            </div>
        </div>
     </div>
</div>
