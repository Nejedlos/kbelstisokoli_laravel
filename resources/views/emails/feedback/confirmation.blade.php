<div style="font-family:ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, Noto Sans, 'Apple Color Emoji','Segoe UI Emoji';">
    <h2 style="margin:0 0 10px 0; font-size:18px;">{{ $type === 'coach' ? __('mail.feedback.confirm_coach_title') : __('mail.feedback.confirm_admin_title') }}</h2>

    @if($team)
        <p style="margin:0 0 8px 0; font-size:14px; color:#334155;">
            {{ __('mail.feedback.team') }}: <strong>{{ $team->name }}</strong>
        </p>
    @endif

    <p style="margin:0 0 8px 0; font-size:14px; color:#334155; white-space:pre-line;">{{ __('mail.feedback.confirm_body') }}</p>

    <div style="margin-top:12px; padding:10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
        <div style="font-size:12px; color:#64748b;">{{ __('mail.feedback.your_message') }}:</div>
        <div style="font-size:14px; color:#334155; white-space:pre-line;">{{ $bodyMessage }}</div>
    </div>
</div>
