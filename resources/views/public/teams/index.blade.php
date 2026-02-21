@extends('layouts.public')

@section('content')
    <x-page-header
        title="Naše týmy"
        subtitle="Přehled všech věkových kategorií našeho basketbalového oddílu."
        :breadcrumbs="['Týmy' => null]"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            @if($teams->isEmpty())
                <x-empty-state
                    title="Zatím žádné týmy"
                    subtitle="Aktuálně připravujeme soupisky pro novou sezónu."
                />
            @else
                @foreach($teams as $category => $categoryTeams)
                    <div class="mb-16 last:mb-0">
                        <div class="flex items-center mb-8">
                            <h2 class="text-3xl font-black uppercase tracking-tight text-secondary mr-6">
                                {{ match($category) {
                                    'youth' => 'Mládež',
                                    'senior' => 'Dospělí',
                                    default => 'Ostatní'
                                } }}
                            </h2>
                            <div class="flex-1 h-px bg-slate-200"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            @foreach($categoryTeams as $team)
                                <div class="card card-hover group">
                                    <div class="bg-secondary p-8 flex items-center justify-center border-b border-white/5 relative overflow-hidden">
                                        <div class="absolute inset-0 bg-primary opacity-0 group-hover:opacity-10 transition-opacity"></div>
                                        <span class="text-6xl font-black text-white opacity-10 group-hover:scale-110 transition-transform duration-500">{{ $team->slug }}</span>
                                    </div>
                                    <div class="p-6">
                                        <h3 class="text-xl font-black uppercase tracking-tight mb-2 group-hover:text-primary transition-colors">
                                            {{ $team->name }}
                                        </h3>
                                        @if($team->description)
                                            <p class="text-slate-600 text-sm mb-6 line-clamp-2">{{ $team->description }}</p>
                                        @endif

                                        <div class="flex items-center justify-between mt-auto">
                                            <div class="flex -space-x-2">
                                                @for($i=0; $i<3; $i++)
                                                    <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-400">?</div>
                                                @endfor
                                            </div>
                                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Přejít na soupisku &rarr;</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
