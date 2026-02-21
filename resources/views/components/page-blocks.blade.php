@props(['blocks' => []])

<div class="page-blocks">
    @foreach($blocks as $block)
        @php
            // Filament Builder vrací pole s 'type' a 'data'
            $type = $block['type'] ?? null;
            $data = $block['data'] ?? [];

            // Pokročilé nastavení z BlockRegistry (Expert UX)
            $customId = $data['custom_id'] ?? null;
            $customClass = $data['custom_class'] ?? null;
            $customAttributes = $data['custom_attributes'] ?? [];

            $attributesString = '';
            foreach($customAttributes as $attr) {
                if (!empty($attr['name'])) {
                    $attributesString .= ' ' . e($attr['name']) . '="' . e($attr['value'] ?? '') . '"';
                }
            }
        @endphp

        @if($type)
            <div
                @if($customId) id="{{ $customId }}" @endif
                class="block-wrapper block-{{ $type }} {{ $customClass }}"
                {!! $attributesString !!}
            >
                @includeFirst(["public.blocks.{$type}", "public.blocks.fallback"], ['data' => $data, 'type' => $type, 'block' => $block])
            </div>
        @endif
    @endforeach
</div>
