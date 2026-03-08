<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 25px; margin-bottom: 25px; border-collapse: collapse;">
    @if(isset($header))
    <tr>
        @foreach($header as $h)
        <th style="padding: 12px 8px; border-bottom: 2px solid {{ config('email_branding.colors.border') }}; font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: {{ config('email_branding.colors.muted') }}; text-align: left; text-transform: uppercase; letter-spacing: 0.05em;">
            {{ $h }}
        </th>
        @endforeach
    </tr>
    @endif
    @foreach($rows as $row)
    <tr>
        @foreach($row as $cell)
        <td style="padding: 12px 8px; border-bottom: 1px solid {{ config('email_branding.colors.border') }}; font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: {{ config('email_branding.colors.text') }};">
            {!! $cell !!}
        </td>
        @endforeach
    </tr>
    @endforeach
</table>
