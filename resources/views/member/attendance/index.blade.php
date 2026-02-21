@extends('layouts.member')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Můj program a docházka</h1>

    @if(session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse($program as $item)
            @php
                $event = $item['data'];
                $type = $item['type'];
                $attendance = $event->attendances->first();
                $status = $attendance?->status ?? 'pending';
            @endphp

            <div class="bg-white shadow rounded-lg p-4 border-l-4 {{ match($status) {
                'confirmed' => 'border-green-500',
                'declined' => 'border-red-500',
                'maybe' => 'border-yellow-500',
                default => 'border-gray-300'
            } }}">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider px-2 py-1 rounded {{ match($type) {
                            'training' => 'bg-blue-100 text-blue-800',
                            'match' => 'bg-red-100 text-red-800',
                            'event' => 'bg-purple-100 text-purple-800',
                            default => 'bg-gray-100'
                        } }}">
                            {{ match($type) { 'training' => 'Trénink', 'match' => 'Zápas', 'event' => 'Akce', default => $type } }}
                        </span>
                        <h2 class="text-lg font-bold mt-1">
                            @if($type === 'match')
                                {{ $event->is_home ? 'Doma' : 'Venku' }} vs {{ $event->opponent->name }}
                            @else
                                {{ $event->title ?? ($type === 'training' ? 'Trénink ' . $event->team->name : 'Událost') }}
                            @endif
                        </h2>
                        <p class="text-sm text-gray-600">
                            {{ $item['time']->format('d.m.Y H:i') }} | {{ $event->location }}
                        </p>
                    </div>

                    <div class="text-right">
                        <span class="text-sm font-medium {{ match($status) {
                            'confirmed' => 'text-green-600',
                            'declined' => 'text-red-600',
                            'maybe' => 'text-yellow-600',
                            default => 'text-gray-500'
                        } }}">
                            {{ match($status) {
                                'confirmed' => 'Potvrzeno',
                                'declined' => 'Omluveno',
                                'maybe' => 'Možná',
                                default => 'Nepotvrzeno'
                            } }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <form action="{{ route('member.attendance.store', [$type, $event->id]) }}" method="POST" class="flex gap-2 w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-green-500 text-white text-sm font-bold rounded hover:bg-green-600 transition">
                            Přijdu
                        </button>
                    </form>

                    <form action="{{ route('member.attendance.store', [$type, $event->id]) }}" method="POST" class="flex gap-2 w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="status" value="declined">
                        <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-red-500 text-white text-sm font-bold rounded hover:bg-red-600 transition">
                            Nepřijdu
                        </button>
                    </form>

                    <form action="{{ route('member.attendance.store', [$type, $event->id]) }}" method="POST" class="flex gap-2 w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="status" value="maybe">
                        <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-yellow-500 text-white text-sm font-bold rounded hover:bg-yellow-600 transition">
                            Možná
                        </button>
                    </form>
                </div>

                @if($status === 'declined' || $status === 'maybe')
                    <div class="mt-3">
                        <form action="{{ route('member.attendance.store', [$type, $event->id]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="{{ $status }}">
                            <div class="flex gap-2">
                                <input type="text" name="note" placeholder="Důvod / Poznámka..." value="{{ $attendance?->note }}" class="flex-1 text-sm border rounded px-3 py-2">
                                <button type="submit" class="px-3 py-2 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">Uložit</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-center text-gray-500 py-10">Žádné nadcházející události nebyly nalezeny.</p>
        @endforelse
    </div>
</div>
@endsection
