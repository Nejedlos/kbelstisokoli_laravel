@php
    $content = $data['content'] ?? '';
    $style = $data['style'] ?? 'default';
@endphp

<section @class([
    'block-rich-text py-16',
    'bg-white' => $style === 'default',
    'bg-slate-50' => $style === 'muted',
    'bg-secondary text-white' => $style === 'dark',
])>
    <div @class([
        'container mx-auto px-4',
        'prose prose-lg lg:prose-xl max-w-4xl',
        'prose-slate' => $style !== 'dark',
        'prose-invert' => $style === 'dark',
    ])>
        {!! $content !!}
    </div>
</section>
