@props(['date'])

@if($date)
    {{ __('general.weekdays_short.' . $date->dayOfWeekIso) }}
@endif
