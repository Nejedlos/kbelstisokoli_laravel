@extends('layouts.public')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Zápasy</h1>

    @if($matches->isEmpty())
        <p class="text-gray-600">Aktuálně nejsou naplánovány žádné zápasy.</p>
    @else
        <div class="space-y-4">
            @foreach($matches as $match)
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex justify-between items-center">
                    <div class="flex-1">
                        <div class="text-sm text-gray-500 mb-1">
                            {{ $match->scheduled_at->format('d.m.Y H:i') }} | {{ $match->season->name }}
                        </div>
                        <div class="text-lg font-semibold">
                            {{ $match->team->name }} vs. {{ $match->opponent->name }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $match->location ?? 'Místo neuvedeno' }}
                        </div>
                    </div>

                    @if($match->status === 'completed')
                        <div class="text-2xl font-bold ml-8">
                            {{ $match->score_home }} : {{ $match->score_away }}
                        </div>
                    @else
                        <div class="ml-8">
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucfirst($match->status) }}
                            </span>
                        </div>
                    @endif

                    <div class="ml-8">
                        <a href="{{ route('public.matches.show', $match->id) }}" class="text-primary hover:underline">
                            Detail
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $matches->links() }}
        </div>
    @endif
</div>
@endsection
