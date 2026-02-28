@extends('layouts.member', [
    'title' => __('member.economy.title'),
    'subtitle' => __('member.economy.subtitle')
])

@section('content')
    <div class="space-y-10">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-member.kpi-card
                :title="__('member.economy.kpi.total_to_pay')"
                :value="number_format($summary['total_to_pay'], 0, ',', ' ') . ' Kč'"
                icon="heroicon-o-banknotes"
                color="primary"
            />
            <x-member.kpi-card
                :title="__('member.economy.kpi.overdue')"
                :value="number_format($summary['overdue_amount'], 0, ',', ' ') . ' Kč'"
                icon="heroicon-o-clock"
                color="danger"
            />
            <x-member.kpi-card
                :title="__('member.economy.kpi.paid_total')"
                :value="number_format($summary['paid_total'], 0, ',', ' ') . ' Kč'"
                icon="heroicon-o-check-badge"
                color="success"
            />
        </div>

        <!-- Open Charges (To Pay) -->
        <div class="space-y-6">
            <h3 class="text-lg font-black uppercase tracking-tight text-secondary">{{ __('member.economy.to_pay_title') }}</h3>

            @if($openCharges->isEmpty())
                <div class="card p-8 text-center border-dashed border-2 border-slate-200 bg-transparent shadow-none">
                    <p class="text-slate-400 italic text-sm">{{ __('member.economy.no_open_charges') }}</p>
                </div>
            @else
                    <div class="flex flex-col gap-4">
                    @foreach($openCharges as $charge)
                        <div class="card p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-6 border-l-4 {{ $charge->is_overdue ? 'border-l-danger' : 'border-l-primary' }}">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded bg-slate-100 text-slate-500">
                                        {{ match($charge->charge_type) {
                                            'membership_fee' => __('member.economy.charge_types.membership_fee'),
                                            'camp_fee' => __('member.economy.charge_types.camp_fee'),
                                            'tournament_fee' => __('member.economy.charge_types.tournament_fee'),
                                            default => __('member.economy.charge_types.other')
                                        } }}
                                    </span>
                                    @if($charge->is_overdue)
                                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded bg-danger-50 text-danger-600">{{ __('member.economy.overdue_badge') }}</span>
                                    @endif
                                </div>
                                <h4 class="font-black text-base sm:text-lg text-secondary leading-tight truncate tracking-tight">{{ $charge->title }}</h4>
                                @if($charge->due_date)
                                    <p class="text-[11px] sm:text-xs text-slate-500 font-bold italic mt-1">{{ __('member.economy.due_date') }} <span class="text-secondary">{{ $charge->due_date->format('d. m. Y') }}</span></p>
                                @endif
                            </div>

                            <div class="flex items-end md:items-center justify-between md:justify-end gap-4 border-t md:border-t-0 border-slate-100 pt-4 md:pt-0">
                                <div class="text-left md:text-right leading-none">
                                    <div class="text-xl sm:text-2xl font-black text-secondary">{{ number_format($charge->amount_remaining, 0, ',', ' ') }} Kč</div>
                                    @if($charge->amount_paid > 0)
                                        <div class="text-[9px] sm:text-[10px] font-black text-success uppercase tracking-widest mt-1">{{ __('member.economy.paid_amount') }} {{ number_format($charge->amount_paid, 0, ',', ' ') }} Kč</div>
                                    @endif
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-primary/10 group-hover:text-primary transition-all shrink-0">
                                    <i class="fa-light fa-chevron-right text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- History (Recent Payments) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <div class="space-y-6">
                <h3 class="text-lg font-black uppercase tracking-tight text-secondary">{{ __('member.economy.recent_payments') }}</h3>
                @if($recentPayments->isEmpty())
                    <p class="text-sm text-slate-400 italic">{{ __('member.economy.no_recent_payments') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach($recentPayments as $payment)
                            <div class="bg-white rounded-club p-4 border border-slate-100 shadow-sm flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-success/10 flex items-center justify-center text-success">
                                        <x-heroicon-o-arrow-down-left class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-secondary">{{ number_format($payment->amount, 0, ',', ' ') }} Kč</div>
                                        <div class="text-[10px] text-slate-500 font-medium uppercase tracking-widest">{{ $payment->paid_at->format('d. m. Y') }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded bg-slate-50 text-slate-400">
                                        {{ $payment->variable_symbol ?: __('member.economy.no_variable_symbol') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-black uppercase tracking-tight text-secondary">{{ __('member.economy.paid_charges') }}</h3>
                @if($paidCharges->isEmpty())
                    <p class="text-sm text-slate-400 italic">{{ __('member.economy.no_paid_charges') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach($paidCharges as $charge)
                            <div class="bg-white rounded-club p-4 border border-slate-100 shadow-sm flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400">
                                        <x-heroicon-o-check class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-600">{{ $charge->title }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">{{ __('member.economy.paid_amount') }} {{ $charge->updated_at->format('d. m. Y') }}</div>
                                    </div>
                                </div>
                                <div class="text-right font-black text-success text-sm">OK</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Bank Info (Placeholder) -->
        <div class="card p-6 sm:p-10 bg-secondary text-white relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-[0.05] group-hover:scale-110 transition-transform duration-1000">
                <i class="fa-light fa-bank text-[120px] sm:text-[180px]"></i>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center relative z-10">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-primary leading-none mb-3">{{ __('member.economy.bank_info.title') }}</h3>
                        <p class="text-[13px] sm:text-sm text-slate-300 font-medium leading-relaxed italic opacity-80">{{ __('member.economy.bank_info.text') }}</p>
                    </div>

                    <div class="space-y-3 pt-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-white/10 pb-3 gap-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-white/40">{{ __('member.economy.bank_info.account_number') }}</span>
                            <span class="font-bold text-base sm:text-lg">123456789 / 0100</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-white/10 pb-3 gap-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-white/40">{{ __('member.economy.bank_info.bank') }}</span>
                            <span class="font-bold text-sm">Komerční banka a.s.</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-white/10 pb-3 gap-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-white/40">{{ __('member.economy.bank_info.recipient_message') }}</span>
                            <span class="font-bold text-sm">Jméno a příjmení člena</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-center gap-4">
                    <div class="w-40 h-40 sm:w-48 sm:h-48 bg-white rounded-[2rem] p-4 flex items-center justify-center shadow-2xl relative group/qr">
                        <x-heroicon-o-qr-code class="w-32 h-32 text-secondary" />
                        <div class="absolute inset-0 bg-secondary/80 backdrop-blur-[2px] opacity-0 group-hover/qr:opacity-100 transition-all rounded-[2rem] flex flex-col items-center justify-center p-6 text-center">
                            <i class="fa-light fa-magnifying-glass-plus text-2xl mb-2"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest text-white leading-tight">{{ __('member.economy.bank_info.qr_payment') }}</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-white/40 italic">{{ __('member.economy.bank_info.qr_payment') }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
