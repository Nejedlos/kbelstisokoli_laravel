@extends('layouts.member', [
    'title' => $title ?? __('nav.my_statistics'),
])

@section('content')
    <div class="space-y-10">
        <div class="card p-12 text-center space-y-4 bg-white/60 backdrop-blur-sm border-dashed">
            <div class="w-20 h-20 rounded-3xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-light fa-chart-bar text-4xl text-slate-300"></i>
            </div>
            <h3 class="text-xl font-black uppercase tracking-tight text-secondary">{{ __('Vaše osobní statistiky') }}</h3>
            <p class="text-slate-500 max-w-md mx-auto italic font-medium">
                {{ __('Na této sekci právě pracujeme. Již brzy zde uvidíte své body, doskoky i další individuální výkony.') }}
            </p>
            <div class="pt-6">
                <a href="{{ route('member.statistics.index') }}" class="btn btn-outline">{{ __('Zpět na přehled') }}</a>
            </div>
        </div>
    </div>
@endsection
