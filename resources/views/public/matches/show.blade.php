@extends('layouts.public')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-4">
        <a href="{{ route('public.matches.index') }}" class="text-primary hover:underline">&larr; Zpět na seznam zápasů</a>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-100">
        <div class="bg-primary text-white p-8 text-center">
            <div class="text-sm uppercase tracking-widest mb-2 opacity-80">{{ $match->season->name }} | {{ $match->scheduled_at->format('d.m.Y H:i') }}</div>
            <div class="flex justify-center items-center gap-12">
                <div class="text-right flex-1">
                    <div class="text-3xl font-bold">{{ $match->team->name }}</div>
                </div>
                <div class="text-5xl font-black">
                    @if($match->status === 'completed')
                        {{ $match->score_home }} : {{ $match->score_away }}
                    @else
                        vs
                    @endif
                </div>
                <div class="text-left flex-1">
                    <div class="text-3xl font-bold">{{ $match->opponent->name }}</div>
                </div>
            </div>
            <div class="mt-4 text-lg opacity-90">{{ $match->location }}</div>
            @if($match->status !== 'completed' && $match->status !== 'planned')
                <div class="mt-4">
                    <span class="bg-yellow-400 text-black px-4 py-1 rounded-full font-bold uppercase text-sm">
                        {{ ucfirst($match->status) }}
                    </span>
                </div>
            @endif
        </div>

        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Informace k zápasu</h2>
                    <div class="prose max-w-none">
                        {!! nl2br(e($match->notes_public)) !!}
                    </div>
                    @if(!$match->notes_public)
                        <p class="text-gray-500 italic">K tomuto zápasu nejsou žádné veřejné informace.</p>
                    @endif
                </div>
                <div>
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Reportáž (připravujeme)</h2>
                    <p class="text-gray-600">Zde se brzy objeví podrobné statistiky a reportáž ze zápasu.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
