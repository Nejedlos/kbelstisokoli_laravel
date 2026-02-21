@props(['blocks' => []])

<div class="page-blocks">
    @foreach($blocks as $block)
        @php
            // Filament Builder vrací pole s 'type' a 'data'
            $type = $block['type'] ?? null;
            $data = $block['data'] ?? [];
        @endphp

        @if($type)
            @includeFirst(["public.blocks.{$type}", "public.blocks.fallback"], ['data' => $data, 'type' => $type, 'block' => $block])
        @endif
    @endforeach
</div>
