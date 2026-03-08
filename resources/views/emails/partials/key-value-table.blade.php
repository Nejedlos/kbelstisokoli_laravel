<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 25px; margin-bottom: 25px; border-collapse: collapse;">
    @foreach($items as $label => $value)
    <tr>
        <td style="padding: 8px 0; border-bottom: 1px solid {{ config('email_branding.colors.border') }}; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: {{ config('email_branding.colors.muted') }}; width: 35%; vertical-align: top;">
            {{ $label }}
        </td>
        <td style="padding: 8px 0; border-bottom: 1px solid {{ config('email_branding.colors.border') }}; font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: {{ config('email_branding.colors.text') }}; font-weight: bold; vertical-align: top;">
            {{ $value }}
        </td>
    </tr>
    @endforeach
</table>
