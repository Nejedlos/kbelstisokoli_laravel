<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <h2 style="color: #c41e3a;">Zpráva z kontaktního formuláře</h2>

    <p><strong>Od:</strong> {{ $senderName }} (<a href="mailto:{{ $senderEmail }}">{{ $senderEmail }}</a>)</p>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

    <div style="white-space: pre-wrap;">{{ $messageBody }}</div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="font-size: 0.85em; color: #777;">Tato zpráva byla odeslána z webu Kbelští sokoli.</p>
</body>
</html>
