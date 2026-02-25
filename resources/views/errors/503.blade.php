@extends('errors.layout')

@section('title', __('errors.503.title'))
@section('code', '503')
@section('headline', __('errors.503.title'))
@section('message', __('errors.503.message'))
@section('tagline', __('errors.503.tagline'))

@section('actions')
    <button onclick="window.location.reload()" class="btn btn-primary px-8 text-white">
        {{ __('errors.try_again') }}
    </button>
@endsection
