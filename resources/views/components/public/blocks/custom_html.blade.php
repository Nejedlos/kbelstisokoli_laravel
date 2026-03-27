<section class="block-custom-html py-8">
    <div class="container mx-auto px-4">
        {!! \App\Support\HtmlSanitizer::clean($data['html'] ?? '', true) !!}
    </div>
</section>
