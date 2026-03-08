<x-mail::message>
# Nový feedback od {{ $report->user->name }}

**Typ:** {{ ucfirst($report->type) }}
**Závažnost:** {{ ucfirst($report->severity ?? 'N/A') }}
**Oblast:** {{ ucfirst($report->source_area) }}

## {{ $report->title }}

{{ $report->description }}

@if($report->steps)
### Kroky k reprodukci
{{ $report->steps }}
@endif

---

**URL:** {{ $report->url }}
**Uživatel:** {{ $report->user->email }} (ID: {{ $report->user_id }})
**App Version:** {{ $report->app_version }}
**Browser:** {{ $report->user_agent }}

<x-mail::button :url="config('app.url') . '/admin/feedback-reports/' . $report->id">
Zobrazit v administraci
</x-mail::button>

Díky,<br>
{{ config('app.name') }}
</x-mail::message>
