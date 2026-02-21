@extends('layouts.public')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-16 sm:px-6 sm:py-24 md:grid-cols-2 lg:px-8">
    <div class="max-w-max mx-auto">
        <main class="sm:flex">
            <p class="text-4xl font-extrabold text-primary-600 sm:text-5xl">404</p>
            <div class="sm:ml-6">
                <div class="sm:border-l sm:border-gray-200 sm:pl-6">
                    <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight sm:text-5xl">Stránka nenalezena</h1>
                    <p class="mt-1 text-base text-gray-500 italic">Mimo vymezené území... tenhle míč skončil v autu.</p>
                </div>
                <div class="mt-10 flex space-x-3 sm:border-l sm:border-transparent sm:pl-6">
                    <a href="{{ route('public.home') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Zpět na úvod
                    </a>
                    <a href="{{ route('public.contact.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Kontaktujte nás
                    </a>
                </div>
                <div class="mt-8 sm:border-l sm:border-transparent sm:pl-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Užitečné odkazy</h3>
                    <ul role="list" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <li>
                            <a href="{{ route('public.news.index') }}" class="text-base font-medium text-gray-900 hover:text-primary-600">
                                Aktuality a novinky
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('public.matches.index') }}" class="text-base font-medium text-gray-900 hover:text-primary-600">
                                Rozpis zápasů
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('public.team.index') }}" class="text-base font-medium text-gray-900 hover:text-primary-600">
                                Naše týmy
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('public.trainings.index') }}" class="text-base font-medium text-gray-900 hover:text-primary-600">
                                Tréninkové info
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
