@props(['blocks' => [], 'animate' => false, 'breadcrumbs' => null])

<div class="page-blocks">
    @forelse($blocks as $block)
        @php
            // Filament Builder vrací pole s 'type' a 'data'
            $type = $block['type'] ?? null;
            $data = brand_text($block['data'] ?? []);

            // Respektuj viditelnost bloku
            $visible = data_get($data, 'is_visible', true);

            // Pokročilé nastavení z BlockRegistry (Expert UX)
            $customId = $data['custom_id'] ?? null;
            $customClass = $data['custom_class'] ?? '';
            $customAttributes = $data['custom_attributes'] ?? [];

            $attributesString = '';
            foreach($customAttributes as $attr) {
                if (!empty($attr['name'])) {
                    $attributesString .= ' ' . e($attr['name']) . '="' . e($attr['value'] ?? '') . '"';
                }
            }

            // Animace (AOS)
            if ($animate) {
                $attributesString .= ' data-aos="fade-up" data-aos-delay="' . ($loop->index * 100) . '"';
            }
        @endphp

        @if($type && $visible)
            <div
                @if($customId) id="{{ $customId }}" @endif
                class="block-wrapper block-{{ $type }} {{ $customClass }}"
                {!! $attributesString !!}
            >
                @includeFirst(["public.blocks.{$type}", "public.blocks.fallback"], [
                    'data' => $data,
                    'type' => $type,
                    'block' => $block,
                    'breadcrumbs' => ($loop->first && $type === 'hero') ? $breadcrumbs : null
                ])
            </div>
        @elseif(!$type)
            @include('public.blocks.fallback', ['data' => [], 'type' => 'unknown'])
        @endif
    @empty
        <div class="container py-20 text-center text-slate-500">Zatím zde nejsou žádné bloky obsahu.</div>
    @endforelse
</div>
