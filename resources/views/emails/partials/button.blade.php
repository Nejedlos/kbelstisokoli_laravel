<table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation" style="margin-top: 25px; margin-bottom: 25px;">
    <tr>
        <td align="{{ $align ?? 'center' }}">
            <table border="0" cellspacing="0" cellpadding="0" role="presentation">
                <tr>
                    <td align="center" bgcolor="{{ $color ?? config('email_branding.colors.primary') }}" style="border-radius: 6px;">
                        <a href="{{ $url }}" target="_blank" style="font-size: 16px; font-family: Arial, Helvetica, sans-serif; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; border: 1px solid {{ $color ?? config('email_branding.colors.primary') }}; display: inline-block; font-weight: bold;">
                            {{ $text ?? ($slot ?? '') }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
