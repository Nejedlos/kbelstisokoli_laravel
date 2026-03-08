@extends('errors.layout')

@section('title', __('errors.shot_clock.title'))
@section('code', '24s')
@section('headline', __('errors.shot_clock.headline'))
@section('message', __('errors.shot_clock.message'))
@section('tagline', __('errors.shot_clock.tagline'))

@section('actions')
    <a href="{{ route('filament.admin.auth.login') }}" class="btn btn-primary px-8 text-white">
        <i class="fa-light fa-door-open mr-2"></i>
        {{ __('errors.shot_clock.login') }}
    </a>
    <a href="{{ url('/') }}" class="btn btn-outline px-8">
        {{ __('errors.back_to_home') }}
    </a>
@endsection

@section('extra')
    <div class="mt-12 flex justify-center">
        <div class="relative w-32 h-32">
             <div class="absolute inset-0 bg-primary/20 rounded-full animate-ping"></div>
             <div class="relative bg-white rounded-full p-4 shadow-xl border-4 border-primary flex items-center justify-center">
                 <i class="fa-light fa-basketball text-primary text-5xl animate-bounce"></i>
             </div>
        </div>
    </div>
@endsection
