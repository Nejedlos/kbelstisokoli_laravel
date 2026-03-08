<table align="center" width="600" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto; padding: 0; width: 600px; text-align: center;">
    <tr>
        <td style="color: {{ config('email_branding.colors.muted') }}; font-size: 12px; font-family: Arial, Helvetica, sans-serif; padding: 0 20px;">
            <p style="margin-bottom: 10px;">{{ config('email_branding.footer_note') }}</p>
            <p style="margin-bottom: 20px;">
                <a href="{{ config('email_branding.brand_url') }}" style="color: {{ config('email_branding.colors.primary') }}; text-decoration: underline;">
                    {{ str_replace(['https://', 'http://'], '', config('email_branding.brand_url')) }}
                </a>
            </p>
            <p style="margin-bottom: 0;">{{ config('email_branding.copyright') }}</p>
        </td>
    </tr>
</table>
