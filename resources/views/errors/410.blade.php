@extends('layouts.public')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
    <div class="max-w-max mx-auto">
        <main class="sm:flex">
            <p class="text-4xl font-extrabold text-gray-400 sm:text-5xl">410</p>
            <div class="sm:ml-6">
                <div class="sm:border-l sm:border-gray-200 sm:pl-6">
                    <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight sm:text-5xl">Obsah byl odstraněn</h1>
                    <p class="mt-1 text-base text-gray-500 italic">Tato akce už skončila... obsah, který hledáte, byl trvale odstraněn.</p>
                </div>
                <div class="mt-10 flex space-x-3 sm:border-l sm:border-transparent sm:pl-6">
                    <a href="{{ route('public.home') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Zpět na úvod
                    </a>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
