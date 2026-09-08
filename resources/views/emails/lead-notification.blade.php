@extends('emails.layouts.base')

@section('title', $assignmentNotice ? __('leads.mail.assigned_title') : __('leads.mail.new_title'))

@section('content')
    <h1 style="margin-top:0;color:{{ config('email_branding.colors.secondary') }};font-size:22px;font-weight:bold">
        {{ $assignmentNotice ? __('leads.mail.assigned_title') : __('leads.mail.new_title') }}
    </h1>

    @include('emails.partials.key-value-table', ['items' => [
        __('leads.fields.type') => __('leads.types.' . $lead->type),
        __('leads.fields.name') => $lead->name,
        __('leads.fields.email') => $lead->email,
        __('leads.fields.phone') => $lead->phone ?: __('leads.not_provided'),
        __('leads.fields.subject') => $lead->subject ?: __('leads.not_provided'),
        __('leads.fields.team') => data_get($lead->payload, 'team_name') ?: __('leads.not_provided'),
        __('leads.fields.birth_year') => data_get($lead->payload, 'birth_year') ?: __('leads.not_provided'),
        __('leads.fields.age') => data_get($lead->payload, 'age') ?: __('leads.not_provided'),
        __('leads.fields.position') => data_get($lead->payload, 'position') ?: __('leads.not_provided'),
        __('leads.fields.level') => data_get($lead->payload, 'level') ?: __('leads.not_provided'),
    ]])

    @if($lead->message)
        @include('emails.partials.divider')
        <div style="white-space:pre-wrap;margin-top:20px">{!! nl2br(e($lead->message)) !!}</div>
    @endif

    @include('emails.partials.divider')
    <a href="{{ config('app.url') }}/admin/leads?tableAction=edit&tableActionRecord={{ $lead->id }}" style="display:inline-block;padding:10px 20px;background-color:{{ config('email_branding.colors.primary') }};color:#fff;text-decoration:none;border-radius:8px;font-weight:bold">
        {{ __('leads.mail.open_lead') }}
    </a>
@endsection
