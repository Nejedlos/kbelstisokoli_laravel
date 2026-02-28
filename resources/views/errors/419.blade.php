@extends('errors.layout')

@section('title', __('errors.419.title'))
@section('code', '419')
@section('headline', __('errors.419.title'))
@section('message', __('errors.419.message'))
@section('tagline', __('errors.419.tagline'))

@section('actions')
    <button onclick="window.location.reload()" class="btn btn-primary px-8 text-white">
        {{ __('errors.419.button') }}
    </button>
    <a href="{{ url('/') }}" class="btn btn-outline px-8">
        {{ __('errors.back_to_home') }}
    </a>
@endsection
