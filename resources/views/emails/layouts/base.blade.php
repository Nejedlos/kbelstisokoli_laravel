<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>@yield('title', config('email_branding.brand_name'))</title>
    <style type="text/css" rel="stylesheet" media="all">
        /* Base styles */
        body {
            width: 100% !important;
            height: 100%;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            background-color: {{ config('email_branding.colors.bg_body') }};
            color: {{ config('email_branding.colors.text') }};
            font-family: Arial, Helvetica, sans-serif;
        }

        /* Mobile overrides */
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }
            .footer {
                width: 100% !important;
            }
            .content-cell {
                padding: 25px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: {{ config('email_branding.colors.bg_body') }}; color: {{ config('email_branding.colors.text') }}; font-family: Arial, Helvetica, sans-serif; line-height: 1.5;">

    <!-- Preheader -->
    <span style="display: none !important; visibility: hidden; opacity: 0; color: transparent; height: 0; width: 0; max-height: 0; max-width: 0; font-size: 1px; line-height: 1px;">
        @yield('preheader')
    </span>

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: {{ config('email_branding.colors.bg_body') }}; margin: 0; padding: 0; width: 100%;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0; padding: 0; width: 100%;">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 30px 0; text-align: center;">
                            @include('emails.partials.header')
                        </td>
                    </tr>

                    <!-- Email Body -->
                    <tr>
                        <td width="100%" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0; width: 100%;">
                            <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0" role="presentation" style="background-color: {{ config('email_branding.colors.bg_content') }}; margin: 0 auto; padding: 0; width: 600px; border: 1px solid {{ config('email_branding.colors.border') }}; border-radius: 8px;">
                                <!-- Body Content -->
                                <tr>
                                    <td class="content-cell" style="padding: 40px;">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 0; text-align: center;">
                            @include('emails.partials.footer')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
