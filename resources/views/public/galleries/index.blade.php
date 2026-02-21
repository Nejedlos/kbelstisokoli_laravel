@extends('layouts.public')

@section('content')
    <x-page-header
        title="Fotogalerie"
        subtitle="Nahlédněte do života našeho oddílu ###TEAM_NAME###."
        :breadcrumbs="['Galerie' => null]"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            @if($galleries->isEmpty())
                <x-empty-state
                    title="Žádné galerie"
                    subtitle="Aktuálně zde nejsou žádné veřejné fotogalerie."
                />
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($galleries as $gallery)
                        <a href="{{ route('public.galleries.show', $gallery->slug) }}" class="card card-hover flex flex-col h-full group">
                            <div class="aspect-video relative overflow-hidden bg-slate-200">
                                @if($gallery->coverAsset)
                                    <img
                                        src="{{ $gallery->coverAsset->getUrl('large') }}"
                                        alt="{{ $gallery->title }}"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute top-4 left-4">
                                    <span class="px-3 py-1 bg-primary text-white text-xs font-black uppercase tracking-widest rounded-full shadow-lg">
                                        {{ $gallery->media_assets_count ?: $gallery->mediaAssets->count() }} fotek
                                    </span>
                                </div>
                            </div>
                            <div class="p-6 flex-1 flex flex-col">
                                <h3 class="text-xl font-bold mb-2 group-hover:text-primary transition-colors">
                                    {{ brand_text($gallery->title) }}
                                </h3>
                                @if($gallery->description)
                                    <p class="text-slate-600 text-sm line-clamp-2 mb-4">
                                        {{ brand_text($gallery->description) }}
                                    </p>
                                @endif
                                <div class="mt-auto pt-4 flex items-center text-primary text-sm font-black uppercase tracking-widest">
                                    Prohlédnout galerii &rarr;
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $galleries->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
