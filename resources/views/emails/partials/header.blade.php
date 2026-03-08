<table align="center" width="600" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto; padding: 0; width: 600px; text-align: center;">
    <tr>
        <td>
            <a href="{{ config('email_branding.brand_url') }}" style="display: inline-block; text-decoration: none;">
                <img src="{{ config('email_branding.logo_url') }}"
                     width="{{ config('email_branding.logo_width') }}"
                     alt="{{ config('email_branding.logo_alt') }}"
                     style="border: none; display: block; margin: 0 auto; max-width: 100%;">
                <span style="display: block; font-size: 18px; font-weight: bold; color: {{ config('email_branding.colors.secondary') }}; margin-top: 10px; font-family: Arial, Helvetica, sans-serif;">
                    {{ config('email_branding.brand_name') }}
                </span>
            </a>
            <hr style="border: none; border-top: 1px solid {{ config('email_branding.colors.border') }}; margin: 25px auto 0 auto; width: 50px;">
        </td>
    </tr>
</table>
