@php
    $proposal = \App\Filament\Resources\ExternalEntityMappings\Tables\ExternalEntityMappingsTable::getMappingProposal($getRecord());
    $user = $proposal['user'];
    $isGhost = $proposal['is_ghost'];
    $isDuplicate = $proposal['is_duplicate'];
    $isMatched = (bool)$getRecord()->internal_id;
@endphp

<div class="flex flex-col gap-1 py-2">
    @if($isMatched)
        <span class="text-xs text-gray-400 italic">Již spárováno</span>
    @elseif($user)
        <div class="flex items-center gap-2">
            @if($isGhost)
                <span class="px-2 py-0.5 rounded-full bg-warning-100 text-warning-700 text-[10px] font-bold uppercase tracking-wider" title="Existuje Ghost profil s tímto jménem">
                    <i class="fa-light fa-ghost"></i> Ghost nalezen
                </span>
            @else
                <span class="px-2 py-0.5 rounded-full bg-success-100 text-success-700 text-[10px] font-bold uppercase tracking-wider" title="Nalezen přesný uživatel v naší databázi">
                    <i class="fa-light fa-user-check"></i> Shoda nalezena
                </span>
            @endif
        </div>
        <div class="text-sm font-medium">
            {{ $user->name }}
        </div>
        <div class="text-[10px] text-gray-500">
            ID: #{{ $user->id }} | {{ $user->email }}
        </div>
    @else
        <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[10px] font-bold uppercase tracking-wider">
                <i class="fa-light fa-plus-circle"></i> Bude vytvořen Ghost
            </span>
        </div>
        <div class="text-[10px] text-gray-400 mt-1 italic leading-tight">
            Nenašli jsme shodu podle jména. Při první synchronizaci statistik bude vytvořen dočasný profil.
        </div>
    @endif

    @if($isDuplicate)
        <div class="mt-1 flex items-center gap-1 text-danger-600 font-bold text-[10px] bg-danger-50 px-2 py-1 rounded border border-danger-100">
            <i class="fa-light fa-triangle-exclamation"></i> Pozor: Duplicita (Uživatel + Ghost)
        </div>
    @endif
</div>
