@extends('errors.layout')

@section('title', __('errors.403.title'))
@section('code', '403')
@section('headline', __('errors.403.title'))
@section('message', __('errors.403.message'))
@section('tagline', __('errors.403.tagline'))

@section('actions')
    @guest
        <a href="{{ url('/login') }}" class="btn btn-primary px-8 text-white">
            {{ __('errors.403.login') }}
        </a>
    @else
        <a href="{{ url('/') }}" class="btn btn-primary px-8 text-white">
            {{ __('errors.back_to_home') }}
        </a>
    @endguest
    <button onclick="window.history.back()" class="btn btn-outline px-8">
        {{ __('errors.back_to_previous') }}
    </button>
@endsection
