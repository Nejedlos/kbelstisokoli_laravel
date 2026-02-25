@extends('errors.layout')

@section('title', __('errors.404.title'))
@section('code', '404')
@section('headline', __('errors.404.title'))
@section('message', __('errors.404.message'))
@section('tagline', __('errors.404.tagline'))

@section('actions')
    <a href="{{ route('public.home') }}" class="btn btn-primary px-8 text-white">
        {{ __('errors.back_to_home') }}
    </a>
    <button onclick="window.history.back()" class="btn btn-outline px-8">
        {{ __('errors.back_to_previous') }}
    </button>
@endsection
