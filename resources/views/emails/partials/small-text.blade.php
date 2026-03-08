<p style="font-size: 12px; color: {{ config('email_branding.colors.muted') }}; font-family: Arial, Helvetica, sans-serif; line-height: 1.5; margin-top: 20px; margin-bottom: 0;">
    {{ $slot ?? ($text ?? '') }}
</p>
