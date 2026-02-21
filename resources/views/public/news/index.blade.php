@extends('layouts.public')

@section('content')
    <x-page-header
        title="Aktuality"
        subtitle="Sledujte nejnovější dění v našem basketbalovém oddíle."
        :breadcrumbs="['Novinky' => null]"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            @if($posts->isEmpty())
                <x-empty-state
                    title="Zatím žádné novinky"
                    subtitle="Aktuálně pro vás nepřipravujeme žádné články, ale brzy se to změní."
                />
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($posts as $post)
                        <x-news-card :post="$post" />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
