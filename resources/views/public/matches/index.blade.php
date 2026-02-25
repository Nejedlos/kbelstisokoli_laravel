@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('matches.title')"
        :subtitle="__('matches.subtitle')"
        :breadcrumbs="[__('matches.breadcrumbs') => null]"
    />

    <div class="bg-slate-50 border-b border-slate-200">
        <div class="container">
            <div class="flex items-center space-x-8">
                <a href="{{ route('public.matches.index', ['type' => 'upcoming']) }}"
                   class="py-6 border-b-2 font-black uppercase tracking-widest text-sm transition-colors {{ $type === 'upcoming' ? 'border-primary text-secondary' : 'border-transparent text-slate-400 hover:text-secondary' }}">
                    {{ __('matches.upcoming') }}
                </a>
                <a href="{{ route('public.matches.index', ['type' => 'latest']) }}"
                   class="py-6 border-b-2 font-black uppercase tracking-widest text-sm transition-colors {{ $type === 'latest' ? 'border-primary text-secondary' : 'border-transparent text-slate-400 hover:text-secondary' }}">
                    {{ __('matches.latest') }}
                </a>
            </div>
        </div>
    </div>

    <div class="section-padding bg-bg">
        <div class="container">
            @if($matches->isEmpty())
                <x-empty-state
                    :title="$type === 'upcoming' ? __('matches.empty_upcoming') : __('matches.empty_latest')"
                    :subtitle="__('matches.empty_subtitle')"
                />
            @else
                <div class="flex flex-col gap-6">
                    @foreach($matches as $match)
                        <x-match-card :match="$match" />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $matches->appends(['type' => $type])->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
