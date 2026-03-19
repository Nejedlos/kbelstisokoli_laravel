<?php use Illuminate\Support\Facades\Route; use Illuminate\Support\Facades\Blade; Route::get('/test-loader', function () { return Blade::render('<x-loader.global title="Test" />'); });
