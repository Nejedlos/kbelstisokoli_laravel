@extends('layouts.member', [
    'title' => 'Můj program',
    'subtitle' => 'Přehled nadcházejících tréninků, zápasů a klubových akcí.'
])

@section('content')
    <div class="space-y-6">
        @if($program->isEmpty())
            <div class="card p-12 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                    <x-heroicon-o-calendar class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold text-secondary mb-2">Žádné nadcházející akce</h3>
                <p class="text-slate-500 max-w-sm mx-auto">V nejbližší době nemáte naplánované žádné tréninky ani zápasy.</p>
            </div>
        @else
            <div class="flex flex-col gap-4">
                @foreach($program as $event)
                    <x-member.event-card :event="$event" />
                @endforeach
            </div>
        @endif

        <div class="pt-6 border-t border-slate-200">
            <a href="{{ route('member.attendance.history') }}" class="btn btn-outline w-full sm:w-auto">
                Zobrazit historii mých odpovědí
            </a>
        </div>
    </div>
@endsection
