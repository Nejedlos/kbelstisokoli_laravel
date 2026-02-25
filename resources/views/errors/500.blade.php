@extends('errors.layout')

@section('title', __('errors.500.title'))
@section('code', '500')
@section('headline', __('errors.500.title'))
@section('message', __('errors.500.message'))
@section('tagline', __('errors.500.tagline'))

@section('actions')
    <button onclick="window.location.reload()" class="btn btn-primary px-8 text-white">
        {{ __('errors.try_again') }}
    </button>
    <a href="{{ route('public.home') }}" class="btn btn-outline px-8">
        {{ __('errors.back_to_home') }}
    </a>
@endsection
