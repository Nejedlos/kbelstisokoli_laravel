@extends('layouts.public')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Tréninkové informace</h1>

    @if($teams->isEmpty())
        <p class="text-gray-600">Aktuálně nejsou k dispozici žádné informace o trénincích.</p>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            @foreach($teams as $team)
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h2 class="text-2xl font-bold mb-4 text-primary border-b pb-2">{{ $team->name }}</h2>
                    <div class="mb-4 text-gray-700">
                        {{ $team->description ?? 'Popis týmu nebyl zadán.' }}
                    </div>

                    <h3 class="text-lg font-semibold mb-3">Nadcházející tréninky</h3>
                    @if($team->trainings->isEmpty())
                        <p class="text-sm text-gray-500 italic">Pro tento tým nejsou vypsány žádné nadcházející tréninky.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($team->trainings as $training)
                                <div class="flex items-start gap-4 p-3 bg-gray-50 rounded-md">
                                    <div class="bg-primary text-white p-2 rounded text-center min-w-[60px]">
                                        <div class="text-xs uppercase">{{ $training->starts_at->format('M') }}</div>
                                        <div class="text-lg font-bold">{{ $training->starts_at->format('d') }}</div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ $training->starts_at->format('H:i') }} - {{ $training->ends_at->format('H:i') }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ $training->location ?? 'Místo neuvedeno' }}
                                        </div>
                                        @if($training->notes)
                                            <div class="text-xs text-gray-500 mt-1 italic">
                                                {{ $training->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
