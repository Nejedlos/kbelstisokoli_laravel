<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 25px; margin-bottom: 25px;">
    <tr>
        <td style="background-color: {{ config('email_branding.colors.bg_body') }}; border-left: 4px solid {{ config('email_branding.colors.primary') }}; padding: 20px; border-radius: 0 4px 4px 0;">
            <div style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: {{ config('email_branding.colors.text') }}; line-height: 1.5;">
                {!! $slot ?? ($text ?? '') !!}
            </div>
        </td>
    </tr>
</table>
